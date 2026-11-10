<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\AuditTrail;
use App\Models\Purchase;
use App\Models\Staff;
use App\Models\Supplier;
use App\Support\Keys;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class SupplierController extends Controller
{
    /**
     * Tampilkan halaman utama Data Pemasok.
     */
    public function index(Request $request): View
    {
        $idSppg = $this->resolveSppgId();

        $q = trim((string) $request->input('q', ''));
        $status = trim((string) $request->input('status', ''));

        // Query Dasar dengan agregasi:
        // - Jumlah pembelian = Jumlah PO selain draft dan cancelled
        // - Total nilai = Jumlah qty_ordered * unit_price dari PO yang sama
        $query = Supplier::query()
            ->where('id_sppg', $idSppg)
            ->withCount(['purchases as jumlah_pembelian' => function ($sq) {
                $sq->whereNotIn('status', ['draft', 'cancelled']);
            }])
            ->addSelect([
                'total_nilai' => DB::table('purchase_items')
                    ->join('purchases', 'purchases.id', '=', 'purchase_items.purchase_id')
                    ->whereColumn('purchases.supplier_id', 'suppliers.id')
                    ->whereNotIn('purchases.status', ['draft', 'cancelled'])
                    ->selectRaw('COALESCE(SUM(purchase_items.qty_ordered * purchase_items.unit_price), 0)'),
            ]);

        // Filter Pencarian (Hanya Berdasarkan Nama Pemasok)
        if ($q !== '') {
            $query->where(function ($sub) use ($q) {
                $sub->where('nama', 'ilike', "%{$q}%")
                    ->orWhere('nama', 'like', "%{$q}%");
            });
        }

        // Filter Status (Semua / Aktif / Nonaktif)
        if ($status !== '' && ! in_array(strtolower($status), ['', 'semua'], true)) {
            $mappedStatus = in_array(strtolower($status), ['active', 'aktif'], true) ? 'active' : (in_array(strtolower($status), ['inactive', 'nonaktif'], true) ? 'inactive' : null);
            if ($mappedStatus) {
                $query->where('status', $mappedStatus);
            }
        }

        $suppliers = $query->orderByDesc('id')
            ->paginate(7)
            ->withQueryString();

        // 3 Kartu Statistik sesuai Mockup
        $totalPemasok = Supplier::where('id_sppg', $idSppg)->count();
        $statusAktif = Supplier::where('id_sppg', $idSppg)->where('status', 'active')->count();

        $totalPembelian = (float) (DB::table('purchase_items')
            ->join('purchases', 'purchases.id', '=', 'purchase_items.purchase_id')
            ->where('purchases.id_sppg', $idSppg)
            ->whereNotIn('purchases.status', ['draft', 'cancelled'])
            ->selectRaw('COALESCE(SUM(purchase_items.qty_ordered * purchase_items.unit_price), 0) as total')
            ->value('total') ?? 0);

        $stats = [
            'total_pemasok' => $totalPemasok,
            'status_aktif' => $statusAktif,
            'total_pembelian' => $totalPembelian,
            'formatted_total_pembelian' => 'Rp. '.number_format($totalPembelian, 0, ',', '.'),
        ];

        return view('staff.pemasok.index', compact('suppliers', 'stats', 'q', 'status'));
    }

    /**
     * Ambil data detail pemasok beserta riwayat PO untuk panel detail (JSON).
     */
    public function show(int $id): JsonResponse
    {
        $idSppg = $this->resolveSppgId();

        $supplier = Supplier::where('id_sppg', $idSppg)
            ->withCount(['purchases as jumlah_pembelian' => function ($sq) {
                $sq->whereNotIn('status', ['draft', 'cancelled']);
            }])
            ->addSelect([
                'total_nilai' => DB::table('purchase_items')
                    ->join('purchases', 'purchases.id', '=', 'purchase_items.purchase_id')
                    ->whereColumn('purchases.supplier_id', 'suppliers.id')
                    ->whereNotIn('purchases.status', ['draft', 'cancelled'])
                    ->selectRaw('COALESCE(SUM(purchase_items.qty_ordered * purchase_items.unit_price), 0)'),
            ])
            ->findOrFail($id);

        $purchases = Purchase::where('supplier_id', $supplier->id)
            ->where('id_sppg', $idSppg)
            ->whereNotIn('status', ['draft', 'cancelled'])
            ->with(['items.material'])
            ->orderByDesc('purchase_date')
            ->orderByDesc('id')
            ->limit(10)
            ->get()
            ->map(fn (Purchase $p) => [
                'id' => $p->id,
                'purchase_no' => $p->purchase_no,
                'purchase_url' => route('staff.purchases.index', ['detail_id' => $p->id]),
                'purchase_date' => $p->purchase_date ? $p->purchase_date->format('d/m/Y') : '-',
                'status' => $p->status,
                'status_label' => $p->status_label,
                'status_badge_class' => $p->status_badge_class,
                'total_items' => $p->items->count(),
                'total_cost' => $p->total_cost,
                'formatted_total_cost' => 'Rp '.number_format($p->total_cost, 0, ',', '.'),
            ]);

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $supplier->id,
                'nama' => $supplier->nama,
                'kontak' => $supplier->kontak,
                'alamat' => $supplier->alamat,
                'status' => $supplier->status,
                'status_label' => $supplier->status === 'active' ? 'Aktif' : 'Nonaktif',
                'jumlah_pembelian' => $supplier->jumlah_pembelian,
                'total_nilai' => (float) $supplier->total_nilai,
                'formatted_total_nilai' => 'Rp '.number_format((float) $supplier->total_nilai, 0, ',', '.'),
                'created_at' => $supplier->created_at ? $supplier->created_at->format('d M Y') : '-',
                'purchases' => $purchases,
            ],
        ]);
    }

    /**
     * Simpan pemasok baru.
     */
    public function store(Request $request): JsonResponse|RedirectResponse
    {
        $idSppg = $this->resolveSppgId();

        $validated = $request->validate([
            'nama' => ['required', 'string', 'max:150'],
            'kontak' => ['required', 'string', 'max:100'],
            'alamat' => ['required', 'string'],
            'status' => ['required', 'in:active,inactive'],
        ], [
            'nama.required' => 'Nama pemasok wajib diisi.',
            'kontak.required' => 'Kontak pemasok wajib diisi.',
            'alamat.required' => 'Alamat pemasok wajib diisi.',
            'status.required' => 'Status pemasok wajib dipilih.',
        ]);

        $supplier = Supplier::create([
            'id_sppg' => $idSppg,
            'nama' => $validated['nama'],
            'kontak' => $validated['kontak'],
            'alamat' => $validated['alamat'],
            'status' => $validated['status'],
        ]);

        AuditTrail::catat('create', $supplier, null, [
            'nama' => $supplier->nama,
            'kontak' => $supplier->kontak,
            'alamat' => $supplier->alamat,
            'status' => $supplier->status,
        ], null, $idSppg);

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Pemasok baru berhasil ditambahkan.',
                'data' => $supplier,
            ]);
        }

        return redirect()->route('staff.suppliers.index')->with('success', 'Pemasok baru berhasil ditambahkan.');
    }

    /**
     * Perbarui data pemasok.
     */
    public function update(Request $request, int $id): JsonResponse|RedirectResponse
    {
        $idSppg = $this->resolveSppgId();
        $supplier = Supplier::where('id_sppg', $idSppg)->findOrFail($id);

        $validated = $request->validate([
            'nama' => ['required', 'string', 'max:150'],
            'kontak' => ['required', 'string', 'max:100'],
            'alamat' => ['required', 'string'],
            'status' => ['required', 'in:active,inactive'],
        ], [
            'nama.required' => 'Nama pemasok wajib diisi.',
            'kontak.required' => 'Kontak pemasok wajib diisi.',
            'alamat.required' => 'Alamat pemasok wajib diisi.',
            'status.required' => 'Status pemasok wajib dipilih.',
        ]);

        $oldValues = [
            'nama' => $supplier->nama,
            'kontak' => $supplier->kontak,
            'alamat' => $supplier->alamat,
            'status' => $supplier->status,
        ];

        $supplier->update($validated);

        AuditTrail::catat('update', $supplier, $oldValues, [
            'nama' => $supplier->nama,
            'kontak' => $supplier->kontak,
            'alamat' => $supplier->alamat,
            'status' => $supplier->status,
        ], null, $idSppg);

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Data pemasok berhasil diperbarui.',
                'data' => $supplier,
            ]);
        }

        return redirect()->route('staff.suppliers.index')->with('success', 'Data pemasok berhasil diperbarui.');
    }

    /**
     * Beralih status aktif/nonaktif pemasok.
     */
    public function toggleStatus(Request $request, int $id): JsonResponse|RedirectResponse
    {
        $idSppg = $this->resolveSppgId();
        $supplier = Supplier::where('id_sppg', $idSppg)->findOrFail($id);

        $oldStatus = $supplier->status;
        $newStatus = $oldStatus === 'active' ? 'inactive' : 'active';

        $supplier->update(['status' => $newStatus]);

        AuditTrail::catat('toggle_status', $supplier, ['status' => $oldStatus], ['status' => $newStatus], null, $idSppg);

        $statusText = $newStatus === 'active' ? 'diaktifkan' : 'dinonaktifkan';

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => "Pemasok {$supplier->nama} berhasil {$statusText}.",
                'data' => [
                    'id' => $supplier->id,
                    'status' => $newStatus,
                ],
            ]);
        }

        return redirect()->route('staff.suppliers.index')->with('success', "Pemasok {$supplier->nama} berhasil {$statusText}.");
    }

    /**
     * Dapatkan staff yang sedang login.
     */
    protected function getAuthenticatedStaff(): ?Staff
    {
        $user = Auth::guard('staff')->user();
        if ($user instanceof Staff) {
            return $user;
        }

        if (Auth::check()) {
            return Staff::where('id_user', Auth::id())->first();
        }

        return null;
    }

    /**
     * Selesaikan id_sppg aktif.
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
