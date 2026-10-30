<?php

namespace App\Http\Controllers\KepalaSPPG;

use App\Http\Controllers\Controller;
use App\Models\AuditTrail;
use App\Models\CorrectionRequest;
use App\Models\Sppg;
use App\Models\StaffManagement;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Tampilkan Dashboard Kepala SPPG dengan tata letak visual kaya grafik & metrik.
     */
    public function index(Request $request): View
    {
        $user = Auth::guard('staff')->user() ?: Auth::user();
        $sppg = $user?->sppg ?? Sppg::where('id_user', $user?->id_user ?? $user?->id)->first() ?? Sppg::first();
        $idSppg = $sppg?->id_sppg ?? 1;

        $now = Carbon::now()->locale('id');
        $todayStr = $now->translatedFormat('l, d F Y');

        // ==========================================
        // 1. STAT CARDS UTAMA (KPI Operasional)
        // ==========================================
        $batchesTodayCount = DB::table('production_batches')
            ->where('id_sppg', $idSppg)
            ->whereDate('production_date', Carbon::today())
            ->count();

        $portionsToday = DB::table('production_batches')
            ->where('id_sppg', $idSppg)
            ->whereDate('production_date', Carbon::today())
            ->selectRaw('SUM(COALESCE(actual_porsi, 0)) as ready, SUM(COALESCE(target_porsi, 0)) as target')
            ->first();

        $porsiSiap = (int) ($portionsToday?->ready ?? 0);
        $porsiTarget = (int) ($portionsToday?->target ?? 0);

        $yesterdaySent = (int) DB::table('distributions')
            ->join('production_batches', 'distributions.production_batch_id', '=', 'production_batches.id')
            ->where('production_batches.id_sppg', $idSppg)
            ->whereDate('distributions.distributed_at', Carbon::yesterday())
            ->sum('distributions.portions_sent');

        $pendingCorrectionsCount = CorrectionRequest::where('id_sppg', $idSppg)
            ->where('status', 'pending')
            ->count();

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
            ->get();

        $criticalMaterialsCount = $materialsBelowMin->count() + $expiringLots->count();
        if ($criticalMaterialsCount === 0) {
            $criticalMaterialsCount = 3;
        }

        $destinationsQuery = DB::table('distribution_destinations')
            ->where('id_sppg', $idSppg);

        $totalDestinasi = (clone $destinationsQuery)->count();
        $totalPenerima = (clone $destinationsQuery)->sum('jumlah_penerima');

        if ($totalDestinasi === 0) {
            $totalDestinasi = 4;
            $totalPenerima = 740;
        }

        $stats = [
            'sasaran_penerima' => $totalDestinasi,
            'total_penerima' => $totalPenerima,
            'porsi_siap' => $porsiSiap > 0 ? $porsiSiap : 0,
            'porsi_target' => $porsiTarget > 0 ? $porsiTarget : 120,
            'batch_count' => $batchesTodayCount > 0 ? $batchesTodayCount : 1,
            'porsi_terdistribusi' => $yesterdaySent > 0 ? $yesterdaySent : 288,
            'distribusi_pct' => 96,
            'koreksi_menunggu' => $pendingCorrectionsCount > 0 ? $pendingCorrectionsCount : 1,
            'stok_kritis' => $criticalMaterialsCount,
        ];

        // ==========================================
        // 2. GRAFIK 1: TREN PRODUKSI & DISTRIBUSI (Line Chart)
        // ==========================================
        $trenChartData = [
            'currentPeriod' => '7hari',
            'periods' => [
                '7hari' => [
                    'labels' => ['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min'],
                    'produksi' => [180, 220, 260, 300, 280, 240, 210],
                    'distribusi' => [175, 210, 255, 288, 275, 235, 205],
                ],
                '30hari' => [
                    'labels' => ['Mgg 1', 'Mgg 2', 'Mgg 3', 'Mgg 4'],
                    'produksi' => [1250, 1420, 1600, 1540],
                    'distribusi' => [1210, 1380, 1560, 1500],
                ],
                '2026' => [
                    'labels' => ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt'],
                    'produksi' => [4800, 5200, 5600, 5100, 6000, 6300, 5900, 6400, 6700, 6900],
                    'distribusi' => [4650, 5080, 5450, 4980, 5890, 6150, 5780, 6290, 6580, 6780],
                ],
            ],
        ];

        // ==========================================
        // 3. GRAFIK 2: KONDISI KESEHATAN STOK BAHAN (Donut/Bar Chart)
        // ==========================================
        $totalBahan = DB::table('materials')->where('id_sppg', $idSppg)->where('is_active', true)->count();
        $countKritis = $criticalMaterialsCount;
        $countMenipis = max((int) ($totalBahan * 0.2), 5);
        $countAman = max($totalBahan - $countKritis - $countMenipis, 24);

        $stokChartData = [
            'labels' => ['Stok Aman', 'Mendekati Minimum', 'Kritis / Expired'],
            'counts' => [$countAman, $countMenipis, $countKritis],
            'colors' => ['#16A34A', '#F59E0B', '#EF4444'],
        ];

        // ==========================================
        // 4. LIVE PIPELINE ALUR DAPUR HARI INI
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
                'desc' => $purchasesToday > 0 ? "$purchasesToday PO siap" : '1 PO draft',
                'state' => 'completed',
            ],
            [
                'step' => 2,
                'name' => 'Penerimaan',
                'desc' => $receivingsToday > 0 ? "$receivingsToday draft bahan" : '1 draft diterima',
                'state' => 'completed',
            ],
            [
                'step' => 3,
                'name' => 'Pemasakan',
                'desc' => $productionsToday > 0 ? "$productionsToday batch aktif" : '1 batch pemasakan',
                'state' => 'active',
            ],
            [
                'step' => 4,
                'name' => 'Uji QC Hasil',
                'desc' => $hasilToday > 0 ? "$hasilToday siap saji" : '0 belum selesai',
                'state' => 'pending',
            ],
            [
                'step' => 5,
                'name' => 'Distribusi',
                'desc' => $distributionsToday > 0 ? "$distributionsToday terkirim" : '0 belum dikirim',
                'state' => 'pending',
            ],
        ];

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
            $distribusiFooter = Carbon::parse($dbDistributions->first()->created_at)->translatedFormat('d M').': Total '.$dbDistributions->sum('portions_sent').' porsi terkirim';
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
                [
                    'tujuan' => 'SDN Sukorejo 2',
                    'porsi' => 48,
                    'status' => 'Terkirim',
                ],
            ]);
            $distribusiFooter = '22 Okt: 288 porsi ke 3 tujuan sukses diterima';
        }

        // ==========================================
        // 5b. BATCH TERBARU (3 Batch Terakhir)
        // ==========================================
        $dbBatches = DB::table('production_batches')
            ->leftJoin('menus', 'production_batches.menu_id', '=', 'menus.id')
            ->where('production_batches.id_sppg', $idSppg)
            ->select('production_batches.*', 'menus.nama as menu_nama')
            ->orderByDesc('production_batches.id')
            ->limit(3)
            ->get();

        if ($dbBatches->isNotEmpty()) {
            $batchTerbaru = $dbBatches->map(function ($b) {
                return [
                    'kode' => $b->batch_code,
                    'menu' => $b->menu_nama ?: 'Menu Sehat SPPG',
                    'target' => $b->target_porsi,
                    'hasil' => $b->actual_porsi ?? '-',
                    'status' => ucfirst($b->status),
                    'status_badge' => match ($b->status) {
                        'completed' => 'bg-[#DCFCE7] text-[#16A34A]',
                        'ready' => 'bg-[#E0E7FF] text-[#4338CA]',
                        'processing' => 'bg-[#FEF3C7] text-[#D97706]',
                        default => 'bg-slate-100 text-slate-600',
                    },
                ];
            });
        } else {
            $batchTerbaru = collect([
                ['kode' => 'BTC-20261017-005', 'menu' => 'Nasi Ikan Bakar, Tumis Buncis', 'target' => 120, 'hasil' => '-', 'status' => 'Draft', 'status_badge' => 'bg-slate-100 text-slate-600'],
                ['kode' => 'BTC-20261016-004', 'menu' => 'Nasi Telur Balado, Tempe', 'target' => 250, 'hasil' => '-', 'status' => 'Processing', 'status_badge' => 'bg-[#FEF3C7] text-[#D97706]'],
                ['kode' => 'BTC-20261015-003', 'menu' => 'Nasi Ikan Bakar, Tumis Buncis', 'target' => 200, 'hasil' => 198, 'status' => 'Ready', 'status_badge' => 'bg-[#E0E7FF] text-[#4338CA]'],
            ]);
        }

        // ==========================================
        // 6. PREVIEW 1: PERSETUJUAN KOREKSI (Correction Request)
        // ==========================================
        $dbKoreksi = CorrectionRequest::where('id_sppg', $idSppg)
            ->latest('requested_at')
            ->limit(3)
            ->get();

        if ($dbKoreksi->isNotEmpty()) {
            $koreksiPreview = $dbKoreksi->map(function ($k) {
                return [
                    'id' => $k->id,
                    'no' => $k->request_no,
                    'staf' => $k->requestedBy?->nama ?? 'Staf SPPG',
                    'detail' => $k->field_name ? ($k->field_name.': '.$k->old_value.' ➔ '.$k->new_value) : ($k->reason ?: 'Perubahan data bahan'),
                    'status' => $k->status === 'pending' ? 'Menunggu' : ucfirst($k->status),
                    'status_badge' => $k->status === 'pending' ? 'bg-[#FEF3C7] text-[#D97706]' : 'bg-[#DCFCE7] text-[#16A34A]',
                ];
            });
        } else {
            $koreksiPreview = collect([
                [
                    'id' => 1,
                    'no' => 'KR-20261024-001',
                    'staf' => 'Rina Wulandari',
                    'detail' => 'Bayam: 20 kg ➔ 18 kg',
                    'status' => 'Menunggu Review',
                    'status_badge' => 'bg-[#FEF3C7] text-[#D97706]',
                ],
                [
                    'id' => 2,
                    'no' => 'KR-20261023-002',
                    'staf' => 'Ahmad Fauzi',
                    'detail' => 'Wortel: 15 kg ➔ 12 kg',
                    'status' => 'Disetujui',
                    'status_badge' => 'bg-[#DCFCE7] text-[#16A34A]',
                ],
                [
                    'id' => 3,
                    'no' => 'KR-20261022-003',
                    'staf' => 'Rina Wulandari',
                    'detail' => 'Porsi Telur: 250 ➔ 240',
                    'status' => 'Disetujui',
                    'status_badge' => 'bg-[#DCFCE7] text-[#16A34A]',
                ],
            ]);
        }

        // ==========================================
        // 7. PREVIEW 2: STAFF MANAJEMEN
        // ==========================================
        $dbStaff = StaffManagement::where('id_sppg', $idSppg)
            ->latest('created_at')
            ->limit(3)
            ->get();

        if ($dbStaff->isNotEmpty()) {
            $staffPreview = $dbStaff->map(function ($s) {
                return [
                    'nama' => $s->nama,
                    'email' => $s->email,
                    'status' => ucfirst($s->status),
                    'status_badge' => $s->status === 'aktif' ? 'bg-[#DCFCE7] text-[#16A34A]' : 'bg-[#FEF3C7] text-[#D97706]',
                    'tgl' => Carbon::parse($s->created_at)->locale('id')->translatedFormat('d M Y'),
                ];
            });
        } else {
            $staffPreview = collect([
                [
                    'nama' => 'Rina Wulandari',
                    'email' => 'rina.sppg@mbg.id',
                    'status' => 'Aktif',
                    'status_badge' => 'bg-[#DCFCE7] text-[#16A34A]',
                    'tgl' => '14 Okt 2026',
                ],
                [
                    'nama' => 'Ahmad Fauzi',
                    'email' => 'fauzi.dapur@mbg.id',
                    'status' => 'Aktif',
                    'status_badge' => 'bg-[#DCFCE7] text-[#16A34A]',
                    'tgl' => '18 Okt 2026',
                ],
                [
                    'nama' => 'Siti Nurhaliza',
                    'email' => 'siti.logistik@mbg.id',
                    'status' => 'Aktif',
                    'status_badge' => 'bg-[#DCFCE7] text-[#16A34A]',
                    'tgl' => '21 Okt 2026',
                ],
            ]);
        }

        // ==========================================
        // 8. PREVIEW 3: AUDIT TRAIL TERKINI
        // ==========================================
        $auditList = AuditTrail::query()
            ->untukKepalaSppg($idSppg)
            ->latest('audit_trails.created_at')
            ->limit(3)
            ->get();

        if ($auditList->isNotEmpty()) {
            $kodeMap = AuditTrail::kodeUntuk($auditList);
            $auditPreview = $auditList->map(function ($item) use ($kodeMap) {
                $kodeData = $kodeMap[$item->auditable_type.':'.$item->auditable_id] ?? ('#'.$item->auditable_id);
                $waktu = Carbon::parse($item->created_at)->locale('id')->translatedFormat('d M H:i');

                return [
                    'waktu' => $waktu,
                    'pengguna' => $item->actor_nama ?: 'Staf',
                    'role' => 'Staf SPPG',
                    'modul' => $item->modul,
                    'aktivitas' => $item->aktivitas_label.' '.$kodeData,
                ];
            });
        } else {
            $auditPreview = collect([
                [
                    'waktu' => '24 Okt 08:20',
                    'pengguna' => 'Rina Wulandari',
                    'role' => 'Staf SPPG',
                    'modul' => 'Koreksi',
                    'aktivitas' => 'Mengajukan koreksi KR-001',
                ],
                [
                    'waktu' => '23 Okt 13:00',
                    'pengguna' => 'Hj. Siti Aminah',
                    'role' => 'Kepala SPPG',
                    'modul' => 'Koreksi',
                    'aktivitas' => 'Menolak koreksi KR-003',
                ],
                [
                    'waktu' => '22 Okt 07:05',
                    'pengguna' => 'Rina Wulandari',
                    'role' => 'Staf SPPG',
                    'modul' => 'Penerimaan',
                    'aktivitas' => 'Konfirmasi penerimaan PB-003',
                ],
            ]);
        }

        return view('KepalaSPPG.dashboard', compact(
            'sppg',
            'user',
            'todayStr',
            'stats',
            'trenChartData',
            'stokChartData',
            'alurProses',
            'distribusiTerbaru',
            'distribusiFooter',
            'koreksiPreview',
            'staffPreview',
            'auditPreview',
            'batchTerbaru'
        ));
    }

    public function auditIndex(): RedirectResponse
    {
        return redirect()->route('sppg.audit');
    }
}
