<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Distribution;
use App\Models\Menu;
use App\Models\ProductionBatch;
use App\Models\Purchase;
use App\Models\Recipe;
use App\Models\Sppg;
use App\Models\Staff;
use App\Support\Keys;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Tampilkan Dashboard Operasional Staff SPPG dengan data riil dari database.
     */
    public function index(Request $request): View
    {
        $user = Auth::guard('staff')->user();
        if (! ($user instanceof Staff) && Auth::check()) {
            $webUser = Auth::user();
            if ($webUser && ! empty($webUser->email)) {
                $user = Staff::where('email', $webUser->email)->first();
            }
            if (! $user && isset($webUser->id_sppg)) {
                $user = Staff::where('id_sppg', $webUser->id_sppg)->first();
            }
            if (! $user && isset($webUser->id_user)) {
                $ownerSppgId = DB::table(Keys::SPPG_TABLE)->where(Keys::SPPG_OWNER, $webUser->id_user)->value(Keys::SPPG_PK);
                if ($ownerSppgId) {
                    $user = Staff::where('id_sppg', $ownerSppgId)->first();
                }
            }
        }

        $idSppg = $user?->id_sppg;
        if (! $idSppg && Auth::check()) {
            $webUser = Auth::user();
            $idSppg = $webUser->id_sppg ?? DB::table(Keys::SPPG_TABLE)->where(Keys::SPPG_OWNER, $webUser->id_user ?? $webUser->id)->value(Keys::SPPG_PK);
        }
        $idSppg = (int) ($idSppg ?? 1);

        $sppg = Sppg::find($idSppg) ?? Sppg::first();
        $now = Carbon::now()->locale('id');
        $todayStr = $now->translatedFormat('l, d F Y');

        // ==========================================
        // 1. STAT CARDS METRIK OPERASIONAL (REAL DATABASE)
        // ==========================================
        $totalMenu = Menu::where('id_sppg', $idSppg)->count();
        $totalResep = Recipe::whereHas('menu', fn ($q) => $q->where('id_sppg', $idSppg))->count();
        $activeResep = Recipe::whereHas('menu', fn ($q) => $q->where('id_sppg', $idSppg))->where('status', 'active')->count();

        // A. Porsi Siap & Target (Sesuai tanggal operasional terkini di database)
        $batchesQuery = DB::table('production_batches')->where('id_sppg', $idSppg);
        $hasTodayBatch = (clone $batchesQuery)->whereDate('production_date', Carbon::today())->exists();

        if ($hasTodayBatch) {
            $batchesScope = (clone $batchesQuery)->whereDate('production_date', Carbon::today())->get();
        } else {
            // Ambil tanggal batch operasional terkini
            $latestBatchDate = (clone $batchesQuery)->orderByDesc('production_date')->value('production_date');
            if ($latestBatchDate) {
                $batchesScope = (clone $batchesQuery)->whereDate('production_date', $latestBatchDate)->get();
            } else {
                $batchesScope = (clone $batchesQuery)->get();
            }
        }

        $porsiSiap = (int) $batchesScope->sum(fn ($b) => $b->actual_porsi ?? $b->target_porsi ?? 0);
        $porsiTarget = (int) $batchesScope->sum('target_porsi');
        $batchCount = $batchesScope->count();

        // Jika porsi target dari batch adalah 0, gunakan akumulasi target dari master destinasi
        if ($porsiTarget <= 0) {
            $porsiTarget = (int) DB::table('distribution_destinations')->where('id_sppg', $idSppg)->sum('jumlah_penerima');
            $porsiSiap = (int) (clone $batchesQuery)->sum('actual_porsi');
            $batchCount = (clone $batchesQuery)->count();
        }

        // B. Porsi Terdistribusi & Tingkat Capaian Riil
        $porsiTerdistribusi = (int) DB::table('distributions')
            ->join('distribution_destinations', 'distributions.destination_id', '=', 'distribution_destinations.id')
            ->where('distribution_destinations.id_sppg', $idSppg)
            ->whereIn('distributions.status', ['sent', 'received'])
            ->sum('distributions.portions_sent');

        $distribusiPct = $porsiTarget > 0 ? min(100, (int) round(($porsiTerdistribusi / $porsiTarget) * 100)) : 100;

        // C. Tugas Pending Operasional
        $poDraftCount = (int) DB::table('purchases')->where('id_sppg', $idSppg)->where('status', 'draft')->count();
        $batchPendingCount = (int) DB::table('production_batches')->where('id_sppg', $idSppg)->whereIn('status', ['draft', 'confirmed', 'processing'])->count();
        $totalPendingTasks = $poDraftCount + $batchPendingCount;

        // D. Bahan Kritis Menipis (Real Database Stock Condition)
        $materialStats = DB::table('materials')
            ->leftJoin('material_lots', function ($join) use ($idSppg) {
                $join->on('materials.id', '=', 'material_lots.material_id')
                    ->where('material_lots.id_sppg', '=', $idSppg);
            })
            ->where('materials.id_sppg', $idSppg)
            ->where('materials.is_active', true)
            ->select(
                'materials.id',
                'materials.nama',
                'materials.kode',
                'materials.satuan',
                'materials.min_stock',
                DB::raw('COALESCE(SUM(material_lots.current_qty), 0) as total_qty')
            )
            ->groupBy('materials.id', 'materials.nama', 'materials.kode', 'materials.satuan', 'materials.min_stock')
            ->orderBy('total_qty', 'asc')
            ->get();

        $criticalMaterials = $materialStats->filter(function ($m) {
            return (float) $m->total_qty <= (float) $m->min_stock;
        })->values();

        $criticalMaterialsCount = $criticalMaterials->count();

        $stats = [
            'total_menu' => $totalMenu,
            'total_resep' => $totalResep,
            'resep_aktif' => $activeResep,
            'porsi_siap' => $porsiSiap,
            'porsi_target' => $porsiTarget,
            'batch_count' => $batchCount,
            'porsi_terdistribusi' => $porsiTerdistribusi,
            'distribusi_pct' => $distribusiPct,
            'pending_tasks_count' => $totalPendingTasks,
            'batch_pending_count' => $batchPendingCount,
            'po_draft_count' => $poDraftCount,
            'critical_materials_count' => $criticalMaterialsCount,
        ];

        // ==========================================
        // 2. VISUALISASI CAPAIAN PER SASARAN (REAL DATABASE)
        // ==========================================
        $types = [
            'sekolah' => ['label' => 'Sekolah (SD / SMP / SMA)', 'icon' => '🏫', 'fill' => 'fill-blue'],
            'posyandu' => ['label' => 'Posyandu / Balita', 'icon' => '👶', 'fill' => 'fill-emerald'],
            'lainnya' => ['label' => 'Sasaran Khusus / Lainnya', 'icon' => '🏢', 'fill' => 'fill-amber'],
        ];

        $targetProgress = [];
        foreach ($types as $key => $info) {
            $destIds = DB::table('distribution_destinations')
                ->where('id_sppg', $idSppg)
                ->where('tipe', $key)
                ->pluck('id');

            $target = (int) DB::table('distribution_destinations')
                ->where('id_sppg', $idSppg)
                ->where('tipe', $key)
                ->sum('jumlah_penerima');

            $sent = (int) DB::table('distributions')
                ->whereIn('destination_id', $destIds)
                ->whereIn('status', ['sent', 'received'])
                ->sum('portions_sent');

            $pct = $target > 0 ? min(100, (int) round(($sent / $target) * 100)) : ($sent > 0 ? 100 : 0);

            $targetProgress[] = [
                'type' => $key,
                'label' => $info['label'],
                'icon' => $info['icon'],
                'fill_class' => $info['fill'],
                'target' => $target,
                'sent' => $sent,
                'pct' => $pct,
            ];
        }

        // ==========================================
        // 3. DONUT CHART DATA (STATUS DISTRIBUSI REAL DATABASE)
        // ==========================================
        $distQuery = DB::table('distributions')
            ->join('distribution_destinations', 'distributions.destination_id', '=', 'distribution_destinations.id')
            ->where('distribution_destinations.id_sppg', $idSppg);

        $porsiReceived = (int) (clone $distQuery)->where('distributions.status', 'received')->sum('distributions.portions_sent');
        $porsiInTransit = (int) (clone $distQuery)->where('distributions.status', 'sent')->sum('distributions.portions_sent');
        $porsiPlanned = (int) (clone $distQuery)->where('distributions.status', 'planned')->sum('distributions.portions_sent');
        $porsiCancelled = (int) (clone $distQuery)->where('distributions.status', 'cancelled')->sum('distributions.portions_sent');

        $totalDistribusiAktif = $porsiReceived + $porsiInTransit + $porsiPlanned;
        $completionPct = $totalDistribusiAktif > 0 ? min(100, (int) round(($porsiReceived / $totalDistribusiAktif) * 100)) : 100;

        $donutData = [
            'received' => $porsiReceived,
            'in_transit' => $porsiInTransit,
            'planned' => $porsiPlanned,
            'cancelled' => $porsiCancelled,
            'total_target' => $totalDistribusiAktif,
            'completion_pct' => $completionPct,
        ];

        // ==========================================
        // 4. LIVE PIPELINE (REAL DATABASE AGGREGATES)
        // ==========================================
        $purchasesCount = DB::table('purchases')->where('id_sppg', $idSppg)->count();
        $receivingsCount = DB::table('receivings')->where('id_sppg', $idSppg)->where('status', 'confirmed')->count();
        $batchesCount = DB::table('production_batches')->where('id_sppg', $idSppg)->count();
        $qcCount = DB::table('pre_distribution_checks')
            ->join('production_batches', 'pre_distribution_checks.production_batch_id', '=', 'production_batches.id')
            ->where('production_batches.id_sppg', $idSppg)
            ->count();
        $distributionsCount = DB::table('distributions')
            ->join('distribution_destinations', 'distributions.destination_id', '=', 'distribution_destinations.id')
            ->where('distribution_destinations.id_sppg', $idSppg)
            ->where('distributions.status', 'received')
            ->count();

        $latestBatch = ProductionBatch::query()
            ->with(['menu'])
            ->where('id_sppg', $idSppg)
            ->orderByDesc('id')
            ->first();

        $pipelineSteps = [
            [
                'number' => 1,
                'title' => 'Pembelian PO',
                'desc' => $purchasesCount > 0 ? "{$purchasesCount} PO tercatat" : '0 PO tercatat',
                'state' => $purchasesCount > 0 ? 'completed' : 'pending',
                'route' => route('staff.purchases.index'),
            ],
            [
                'number' => 2,
                'title' => 'Penerimaan',
                'desc' => $receivingsCount > 0 ? "{$receivingsCount} Masuk terverifikasi" : '0 Masuk terverifikasi',
                'state' => $receivingsCount > 0 ? 'completed' : 'pending',
                'route' => route('staff.receivings.index'),
            ],
            [
                'number' => 3,
                'title' => 'Pemasakan',
                'desc' => $batchesCount > 0 ? "{$batchesCount} Batch aktif" : '0 Batch aktif',
                'state' => $batchesCount > 0 ? 'active' : 'pending',
                'route' => route('staff.batches.index'),
            ],
            [
                'number' => 4,
                'title' => 'Uji QC Hasil',
                'desc' => $qcCount > 0 ? "{$qcCount} Batch siap saji" : '0 Batch siap saji',
                'state' => $qcCount > 0 ? 'completed' : 'pending',
                'route' => route('staff.hasil.index'),
            ],
            [
                'number' => 5,
                'title' => 'Distribusi',
                'desc' => $distributionsCount > 0 ? "{$distributionsCount} Titik terkirim" : '0 Titik terkirim',
                'state' => $distributionsCount > 0 ? 'completed' : 'pending',
                'route' => route('staff.pengiriman.index'),
            ],
        ];

        // ==========================================
        // 5. DETAIL TABEL & ANTREAN HARIAN (REAL DATABASE)
        // ==========================================
        $recentDistributions = Distribution::query()
            ->with(['batch.menu', 'destination', 'recorder'])
            ->whereHas('destination', fn ($q) => $q->where('id_sppg', $idSppg))
            ->orderByDesc('id')
            ->take(5)
            ->get();

        $waitingPurchases = Purchase::query()
            ->with(['supplier', 'items'])
            ->where('id_sppg', $idSppg)
            ->orderByDesc('id')
            ->take(4)
            ->get();

        return view('staff.dashboard', compact(
            'user',
            'sppg',
            'todayStr',
            'stats',
            'targetProgress',
            'donutData',
            'pipelineSteps',
            'latestBatch',
            'recentDistributions',
            'criticalMaterials',
            'waitingPurchases'
        ));
    }
}
