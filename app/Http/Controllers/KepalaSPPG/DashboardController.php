<?php

namespace App\Http\Controllers\KepalaSPPG;

use App\Http\Controllers\Controller;
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
     * Tampilkan Dashboard Kepala SPPG dengan metrik dan visualisasi riil dari database.
     */
    public function index(Request $request): View
    {
        $user = Auth::guard('staff')->user() ?: Auth::user();
        $sppg = $user?->sppg ?? Sppg::where('id_user', $user?->id_user ?? $user?->id)->first() ?? Sppg::first();
        $idSppg = $sppg?->id_sppg ?? 1;

        $now = Carbon::now()->locale('id');
        $todayStr = $now->translatedFormat('l, d F Y');

        // ==========================================
        // 1. STAT CARDS UTAMA (KPI Operasional Riil)
        // ==========================================

        // Card 1: Porsi Siap & Target Hari Ini (atau batch operasional terkini)
        $batchesToday = DB::table('production_batches')
            ->where('id_sppg', $idSppg)
            ->whereDate('production_date', Carbon::today())
            ->get();

        if ($batchesToday->isNotEmpty()) {
            $porsiSiap = (int) $batchesToday->sum(fn ($b) => $b->actual_porsi ?? 0);
            $porsiTarget = (int) $batchesToday->sum('target_porsi');
            $batchCount = $batchesToday->count();
        } else {
            // Ambil data batch operasional terkini di database
            $latestBatchDate = DB::table('production_batches')
                ->where('id_sppg', $idSppg)
                ->orderByDesc('production_date')
                ->value('production_date');

            if ($latestBatchDate) {
                $batchesLatest = DB::table('production_batches')
                    ->where('id_sppg', $idSppg)
                    ->whereDate('production_date', $latestBatchDate)
                    ->get();
                $porsiSiap = (int) $batchesLatest->sum(fn ($b) => $b->actual_porsi ?? 0);
                $porsiTarget = (int) $batchesLatest->sum('target_porsi');
                $batchCount = $batchesLatest->count();
            } else {
                $porsiSiap = 0;
                $porsiTarget = 0;
                $batchCount = 0;
            }
        }

        // Card 2: Porsi Terdistribusi & Tingkat Capaian
        $distributionsToday = (int) DB::table('distributions')
            ->join('production_batches', 'distributions.production_batch_id', '=', 'production_batches.id')
            ->where('production_batches.id_sppg', $idSppg)
            ->whereDate('distributions.distributed_at', Carbon::today())
            ->sum('distributions.portions_sent');

        if ($distributionsToday > 0) {
            $porsiTerdistribusi = $distributionsToday;
            $distribusiPct = $porsiTarget > 0 ? min(100, (int) round(($porsiTerdistribusi / $porsiTarget) * 100)) : 100;
        } else {
            // Cek distribusi terkini di database
            $latestDist = DB::table('distributions')
                ->join('production_batches', 'distributions.production_batch_id', '=', 'production_batches.id')
                ->where('production_batches.id_sppg', $idSppg)
                ->orderByDesc('distributions.distributed_at')
                ->first(['distributions.distributed_at']);

            if ($latestDist) {
                $latestDate = Carbon::parse($latestDist->distributed_at)->toDateString();
                $porsiTerdistribusi = (int) DB::table('distributions')
                    ->join('production_batches', 'distributions.production_batch_id', '=', 'production_batches.id')
                    ->where('production_batches.id_sppg', $idSppg)
                    ->whereDate('distributions.distributed_at', $latestDate)
                    ->sum('distributions.portions_sent');

                $targetOnDate = (int) DB::table('production_batches')
                    ->where('id_sppg', $idSppg)
                    ->whereDate('production_date', $latestDate)
                    ->sum('target_porsi');

                $distribusiPct = $targetOnDate > 0 ? min(100, (int) round(($porsiTerdistribusi / $targetOnDate) * 100)) : 100;
            } else {
                $porsiTerdistribusi = 0;
                $distribusiPct = 0;
            }
        }

        // Card 3 & Donut Chart: Kondisi Kesehatan Stok Bahan
        $materialStats = DB::table('materials')
            ->leftJoin('material_lots', function ($join) use ($idSppg) {
                $join->on('materials.id', '=', 'material_lots.material_id')
                    ->where('material_lots.id_sppg', '=', $idSppg);
            })
            ->where('materials.id_sppg', $idSppg)
            ->where('materials.is_active', true)
            ->select(
                'materials.id',
                'materials.min_stock',
                DB::raw('COALESCE(SUM(material_lots.current_qty), 0) as total_qty'),
                DB::raw("MAX(CASE WHEN material_lots.expiry_date <= CURRENT_DATE + INTERVAL '7 days' AND material_lots.current_qty > 0 THEN 1 ELSE 0 END) as has_expiring")
            )
            ->groupBy('materials.id', 'materials.min_stock')
            ->get();

        $countKritis = 0;
        $countMenipis = 0;
        $countAman = 0;

        foreach ($materialStats as $m) {
            $min = (float) ($m->min_stock ?? 0);
            $qty = (float) $m->total_qty;
            $hasExpiring = (int) $m->has_expiring === 1;

            if ($qty <= $min || $hasExpiring) {
                $countKritis++;
            } elseif ($qty <= ($min * 1.5)) {
                $countMenipis++;
            } else {
                $countAman++;
            }
        }

        $criticalMaterialsCount = $countKritis;

        // Card 4: Perlu Koreksi
        $pendingCorrectionsCount = CorrectionRequest::where('id_sppg', $idSppg)
            ->where('status', 'pending')
            ->count();

        $destinationsQuery = DB::table('distribution_destinations')->where('id_sppg', $idSppg);
        $totalDestinasi = (clone $destinationsQuery)->count();
        $totalPenerima = (clone $destinationsQuery)->sum('jumlah_penerima');

        $stats = [
            'sasaran_penerima' => $totalDestinasi,
            'total_penerima' => $totalPenerima,
            'porsi_siap' => $porsiSiap,
            'porsi_target' => $porsiTarget,
            'batch_count' => $batchCount,
            'porsi_terdistribusi' => $porsiTerdistribusi,
            'distribusi_pct' => $distribusiPct,
            'koreksi_menunggu' => $pendingCorrectionsCount,
            'stok_kritis' => $criticalMaterialsCount,
        ];

        // ==========================================
        // 2. GRAFIK 1: TREN PRODUKSI & DISTRIBUSI (Line Chart Murni Database)
        // ==========================================
        $hasRecentBatch = DB::table('production_batches')
            ->where('id_sppg', $idSppg)
            ->whereDate('production_date', '>=', Carbon::today()->subDays(6))
            ->exists();

        $anchorDate = $hasRecentBatch
            ? Carbon::today()
            : (DB::table('production_batches')->where('id_sppg', $idSppg)->max('production_date')
                ? Carbon::parse(DB::table('production_batches')->where('id_sppg', $idSppg)->max('production_date'))
                : Carbon::today());

        // A. 7 Hari Terakhir
        $days7 = collect(range(6, 0))->map(fn ($ago) => (clone $anchorDate)->subDays($ago));
        $hariMap = ['Sun' => 'Min', 'Mon' => 'Sen', 'Tue' => 'Sel', 'Wed' => 'Rab', 'Thu' => 'Kam', 'Fri' => 'Jum', 'Sat' => 'Sab'];
        $labels7Hari = $days7->map(fn ($d) => $hariMap[$d->format('D')] ?? $d->format('D'))->values()->all();
        $start7 = $days7->first()->toDateString();
        $end7 = $days7->last()->toDateString();

        $prod7 = DB::table('production_batches')
            ->where('id_sppg', $idSppg)
            ->whereBetween('production_date', [$start7, $end7])
            ->selectRaw('production_date, SUM(COALESCE(actual_porsi, target_porsi, 0)) as total')
            ->groupBy('production_date')
            ->pluck('total', 'production_date');

        $dist7 = DB::table('distributions')
            ->join('production_batches', 'distributions.production_batch_id', '=', 'production_batches.id')
            ->where('production_batches.id_sppg', $idSppg)
            ->whereDate('distributions.distributed_at', '>=', $start7)
            ->whereDate('distributions.distributed_at', '<=', $end7)
            ->selectRaw('DATE(distributions.distributed_at) as d_date, SUM(distributions.portions_sent) as total')
            ->groupBy('d_date')
            ->pluck('total', 'd_date');

        $produksi7Hari = $days7->map(fn ($d) => (int) ($prod7[$d->toDateString()] ?? 0))->values()->all();
        $distribusi7Hari = $days7->map(fn ($d) => (int) ($dist7[$d->toDateString()] ?? 0))->values()->all();

        // B. 30 Hari Terakhir (Dikelompokkan per minggu: Mgg 1, Mgg 2, Mgg 3, Mgg 4)
        $labels30Hari = ['Mgg 1', 'Mgg 2', 'Mgg 3', 'Mgg 4'];
        $produksi30Hari = [];
        $distribusi30Hari = [];

        for ($w = 3; $w >= 0; $w--) {
            $wStart = (clone $anchorDate)->subDays(($w * 7) + 6)->toDateString();
            $wEnd = (clone $anchorDate)->subDays($w * 7)->toDateString();

            $pWeek = (int) DB::table('production_batches')
                ->where('id_sppg', $idSppg)
                ->whereBetween('production_date', [$wStart, $wEnd])
                ->sum(DB::raw('COALESCE(actual_porsi, target_porsi, 0)'));

            $dWeek = (int) DB::table('distributions')
                ->join('production_batches', 'distributions.production_batch_id', '=', 'production_batches.id')
                ->where('production_batches.id_sppg', $idSppg)
                ->whereDate('distributions.distributed_at', '>=', $wStart)
                ->whereDate('distributions.distributed_at', '<=', $wEnd)
                ->sum('distributions.portions_sent');

            $produksi30Hari[] = $pWeek;
            $distribusi30Hari[] = $dWeek;
        }

        // C. Tahun Terkait (Per Bulan: Jan - Des)
        $yearInt = (int) $anchorDate->year;
        $labelsTahun = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
        $produksiTahun = [];
        $distribusiTahun = [];

        $prodMonthly = DB::table('production_batches')
            ->where('id_sppg', $idSppg)
            ->whereRaw('EXTRACT(YEAR FROM production_date) = ?', [$yearInt])
            ->selectRaw('EXTRACT(MONTH FROM production_date)::int as m_num, SUM(COALESCE(actual_porsi, target_porsi, 0)) as total')
            ->groupBy('m_num')
            ->pluck('total', 'm_num');

        $distMonthly = DB::table('distributions')
            ->join('production_batches', 'distributions.production_batch_id', '=', 'production_batches.id')
            ->where('production_batches.id_sppg', $idSppg)
            ->whereRaw('EXTRACT(YEAR FROM distributions.distributed_at) = ?', [$yearInt])
            ->selectRaw('EXTRACT(MONTH FROM distributions.distributed_at)::int as m_num, SUM(distributions.portions_sent) as total')
            ->groupBy('m_num')
            ->pluck('total', 'm_num');

        for ($m = 1; $m <= 12; $m++) {
            $produksiTahun[] = (int) ($prodMonthly[$m] ?? 0);
            $distribusiTahun[] = (int) ($distMonthly[$m] ?? 0);
        }

        $trenChartData = [
            'currentPeriod' => '7hari',
            'periods' => [
                '7hari' => [
                    'labels' => $labels7Hari,
                    'produksi' => $produksi7Hari,
                    'distribusi' => $distribusi7Hari,
                ],
                '30hari' => [
                    'labels' => $labels30Hari,
                    'produksi' => $produksi30Hari,
                    'distribusi' => $distribusi30Hari,
                ],
                '2026' => [
                    'labels' => $labelsTahun,
                    'produksi' => $produksiTahun,
                    'distribusi' => $distribusiTahun,
                ],
            ],
        ];

        // ==========================================
        // 3. GRAFIK 2: KONDISI KESEHATAN STOK BAHAN
        // ==========================================
        $stokChartData = [
            'labels' => ['Stok Aman', 'Mendekati Minimum', 'Kritis / Expired'],
            'counts' => [$countAman, $countMenipis, $countKritis],
            'colors' => ['#16A34A', '#F59E0B', '#EF4444'],
        ];

        // ==========================================
        // 4. LIVE PIPELINE ALUR DAPUR
        // ==========================================
        $purchasesToday = DB::table('purchases')->where('id_sppg', $idSppg)->whereDate('purchase_date', Carbon::today())->count();
        $receivingsToday = DB::table('receivings')->where('id_sppg', $idSppg)->whereDate('received_at', Carbon::today())->count();
        $productionsToday = DB::table('production_batches')->where('id_sppg', $idSppg)->whereDate('production_date', Carbon::today())->count();

        $hasilToday = DB::table('pre_distribution_checks')
            ->join('production_batches', 'pre_distribution_checks.production_batch_id', '=', 'production_batches.id')
            ->where('production_batches.id_sppg', $idSppg)
            ->whereDate('pre_distribution_checks.created_at', Carbon::today())
            ->count();

        $distributionsCountToday = DB::table('distributions')
            ->join('production_batches', 'distributions.production_batch_id', '=', 'production_batches.id')
            ->where('production_batches.id_sppg', $idSppg)
            ->whereDate('distributions.distributed_at', Carbon::today())
            ->count();

        $alurProses = [
            [
                'step' => 1,
                'name' => 'Pembelian',
                'desc' => $purchasesToday > 0 ? "$purchasesToday PO siap" : '0 PO siap',
                'state' => $purchasesToday > 0 ? 'completed' : 'pending',
            ],
            [
                'step' => 2,
                'name' => 'Penerimaan',
                'desc' => $receivingsToday > 0 ? "$receivingsToday bahan diterima" : '0 bahan diterima',
                'state' => $receivingsToday > 0 ? 'completed' : 'pending',
            ],
            [
                'step' => 3,
                'name' => 'Pemasakan',
                'desc' => $productionsToday > 0 ? "$productionsToday batch aktif" : '0 batch aktif',
                'state' => $productionsToday > 0 ? 'active' : 'pending',
            ],
            [
                'step' => 4,
                'name' => 'Uji QC Hasil',
                'desc' => $hasilToday > 0 ? "$hasilToday siap saji" : '0 siap saji',
                'state' => $hasilToday > 0 ? 'completed' : 'pending',
            ],
            [
                'step' => 5,
                'name' => 'Distribusi',
                'desc' => $distributionsCountToday > 0 ? "$distributionsCountToday terkirim" : '0 terkirim',
                'state' => $distributionsCountToday > 0 ? 'completed' : 'pending',
            ],
        ];

        // ==========================================
        // 5. TABEL RINGKASAN OPERASIONAL RIIL
        // ==========================================
        $dbBatches = DB::table('production_batches')
            ->leftJoin('menus', 'production_batches.menu_id', '=', 'menus.id')
            ->where('production_batches.id_sppg', $idSppg)
            ->select('production_batches.*', 'menus.nama as menu_nama')
            ->orderByDesc('production_batches.id')
            ->limit(3)
            ->get();

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

        $dbDistributions = DB::table('distributions')
            ->join('distribution_destinations', 'distributions.destination_id', '=', 'distribution_destinations.id')
            ->where('distribution_destinations.id_sppg', $idSppg)
            ->select('distributions.*', 'distribution_destinations.nama as tujuan_nama')
            ->orderByDesc('distributions.created_at')
            ->limit(3)
            ->get();

        $distribusiTerbaru = $dbDistributions->map(function ($d) {
            return [
                'tujuan' => $d->tujuan_nama,
                'porsi' => $d->portions_sent,
                'status' => ($d->status === 'sent' || $d->status === 'received') ? 'Terkirim' : ucfirst($d->status),
            ];
        });

        $distribusiFooter = $dbDistributions->isNotEmpty()
            ? Carbon::parse($dbDistributions->first()->created_at)->locale('id')->translatedFormat('d M').': Total '.$dbDistributions->sum('portions_sent').' porsi terkirim'
            : 'Belum ada riwayat distribusi tercatat';

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
            'batchTerbaru'
        ));
    }

    public function auditIndex(): RedirectResponse
    {
        return redirect()->route('sppg.audit');
    }
}
