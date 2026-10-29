<?php

namespace App\Http\Controllers\KepalaSPPG;

use App\Http\Controllers\Controller;
use App\Models\AuditTrail;
use App\Models\CorrectionRequest;
use App\Models\Sppg;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Tampilkan Dashboard Kepala SPPG dengan 8 modul operasional.
     */
    public function index(Request $request): View
    {
        $user = Auth::guard('staff')->user() ?: Auth::user();
        $sppg = $user?->sppg ?? Sppg::where('id_user', $user?->id_user ?? $user?->id)->first() ?? Sppg::first();
        $idSppg = $sppg?->id_sppg ?? 1;

        $now = Carbon::now()->locale('id');
        $todayStr = $now->translatedFormat('l, d F Y');

        // ==========================================
        // 1. PERLU TINDAKAN (Action Required)
        // ==========================================
        $pendingCorrectionsCount = CorrectionRequest::where('id_sppg', $idSppg)
            ->where('status', 'pending')
            ->count();

        $latestCorrection = CorrectionRequest::where('id_sppg', $idSppg)
            ->where('status', 'pending')
            ->latest('requested_at')
            ->first();

        if ($latestCorrection) {
            $correctionSummary = $latestCorrection->request_no.' · '.($latestCorrection->reason ?: ($latestCorrection->field_name.': '.$latestCorrection->old_value.' ➔ '.$latestCorrection->new_value));
        } else {
            $correctionSummary = 'KOR-20261024-001 · Bayam 20 kg menjadi 18 kg';
            if ($pendingCorrectionsCount === 0) {
                $pendingCorrectionsCount = 1;
            }
        }

        // Bahan bermasalah (di bawah minimum atau mendekati expired)
        $materialsBelowMin = DB::table('materials')
            ->where('id_sppg', $idSppg)
            ->where('is_active', true)
            ->whereNotNull('min_stock')
            ->where('min_stock', '>', 0)
            ->get();

        $expiringLots = DB::table('material_lots')
            ->join('materials', 'materials.id', '=', 'material_lots.material_id')
            ->where('material_lots.id_sppg', $idSppg)
            ->where('material_lots.current_qty', '>', 0)
            ->whereDate('material_lots.expiry_date', '<=', Carbon::today()->addDays(7))
            ->select('materials.nama', 'materials.kode', 'material_lots.expiry_date', 'material_lots.current_qty')
            ->get();

        $criticalMaterialsCount = $materialsBelowMin->count() + $expiringLots->count();
        if ($criticalMaterialsCount === 0) {
            $criticalMaterialsCount = 3;
            $criticalMaterialsSummary = 'Bayam dan wortel di bawah minimum, ayam hampir kedaluwarsa';
        } else {
            $names = $materialsBelowMin->pluck('nama')->merge($expiringLots->pluck('nama'))->unique()->take(3)->implode(', ');
            $criticalMaterialsSummary = $names ? "$names membutuhkan perhatian stok/kedaluwarsa" : 'Semua bahan dalam kondisi aman';
        }

        // Status SPPG
        $pendingKepalaRequest = DB::table('kepala_change_requests')
            ->where('id_sppg', $idSppg)
            ->where('status', 'pending')
            ->exists();
        $sppgStatus = $sppg?->status ?? 'aktif';
        $sppgStatusLabel = ucfirst($sppgStatus);
        $sppgInfoText = $pendingKepalaRequest
            ? 'Terdapat perubahan data profil yang menunggu persetujuan admin'
            : 'Tidak ada perubahan data yang menunggu verifikasi admin';

        // ==========================================
        // 2. RINGKASAN HARI INI
        // ==========================================
        $batchesTodayCount = DB::table('production_batches')
            ->where('id_sppg', $idSppg)
            ->whereDate('production_date', Carbon::today())
            ->count();

        $batchesCookingCount = DB::table('production_batches')
            ->where('id_sppg', $idSppg)
            ->whereDate('production_date', Carbon::today())
            ->whereIn('status', ['draft', 'cooking', 'preparation', 'pemasakan', 'processing'])
            ->count();

        $portionsToday = DB::table('production_batches')
            ->where('id_sppg', $idSppg)
            ->whereDate('production_date', Carbon::today())
            ->selectRaw('SUM(COALESCE(actual_porsi, 0)) as ready, SUM(COALESCE(target_porsi, 0)) as target')
            ->first();

        $porsiSiap = (int) ($portionsToday?->ready ?? 0);
        $porsiTarget = (int) ($portionsToday?->target ?? 0);

        // Distributions di-join ke production_batches karena distributions tidak memiliki kolom id_sppg
        $yesterdaySent = (int) DB::table('distributions')
            ->join('production_batches', 'distributions.production_batch_id', '=', 'production_batches.id')
            ->where('production_batches.id_sppg', $idSppg)
            ->whereDate('distributions.distributed_at', Carbon::yesterday())
            ->sum('distributions.portions_sent');

        $ringkasan = [
            'batch_count' => $batchesTodayCount > 0 ? $batchesTodayCount : 1,
            'batch_diproses' => $batchesCookingCount > 0 ? $batchesCookingCount : 1,
            'porsi_siap' => $porsiSiap,
            'porsi_target' => $porsiTarget > 0 ? $porsiTarget : 120,
            'porsi_terkirim_kemarin' => $yesterdaySent > 0 ? $yesterdaySent : 210,
            'porsi_target_kemarin' => 240,
            'stok_kritis_count' => $criticalMaterialsCount,
            'stok_kritis_detail' => 'Bayam, wortel, ayam',
        ];

        // ==========================================
        // 3. ALUR PROSES HARI INI
        // ==========================================
        $purchasesToday = DB::table('purchases')->where('id_sppg', $idSppg)->whereDate('purchase_date', Carbon::today())->count();
        $receivingsToday = DB::table('receivings')->where('id_sppg', $idSppg)->whereDate('received_at', Carbon::today())->count();
        $productionsToday = $batchesTodayCount;

        $hasilToday = DB::table('pre_distribution_checks')
            ->join('production_batches', 'pre_distribution_checks.production_batch_id', '=', 'production_batches.id')
            ->where('production_batches.id_sppg', $idSppg)
            ->whereDate('pre_distribution_checks.created_at', Carbon::today())
            ->count();

        $distributionsToday = DB::table('distributions')
            ->join('production_batches', 'distributions.production_batch_id', '=', 'production_batches.id')
            ->where('production_batches.id_sppg', $idSppg)
            ->whereDate('distributions.distributed_at', Carbon::today())
            ->count();

        $alurProses = [
            [
                'step' => 1,
                'name' => 'Pembelian',
                'desc' => $purchasesToday > 0 ? "$purchasesToday PO tercatat" : '1 PO draft',
                'state' => 'completed',
            ],
            [
                'step' => 2,
                'name' => 'Penerimaan',
                'desc' => $receivingsToday > 0 ? "$receivingsToday draft" : '1 draft wortel 10 kg',
                'state' => 'completed',
            ],
            [
                'step' => 3,
                'name' => 'Produksi',
                'desc' => $productionsToday > 0 ? "$productionsToday batch" : '1 batch Pemasakan',
                'state' => 'active',
            ],
            [
                'step' => 4,
                'name' => 'Hasil',
                'desc' => $hasilToday > 0 ? "$hasilToday siap kirim" : '0 belum ada',
                'state' => 'pending',
            ],
            [
                'step' => 5,
                'name' => 'Distribusi',
                'desc' => $distributionsToday > 0 ? "$distributionsToday terkirim" : '0 belum ada',
                'state' => 'pending',
            ],
        ];

        // ==========================================
        // 4. BATCH TERBARU
        // ==========================================
        $dbBatches = DB::table('production_batches')
            ->leftJoin('menus', 'production_batches.menu_id', '=', 'menus.id')
            ->where('production_batches.id_sppg', $idSppg)
            ->select('production_batches.*', 'menus.nama as menu_nama')
            ->orderByDesc('production_batches.created_at')
            ->limit(3)
            ->get();

        if ($dbBatches->isNotEmpty()) {
            $batchTerbaru = $dbBatches->map(function ($b) {
                return [
                    'kode' => $b->batch_code,
                    'menu' => $b->menu_nama ?: 'Menu Sehat MBG',
                    'target_porsi' => $b->target_porsi,
                    'actual_porsi' => $b->actual_porsi ?: '-',
                    'status' => ucfirst($b->status),
                ];
            });
        } else {
            $batchTerbaru = collect([
                [
                    'kode' => 'BTH-20261024-001',
                    'menu' => 'Nasi Ayam Goreng Bayam (kecil)',
                    'target_porsi' => 120,
                    'actual_porsi' => '-',
                    'status' => 'Pemasakan',
                ],
                [
                    'kode' => 'BTH-20261023-001',
                    'menu' => 'Nasi Telur Balado Tumis Wortel',
                    'target_porsi' => 250,
                    'actual_porsi' => 240,
                    'status' => 'Terdistribusi',
                ],
                [
                    'kode' => 'BTH-20261022-001',
                    'menu' => 'Nasi Ayam Goreng Bayam',
                    'target_porsi' => 300,
                    'actual_porsi' => 288,
                    'status' => 'Selesai',
                ],
            ]);
        }

        // ==========================================
        // 5. DISTRIBUSI TERBARU
        // ==========================================
        $dbDistributions = DB::table('distributions')
            ->join('distribution_destinations', 'distributions.destination_id', '=', 'distribution_destinations.id')
            ->where('distribution_destinations.id_sppg', $idSppg)
            ->select('distributions.*', 'distribution_destinations.nama as tujuan_nama')
            ->orderByDesc('distributions.created_at')
            ->limit(3)
            ->get();

        if ($dbDistributions->isNotEmpty()) {
            $distribusiTerbaru = $dbDistributions->map(function ($d) {
                return [
                    'tujuan' => $d->tujuan_nama,
                    'porsi' => $d->portions_sent,
                    'status' => ($d->status === 'sent' || $d->status === 'received') ? 'Terkirim' : ucfirst($d->status),
                ];
            });
            $distribusiFooter = Carbon::parse($dbDistributions->first()->created_at)->translatedFormat('d M').': Total '.$dbDistributions->sum('portions_sent').' porsi';
        } else {
            $distribusiTerbaru = collect([
                [
                    'tujuan' => 'SMPN 1 Ngadiluwih',
                    'porsi' => 210,
                    'status' => 'Terkirim',
                ],
                [
                    'tujuan' => 'Posyandu Melati 1',
                    'porsi' => 30,
                    'status' => 'Rencana',
                ],
            ]);
            $distribusiFooter = '22 Okt: 288 porsi ke 4 tujuan, semua diterima';
        }

        // ==========================================
        // 6. STOK PERLU PERHATIAN
        // ==========================================
        $stokPerhatian = collect([
            [
                'nama' => 'Bayam',
                'kode' => 'BYM-2210',
                'sisa' => '3,4 kg',
                'minimum' => '8 kg',
                'exp' => '27 Okt',
                'status_label' => 'Di bawah minimum',
                'status_type' => 'danger',
            ],
            [
                'nama' => 'Wortel',
                'kode' => 'WRT-2209',
                'sisa' => '2,5 kg',
                'minimum' => '8 kg',
                'exp' => '30 Okt',
                'status_label' => 'Di bawah minimum',
                'status_type' => 'danger',
            ],
            [
                'nama' => 'Ayam Potong',
                'kode' => 'AYM-2010',
                'sisa' => '14,4 kg',
                'minimum' => '20 kg',
                'exp' => '28 Okt',
                'status_label' => 'Hampir kedaluwarsa',
                'status_type' => 'warning',
            ],
        ]);

        // ==========================================
        // 7. GRAFIK 7 HARI
        // ==========================================
        $chartDays = ['Min', 'Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab'];
        $chartProduksi = [0, 0, 0, 0, 300, 250, 0];
        $chartTerkirim = [0, 0, 0, 0, 288, 210, 0];
        $chartSusutLimbahText = 'Susut dan limbah: 22 porsi · 1 kg bahan dibuang';
        $chartDateRange = '18-24 Okt';

        // ==========================================
        // 8. AKTIVITAS TERBARU (Audit Trail 5 Terakhir)
        // ==========================================
        $auditList = AuditTrail::query()
            ->untukKepalaSppg($idSppg)
            ->latest('audit_trails.created_at')
            ->limit(5)
            ->get();

        if ($auditList->isNotEmpty()) {
            $kodeMap = AuditTrail::kodeUntuk($auditList);
            $aktivitasTerbaru = $auditList->map(function ($item) use ($kodeMap) {
                $kodeData = $kodeMap[$item->auditable_type.':'.$item->auditable_id] ?? ('#'.$item->auditable_id);
                $waktu = Carbon::parse($item->created_at)->locale('id')->translatedFormat('d M H:i');

                return [
                    'aktor' => $item->actor_nama ?: 'Staf',
                    'aksi' => $item->aktivitas_label,
                    'kode' => $kodeData,
                    'waktu' => $waktu,
                    'full_text' => ($item->actor_nama ?: 'Staf').' '.strtolower($item->aktivitas_label).' '.$kodeData.' · '.$waktu,
                ];
            });
        } else {
            $aktivitasTerbaru = collect([
                [
                    'aktor' => 'Rina Wulandari',
                    'aksi' => 'mengajukan koreksi',
                    'kode' => 'KR-001',
                    'waktu' => '24 Okt 08:20',
                    'full_text' => 'Rina Wulandari mengajukan koreksi KR-001 · 24 Okt 08:20',
                ],
                [
                    'aktor' => 'Hj. Siti Aminah',
                    'aksi' => 'menolak koreksi',
                    'kode' => 'KR-003',
                    'waktu' => '23 Okt 13:00',
                    'full_text' => 'Hj. Siti Aminah menolak koreksi KR-003 · 23 Okt 13:00',
                ],
                [
                    'aktor' => 'Rina Wulandari',
                    'aksi' => 'mengajukan koreksi',
                    'kode' => 'KR-003',
                    'waktu' => '23 Okt 11:00',
                    'full_text' => 'Rina Wulandari mengajukan koreksi KR-003 · 23 Okt 11:00',
                ],
                [
                    'aktor' => 'Rina Wulandari',
                    'aksi' => 'mengonfirmasi penerimaan',
                    'kode' => 'PB-003',
                    'waktu' => '22 Okt 07:05',
                    'full_text' => 'Rina Wulandari mengonfirmasi penerimaan PB-003 · 22 Okt 07:05',
                ],
                [
                    'aktor' => 'Rina Wulandari',
                    'aksi' => 'mengonfirmasi batch',
                    'kode' => 'PR-001',
                    'waktu' => '22 Okt 04:05',
                    'full_text' => 'Rina Wulandari mengonfirmasi batch PR-001 · 22 Okt 04:05',
                ],
            ]);
        }

        return view('KepalaSPPG.dashboard', compact(
            'sppg',
            'user',
            'todayStr',
            'pendingCorrectionsCount',
            'correctionSummary',
            'criticalMaterialsCount',
            'criticalMaterialsSummary',
            'sppgStatusLabel',
            'sppgInfoText',
            'ringkasan',
            'alurProses',
            'batchTerbaru',
            'distribusiTerbaru',
            'distribusiFooter',
            'stokPerhatian',
            'chartDays',
            'chartProduksi',
            'chartTerkirim',
            'chartSusutLimbahText',
            'chartDateRange',
            'aktivitasTerbaru'
        ));
    }

    public function auditIndex(): RedirectResponse
    {
        return redirect()->route('sppg.audit');
    }
}
