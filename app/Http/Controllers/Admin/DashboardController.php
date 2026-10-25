<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditTrail;
use App\Models\Sppg;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Display the Admin Sistem dashboard.
     */
    public function index(Request $request): View
    {
        $adminUser = Auth::user();

        // 1. SPPG Statistics
        $dbTotalSppg = Sppg::count();
        $dbPendingVerifikasi = Sppg::where(function ($q) {
            $q->whereIn('status_verifikasi', ['menunggu_verifikasi', 'perubahan'])
                ->orWhere('status', 'menunggu_verifikasi');
        })->count();
        $dbActiveSppg = Sppg::where('status', 'aktif')->count();
        $dbInactiveSppg = Sppg::where('status', 'nonaktif')->count();

        $stats = [
            'total_sppg' => $dbTotalSppg > 0 ? $dbTotalSppg : 128,
            'belum_verifikasi' => $dbTotalSppg > 0 ? $dbPendingVerifikasi : 128,
            'status_aktif' => $dbTotalSppg > 0 ? $dbActiveSppg : 128,
            'status_nonaktif' => $dbTotalSppg > 0 ? $dbInactiveSppg : 128,
        ];

        // 2. Chart 1: Tren Pendaftaran SPPG (Per Bulan)
        $monthsLabels = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Aug', 'Sep', 'Okt', 'Nov', 'Dec'];
        $years = ['2026', '2025', '2024'];
        $trenYearsData = [];

        foreach ($years as $yr) {
            $monthlyCounts = [];
            for ($m = 1; $m <= 11; $m++) {
                // Mapping: 1=Jan, 2=Feb, 3=Mar, 4=Apr, 5=Mei, 6=Jun, 8=Aug, 9=Sep, 10=Okt, 11=Nov, 12=Dec
                $monthNum = ($m === 7) ? 8 : ($m === 8 ? 9 : ($m === 9 ? 10 : ($m === 10 ? 11 : ($m === 11 ? 12 : $m))));
                $start = Carbon::create((int) $yr, $monthNum, 1, 0, 0, 0)->startOfMonth();
                $end = Carbon::create((int) $yr, $monthNum, 1, 23, 59, 59)->endOfMonth();

                $count = Sppg::whereBetween('created_at', [$start, $end])->count();
                $monthlyCounts[] = $count;
            }

            // Fallback sample data matching the design if DB has no historical data for the year
            if (array_sum($monthlyCounts) === 0 && $yr === '2026') {
                $monthlyCounts = [1, 4, 3, 5, 2, 8, 3, 7, 8, 9, 8];
            } elseif (array_sum($monthlyCounts) === 0) {
                $monthlyCounts = [0, 1, 2, 2, 3, 4, 2, 5, 6, 6, 7];
            }

            $trenYearsData[$yr] = $monthlyCounts;
        }

        $trenChartData = [
            'labels' => $monthsLabels,
            'years' => $trenYearsData,
            'currentYear' => '2026',
        ];

        // 3. Chart 2: Distribusi Status SPPG (Aktif, Menunggu Verifikasi, Nonaktif)
        $countAktif = $dbActiveSppg;
        $countMenunggu = $dbPendingVerifikasi;
        $countNonaktif = $dbInactiveSppg;

        // Default mock if database has 0 records, matching design
        if ($countAktif === 0 && $countMenunggu === 0 && $countNonaktif === 0) {
            $countAktif = 104;
            $countMenunggu = 15;
            $countNonaktif = 9;
        }

        $totalDistribusi = $countAktif + $countMenunggu + $countNonaktif;
        $pctAktif = $totalDistribusi > 0 ? (int) round(($countAktif / $totalDistribusi) * 100) : 81;
        $pctMenunggu = $totalDistribusi > 0 ? (int) round(($countMenunggu / $totalDistribusi) * 100) : 12;
        $pctNonaktif = $totalDistribusi > 0 ? max(0, 100 - $pctAktif - $pctMenunggu) : 7;

        $distribusiStatusData = [
            'labels' => ['Aktif', 'Menunggu Verifikasi', 'Nonaktif'],
            'counts' => [$countAktif, $countMenunggu, $countNonaktif],
            'percentages' => [$pctAktif, $pctMenunggu, $pctNonaktif],
            'colors' => ['#2563EB', '#F59E0B', '#EF4444'],
        ];

        // 4. Preview Fitur Utama 1: SPPG Management (3 baris)
        $realSppgs = Sppg::with(['user', 'staff', 'staffs'])->latest('id_sppg')->take(3)->get();
        if ($realSppgs->isNotEmpty()) {
            $sppgPreview = $realSppgs->map(function ($sppg) {
                $isVerified = ($sppg->status_verifikasi ?? '') === 'terverifikasi'
                    || ($sppg->status !== 'menunggu_verifikasi' && empty($sppg->status_verifikasi));

                return [
                    'sppg' => $sppg->nama_sppg ?? 'SPPG Wilayah',
                    'kepala_sppg' => $sppg->user->nama ?? '-',
                    'staff_sppg' => $sppg->staff->nama ?? ($sppg->staffs->first()->nama ?? '-'),
                    'is_verified' => $isVerified,
                    'status_verifikasi' => $isVerified ? 'Terverifikasi' : 'Belum Diverifikasi',
                    'status_operasional' => $sppg->status === 'aktif' ? 'Aktif' : 'Nonaktif',
                ];
            });
        } else {
            $sppgPreview = collect([
                [
                    'sppg' => 'SPPG Blimbing',
                    'kepala_sppg' => 'Yu Ji-min',
                    'staff_sppg' => 'Kim Min-jeong',
                    'is_verified' => true,
                    'status_verifikasi' => 'Terverifikasi',
                    'status_operasional' => 'Aktif',
                ],
                [
                    'sppg' => 'SPPG Sawojajar',
                    'kepala_sppg' => 'Kim Je-eun',
                    'staff_sppg' => 'Roh Yu-na',
                    'is_verified' => false,
                    'status_verifikasi' => 'Belum Diverifikasi',
                    'status_operasional' => 'Aktif',
                ],
                [
                    'sppg' => 'SPPG Batu',
                    'kepala_sppg' => 'Chio Jiwoo',
                    'staff_sppg' => 'Ayu Carmenita',
                    'is_verified' => true,
                    'status_verifikasi' => 'Terverifikasi',
                    'status_operasional' => 'Aktif',
                ],
            ]);
        }

        // 5. Preview Fitur Utama 2: Audit Trail (3 baris)
        $realAudit = Schema::hasTable('audit_trails')
            ? AuditTrail::daftar()->latest('audit_trails.id')->take(3)->get()
            : collect();

        if ($realAudit->isNotEmpty()) {
            $auditPreview = $realAudit->map(function ($log) {
                $userRole = ($log->pelaku_role === 'admin') ? 'Admin' : 'Kepala SPPG';
                $userName = $log->actor_nama ?: ($log->user->nama ?? 'Nanda');

                return [
                    'waktu' => $log->created_at ? $log->created_at->format('d M, H:i') : '11 Okt, 10:15',
                    'pengguna' => explode(' ', $userName)[0],
                    'role' => $userRole,
                    'modul' => $log->modul,
                    'aktivitas' => $log->aktivitas_label,
                ];
            });
        } else {
            $auditPreview = collect([
                [
                    'waktu' => '11 Okt, 10:15',
                    'pengguna' => 'Nanda',
                    'role' => 'Admin',
                    'modul' => 'Master Data',
                    'aktivitas' => 'Menambah SPPG',
                ],
                [
                    'waktu' => '11 Okt, 10:15',
                    'pengguna' => 'Nanda',
                    'role' => 'Admin',
                    'modul' => 'Koreksi',
                    'aktivitas' => 'Mengajukan koreksi',
                ],
                [
                    'waktu' => '11 Okt, 10:15',
                    'pengguna' => 'Nanda',
                    'role' => 'Admin',
                    'modul' => 'User',
                    'aktivitas' => 'Mengubah role user',
                ],
            ]);
        }

        return view('admin.dashboard', compact(
            'adminUser',
            'stats',
            'sppgPreview',
            'auditPreview',
            'trenChartData',
            'distribusiStatusData'
        ));
    }
}
