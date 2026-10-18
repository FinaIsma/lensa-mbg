<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditTrail;
use App\Models\CorrectionRequest;
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
        $dbPendingVerifikasi = Sppg::where('status', 'menunggu_verifikasi')->count();
        $dbActiveSppg = Sppg::where('status', 'aktif')->count();
        $dbInactiveSppg = Sppg::where('status', 'nonaktif')->count();

        $stats = [
            'total_sppg' => $dbTotalSppg > 0 ? $dbTotalSppg : 128,
            'belum_verifikasi' => $dbTotalSppg > 0 ? $dbPendingVerifikasi : 128,
            'status_aktif' => $dbTotalSppg > 0 ? $dbActiveSppg : 128,
            'status_nonaktif' => $dbTotalSppg > 0 ? $dbInactiveSppg : 128,
        ];

        // 2. Preview Fitur Utama 1: User Management (3 baris sesuai desain awal)
        $realUsers = Sppg::with('user')->latest()->take(3)->get();
        if ($realUsers->isNotEmpty()) {
            $userPreview = $realUsers->map(function ($sppg) {
                return [
                    'nama' => $sppg->user->nama ?? $sppg->nama_pegawai ?? 'Pengguna SPPG',
                    'sppg' => $sppg->nama_sppg ?? 'SPPG Wilayah',
                    'is_verified' => $sppg->status !== 'menunggu_verifikasi',
                    'is_active' => $sppg->status === 'aktif',
                    'status_verifikasi' => $sppg->status === 'menunggu_verifikasi' ? 'Belum Diverifikasi' : 'Terverifikasi',
                    'status_akun' => $sppg->status === 'aktif' ? 'Aktif' : ($sppg->status === 'nonaktif' ? 'Nonaktif' : 'Menunggu'),
                ];
            });
        } else {
            $userPreview = collect([
                [
                    'nama' => 'Yu Ji-min',
                    'sppg' => 'SPPG Sawojajar',
                    'is_verified' => true,
                    'is_active' => true,
                    'status_verifikasi' => 'Terverifikasi',
                    'status_akun' => 'Aktif',
                ],
                [
                    'nama' => 'Kim Je-eun',
                    'sppg' => 'SPPG Batu',
                    'is_verified' => false,
                    'is_active' => true,
                    'status_verifikasi' => 'Belum Diverifikasi',
                    'status_akun' => 'Aktif',
                ],
                [
                    'nama' => 'Chio Jiwoo',
                    'sppg' => 'SPPG Sulfat',
                    'is_verified' => true,
                    'is_active' => true,
                    'status_verifikasi' => 'Terverifikasi',
                    'status_akun' => 'Aktif',
                ],
            ]);
        }

        // 3. Preview Fitur Utama 2: Correction Request (5 baris)
        $realCr = Schema::hasTable('correction_requests')
            ? CorrectionRequest::latest()->take(5)->get()
            : collect();

        if ($realCr->isNotEmpty()) {
            $correctionPreview = $realCr->map(function ($cr) {
                return [
                    'id' => $cr->request_no ?? 'CR-'.str_pad((string) $cr->id, 3, '0', STR_PAD_LEFT),
                    'jenis_data' => $cr->jenis_data,
                    'status' => $cr->status_label,
                ];
            });
        } else {
            $correctionPreview = collect([
                ['id' => 'CR-001', 'jenis_data' => 'Distribusi', 'status' => 'Menunggu'],
                ['id' => 'CR-002', 'jenis_data' => 'Produksi', 'status' => 'Disetujui'],
                ['id' => 'CR-003', 'jenis_data' => 'Penerimaan', 'status' => 'Ditolak'],
                ['id' => 'CR-004', 'jenis_data' => 'Produksi', 'status' => 'Disetujui'],
                ['id' => 'CR-005', 'jenis_data' => 'Produksi', 'status' => 'Disetujui'],
            ]);
        }

        // 4. Preview Fitur Utama 3: Audit Trail (3 baris)
        $realAudit = Schema::hasTable('audit_trails')
            ? AuditTrail::daftar()->latest('audit_trails.id')->take(3)->get()
            : collect();

        if ($realAudit->isNotEmpty()) {
            $auditPreview = $realAudit->map(function ($log) {
                $userRole = ($log->pelaku_role === 'admin') ? 'Admin' : 'Kepala SPPG';
                $userName = $log->actor_nama ?: ($log->user->nama ?? 'Admin');

                return [
                    'waktu' => $log->created_at ? $log->created_at->format('d M, H:i') : '11 Okt, 10:15',
                    'pengguna' => "{$userName} ({$userRole})",
                    'modul' => $log->modul,
                    'aktivitas' => $log->aktivitas_label,
                ];
            });
        } else {
            $auditPreview = collect([
                [
                    'waktu' => '11 Okt, 10:15',
                    'pengguna' => 'Nanda (Admin)',
                    'modul' => 'Master Data',
                    'aktivitas' => 'Menambah SPPG',
                ],
                [
                    'waktu' => '11 Okt, 10:15',
                    'pengguna' => 'Nanda (Admin)',
                    'modul' => 'Koreksi',
                    'aktivitas' => 'Mengajukan koreksi',
                ],
                [
                    'waktu' => '11 Okt, 10:15',
                    'pengguna' => 'Nanda (Admin)',
                    'modul' => 'User',
                    'aktivitas' => 'Mengubah role user',
                ],
            ]);
        }

        // 5. Chart 1: Tren Pendaftaran SPPG (Dinamis dari database SPPG dengan dukungan filter Periode)
        $allSppg = Sppg::all();

        // 5a. Periode: Bulan Berjalan (Oktober 2026)
        $currentLabels = ['1 Okt', '4 Okt', '7 Okt', '10 Okt', '13 Okt', '16 Okt', '19 Okt', '22 Okt', '25 Okt', '28 Okt', '31 Okt'];
        $currentRanges = [
            ['2026-10-01 00:00:00', '2026-10-03 23:59:59'],
            ['2026-10-04 00:00:00', '2026-10-06 23:59:59'],
            ['2026-10-07 00:00:00', '2026-10-09 23:59:59'],
            ['2026-10-10 00:00:00', '2026-10-12 23:59:59'],
            ['2026-10-13 00:00:00', '2026-10-15 23:59:59'],
            ['2026-10-16 00:00:00', '2026-10-18 23:59:59'],
            ['2026-10-19 00:00:00', '2026-10-21 23:59:59'],
            ['2026-10-22 00:00:00', '2026-10-24 23:59:59'],
            ['2026-10-25 00:00:00', '2026-10-27 23:59:59'],
            ['2026-10-28 00:00:00', '2026-10-30 23:59:59'],
            ['2026-10-31 00:00:00', '2026-10-31 23:59:59'],
        ];

        $currentDisetujui = [];
        $currentMenunggu = [];
        $currentDitolak = [];
        $currentKoreksi = [];

        foreach ($currentRanges as $range) {
            $start = Carbon::parse($range[0]);
            $end = Carbon::parse($range[1]);

            $bucket = $allSppg->filter(function ($s) use ($start, $end) {
                return $s->created_at && Carbon::parse($s->created_at)->betweenIncluded($start, $end);
            });

            $currentDisetujui[] = $bucket->where('status', 'aktif')->count();
            $currentMenunggu[] = $bucket->where('status', 'menunggu_verifikasi')->count();
            $currentDitolak[] = $bucket->where('status', 'nonaktif')->count();
            $currentKoreksi[] = 0;
        }

        // 5b. Periode: Bulan Lalu (September 2026)
        $lastMonthLabels = ['1 Sep', '4 Sep', '7 Sep', '10 Sep', '13 Sep', '16 Sep', '19 Sep', '22 Sep', '25 Sep', '28 Sep', '30 Sep'];
        $lastMonthRanges = [
            ['2026-09-01 00:00:00', '2026-09-03 23:59:59'],
            ['2026-09-04 00:00:00', '2026-09-06 23:59:59'],
            ['2026-09-07 00:00:00', '2026-09-09 23:59:59'],
            ['2026-09-10 00:00:00', '2026-09-12 23:59:59'],
            ['2026-09-13 00:00:00', '2026-09-15 23:59:59'],
            ['2026-09-16 00:00:00', '2026-09-18 23:59:59'],
            ['2026-09-19 00:00:00', '2026-09-21 23:59:59'],
            ['2026-09-22 00:00:00', '2026-09-24 23:59:59'],
            ['2026-09-25 00:00:00', '2026-09-27 23:59:59'],
            ['2026-09-28 00:00:00', '2026-09-30 23:59:59'],
        ];

        $lastMonthDisetujui = [];
        $lastMonthMenunggu = [];
        $lastMonthDitolak = [];
        $lastMonthKoreksi = [];

        foreach ($lastMonthRanges as $range) {
            $start = Carbon::parse($range[0]);
            $end = Carbon::parse($range[1]);

            $bucket = $allSppg->filter(function ($s) use ($start, $end) {
                return $s->created_at && Carbon::parse($s->created_at)->betweenIncluded($start, $end);
            });

            $lastMonthDisetujui[] = $bucket->where('status', 'aktif')->count();
            $lastMonthMenunggu[] = $bucket->where('status', 'menunggu_verifikasi')->count();
            $lastMonthDitolak[] = $bucket->where('status', 'nonaktif')->count();
            $lastMonthKoreksi[] = 0;
        }

        // 5c. Periode: Tahun 2026 (Januari - Desember)
        $yearLabels = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
        $yearDisetujui = [];
        $yearMenunggu = [];
        $yearDitolak = [];
        $yearKoreksi = [];

        for ($m = 1; $m <= 12; $m++) {
            $start = Carbon::create(2026, $m, 1, 0, 0, 0)->startOfMonth();
            $end = Carbon::create(2026, $m, 1, 23, 59, 59)->endOfMonth();

            $bucket = $allSppg->filter(function ($s) use ($start, $end) {
                return $s->created_at && Carbon::parse($s->created_at)->betweenIncluded($start, $end);
            });

            $yearDisetujui[] = $bucket->where('status', 'aktif')->count();
            $yearMenunggu[] = $bucket->where('status', 'menunggu_verifikasi')->count();
            $yearDitolak[] = $bucket->where('status', 'nonaktif')->count();
            $yearKoreksi[] = 0;
        }

        $trenChartData = [
            'labels' => $currentLabels,
            'disetujui' => $currentDisetujui,
            'menunggu' => $currentMenunggu,
            'ditolak' => $currentDitolak,
            'koreksi' => $currentKoreksi,
            'periods' => [
                'current' => [
                    'labels' => $currentLabels,
                    'disetujui' => $currentDisetujui,
                    'menunggu' => $currentMenunggu,
                    'ditolak' => $currentDitolak,
                    'koreksi' => $currentKoreksi,
                ],
                'last_month' => [
                    'labels' => $lastMonthLabels,
                    'disetujui' => $lastMonthDisetujui,
                    'menunggu' => $lastMonthMenunggu,
                    'ditolak' => $lastMonthDitolak,
                    'koreksi' => $lastMonthKoreksi,
                ],
                'year' => [
                    'labels' => $yearLabels,
                    'disetujui' => $yearDisetujui,
                    'menunggu' => $yearMenunggu,
                    'ditolak' => $yearDitolak,
                    'koreksi' => $yearKoreksi,
                ],
            ],
        ];

        // 6. Chart 2: Status Pengajuan Koreksi (100% Dinamis dari Tabel correction_requests dengan dukungan Periode dan SPPG)
        $sppgList = Sppg::select('id_sppg', 'nama_sppg')->distinct()->orderBy('nama_sppg')->get();

        $allCrRecords = Schema::hasTable('correction_requests')
            ? CorrectionRequest::all()
            : collect();

        // Hitung data per periode untuk Chart 2
        $periodRanges = [
            'current' => [Carbon::parse('2026-10-01 00:00:00'), Carbon::parse('2026-10-31 23:59:59')],
            'last_month' => [Carbon::parse('2026-09-01 00:00:00'), Carbon::parse('2026-09-30 23:59:59')],
            'year' => [Carbon::parse('2026-01-01 00:00:00'), Carbon::parse('2026-12-31 23:59:59')],
        ];

        $sppgCrPeriodMap = [];

        foreach ($periodRanges as $pKey => $range) {
            [$pStart, $pEnd] = $range;

            $periodCrs = $allCrRecords->filter(function ($cr) use ($pStart, $pEnd) {
                if (! $cr->created_at) {
                    return false;
                }

                return Carbon::parse($cr->created_at)->betweenIncluded($pStart, $pEnd);
            });

            $pMenunggu = $periodCrs->whereIn('status', ['pending', 'waiting', 'Menunggu'])->count();
            $pDisetujui = $periodCrs->whereIn('status', ['approved', 'applied', 'Disetujui'])->count();
            $pDitolak = $periodCrs->whereIn('status', ['rejected', 'Ditolak'])->count();

            $sppgCrPeriodMap[$pKey] = [
                'all' => [$pMenunggu, $pDisetujui, $pDitolak],
            ];

            foreach ($sppgList as $sppgItem) {
                $sCrs = $periodCrs->where('id_sppg', $sppgItem->id_sppg);
                $sppgCrPeriodMap[$pKey][$sppgItem->id_sppg] = [
                    $sCrs->whereIn('status', ['pending', 'waiting', 'Menunggu'])->count(),
                    $sCrs->whereIn('status', ['approved', 'applied', 'Disetujui'])->count(),
                    $sCrs->whereIn('status', ['rejected', 'Ditolak'])->count(),
                ];
            }
        }

        $sppgCrMap = $sppgCrPeriodMap['current'];

        // Status awal
        $koreksiChartData = [
            'labels' => ['Menunggu', 'Disetujui', 'Ditolak'],
            'counts' => $sppgCrPeriodMap['current']['all'],
            'colors' => ['#F59E0B', '#34D399', '#F87171'],
        ];

        return view('admin.dashboard', compact(
            'adminUser',
            'stats',
            'userPreview',
            'correctionPreview',
            'auditPreview',
            'trenChartData',
            'koreksiChartData',
            'sppgList',
            'sppgCrMap',
            'sppgCrPeriodMap'
        ));
    }
}
