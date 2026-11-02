<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\AuditTrail;
use App\Models\Evidence;
use App\Models\Material;
use App\Models\MaterialLot;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\Receiving;
use App\Models\ReceivingItem;
use App\Models\Staff;
use App\Models\StockMovement;
use App\Models\Supplier;
use App\Support\Keys;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ReceivingController extends Controller
{
    /**
     * Tampilkan daftar penerimaan bahan beserta data modal & filter.
     */
    public function index(Request $request): View
    {
        $idSppg = $this->resolveSppgId();
        $this->ensureSampleReceivings($idSppg);

        $q = trim((string) $request->input('q', ''));
        $status = trim((string) $request->input('status', ''));
        $dateFrom = trim((string) $request->input('date_from', ''));
        $dateTo = trim((string) $request->input('date_to', ''));

        $query = Receiving::query()
            ->with(['purchase.supplier', 'receiver', 'items.material', 'items.lot', 'evidences'])
            ->where('id_sppg', $idSppg);

        if ($q !== '') {
            $query->where(function ($sub) use ($q) {
                $sub->where('receiving_no', 'ilike', "%{$q}%")
                    ->orWhereHas('purchase', fn ($pq) => $pq->where('purchase_no', 'ilike', "%{$q}%")
                        ->orWhereHas('supplier', fn ($sq) => $sq->where('nama', 'ilike', "%{$q}%"))
                    )
                    ->orWhereHas('receiver', fn ($rq) => $rq->where('nama', 'ilike', "%{$q}%"));
            });
        }

        if ($status !== '' && in_array($status, ['draft', 'confirmed', 'voided'], true)) {
            $query->where('status', $status);
        }

        if ($dateFrom !== '') {
            $query->whereDate('received_at', '>=', $dateFrom);
        }

        if ($dateTo !== '') {
            $query->whereDate('received_at', '<=', $dateTo);
        }

        $receivings = $query->orderByDesc('received_at')
            ->orderByDesc('id')
            ->paginate(10)
            ->withQueryString();

        // 4 Kartu Statistik sesuai tampilan
        $totalPenerimaan = Receiving::where('id_sppg', $idSppg)->count();

        $menungguCek = Receiving::where('id_sppg', $idSppg)
            ->where('status', 'draft')
            ->count();

        $terkonfirmasi = Receiving::where('id_sppg', $idSppg)
            ->where('status', 'confirmed')
            ->count();

        $totalItemMasuk = (int) DB::table('receiving_items')
            ->join('receivings', 'receiving_items.receiving_id', '=', 'receivings.id')
            ->where('receivings.id_sppg', $idSppg)
            ->where('receivings.status', 'confirmed')
            ->count();

        $stats = [
            'total_penerimaan' => $totalPenerimaan,
            'menunggu_cek' => $menungguCek,
            'terkonfirmasi' => $terkonfirmasi,
            'total_item_masuk' => $totalItemMasuk,
        ];

        // Daftar PO yang siap diterima (status 'ordered' atau 'received_partial')
        $receivablePurchases = Purchase::query()
            ->with(['supplier', 'items.material'])
            ->where('id_sppg', $idSppg)
            ->whereIn('status', ['ordered', 'received_partial'])
            ->orderByDesc('purchase_date')
            ->get();

        $selectedPurchaseId = $request->input('purchase_id');
        if ($selectedPurchaseId) {
            $specificPo = Purchase::where('id_sppg', $idSppg)->with(['supplier', 'items.material'])->find($selectedPurchaseId);
            if ($specificPo && ! $receivablePurchases->contains('id', $specificPo->id)) {
                $receivablePurchases->prepend($specificPo);
            }
        }

        $currentStaff = $this->getAuthenticatedStaff();
        $autoReceivingNo = Receiving::generateReceivingNo($idSppg, date('Y-m-d'));
        $defaultReceivedAt = Carbon::now()->format('Y-m-d\TH:i');

        return view('staff.receivings.index', compact(
            'receivings',
            'stats',
            'receivablePurchases',
            'currentStaff',
            'autoReceivingNo',
            'defaultReceivedAt',
            'q',
            'status',
            'dateFrom',
            'dateTo',
            'selectedPurchaseId'
        ));
    }

    /**
     * Redirect create ke form tambah di index dengan pre-selected purchase_id.
     */
    public function create(Request $request): RedirectResponse
    {
        $params = [];
        if ($request->has('purchase_id')) {
            $params['purchase_id'] = $request->input('purchase_id');
        }

        return redirect()->route('staff.receivings.index', $params);
    }

    /**
     * Ambil item-item dari sebuah Nota Pembelian (PO) yang masih bersisa.
     */
    public function getPurchaseItems(int $id): JsonResponse
    {
        $idSppg = $this->resolveSppgId();

        $purchase = Purchase::query()
            ->with(['supplier', 'items.material'])
            ->where('id_sppg', $idSppg)
            ->findOrFail($id);

        $items = $purchase->items->map(function (PurchaseItem $item) {
            $qtyReceivedTotal = $item->qty_received_total;
            $qtyRemaining = max(0, (float) $item->qty_ordered - $qtyReceivedTotal);

            return [
                'purchase_item_id' => $item->id,
                'material_id' => $item->material_id,
                'material_name' => $item->material?->nama ?? 'Bahan Tanpa Nama',
                'satuan' => $item->material?->satuan ?? 'kg',
                'qty_ordered' => (float) $item->qty_ordered,
                'qty_received_total' => $qtyReceivedTotal,
                'qty_remaining' => $qtyRemaining,
                'unit_price' => (float) $item->unit_price,
            ];
        })->filter(function ($item) {
            return $item['qty_remaining'] > 0;
        })->values();

        return response()->json([
            'success' => true,
            'purchase' => [
                'id' => $purchase->id,
                'purchase_no' => $purchase->purchase_no,
                'supplier_name' => $purchase->supplier?->nama ?? '-',
            ],
            'items' => $items,
        ]);
    }

    /**
     * Tampilkan detail penerimaan (View standalone atau JSON untuk drawer).
     */
    public function show(Request $request, int $id): JsonResponse|View
    {
        $idSppg = $this->resolveSppgId();

        $receiving = Receiving::query()
            ->with([
                'purchase.supplier',
                'receiver',
                'items.material',
                'items.purchaseItem',
                'items.lot',
                'evidences',
            ])
            ->where('id_sppg', $idSppg)
            ->findOrFail($id);

        if ($request->ajax() || $request->wantsJson() || $request->header('Accept') === 'application/json' || $request->query('format') === 'json') {
            $items = $receiving->items->map(function (ReceivingItem $item, int $index) {
                $lot = $item->lot;

                return [
                    'no' => $index + 1,
                    'material_name' => $item->material?->nama ?? 'Bahan',
                    'satuan' => $item->material?->satuan ?? 'kg',
                    'qty_ordered' => (float) ($item->purchaseItem?->qty_ordered ?? 0),
                    'qty_received' => (float) $item->qty_received,
                    'expiry_date' => $item->expiry_date_input ? Carbon::parse($item->expiry_date_input)->format('d - m - Y') : '-',
                    'kondisi_fisik' => $item->kondisi_dilaporkan ?: 'Segar, Dingin',
                    'lot_number' => $lot?->lot_number ?? $item->lot_number_input,
                    'barcode' => $lot?->barcode ?? null,
                    'lot_id' => $lot?->id ?? null,
                ];
            });

            $evidences = $receiving->evidences->map(function (Evidence $ev) {
                return [
                    'id' => $ev->id,
                    'url' => asset('storage/' . $ev->file_path),
                    'file_name' => basename($ev->file_path),
                    'description' => $ev->description,
                ];
            });

            return response()->json([
                'success' => true,
                'receiving' => [
                    'id' => $receiving->id,
                    'receiving_no' => $receiving->receiving_no,
                    'purchase_no' => $receiving->purchase?->purchase_no ?? '-',
                    'supplier_name' => $receiving->purchase?->supplier?->nama ?? '-',
                    'received_at' => $receiving->received_at ? $receiving->received_at->format('d-m-Y H:i') : '-',
                    'confirmed_at' => $receiving->confirmed_at ? $receiving->confirmed_at->format('d-m-Y H:i') : '-',
                    'receiver_name' => ($receiving->receiver?->nama ?? 'Staff') . ' (Staff SPPG)',
                    'status' => $receiving->status,
                    'status_label' => $receiving->status_label,
                    'status_badge_class' => $receiving->status_badge_class,
                    'is_locked' => $receiving->is_locked,
                    'can_edit' => $receiving->canBeEdited(),
                    'can_confirm' => $receiving->canBeConfirmed(),
                    'can_void' => $receiving->canBeVoided(),
                ],
                'items' => $items,
                'evidences' => $evidences,
            ]);
        }

        return view('staff.receivings.show', compact('receiving'));
    }

    /**
     * Simpan Penerimaan Baru (Draft atau Langsung Konfirmasi).
     */
    public function store(Request $request): JsonResponse|RedirectResponse
    {
        $idSppg = $this->resolveSppgId();
        $staff = $this->getAuthenticatedStaff();

        $action = $request->input('action', 'confirm'); // 'draft' atau 'confirm'
        if (! in_array($action, ['draft', 'confirm'], true)) {
            $action = 'confirm';
        }

        $request->validate([
            'purchase_id' => 'required|integer|exists:purchases,id',
            'received_at' => 'required|date',
            'items' => 'required|array|min:1',
            'items.*.purchase_item_id' => 'required|integer|exists:purchase_items,id',
            'items.*.material_id' => 'required|integer|exists:materials,id',
            'items.*.qty_received' => 'required|numeric|gt:0',
        ]);

        $purchase = Purchase::where('id_sppg', $idSppg)
            ->whereIn('status', ['ordered', 'received_partial'])
            ->findOrFail($request->input('purchase_id'));

        $itemsData = $request->input('items', []);

        // Validasi per-baris item & sisa pesanan
        foreach ($itemsData as $idx => $row) {
            $pItem = PurchaseItem::where('purchase_id', $purchase->id)->findOrFail($row['purchase_item_id']);
            $sisa = $pItem->qty_remaining;

            if ((float) $row['qty_received'] > $sisa) {
                $msg = "Jumlah terima untuk '{$pItem->material?->nama}' melebihi sisa pesanan ({$sisa} {$pItem->material?->satuan}).";
                if ($request->ajax() || $request->wantsJson() || $request->header('Accept') === 'application/json' || $request->query('format') === 'json') {
                    return response()->json(['success' => false, 'message' => $msg], 422);
                }

                return back()->withInput()->with('error', $msg);
            }

            if ($action === 'confirm') {
                if (empty($row['lot_number_input'])) {
                    $row['lot_number_input'] = 'LOT-' . date('Ymd') . '-' . ($idx + 1);
                }

                if (empty($row['expiry_date_input'])) {
                    $msg = "Tanggal kedaluwarsa untuk '{$pItem->material?->nama}' wajib diisi saat konfirmasi.";
                    if ($request->ajax() || $request->wantsJson() || $request->header('Accept') === 'application/json' || $request->query('format') === 'json') {
                        return response()->json(['success' => false, 'message' => $msg], 422);
                    }

                    return back()->withInput()->with('error', $msg);
                }

                $expiryDate = Carbon::parse($row['expiry_date_input']);
                $receivedDate = Carbon::parse($request->input('received_at'));
                if ($expiryDate->lt($receivedDate->startOfDay())) {
                    $msg = "Tanggal kedaluwarsa untuk '{$pItem->material?->nama}' harus setelah tanggal terima.";
                    if ($request->ajax() || $request->wantsJson() || $request->header('Accept') === 'application/json' || $request->query('format') === 'json') {
                        return response()->json(['success' => false, 'message' => $msg], 422);
                    }

                    return back()->withInput()->with('error', $msg);
                }
            }
        }

        // Jika langsung konfirmasi, wajib minimal 1 foto bukti kedatangan
        if ($action === 'confirm' && ! $request->hasFile('evidence_files')) {
            $msg = 'Lampirkan minimal satu foto nota atau fisik barang saat melakukan konfirmasi.';
            if ($request->ajax() || $request->wantsJson() || $request->header('Accept') === 'application/json' || $request->query('format') === 'json') {
                return response()->json(['success' => false, 'message' => $msg], 422);
            }

            return back()->withInput()->with('error', $msg);
        }

        // DB Transaction - Satu transaksi utuh
        $receiving = DB::transaction(function () use ($request, $purchase, $idSppg, $staff, $action, $itemsData) {
            $receivedAt = Carbon::parse($request->input('received_at'));
            $receivingNo = Receiving::generateReceivingNo($idSppg, $receivedAt->toDateString());

            $isConfirmed = ($action === 'confirm');

            $receiving = Receiving::create([
                'purchase_id' => $purchase->id,
                'id_sppg' => $idSppg,
                'received_by' => $staff?->id,
                'receiving_no' => $receivingNo,
                'received_at' => $receivedAt,
                'status' => $isConfirmed ? 'confirmed' : 'draft',
                'is_locked' => $isConfirmed,
                'confirmed_at' => $isConfirmed ? now() : null,
            ]);

            // Simpan foto bukti jika diunggah
            if ($request->hasFile('evidence_files')) {
                foreach ($request->file('evidence_files') as $file) {
                    $path = $file->store('evidences/receivings', 'public');
                    Evidence::create([
                        'uploaded_by' => $staff?->id,
                        'evidenceable_type' => 'receivings',
                        'evidenceable_id' => $receiving->id,
                        'file_path' => $path,
                        'file_type' => $file->getClientMimeType(),
                        'description' => 'Foto bukti penerimaan fisik / timbangan barang',
                        'is_public' => false,
                        'uploaded_at' => now(),
                    ]);
                }
            }

            // Simpan item-item penerimaan
            foreach ($itemsData as $idx => $row) {
                $pItem = PurchaseItem::findOrFail($row['purchase_item_id']);

                $rItem = ReceivingItem::create([
                    'receiving_id' => $receiving->id,
                    'purchase_item_id' => $pItem->id,
                    'material_id' => $pItem->material_id,
                    'qty_received' => $row['qty_received'],
                    'lot_number_input' => $row['lot_number_input'] ?? null,
                    'expiry_date_input' => ! empty($row['expiry_date_input']) ? $row['expiry_date_input'] : null,
                    'kondisi_dilaporkan' => $row['kondisi_dilaporkan'] ?? 'Kemasan utuh, segar',
                ]);

                // Jika terkonfirmasi, terbitkan lot bahan dan pergerakan stok (RECEIVE_IN)
                if ($isConfirmed) {
                    $barcode = MaterialLot::generateBarcode($idSppg, $receivedAt->toDateString());
                    $lotNo = ! empty($rItem->lot_number_input) ? $rItem->lot_number_input : $barcode;

                    $lot = MaterialLot::create([
                        'receiving_item_id' => $rItem->id,
                        'material_id' => $rItem->material_id,
                        'supplier_id' => $purchase->supplier_id,
                        'id_sppg' => $idSppg,
                        'lot_number' => $lotNo,
                        'barcode' => $barcode,
                        'received_date' => $receivedAt->toDateString(),
                        'expiry_date' => $rItem->expiry_date_input,
                        'initial_qty' => $rItem->qty_received,
                        'current_qty' => $rItem->qty_received,
                        'unit_cost' => $pItem->unit_price,
                        'status' => 'available',
                        'is_locked' => true,
                    ]);

                    StockMovement::create([
                        'material_lot_id' => $lot->id,
                        'created_by' => $staff?->id,
                        'movement_type' => 'RECEIVE_IN',
                        'direction' => 'IN',
                        'qty' => $rItem->qty_received,
                        'reference_type' => 'receiving_items',
                        'reference_id' => $rItem->id,
                        'created_at' => now(),
                    ]);
                }
            }

            // Jika terkonfirmasi, hitung ulang status PO induk
            if ($isConfirmed) {
                $this->recalculatePurchaseStatus($purchase);
                AuditTrail::catat('confirm', $receiving, null, [
                    'receiving_no' => $receiving->receiving_no,
                    'purchase_no' => $purchase->purchase_no,
                    'status' => 'confirmed',
                ]);
            } else {
                AuditTrail::catat('create', $receiving, null, [
                    'receiving_no' => $receiving->receiving_no,
                    'purchase_no' => $purchase->purchase_no,
                    'status' => 'draft',
                ]);
            }

            return $receiving;
        });

        $message = $action === 'confirm'
            ? "Penerimaan {$receiving->receiving_no} berhasil dikonfirmasi dan stok telah bertambah."
            : "Draft penerimaan {$receiving->receiving_no} berhasil disimpan.";

        if ($request->ajax() || $request->wantsJson() || $request->header('Accept') === 'application/json' || $request->query('format') === 'json') {
            return response()->json([
                'success' => true,
                'message' => $message,
                'receiving_id' => $receiving->id,
            ]);
        }

        return redirect()->route('staff.receivings.index')->with('success', $message);
    }

    /**
     * Konfirmasi penerimaan yang masih berstatus draft.
     */
    public function confirm(Request $request, int $id): JsonResponse|RedirectResponse
    {
        $idSppg = $this->resolveSppgId();
        $staff = $this->getAuthenticatedStaff();

        $receiving = Receiving::query()
            ->with(['purchase', 'items.purchaseItem', 'evidences'])
            ->where('id_sppg', $idSppg)
            ->findOrFail($id);

        if ($receiving->status !== 'draft') {
            $msg = 'Dokumen penerimaan ini sudah dikonfirmasi atau telah dibatalkan.';
            if ($request->ajax() || $request->wantsJson() || $request->header('Accept') === 'application/json' || $request->query('format') === 'json') {
                return response()->json(['success' => false, 'message' => $msg], 422);
            }

            return back()->with('error', $msg);
        }

        // Validasi evidence minimal 1
        $hasExistingEvidence = $receiving->evidences()->exists();
        $hasNewEvidence = $request->hasFile('evidence_files');
        if (! $hasExistingEvidence && ! $hasNewEvidence) {
            $msg = 'Lampirkan minimal satu foto nota atau fisik barang saat konfirmasi.';
            if ($request->ajax() || $request->wantsJson() || $request->header('Accept') === 'application/json' || $request->query('format') === 'json') {
                return response()->json(['success' => false, 'message' => $msg], 422);
            }

            return back()->with('error', $msg);
        }

        DB::transaction(function () use ($request, $receiving, $idSppg, $staff) {
            // Simpan foto baru jika ada
            if ($request->hasFile('evidence_files')) {
                foreach ($request->file('evidence_files') as $file) {
                    $path = $file->store('evidences/receivings', 'public');
                    Evidence::create([
                        'uploaded_by' => $staff?->id,
                        'evidenceable_type' => 'receivings',
                        'evidenceable_id' => $receiving->id,
                        'file_path' => $path,
                        'file_type' => $file->getClientMimeType(),
                        'description' => 'Foto bukti penerimaan fisik / timbangan barang',
                        'is_public' => false,
                        'uploaded_at' => now(),
                    ]);
                }
            }

            $receiving->update([
                'status' => 'confirmed',
                'is_locked' => true,
                'confirmed_at' => now(),
            ]);

            foreach ($receiving->items as $idx => $rItem) {
                $pItem = $rItem->purchaseItem;
                $receivedDate = $receiving->received_at ? $receiving->received_at->toDateString() : date('Y-m-d');
                $barcode = MaterialLot::generateBarcode($idSppg, $receivedDate);
                $lotNo = $rItem->lot_number_input ?: $barcode;

                $expiryDate = $rItem->expiry_date_input ?: Carbon::parse($receivedDate)->addDays(14)->toDateString();

                $lot = MaterialLot::create([
                    'receiving_item_id' => $rItem->id,
                    'material_id' => $rItem->material_id,
                    'supplier_id' => $receiving->purchase->supplier_id,
                    'id_sppg' => $idSppg,
                    'lot_number' => $lotNo,
                    'barcode' => $barcode,
                    'received_date' => $receivedDate,
                    'expiry_date' => $expiryDate,
                    'initial_qty' => $rItem->qty_received,
                    'current_qty' => $rItem->qty_received,
                    'unit_cost' => $pItem?->unit_price ?? 0,
                    'status' => 'available',
                    'is_locked' => true,
                ]);

                StockMovement::create([
                    'material_lot_id' => $lot->id,
                    'created_by' => $staff?->id,
                    'movement_type' => 'RECEIVE_IN',
                    'direction' => 'IN',
                    'qty' => $rItem->qty_received,
                    'reference_type' => 'receiving_items',
                    'reference_id' => $rItem->id,
                    'created_at' => now(),
                ]);
            }

            $this->recalculatePurchaseStatus($receiving->purchase);

            AuditTrail::catat('confirm', $receiving, ['status' => 'draft'], ['status' => 'confirmed']);
        });

        $msg = "Penerimaan {$receiving->receiving_no} berhasil dikonfirmasi.";
        if ($request->ajax() || $request->wantsJson() || $request->header('Accept') === 'application/json' || $request->query('format') === 'json') {
            return response()->json(['success' => true, 'message' => $msg]);
        }

        return redirect()->route('staff.receivings.index')->with('success', $msg);
    }

    /**
     * Batalkan penerimaan yang berstatus draft (voided tanpa dampak stok).
     */
    public function void(Request $request, int $id): JsonResponse|RedirectResponse
    {
        $idSppg = $this->resolveSppgId();

        $receiving = Receiving::where('id_sppg', $idSppg)->findOrFail($id);

        if ($receiving->status !== 'draft') {
            $msg = 'Penerimaan yang sudah dikonfirmasi hanya dapat dibatalkan melalui pengajuan Koreksi.';
            if ($request->ajax() || $request->wantsJson() || $request->header('Accept') === 'application/json' || $request->query('format') === 'json') {
                return response()->json(['success' => false, 'message' => $msg], 422);
            }

            return back()->with('error', $msg);
        }

        DB::transaction(function () use ($receiving) {
            $receiving->update([
                'status' => 'voided',
            ]);

            AuditTrail::catat('void', $receiving, ['status' => 'draft'], ['status' => 'voided']);
        });

        $msg = "Penerimaan {$receiving->receiving_no} berhasil dibatalkan.";
        if ($request->ajax() || $request->wantsJson() || $request->header('Accept') === 'application/json' || $request->query('format') === 'json') {
            return response()->json(['success' => true, 'message' => $msg]);
        }

        return redirect()->route('staff.receivings.index')->with('success', $msg);
    }

    /**
     * Cetak label barcode untuk satu lot material.
     */
    public function printBarcode(int $id, int $lotId): View
    {
        $idSppg = $this->resolveSppgId();

        $receiving = Receiving::query()
            ->with(['purchase.supplier', 'sppg'])
            ->where('id_sppg', $idSppg)
            ->findOrFail($id);

        $lot = MaterialLot::query()
            ->with(['material', 'supplier'])
            ->where('id_sppg', $idSppg)
            ->findOrFail($lotId);

        $sppg = $receiving->sppg;

        return view('staff.receivings.barcode', [
            'receiving' => $receiving,
            'lots' => collect([$lot]),
            'sppg' => $sppg,
        ]);
    }

    /**
     * Cetak semua label barcode untuk sebuah penerimaan.
     */
    public function printAllBarcodes(int $id): View
    {
        $idSppg = $this->resolveSppgId();

        $receiving = Receiving::query()
            ->with(['purchase.supplier', 'sppg', 'items.lot.material', 'items.lot.supplier'])
            ->where('id_sppg', $idSppg)
            ->findOrFail($id);

        $lots = $receiving->items->map(fn ($item) => $item->lot)->filter()->values();
        $sppg = $receiving->sppg;

        return view('staff.receivings.barcode', [
            'receiving' => $receiving,
            'lots' => $lots,
            'sppg' => $sppg,
        ]);
    }

    /**
     * Hitung ulang status PO induk berdasarkan total barang yang telah diterima.
     */
    protected function recalculatePurchaseStatus(Purchase $purchase): void
    {
        $items = $purchase->items()->get();
        if ($items->isEmpty()) {
            return;
        }

        $allFulfilled = true;
        $anyReceived = false;

        foreach ($items as $item) {
            $totalReceived = (float) DB::table('receiving_items')
                ->join('receivings', 'receiving_items.receiving_id', '=', 'receivings.id')
                ->where('receiving_items.purchase_item_id', $item->id)
                ->where('receivings.status', 'confirmed')
                ->sum('receiving_items.qty_received');

            if ($totalReceived > 0) {
                $anyReceived = true;
            }

            if ($totalReceived < (float) $item->qty_ordered) {
                $allFulfilled = false;
            }
        }

        $newStatus = $purchase->status;
        if ($allFulfilled) {
            $newStatus = 'received';
        } elseif ($anyReceived) {
            $newStatus = 'received_partial';
        }

        if ($newStatus !== $purchase->status) {
            $old = $purchase->status;
            $purchase->update(['status' => $newStatus]);
            AuditTrail::catat('toggle_status', $purchase, ['status' => $old], ['status' => $newStatus]);
        }
    }

    /**
     * Ambil staff login aktif.
     */
    protected function getAuthenticatedStaff(): ?Staff
    {
        if (Auth::guard('staff')->check()) {
            return Auth::guard('staff')->user();
        }

        if (Auth::check()) {
            $user = Auth::user();
            $staff = Staff::where('id_user', $user->id_user)->first();
            if ($staff) {
                return $staff;
            }
        }

        return Staff::first();
    }

    /**
     * Resolve ID SPPG aktif.
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

    /**
     * Pastikan contoh data penerimaan tersedia agar tampilan langsung berisi data sesuai mockup.
     */
    private function ensureSampleReceivings(int $idSppg): void
    {
        if (Receiving::where('id_sppg', $idSppg)->count() > 0) {
            return;
        }

        $purchase = Purchase::where('id_sppg', $idSppg)->with('items.material')->first();
        if (! $purchase) {
            return;
        }

        $staff = $this->getAuthenticatedStaff();

        // Buat 1 Penerimaan Terkonfirmasi persis seperti di Gambar 1 Mockup
        $rcv1 = Receiving::create([
            'purchase_id' => $purchase->id,
            'id_sppg' => $idSppg,
            'received_by' => $staff?->id,
            'receiving_no' => 'PO-20260927-009',
            'received_at' => Carbon::parse('2026-10-25 08:30:00'),
            'status' => 'confirmed',
            'is_locked' => true,
            'confirmed_at' => Carbon::parse('2026-10-25 08:30:00'),
        ]);

        $lotsInfo = [
            ['barcode' => 'LOT-SPPG01-20261027-001', 'kondisi' => 'Segar, Dingin'],
            ['barcode' => 'LOT-SPPG01-20261027-002', 'kondisi' => 'Segar, Dingin'],
            ['barcode' => 'LOT-SPPG01-20261027-003', 'kondisi' => 'Segar, Dingin'],
        ];

        foreach ($purchase->items as $idx => $pItem) {
            $info = $lotsInfo[$idx] ?? ['barcode' => "LOT-SPPG01-20261027-00" . ($idx + 1), 'kondisi' => 'Segar, Dingin'];

            $rItem = ReceivingItem::create([
                'receiving_id' => $rcv1->id,
                'purchase_item_id' => $pItem->id,
                'material_id' => $pItem->material_id,
                'qty_received' => $pItem->qty_ordered,
                'lot_number_input' => $info['barcode'],
                'expiry_date_input' => Carbon::parse('2026-11-03'),
                'kondisi_dilaporkan' => $info['kondisi'],
            ]);

            $lot = MaterialLot::create([
                'receiving_item_id' => $rItem->id,
                'material_id' => $rItem->material_id,
                'supplier_id' => $purchase->supplier_id,
                'id_sppg' => $idSppg,
                'lot_number' => $info['barcode'],
                'barcode' => $info['barcode'],
                'received_date' => Carbon::parse('2026-10-25')->toDateString(),
                'expiry_date' => Carbon::parse('2026-11-03')->toDateString(),
                'initial_qty' => $rItem->qty_received,
                'current_qty' => $rItem->qty_received,
                'unit_cost' => $pItem->unit_price,
                'status' => 'available',
                'is_locked' => true,
            ]);

            StockMovement::create([
                'material_lot_id' => $lot->id,
                'created_by' => $staff?->id,
                'movement_type' => 'RECEIVE_IN',
                'direction' => 'IN',
                'qty' => $rItem->qty_received,
                'reference_type' => 'receiving_items',
                'reference_id' => $rItem->id,
                'created_at' => Carbon::parse('2026-10-25 08:30:00'),
            ]);
        }
    }
}
