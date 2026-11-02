<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\AuditTrail;
use App\Models\Material;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\Staff;
use App\Models\Supplier;
use App\Support\Keys;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PurchaseController extends Controller
{
    /**
     * Tampilkan daftar pembelian bahan beserta data modal popup.
     */
    public function index(Request $request): View
    {
        $idSppg = $this->resolveSppgId();
        $this->ensureSampleSuppliersAndMaterials($idSppg);

        $q = trim((string) $request->input('q', ''));
        $status = trim((string) $request->input('status', ''));

        $query = Purchase::query()
            ->with(['supplier', 'items.material', 'creator', 'receivings.receiver', 'receivings.items.material'])
            ->where('id_sppg', $idSppg);

        if ($q !== '') {
            $query->where(function ($sub) use ($q) {
                $sub->where('purchase_no', 'ilike', "%{$q}%")
                    ->orWhereHas('supplier', fn ($sq) => $sq->where('nama', 'ilike', "%{$q}%"));
            });
        }

        if ($status !== '' && in_array($status, ['draft', 'ordered', 'received_partial', 'received', 'cancelled'], true)) {
            $query->where('status', $status);
        }

        $purchases = $query->orderByDesc('purchase_date')
            ->orderByDesc('id')
            ->paginate(10)
            ->withQueryString();

        // 4 Kartu Statistik sesuai tampilan
        $totalPembelian = Purchase::where('id_sppg', $idSppg)->count();

        $menungguCek = Purchase::where('id_sppg', $idSppg)
            ->whereIn('status', ['draft', 'ordered'])
            ->count();

        $selesaiDiterima = Purchase::where('id_sppg', $idSppg)
            ->where('status', 'received')
            ->count();

        $totalBelanja = (float) DB::table('purchase_items')
            ->join('purchases', 'purchase_items.purchase_id', '=', 'purchases.id')
            ->where('purchases.id_sppg', $idSppg)
            ->where('purchases.status', '!=', 'cancelled')
            ->selectRaw('SUM(purchase_items.qty_ordered * purchase_items.unit_price) as total')
            ->value('total') ?? 0;

        $stats = [
            'total_pembelian' => $totalPembelian,
            'menunggu_cek' => $menungguCek,
            'selesai_diterima' => $selesaiDiterima,
            'total_belanja' => $totalBelanja,
            'formatted_total_belanja' => 'Rp ' . number_format($totalBelanja, 0, ',', '.'),
        ];

        // Pemasok dan Bahan Aktif untuk form pop-up di tengah
        $suppliers = Supplier::where('id_sppg', $idSppg)
            ->where('status', 'active')
            ->orderBy('nama')
            ->get();

        $materials = Material::where('id_sppg', $idSppg)
            ->where('is_active', true)
            ->orderBy('nama')
            ->get();

        $autoPurchaseNo = Purchase::generatePurchaseNo($idSppg, date('Y-m-d'));
        $today = date('Y-m-d');

        return view('staff.purchases.index', compact(
            'purchases',
            'stats',
            'suppliers',
            'materials',
            'autoPurchaseNo',
            'today',
            'q',
            'status'
        ));
    }

    /**
     * Form pembuatan nota pembelian (redirect ke index dengan trigger modal).
     */
    public function create(): RedirectResponse
    {
        return redirect()->route('staff.purchases.index', ['action' => 'create']);
    }

    /**
     * Simpan nota pembelian baru dari modal pop-up.
     */
    public function store(Request $request): RedirectResponse
    {
        $idSppg = $this->resolveSppgId();
        $staff = $this->getAuthenticatedStaff();

        $validated = $request->validate([
            'supplier_id' => ['required', 'exists:suppliers,id'],
            'purchase_date' => ['required', 'date'],
            'action' => ['nullable', 'in:draft,order'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.material_id' => ['required', 'distinct', 'exists:materials,id'],
            'items.*.qty_ordered' => ['required', 'numeric', 'gt:0'],
            'items.*.unit_price' => ['required', 'numeric', 'gte:0'],
        ], [
            'supplier_id.required' => 'Pemasok wajib dipilih.',
            'items.required' => 'Minimal satu item bahan harus ditambahkan.',
            'items.min' => 'Minimal satu item bahan harus ditambahkan.',
            'items.*.material_id.distinct' => 'Bahan sudah ada di daftar. Satu bahan hanya boleh satu baris.',
            'items.*.qty_ordered.gt' => 'Jumlah pesanan harus lebih besar dari 0.',
            'items.*.unit_price.gte' => 'Harga satuan harus bernilai minimal Rp 0.',
        ]);

        $status = ($request->input('action') === 'order') ? 'ordered' : 'draft';

        // Pencegahan Duplikasi: Jika form terkirim dua kali (double submit) dalam selang 5 detik terakhir
        $recentDuplicate = Purchase::where('id_sppg', $idSppg)
            ->where('supplier_id', $validated['supplier_id'])
            ->where('purchase_date', $validated['purchase_date'])
            ->where('created_at', '>=', now()->subSeconds(5))
            ->first();

        if ($recentDuplicate) {
            return redirect()->route('staff.purchases.index', ['detail_id' => $recentDuplicate->id])
                ->with('success', "Nota pembelian {$recentDuplicate->purchase_no} berhasil disimpan.");
        }

        $purchaseNo = Purchase::generatePurchaseNo($idSppg, $validated['purchase_date']);

        $purchase = DB::transaction(function () use ($idSppg, $staff, $validated, $status, $purchaseNo) {
            $p = Purchase::create([
                'supplier_id' => $validated['supplier_id'],
                'id_sppg' => $idSppg,
                'created_by' => $staff?->id,
                'purchase_no' => $purchaseNo,
                'purchase_date' => $validated['purchase_date'],
                'status' => $status,
            ]);

            foreach ($validated['items'] as $item) {
                PurchaseItem::create([
                    'purchase_id' => $p->id,
                    'material_id' => $item['material_id'],
                    'qty_ordered' => $item['qty_ordered'],
                    'unit_price' => $item['unit_price'],
                ]);
            }

            AuditTrail::catat('create', $p, null, [
                'purchase_no' => $p->purchase_no,
                'supplier_id' => $p->supplier_id,
                'purchase_date' => $p->purchase_date->format('Y-m-d'),
                'status' => $status,
                'items_count' => count($validated['items']),
            ]);

            if ($status === 'ordered') {
                AuditTrail::catat('confirm', $p, ['status' => 'draft'], ['status' => 'ordered']);
            }

            return $p;
        });

        $msg = ($status === 'ordered')
            ? "Nota pembelian {$purchase->purchase_no} berhasil dibuat dan langsung dipesan ke pemasok."
            : "Draft nota pembelian {$purchase->purchase_no} berhasil disimpan.";

        return redirect()->route('staff.purchases.index', ['detail_id' => $purchase->id])->with('success', $msg);
    }

    /**
     * Tampilkan detail nota pembelian (JSON untuk drawer kiri, atau redirect ke index dengan detail_id).
     */
    public function show(Request $request, int $id): JsonResponse|RedirectResponse|View
    {
        $idSppg = $this->resolveSppgId();

        $purchase = Purchase::with([
            'supplier',
            'creator',
            'items.material',
            'receivings.receiver',
            'receivings.items.material',
        ])
            ->where('id_sppg', $idSppg)
            ->findOrFail($id);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'id' => $purchase->id,
                'purchase_no' => $purchase->purchase_no,
                'purchase_date' => $purchase->purchase_date ? $purchase->purchase_date->format('d-m-Y') : '-',
                'purchase_date_raw' => $purchase->purchase_date ? $purchase->purchase_date->format('Y-m-d') : '',
                'status' => $purchase->status,
                'status_label' => $purchase->status_label,
                'status_badge_class' => $purchase->status_badge_class,
                'can_be_edited' => $purchase->canBeEdited(),
                'can_be_ordered' => $purchase->canBeOrdered(),
                'can_be_cancelled' => $purchase->canBeCancelled(),
                'can_receive' => $purchase->canReceive(),
                'total_cost' => $purchase->total_cost,
                'formatted_total_cost' => $purchase->formatted_total_cost,
                'supplier' => [
                    'id' => $purchase->supplier->id ?? null,
                    'nama' => $purchase->supplier->nama ?? '-',
                    'alamat' => $purchase->supplier->alamat ?? '-',
                    'kontak' => $purchase->supplier->kontak ?? '-',
                ],
                'creator_nama' => $purchase->creator->nama ?? (auth('staff')->user()->nama ?? 'Staff'),
                'items' => $purchase->items->map(fn (PurchaseItem $item) => [
                    'id' => $item->id,
                    'material_id' => $item->material_id,
                    'material_nama' => $item->material->nama ?? 'Bahan',
                    'material_kode' => $item->material->kode ?? '',
                    'satuan' => $item->material->satuan ?? '',
                    'qty_ordered' => (float) $item->qty_ordered,
                    'qty_ordered_formatted' => rtrim(rtrim(number_format((float) $item->qty_ordered, 3, ',', '.'), '0'), ','),
                    'qty_received_total' => (float) $item->qty_received_total,
                    'qty_received_formatted' => rtrim(rtrim(number_format((float) $item->qty_received_total, 3, ',', '.'), '0'), ','),
                    'qty_remaining' => (float) $item->qty_remaining,
                    'qty_remaining_formatted' => rtrim(rtrim(number_format((float) $item->qty_remaining, 3, ',', '.'), '0'), ','),
                    'unit_price' => (float) $item->unit_price,
                    'formatted_unit_price' => $item->formatted_unit_price,
                    'subtotal' => $item->subtotal,
                    'formatted_subtotal' => $item->formatted_subtotal,
                ]),
                'receivings' => $purchase->receivings->map(fn ($r) => [
                    'receiving_no' => $r->receiving_no,
                    'received_at' => $r->received_at ? $r->received_at->format('d-m-Y H:i') : '-',
                    'receiver_nama' => $r->receiver->nama ?? '-',
                    'status' => ucfirst((string) $r->status),
                    'items_count' => $r->items->count(),
                ]),
                'print_url' => route('staff.purchases.print', $purchase->id),
                'order_url' => route('staff.purchases.order', $purchase->id),
                'cancel_url' => route('staff.purchases.cancel', $purchase->id),
                'update_url' => route('staff.purchases.update', $purchase->id),
            ]);
        }

        return redirect()->route('staff.purchases.index', ['detail_id' => $id]);
    }

    /**
     * Form edit nota pembelian (redirect ke index dengan trigger modal edit).
     */
    public function edit(int $id): RedirectResponse
    {
        return redirect()->route('staff.purchases.index', ['edit_id' => $id]);
    }

    /**
     * Perbarui nota pembelian dari modal pop-up.
     */
    public function update(Request $request, int $id): RedirectResponse
    {
        $idSppg = $this->resolveSppgId();

        $purchase = Purchase::with('items')->where('id_sppg', $idSppg)->findOrFail($id);

        if (! $purchase->canBeEdited()) {
            return redirect()->route('staff.purchases.index')
                ->with('error', 'Nota pembelian yang sudah dipesan tidak dapat diubah.');
        }

        $validated = $request->validate([
            'supplier_id' => ['required', 'exists:suppliers,id'],
            'purchase_date' => ['required', 'date'],
            'action' => ['nullable', 'in:draft,order'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.material_id' => ['required', 'distinct', 'exists:materials,id'],
            'items.*.qty_ordered' => ['required', 'numeric', 'gt:0'],
            'items.*.unit_price' => ['required', 'numeric', 'gte:0'],
        ], [
            'supplier_id.required' => 'Pemasok wajib dipilih.',
            'items.required' => 'Minimal satu item bahan harus ditambahkan.',
            'items.min' => 'Minimal satu item bahan harus ditambahkan.',
            'items.*.material_id.distinct' => 'Bahan sudah ada di daftar. Satu bahan hanya boleh satu baris.',
            'items.*.qty_ordered.gt' => 'Jumlah pesanan harus lebih besar dari 0.',
            'items.*.unit_price.gte' => 'Harga satuan harus bernilai minimal Rp 0.',
        ]);

        $newStatus = ($request->input('action') === 'order') ? 'ordered' : 'draft';

        DB::transaction(function () use ($purchase, $validated, $newStatus) {
            $oldValues = [
                'supplier_id' => $purchase->supplier_id,
                'purchase_date' => $purchase->purchase_date?->format('Y-m-d'),
                'status' => $purchase->status,
                'items_count' => $purchase->items->count(),
            ];

            $purchase->update([
                'supplier_id' => $validated['supplier_id'],
                'purchase_date' => $validated['purchase_date'],
                'status' => $newStatus,
            ]);

            $purchase->items()->delete();
            foreach ($validated['items'] as $item) {
                PurchaseItem::create([
                    'purchase_id' => $purchase->id,
                    'material_id' => $item['material_id'],
                    'qty_ordered' => $item['qty_ordered'],
                    'unit_price' => $item['unit_price'],
                ]);
            }

            $newValues = [
                'supplier_id' => $purchase->supplier_id,
                'purchase_date' => $purchase->purchase_date?->format('Y-m-d'),
                'status' => $newStatus,
                'items_count' => count($validated['items']),
            ];

            AuditTrail::catat('update', $purchase, $oldValues, $newValues);

            if ($newStatus === 'ordered') {
                AuditTrail::catat('confirm', $purchase, ['status' => 'draft'], ['status' => 'ordered']);
            }
        });

        $msg = ($newStatus === 'ordered')
            ? 'Nota pembelian berhasil diperbarui dan dipesan ke pemasok.'
            : 'Perubahan draft nota pembelian berhasil disimpan.';

        return redirect()->route('staff.purchases.index', ['detail_id' => $purchase->id])->with('success', $msg);
    }

    /**
     * Konfirmasi Pesan (status draft -> ordered).
     */
    public function order(int $id): RedirectResponse
    {
        $idSppg = $this->resolveSppgId();
        $purchase = Purchase::where('id_sppg', $idSppg)->findOrFail($id);

        if (! $purchase->canBeOrdered()) {
            return back()->with('error', 'Hanya nota berstatus draft yang dapat dipesan.');
        }

        DB::transaction(function () use ($purchase) {
            $oldStatus = $purchase->status;
            $purchase->update(['status' => 'ordered']);
            AuditTrail::catat('confirm', $purchase, ['status' => $oldStatus], ['status' => 'ordered']);
        });

        return redirect()->route('staff.purchases.index', ['detail_id' => $purchase->id])
            ->with('success', "Nota {$purchase->purchase_no} berhasil dikonfirmasi dan dipesan ke pemasok.");
    }

    /**
     * Batalkan nota pembelian (status -> cancelled) dengan alasan wajib.
     */
    public function cancel(Request $request, int $id): RedirectResponse
    {
        $idSppg = $this->resolveSppgId();
        $purchase = Purchase::where('id_sppg', $idSppg)->findOrFail($id);

        $request->validate([
            'reason' => ['required', 'string', 'min:3', 'max:500'],
        ], [
            'reason.required' => 'Alasan pembatalan wajib diisi.',
            'reason.min' => 'Alasan pembatalan minimal 3 karakter.',
        ]);

        if (! $purchase->canBeCancelled()) {
            return back()->with('error', 'Pembelian tidak dapat dibatalkan karena sudah ada penerimaan yang terkonfirmasi atau sudah dibatalkan sebelumnya.');
        }

        DB::transaction(function () use ($purchase, $request) {
            $oldStatus = $purchase->status;
            $purchase->update(['status' => 'cancelled']);

            AuditTrail::catat(
                'cancel',
                $purchase,
                ['status' => $oldStatus],
                ['status' => 'cancelled', 'alasan_pembatalan' => $request->input('reason')]
            );
        });

        return redirect()->route('staff.purchases.index', ['detail_id' => $purchase->id])
            ->with('success', "Nota {$purchase->purchase_no} berhasil dibatalkan.");
    }

    /**
     * Tampilan cetak nota pembelian (A4 layout).
     */
    public function print(int $id): View
    {
        $idSppg = $this->resolveSppgId();

        $purchase = Purchase::with(['supplier', 'creator', 'items.material', 'sppg'])
            ->where('id_sppg', $idSppg)
            ->findOrFail($id);

        return view('staff.purchases.print', compact('purchase'));
    }

    /**
     * Helper untuk mengambil data staff yang sedang login.
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
     * Resolve SPPG ID dari user/staff aktif.
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
     * Pastikan data pemasok dan bahan sample tersedia jika masih kosong.
     */
    private function ensureSampleSuppliersAndMaterials(int $idSppg): void
    {
        // Pembersihan otomatis jika ada duplikasi record dari double-submit sebelumnya
        $duplicateNoList = Purchase::where('id_sppg', $idSppg)
            ->select('purchase_no', DB::raw('COUNT(*) as total'))
            ->groupBy('purchase_no')
            ->havingRaw('COUNT(*) > 1')
            ->pluck('purchase_no');

        if ($duplicateNoList->isNotEmpty()) {
            foreach ($duplicateNoList as $pNo) {
                $keepId = Purchase::where('id_sppg', $idSppg)->where('purchase_no', $pNo)->min('id');
                $removeIds = Purchase::where('id_sppg', $idSppg)->where('purchase_no', $pNo)->where('id', '!=', $keepId)->pluck('id');
                PurchaseItem::whereIn('purchase_id', $removeIds)->delete();
                Purchase::whereIn('id', $removeIds)->delete();
            }
        }

        if (Supplier::where('id_sppg', $idSppg)->count() === 0) {
            Supplier::create([
                'id_sppg' => $idSppg,
                'nama' => 'Toko Berkah Sayur',
                'kontak' => '081234567890',
                'alamat' => 'Jl. Pasar Induk No. 26',
                'status' => 'active',
            ]);

            Supplier::create([
                'id_sppg' => $idSppg,
                'nama' => 'UD Pangan Makmur',
                'kontak' => '089876543210',
                'alamat' => 'Komplek Pergudangan Cikupa Blok B3',
                'status' => 'active',
            ]);
        }

        $sampleMaterials = [
            ['kode' => 'AYM-BRL', 'nama' => 'Daging Ayam Broiler', 'satuan' => 'kg', 'kategori' => 'Lauk Hewani'],
            ['kode' => 'BRS-RMS', 'nama' => 'Beras Ramos', 'satuan' => 'kg', 'kategori' => 'Bahan Pokok'],
            ['kode' => 'TLR-AYM', 'nama' => 'Telur Ayam Negeri', 'satuan' => 'kg', 'kategori' => 'Lauk Hewani'],
            ['kode' => 'BYM-HJU', 'nama' => 'Bayam Segar Hijau', 'satuan' => 'kg', 'kategori' => 'Sayuran'],
            ['kode' => 'WRT-LKL', 'nama' => 'Wortel Lokal', 'satuan' => 'kg', 'kategori' => 'Sayuran'],
            ['kode' => 'MNY-GRG', 'nama' => 'Minyak Goreng Sawit', 'satuan' => 'liter', 'kategori' => 'Bumbu & Minyak'],
        ];

        foreach ($sampleMaterials as $m) {
            Material::firstOrCreate(
                ['id_sppg' => $idSppg, 'kode' => $m['kode']],
                ['nama' => $m['nama'], 'satuan' => $m['satuan'], 'kategori' => $m['kategori'], 'min_stock' => 10, 'is_active' => true]
            );
        }

        if (Purchase::where('id_sppg', $idSppg)->count() === 0) {
            $supplier = Supplier::where('id_sppg', $idSppg)->first();
            $staff = $this->getAuthenticatedStaff();
            $m1 = Material::where('id_sppg', $idSppg)->where('nama', 'Daging Ayam Broiler')->first();
            $m2 = Material::where('id_sppg', $idSppg)->where('nama', 'Beras Ramos')->first();
            $m3 = Material::where('id_sppg', $idSppg)->where('nama', 'Telur Ayam Negeri')->first() ?: $m1;

            if ($supplier && $m1 && $m2) {
                $statuses = [
                    1 => 'ordered',
                    2 => 'ordered',
                    3 => 'received',
                    4 => 'received',
                    5 => 'ordered',
                    6 => 'ordered',
                    7 => 'received',
                    8 => 'ordered',
                ];

                for ($i = 1; $i <= 8; $i++) {
                    $noStr = sprintf('%03d', $i);
                    $po = Purchase::create([
                        'supplier_id' => $supplier->id,
                        'id_sppg' => $idSppg,
                        'created_by' => $staff?->id,
                        'purchase_no' => "PO-20260927-{$noStr}",
                        'purchase_date' => '2026-10-25',
                        'status' => $statuses[$i] ?? 'ordered',
                    ]);

                    PurchaseItem::create([
                        'purchase_id' => $po->id,
                        'material_id' => $m1->id,
                        'qty_ordered' => 50,
                        'unit_price' => 35000,
                    ]);

                    PurchaseItem::create([
                        'purchase_id' => $po->id,
                        'material_id' => $m2->id,
                        'qty_ordered' => 100,
                        'unit_price' => 14000,
                    ]);

                    PurchaseItem::create([
                        'purchase_id' => $po->id,
                        'material_id' => $m3->id,
                        'qty_ordered' => 25,
                        'unit_price' => 28000,
                    ]);
                }
            }
        }
    }
}
