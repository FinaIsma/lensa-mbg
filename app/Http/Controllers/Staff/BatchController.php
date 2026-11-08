<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\AuditTrail;
use App\Models\Material;
use App\Models\MaterialLot;
use App\Models\MaterialUsage;
use App\Models\Menu;
use App\Models\ProductionBatch;
use App\Models\Recipe;
use App\Models\Staff;
use App\Models\StockMovement;
use App\Support\Keys;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class BatchController extends Controller
{
    /**
     * Tampilkan daftar batch produksi dengan filter, statistik, dan pencarian.
     */
    public function index(Request $request): View
    {
        $idSppg = $this->resolveSppgId();

        $q = trim((string) $request->input('q', ''));
        $status = trim((string) $request->input('status', ''));
        $menuId = $request->input('menu_id');
        $kategori = trim((string) $request->input('kategori', ''));
        $dateFrom = $request->input('date_from');
        $dateTo = $request->input('date_to');

        $query = ProductionBatch::query()
            ->with([
                'menu',
                'recipe',
                'creator',
                'materialUsages.lot.material',
                'processingSteps',
                'preDistributionCheck',
            ])
            ->where('id_sppg', $idSppg);

        if ($q !== '') {
            $query->where(function ($sub) use ($q) {
                $sub->where('batch_code', 'ilike', "%{$q}%")
                    ->orWhereHas('menu', fn ($sq) => $sq->where('nama', 'ilike', "%{$q}%"));
            });
        }

        if ($status !== '') {
            $query->where('status', $status);
        }

        if ($menuId) {
            $query->where('menu_id', $menuId);
        }

        if ($kategori !== '') {
            $query->whereHas('menu', fn ($sq) => $sq->where('kategori', $kategori));
        }

        if ($dateFrom) {
            $query->whereDate('production_date', '>=', $dateFrom);
        }

        if ($dateTo) {
            $query->whereDate('production_date', '<=', $dateTo);
        }

        $batches = $query->orderBy('production_date', 'desc')
            ->orderBy('id', 'desc')
            ->paginate(15)
            ->withQueryString();

        // 4 Statistik untuk Stat Cards
        $stats = [
            'total_batch' => ProductionBatch::where('id_sppg', $idSppg)->count(),
            'draft' => ProductionBatch::where('id_sppg', $idSppg)->where('status', 'draft')->count(),
            'berjalan' => ProductionBatch::where('id_sppg', $idSppg)->whereIn('status', ['confirmed', 'processing'])->count(),
            'selesai' => ProductionBatch::where('id_sppg', $idSppg)->whereIn('status', ['ready', 'distributed', 'completed'])->count(),
        ];

        // Master menu aktif untuk dropdown filter
        $menus = Menu::where('id_sppg', $idSppg)
            ->where('is_active', true)
            ->orderBy('nama')
            ->get();

        // Master menu & bahan untuk modal pop up Buat Batch
        $menusWithRecipes = Menu::where('id_sppg', $idSppg)
            ->where('is_active', true)
            ->whereHas('recipes', fn ($q) => $q->where('status', 'active'))
            ->with(['recipes' => fn ($q) => $q->where('status', 'active')->with('items.material')])
            ->orderBy('nama')
            ->get();

        $allMaterials = Material::where('id_sppg', $idSppg)
            ->where('is_active', true)
            ->orderBy('nama')
            ->get();

        $autoBatchCode = ProductionBatch::generateBatchCode($idSppg, date('Y-m-d'));
        $today = date('Y-m-d');

        return view('staff.batch.index', compact(
            'batches',
            'stats',
            'menus',
            'menusWithRecipes',
            'allMaterials',
            'autoBatchCode',
            'today',
            'q',
            'status',
            'menuId',
            'kategori',
            'dateFrom',
            'dateTo'
        ));
    }

    /**
     * Tampilkan formulir pembuatan batch baru (draft).
     */
    public function create(): View
    {
        $idSppg = $this->resolveSppgId();

        // Ambil menu aktif yang memiliki minimal satu resep aktif
        $menus = Menu::where('id_sppg', $idSppg)
            ->where('is_active', true)
            ->whereHas('recipes', fn ($q) => $q->where('status', 'active'))
            ->with(['recipes' => fn ($q) => $q->where('status', 'active')->with('items.material')])
            ->orderBy('nama')
            ->get();

        // Seluruh bahan aktif SPPG (untuk opsi + Bahan di Luar Resep)
        $allMaterials = Material::where('id_sppg', $idSppg)
            ->where('is_active', true)
            ->orderBy('nama')
            ->get();

        $autoBatchCode = ProductionBatch::generateBatchCode($idSppg, date('Y-m-d'));
        $today = date('Y-m-d');

        return view('staff.batch.create', compact(
            'menus',
            'allMaterials',
            'autoBatchCode',
            'today'
        ));
    }

    /**
     * Endpoint API JSON untuk mengambil kebutuhan bahan resep & lot tersedia (urut FEFO).
     */
    public function getRecipeMaterials(Request $request): JsonResponse
    {
        $idSppg = $this->resolveSppgId();

        $menuId = (int) $request->query('menu_id');
        $ukuranPorsi = trim((string) $request->query('ukuran_porsi'));
        $targetPorsi = (int) $request->query('target_porsi', 0);
        $productionDate = $request->query('production_date', date('Y-m-d'));

        if (! $menuId || ! in_array($ukuranPorsi, ['besar', 'kecil']) || $targetPorsi <= 0) {
            return response()->json([
                'success' => false,
                'message' => 'Parameter menu_id, ukuran_porsi, dan target_porsi wajib diisi dengan benar.',
            ], 422);
        }

        $recipe = Recipe::where('menu_id', $menuId)
            ->where('ukuran_porsi', $ukuranPorsi)
            ->where('status', 'active')
            ->with(['items.material'])
            ->first();

        if (! $recipe) {
            return response()->json([
                'success' => false,
                'message' => "Belum ada resep aktif untuk ukuran porsi {$ukuranPorsi} pada menu ini.",
            ], 404);
        }

        $blocks = [];
        $prodDateCarbon = Carbon::parse($productionDate)->startOfDay();

        foreach ($recipe->items as $item) {
            $material = $item->material;
            if (! $material) {
                continue;
            }

            $kebutuhan = round(((float) $item->qty_per_porsi) * $targetPorsi, 3);

            // Ambil lot tersedia untuk bahan ini:
            // Syarat: status = 'available', current_qty > 0, expiry_date >= production_date, urut FEFO
            $lots = MaterialLot::where('material_id', $material->id)
                ->where('id_sppg', $idSppg)
                ->where('status', 'available')
                ->where('current_qty', '>', 0)
                ->where(function ($q) use ($productionDate) {
                    $q->whereNull('expiry_date')
                        ->orWhereDate('expiry_date', '>=', $productionDate);
                })
                ->orderByRaw('expiry_date ASC NULLS LAST')
                ->orderBy('id', 'asc')
                ->with('supplier')
                ->get()
                ->map(function (MaterialLot $lot) use ($prodDateCarbon) {
                    $daysToExpiry = null;
                    $isNearExpiry = false;

                    if ($lot->expiry_date) {
                        $daysToExpiry = (int) $prodDateCarbon->diffInDays($lot->expiry_date, false);
                        $isNearExpiry = $daysToExpiry >= 0 && $daysToExpiry <= 7;
                    }

                    return [
                        'id' => $lot->id,
                        'lot_number' => $lot->lot_number,
                        'barcode' => $lot->barcode,
                        'current_qty' => (float) $lot->current_qty,
                        'unit_cost' => (float) $lot->unit_cost,
                        'expiry_date' => $lot->expiry_date ? $lot->expiry_date->format('Y-m-d') : null,
                        'expiry_date_formatted' => $lot->expiry_date ? $lot->expiry_date->format('d/m/Y') : '-',
                        'days_to_expiry' => $daysToExpiry,
                        'is_near_expiry' => $isNearExpiry,
                        'supplier_name' => $lot->supplier?->nama ?? '-',
                    ];
                });

            $totalStokTersedia = $lots->sum('current_qty');

            $blocks[] = [
                'material_id' => $material->id,
                'material_name' => $material->nama,
                'kode' => $material->kode,
                'satuan' => $item->satuan ?: ($material->satuan ?: 'kg'),
                'qty_per_porsi' => (float) $item->qty_per_porsi,
                'kebutuhan' => $kebutuhan,
                'total_stok_tersedia' => (float) $totalStokTersedia,
                'is_stock_sufficient' => $totalStokTersedia >= $kebutuhan,
                'lots' => $lots,
            ];
        }

        return response()->json([
            'success' => true,
            'recipe_id' => $recipe->id,
            'version' => $recipe->version,
            'blocks' => $blocks,
        ]);
    }

    /**
     * Endpoint API JSON untuk mengambil lot bahan di luar resep.
     */
    public function getMaterialLots(Request $request): JsonResponse
    {
        $idSppg = $this->resolveSppgId();
        $materialId = (int) $request->query('material_id');
        $productionDate = $request->query('production_date', date('Y-m-d'));

        $material = Material::where('id', $materialId)
            ->where('id_sppg', $idSppg)
            ->firstOrFail();

        $prodDateCarbon = Carbon::parse($productionDate)->startOfDay();

        $lots = MaterialLot::where('material_id', $material->id)
            ->where('id_sppg', $idSppg)
            ->where('status', 'available')
            ->where('current_qty', '>', 0)
            ->where(function ($q) use ($productionDate) {
                $q->whereNull('expiry_date')
                    ->orWhereDate('expiry_date', '>=', $productionDate);
            })
            ->orderByRaw('expiry_date ASC NULLS LAST')
            ->orderBy('id', 'asc')
            ->with('supplier')
            ->get()
            ->map(function (MaterialLot $lot) use ($prodDateCarbon) {
                $daysToExpiry = null;
                $isNearExpiry = false;

                if ($lot->expiry_date) {
                    $daysToExpiry = (int) $prodDateCarbon->diffInDays($lot->expiry_date, false);
                    $isNearExpiry = $daysToExpiry >= 0 && $daysToExpiry <= 7;
                }

                return [
                    'id' => $lot->id,
                    'lot_number' => $lot->lot_number,
                    'barcode' => $lot->barcode,
                    'current_qty' => (float) $lot->current_qty,
                    'unit_cost' => (float) $lot->unit_cost,
                    'expiry_date' => $lot->expiry_date ? $lot->expiry_date->format('Y-m-d') : null,
                    'expiry_date_formatted' => $lot->expiry_date ? $lot->expiry_date->format('d/m/Y') : '-',
                    'days_to_expiry' => $daysToExpiry,
                    'is_near_expiry' => $isNearExpiry,
                    'supplier_name' => $lot->supplier?->nama ?? '-',
                ];
            });

        return response()->json([
            'success' => true,
            'material' => [
                'id' => $material->id,
                'nama' => $material->nama,
                'kode' => $material->kode,
                'satuan' => $material->satuan ?: 'kg',
            ],
            'lots' => $lots,
        ]);
    }

    /**
     * Simpan batch baru (Draft atau langsung Konfirmasi).
     */
    public function store(Request $request): RedirectResponse|JsonResponse
    {
        $idSppg = $this->resolveSppgId();
        $staff = $this->getAuthenticatedStaff();

        $this->filterUsagesInput($request);

        $validated = $request->validate([
            'menu_id' => ['required', 'exists:menus,id'],
            'ukuran_porsi' => ['required', 'in:besar,kecil'],
            'production_date' => ['required', 'date'],
            'target_porsi' => ['required', 'integer', 'min:1'],
            'action' => ['nullable', 'in:draft,confirm'],
            'usages' => ['nullable', 'array'],
            'usages.*.material_lot_id' => ['required', 'exists:material_lots,id'],
            'usages.*.qty_used' => ['required', 'numeric', 'min:0.001'],
        ]);

        $action = $validated['action'] ?? 'draft';

        // Cari resep aktif untuk menu dan ukuran porsi
        $recipe = Recipe::where('menu_id', $validated['menu_id'])
            ->where('ukuran_porsi', $validated['ukuran_porsi'])
            ->where('status', 'active')
            ->first();

        if (! $recipe) {
            $msg = 'Resep aktif untuk menu dan ukuran porsi yang dipilih tidak ditemukan.';
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $msg], 422);
            }

            return back()->withInput()->with('error', $msg);
        }

        try {
            $batch = DB::transaction(function () use ($validated, $idSppg, $staff, $recipe, $action) {
                $batchCode = ProductionBatch::generateBatchCode($idSppg, $validated['production_date']);

                $batch = ProductionBatch::create([
                    'menu_id' => $validated['menu_id'],
                    'recipe_id' => $recipe->id,
                    'id_sppg' => $idSppg,
                    'created_by' => $staff?->id,
                    'batch_code' => $batchCode,
                    'production_date' => $validated['production_date'],
                    'target_porsi' => $validated['target_porsi'],
                    'actual_porsi' => null,
                    'total_cost' => null,
                    'status' => 'draft',
                    'is_locked' => false,
                ]);

                // Simpan pemakaian lot
                $usagesInput = $validated['usages'] ?? [];
                $seenLotIds = [];

                foreach ($usagesInput as $u) {
                    $lotId = (int) $u['material_lot_id'];
                    $qty = (float) $u['qty_used'];

                    if ($qty <= 0) {
                        continue;
                    }

                    // Validasi: 1 lot hanya 1 baris per batch
                    if (in_array($lotId, $seenLotIds, true)) {
                        throw new \InvalidArgumentException('Satu lot hanya boleh dipilih satu kali dalam satu batch.');
                    }
                    $seenLotIds[] = $lotId;

                    $lot = MaterialLot::where('id', $lotId)
                        ->where('id_sppg', $idSppg)
                        ->firstOrFail();

                    // Validasi stok lot
                    if ($qty > (float) $lot->current_qty) {
                        throw new \InvalidArgumentException("Stok lot {$lot->lot_number} ({$lot->barcode}) hanya {$lot->current_qty}.");
                    }

                    MaterialUsage::create([
                        'production_batch_id' => $batch->id,
                        'material_lot_id' => $lotId,
                        'qty_used' => $qty,
                        'unit_cost_snapshot' => null,
                    ]);
                }

                if ($action === 'confirm') {
                    $this->executeBatchConfirmation($batch, $idSppg, $staff);
                } else {
                    AuditTrail::catat('create', $batch, null, [
                        'batch_code' => $batch->batch_code,
                        'menu' => $batch->menu?->nama,
                        'ukuran_porsi' => $recipe->ukuran_porsi,
                        'target_porsi' => $batch->target_porsi,
                    ]);
                }

                return $batch;
            });

            $successMsg = $action === 'confirm'
                ? "Batch produksi {$batch->batch_code} berhasil dibuat dan dikonfirmasi!"
                : "Draft batch produksi {$batch->batch_code} berhasil disimpan.";

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => $successMsg,
                    'redirect_url' => route('staff.batches.show', $batch->id),
                ]);
            }

            return redirect()->route('staff.batches.show', $batch->id)->with('success', $successMsg);
        } catch (\Throwable $e) {
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
            }

            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    /**
     * Tampilkan detail batch produksi (header, tab-tab komprehensif, penelusuran).
     */
    public function show(int $id): View
    {
        $idSppg = $this->resolveSppgId();

        $batch = ProductionBatch::query()
            ->with([
                'menu',
                'recipe.items.material',
                'creator',
                'materialUsages.lot.material',
                'materialUsages.lot.supplier',
                'materialUsages.lot.receivingItem.receiving.purchase.supplier',
                'processingSteps.recorder',
                'processingSteps.evidences.uploader',
                'preDistributionCheck.checker',
                'evidences',
            ])
            ->where('id_sppg', $idSppg)
            ->findOrFail($id);

        // Audit Trail untuk batch ini
        $auditTrails = AuditTrail::where('auditable_type', 'production_batches')
            ->where('auditable_id', $batch->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return view('staff.batch.show', compact('batch', 'auditTrails'));
    }

    /**
     * Tampilkan formulir ubah batch (hanya saat draft).
     */
    public function edit(int $id): View|RedirectResponse
    {
        $idSppg = $this->resolveSppgId();

        $batch = ProductionBatch::with(['menu', 'recipe.items.material', 'materialUsages.lot.material', 'materialUsages.lot.supplier'])
            ->where('id_sppg', $idSppg)
            ->findOrFail($id);

        if ($batch->status !== 'draft') {
            return redirect()->route('staff.batches.show', $batch->id)
                ->with('error', 'Batch yang sudah dikonfirmasi atau dibatalkan tidak dapat diubah.');
        }

        $allMaterials = Material::where('id_sppg', $idSppg)
            ->where('is_active', true)
            ->orderBy('nama')
            ->get();

        return view('staff.batch.edit', compact('batch', 'allMaterials'));
    }

    /**
     * Update draft batch produksi.
     */
    public function update(Request $request, int $id): RedirectResponse|JsonResponse
    {
        $idSppg = $this->resolveSppgId();
        $staff = $this->getAuthenticatedStaff();

        $batch = ProductionBatch::where('id_sppg', $idSppg)->findOrFail($id);

        if ($batch->status !== 'draft') {
            $msg = 'Batch yang sudah dikonfirmasi atau dibatalkan tidak dapat diubah.';
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $msg], 422);
            }

            return redirect()->route('staff.batches.show', $batch->id)->with('error', $msg);
        }

        $this->filterUsagesInput($request);

        $validated = $request->validate([
            'production_date' => ['required', 'date'],
            'target_porsi' => ['required', 'integer', 'min:1'],
            'action' => ['nullable', 'in:draft,confirm'],
            'usages' => ['nullable', 'array'],
            'usages.*.material_lot_id' => ['required', 'exists:material_lots,id'],
            'usages.*.qty_used' => ['required', 'numeric', 'min:0.001'],
        ]);

        $action = $validated['action'] ?? 'draft';

        try {
            DB::transaction(function () use ($batch, $validated, $idSppg, $staff, $action) {
                $oldValues = [
                    'production_date' => $batch->production_date->format('Y-m-d'),
                    'target_porsi' => $batch->target_porsi,
                ];

                $batch->production_date = $validated['production_date'];
                $batch->target_porsi = $validated['target_porsi'];
                $batch->save();

                // Hapus material_usages lama dan simpan ulang dari input
                $batch->materialUsages()->delete();

                $usagesInput = $validated['usages'] ?? [];
                $seenLotIds = [];

                foreach ($usagesInput as $u) {
                    $lotId = (int) $u['material_lot_id'];
                    $qty = (float) $u['qty_used'];

                    if ($qty <= 0) {
                        continue;
                    }

                    if (in_array($lotId, $seenLotIds, true)) {
                        throw new \InvalidArgumentException('Satu lot hanya boleh dipilih satu kali dalam satu batch.');
                    }
                    $seenLotIds[] = $lotId;

                    $lot = MaterialLot::where('id', $lotId)->where('id_sppg', $idSppg)->firstOrFail();

                    if ($qty > (float) $lot->current_qty) {
                        throw new \InvalidArgumentException("Stok lot {$lot->lot_number} ({$lot->barcode}) hanya {$lot->current_qty}.");
                    }

                    MaterialUsage::create([
                        'production_batch_id' => $batch->id,
                        'material_lot_id' => $lotId,
                        'qty_used' => $qty,
                        'unit_cost_snapshot' => null,
                    ]);
                }

                if ($action === 'confirm') {
                    $this->executeBatchConfirmation($batch, $idSppg, $staff);
                } else {
                    AuditTrail::catat('update', $batch, $oldValues, [
                        'production_date' => $batch->production_date->format('Y-m-d'),
                        'target_porsi' => $batch->target_porsi,
                    ]);
                }
            });

            $successMsg = $action === 'confirm'
                ? "Batch {$batch->batch_code} berhasil dikonfirmasi dan stok bahan telah dipotong!"
                : "Perubahan draft batch {$batch->batch_code} berhasil disimpan.";

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => $successMsg,
                    'redirect_url' => route('staff.batches.show', $batch->id),
                ]);
            }

            return redirect()->route('staff.batches.show', $batch->id)->with('success', $successMsg);
        } catch (\Throwable $e) {
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
            }

            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    /**
     * Konfirmasi Batch Produksi (potong stok, lockForUpdate, audit confirm).
     */
    public function confirm(Request $request, int $id): RedirectResponse|JsonResponse
    {
        $idSppg = $this->resolveSppgId();
        $staff = $this->getAuthenticatedStaff();

        try {
            DB::transaction(function () use ($id, $idSppg, $staff) {
                $batch = ProductionBatch::where('id_sppg', $idSppg)
                    ->lockForUpdate()
                    ->findOrFail($id);

                if ($batch->status !== 'draft') {
                    throw new \InvalidArgumentException('Hanya batch berstatus draft yang dapat dikonfirmasi.');
                }

                $this->executeBatchConfirmation($batch, $idSppg, $staff);
            });

            $msg = 'Batch produksi berhasil dikonfirmasi. Stok bahan telah dipotong dan dokumen terkunci.';
            if ($request->wantsJson()) {
                return response()->json(['success' => true, 'message' => $msg]);
            }

            return redirect()->route('staff.batches.show', $id)->with('success', $msg);
        } catch (\Throwable $e) {
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
            }

            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Batalkan Batch Produksi (hanya dari status draft).
     */
    public function cancel(Request $request, int $id): RedirectResponse|JsonResponse
    {
        $idSppg = $this->resolveSppgId();
        $reason = trim((string) $request->input('reason', 'Dibatalkan oleh staf'));

        try {
            $batch = ProductionBatch::where('id_sppg', $idSppg)->findOrFail($id);

            if ($batch->status !== 'draft') {
                throw new \InvalidArgumentException('Hanya batch berstatus draft yang dapat dibatalkan.');
            }

            DB::transaction(function () use ($batch, $reason) {
                $batch->status = 'cancelled';
                $batch->save();

                AuditTrail::catat('cancel', $batch, ['status' => 'draft'], [
                    'status' => 'cancelled',
                    'alasan' => $reason,
                ]);
            });

            $msg = "Batch produksi {$batch->batch_code} telah dibatalkan.";
            if ($request->wantsJson()) {
                return response()->json(['success' => true, 'message' => $msg]);
            }

            return redirect()->route('staff.batches.index')->with('success', $msg);
        } catch (\Throwable $e) {
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
            }

            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Logika Inti Konfirmasi Batch:
     * 1. Validasi ulang lockForUpdate (current_qty, non-expired, terpenuhi)
     * 2. Snapshot unit_cost
     * 3. Hitung total_cost
     * 4. Stock movement PRODUCTION_OUT, kurangi current_qty, update status depleted jika habis
     * 5. Batch status = 'confirmed', is_locked = true
     * 6. Audit confirm
     */
    protected function executeBatchConfirmation(ProductionBatch $batch, int $idSppg, ?Staff $staff): void
    {
        $usages = $batch->materialUsages()->get();

        if ($usages->isEmpty()) {
            throw new \InvalidArgumentException('Batch belum memiliki baris pemakaian bahan.');
        }

        $recipe = $batch->recipe()->with('items.material')->first();
        if (! $recipe) {
            throw new \InvalidArgumentException('Resep tidak ditemukan untuk batch ini.');
        }

        $prodDateCarbon = Carbon::parse($batch->production_date)->startOfDay();

        // 1. Kumpulkan pemakaian per material
        $materialUsageTotals = [];

        foreach ($usages as $usage) {
            // Lock lot untuk update
            $lot = MaterialLot::where('id', $usage->material_lot_id)
                ->where('id_sppg', $idSppg)
                ->lockForUpdate()
                ->firstOrFail();

            // Cek kedaluwarsa pada tanggal produksi
            if ($lot->expiry_date && Carbon::parse($lot->expiry_date)->startOfDay()->lt($prodDateCarbon)) {
                throw new \InvalidArgumentException("Lot {$lot->barcode} ({$lot->material?->nama}) telah kedaluwarsa pada tanggal produksi ({$batch->production_date->format('d/m/Y')}).");
            }

            // Cek stok cukup
            if (((float) $usage->qty_used) > ((float) $lot->current_qty)) {
                throw new \InvalidArgumentException("Stok lot {$lot->barcode} ({$lot->lot_number}) tidak mencukupi. Tersedia: {$lot->current_qty}, Dibutuhkan: {$usage->qty_used}.");
            }

            $matId = $lot->material_id;
            $materialUsageTotals[$matId] = ($materialUsageTotals[$matId] ?? 0.0) + (float) $usage->qty_used;
        }

        // 2. Validasi pemenuhan resep: Semua bahan resep HARUS memenuhi target
        foreach ($recipe->items as $recipeItem) {
            $required = round(((float) $recipeItem->qty_per_porsi) * $batch->target_porsi, 3);
            $selected = $materialUsageTotals[$recipeItem->material_id] ?? 0.0;

            if ($selected < $required) {
                $shortage = round($required - $selected, 3);
                $matName = $recipeItem->material?->nama ?? 'Bahan';
                $satuan = $recipeItem->satuan ?: ($recipeItem->material?->satuan ?: 'kg');
                throw new \InvalidArgumentException("Bahan {$matName} masih kurang {$shortage} {$satuan} untuk memenuhi kebutuhan resep ({$required} {$satuan}).");
            }
        }

        // 3. Snapshot unit cost & potong stok
        $totalCost = 0.0;

        foreach ($usages as $usage) {
            $lot = MaterialLot::where('id', $usage->material_lot_id)->lockForUpdate()->firstOrFail();

            $unitCost = (float) $lot->unit_cost;
            $usage->unit_cost_snapshot = $unitCost;
            $usage->save();

            $rowCost = round(((float) $usage->qty_used) * $unitCost, 2);
            $totalCost += $rowCost;

            // Kurangi stok lot
            $newQty = round(((float) $lot->current_qty) - ((float) $usage->qty_used), 3);
            $lot->current_qty = max(0, $newQty);
            if ($lot->current_qty <= 0) {
                $lot->status = 'depleted';
            }
            $lot->save();

            // Catat mutasi stok PRODUCTION_OUT
            StockMovement::create([
                'material_lot_id' => $lot->id,
                'created_by' => $staff?->id,
                'movement_type' => 'PRODUCTION_OUT',
                'direction' => 'OUT',
                'qty' => $usage->qty_used,
                'reference_type' => 'material_usages',
                'reference_id' => $usage->id,
                'created_at' => now(),
            ]);
        }

        // 4. Update batch
        $batch->total_cost = round($totalCost, 2);
        $batch->status = 'confirmed';
        $batch->is_locked = true;
        $batch->save();

        // 5. Catat audit trail
        AuditTrail::catat('confirm', $batch, ['status' => 'draft'], ['status' => 'confirmed']);
    }

    /**
     * Filter array usages agar baris lot yang tidak diisi (qty_used kosong atau <= 0)
     * tidak memicu validasi error 'min:0.001'.
     */
    protected function filterUsagesInput(Request $request): void
    {
        if ($request->has('usages') && is_array($request->input('usages'))) {
            $filtered = [];
            foreach ($request->input('usages') as $lotId => $u) {
                if (! is_array($u)) {
                    continue;
                }
                $qty = isset($u['qty_used']) ? trim((string) $u['qty_used']) : '';
                if ($qty !== '' && is_numeric($qty) && (float) $qty > 0) {
                    $filtered[$lotId] = [
                        'material_lot_id' => $u['material_lot_id'] ?? $lotId,
                        'qty_used' => (float) $qty,
                    ];
                }
            }
            $request->merge(['usages' => $filtered]);
        }
    }

    /**
     * Helper ambil staf aktif
     */
    protected function getAuthenticatedStaff(): ?Staff
    {
        $user = Auth::guard('staff')->user();
        if ($user instanceof Staff) {
            return $user;
        }

        $authUser = Auth::user();
        if ($authUser) {
            $staff = Staff::where('email', $authUser->email)->first();
            if ($staff) {
                return $staff;
            }
        }

        return Staff::first();
    }

    /**
     * Helper ambil id SPPG aktif
     */
    protected function resolveSppgId(): int
    {
        $staff = $this->getAuthenticatedStaff();
        $idSppg = $staff?->id_sppg;

        if (! $idSppg && Auth::check()) {
            $idSppg = DB::table(Keys::SPPG_TABLE)->where(Keys::SPPG_OWNER, Auth::id())->value(Keys::SPPG_PK);
        }

        if (! $idSppg) {
            $idSppg = DB::table(Keys::SPPG_TABLE)->orderBy(Keys::SPPG_PK)->value(Keys::SPPG_PK);
        }

        abort_unless($idSppg, 403, 'Akun ini tidak terhubung ke SPPG mana pun.');

        return (int) $idSppg;
    }
}
