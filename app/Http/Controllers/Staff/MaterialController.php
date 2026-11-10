<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\AuditTrail;
use App\Models\Material;
use App\Models\MaterialLot;
use App\Models\Staff;
use App\Support\Keys;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class MaterialController extends Controller
{
    /**
     * Tampilkan halaman utama Data Bahan Baku.
     */
    public function index(Request $request): View
    {
        $idSppg = $this->resolveSppgId();

        $q = trim((string) $request->input('q', ''));
        $kategori = trim((string) $request->input('kategori', ''));
        $indikator = trim((string) $request->input('indikator', $request->input('status', '')));

        // Subquery untuk menghitung stok total:
        // Jumlah current_qty lot available yang belum kedaluwarsa (expiry_date >= CURRENT_DATE atau expiry_date IS NULL)
        $stockSubquery = MaterialLot::selectRaw('COALESCE(SUM(current_qty), 0)')
            ->whereColumn('material_lots.material_id', 'materials.id')
            ->whereRaw("material_lots.status = 'available'")
            ->whereRaw('(material_lots.expiry_date IS NULL OR material_lots.expiry_date >= CURRENT_DATE)');

        $query = Material::query()
            ->where('materials.id_sppg', $idSppg)
            ->select('materials.*')
            ->selectSub($stockSubquery, 'total_stok');

        // Filter Pencarian (Kode atau Nama Bahan)
        if ($q !== '') {
            $query->where(function ($sub) use ($q) {
                $sub->whereRaw('LOWER(materials.kode) LIKE ?', ['%'.strtolower($q).'%'])
                    ->orWhereRaw('LOWER(materials.nama) LIKE ?', ['%'.strtolower($q).'%']);
            });
        }

        // Filter Kategori
        if ($kategori !== '' && ! in_array(strtolower($kategori), ['', 'semua', 'semua kategori'], true)) {
            $query->where('materials.kategori', $kategori);
        }

        // Filter Indikator (Aman / Di Bawah Minimum)
        if ($indikator !== '' && ! in_array(strtolower($indikator), ['', 'semua', 'semua status', 'semua indikator'], true)) {
            $indikatorLower = strtolower($indikator);
            $stockSql = '('.$stockSubquery->toSql().')';

            if ($indikatorLower === 'aman') {
                $query->whereRaw("{$stockSql} >= materials.min_stock");
            } elseif (in_array($indikatorLower, ['di bawah minimum', 'di_bawah_minimum', 'menipis', 'habis'], true)) {
                $query->whereRaw("{$stockSql} < materials.min_stock");
            }
        }

        // Urutkan berdasarkan ID
        $materials = $query->orderBy('materials.id', 'asc')
            ->paginate(8)
            ->withQueryString();

        // 4 Kartu Statistik Ringkasan
        $allWithStock = Material::query()
            ->where('materials.id_sppg', $idSppg)
            ->select('materials.id', 'materials.min_stock')
            ->selectSub($stockSubquery, 'total_stok')
            ->get();

        $totalBahan = $allWithStock->count();
        $stokAman = $allWithStock->filter(fn ($m) => (float) $m->total_stok >= (float) $m->min_stock)->count();
        $stokMenipis = $allWithStock->filter(fn ($m) => (float) $m->total_stok < (float) $m->min_stock && (float) $m->total_stok > 0)->count();
        $stokHabis = $allWithStock->filter(fn ($m) => (float) $m->total_stok <= 0)->count();

        $stats = [
            'total_bahan' => $totalBahan,
            'stok_aman' => $stokAman,
            'stok_menipis' => $stokMenipis,
            'stok_habis' => $stokHabis,
        ];

        // Daftar kategori unik untuk dropdown filter
        $availableCategories = Material::where('id_sppg', $idSppg)
            ->whereNotNull('kategori')
            ->where('kategori', '!=', '')
            ->distinct()
            ->orderBy('kategori', 'asc')
            ->pluck('kategori');

        return view('staff.bahan.index', compact(
            'materials',
            'stats',
            'availableCategories',
            'q',
            'kategori',
            'indikator'
        ));
    }

    /**
     * Dapatkan detail satu bahan baku beserta riwayat lot-nya (JSON).
     */
    public function show(int $id): JsonResponse
    {
        $idSppg = $this->resolveSppgId();

        $stockSubquery = MaterialLot::selectRaw('COALESCE(SUM(current_qty), 0)')
            ->whereColumn('material_lots.material_id', 'materials.id')
            ->where('material_lots.status', 'available')
            ->whereRaw('(material_lots.expiry_date IS NULL OR material_lots.expiry_date >= CURRENT_DATE)');

        $material = Material::where('id_sppg', $idSppg)
            ->select('materials.*')
            ->selectSub($stockSubquery, 'total_stok')
            ->findOrFail($id);

        $totalStok = (float) $material->total_stok;
        $minStock = (float) $material->min_stock;

        $isAman = $totalStok >= $minStock;
        $indikatorLabel = $isAman ? 'Aman' : 'Di bawah minimum';
        $indikatorClass = $isAman ? 'chip-aman' : 'chip-di-bawah-min';

        // Ambil riwayat lot untuk bahan ini
        $lots = MaterialLot::where('material_id', $material->id)
            ->with(['supplier:id,nama'])
            ->orderByDesc('received_date')
            ->limit(20)
            ->get()
            ->map(function ($lot) {
                $isExpired = $lot->expiry_date && Carbon::parse($lot->expiry_date)->isPast();

                return [
                    'id' => $lot->id,
                    'lot_number' => $lot->lot_number,
                    'barcode' => $lot->barcode,
                    'supplier_nama' => $lot->supplier?->nama ?? '-',
                    'received_date' => $lot->received_date ? Carbon::parse($lot->received_date)->translatedFormat('d M Y') : '-',
                    'expiry_date' => $lot->expiry_date ? Carbon::parse($lot->expiry_date)->translatedFormat('d M Y') : '-',
                    'is_expired' => $isExpired,
                    'initial_qty' => $this->formatQty((float) $lot->initial_qty),
                    'current_qty' => $this->formatQty((float) $lot->current_qty),
                    'unit_cost' => (float) $lot->unit_cost,
                    'formatted_unit_cost' => 'Rp '.number_format((float) $lot->unit_cost, 0, ',', '.'),
                    'status' => $lot->status,
                ];
            });

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $material->id,
                'kode' => $material->kode,
                'nama' => $material->nama,
                'kategori' => $material->kategori,
                'satuan' => $material->satuan,
                'min_stock' => $minStock,
                'total_stok' => $totalStok,
                'is_active' => (bool) $material->is_active,
                'indikator' => $indikatorLabel,
                'indikator_class' => $indikatorClass,
                'formatted_min_stock' => $this->formatQty($minStock).' '.$material->satuan,
                'formatted_total_stok' => $this->formatQty($totalStok).' '.$material->satuan,
                'created_at' => $material->created_at ? $material->created_at->translatedFormat('d M Y H:i') : '-',
                'lots' => $lots,
            ],
        ]);
    }

    /**
     * Simpan bahan baku baru.
     */
    public function store(Request $request): RedirectResponse
    {
        $idSppg = $this->resolveSppgId();

        $validated = $request->validate([
            'kode' => [
                'required',
                'string',
                'max:30',
                Rule::unique('materials')->where('id_sppg', $idSppg),
            ],
            'nama' => ['required', 'string', 'max:150'],
            'kategori' => ['required', 'string', 'max:80'],
            'satuan' => ['required', 'string', 'max:20'],
            'min_stock' => ['required', 'numeric', 'gte:0'],
            'is_active' => ['nullable', 'boolean'],
        ], [
            'kode.required' => 'Kode bahan wajib diisi.',
            'kode.unique' => 'Kode bahan sudah digunakan di SPPG ini.',
            'nama.required' => 'Nama bahan wajib diisi.',
            'kategori.required' => 'Kategori bahan wajib dipilih/diisi.',
            'satuan.required' => 'Satuan bahan wajib dipilih/diisi.',
            'min_stock.required' => 'Batas stok minimum wajib diisi.',
            'min_stock.gte' => 'Batas stok minimum tidak boleh bernilai negatif.',
        ]);

        $material = Material::create([
            'id_sppg' => $idSppg,
            'kode' => strtoupper(trim($validated['kode'])),
            'nama' => trim($validated['nama']),
            'kategori' => trim($validated['kategori']),
            'satuan' => trim($validated['satuan']),
            'min_stock' => $validated['min_stock'],
            'is_active' => $request->boolean('is_active', true),
        ]);

        AuditTrail::catat('create', $material, null, [
            'kode' => $material->kode,
            'nama' => $material->nama,
            'kategori' => $material->kategori,
        ]);

        return redirect()->route('staff.materials.index')->with('success', "Bahan baku '{$material->nama}' berhasil ditambahkan.");
    }

    /**
     * Perbarui data bahan baku.
     */
    public function update(Request $request, int $id): RedirectResponse
    {
        $idSppg = $this->resolveSppgId();
        $material = Material::where('id_sppg', $idSppg)->findOrFail($id);

        $validated = $request->validate([
            'kode' => [
                'required',
                'string',
                'max:30',
                Rule::unique('materials')->where('id_sppg', $idSppg)->ignore($material->id),
            ],
            'nama' => ['required', 'string', 'max:150'],
            'kategori' => ['required', 'string', 'max:80'],
            'satuan' => ['required', 'string', 'max:20'],
            'min_stock' => ['required', 'numeric', 'gte:0'],
            'is_active' => ['nullable', 'boolean'],
        ], [
            'kode.required' => 'Kode bahan wajib diisi.',
            'kode.unique' => 'Kode bahan sudah digunakan oleh bahan lain di SPPG ini.',
            'nama.required' => 'Nama bahan wajib diisi.',
            'kategori.required' => 'Kategori bahan wajib dipilih/diisi.',
            'satuan.required' => 'Satuan bahan wajib dipilih/diisi.',
            'min_stock.required' => 'Batas stok minimum wajib diisi.',
            'min_stock.gte' => 'Batas stok minimum tidak boleh negatif.',
        ]);

        $oldData = $material->only(['kode', 'nama', 'kategori', 'satuan', 'min_stock', 'is_active']);

        $material->update([
            'kode' => strtoupper(trim($validated['kode'])),
            'nama' => trim($validated['nama']),
            'kategori' => trim($validated['kategori']),
            'satuan' => trim($validated['satuan']),
            'min_stock' => $validated['min_stock'],
            'is_active' => $request->has('is_active') ? $request->boolean('is_active') : $material->is_active,
        ]);

        AuditTrail::catat('update', $material, $oldData, [
            'kode' => $material->kode,
            'nama' => $material->nama,
        ]);

        return redirect()->route('staff.materials.index')->with('success', "Data bahan baku '{$material->nama}' berhasil diperbarui.");
    }

    /**
     * Toggle status aktif / nonaktif bahan.
     */
    public function toggleStatus(int $id): RedirectResponse
    {
        $idSppg = $this->resolveSppgId();
        $material = Material::where('id_sppg', $idSppg)->findOrFail($id);

        $newStatus = ! $material->is_active;
        $material->update(['is_active' => $newStatus]);

        $statusText = $newStatus ? 'diaktifkan kembali' : 'dinonaktifkan';

        AuditTrail::catat('toggle_status', $material, null, [
            'is_active' => $newStatus,
            'nama' => $material->nama,
        ]);

        return redirect()->route('staff.materials.index')->with('success', "Bahan baku '{$material->nama}' berhasil {$statusText}.");
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

    /**
     * Format angka kuantitas agar desimal rapi.
     */
    protected function formatQty(float $qty): string
    {
        return (float) $qty == (int) $qty
            ? (string) (int) $qty
            : rtrim(rtrim(number_format($qty, 3, ',', '.'), '0'), ',');
    }
}
