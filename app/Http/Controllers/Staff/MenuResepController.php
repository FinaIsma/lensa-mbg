<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\AuditTrail;
use App\Models\Material;
use App\Models\Menu;
use App\Models\NutritionType;
use App\Models\Recipe;
use App\Models\RecipeItem;
use App\Models\RecipeNutrition;
use App\Support\Keys;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class MenuResepController extends Controller
{
    /**
     * Halaman Utama Menu & Resep (Overview Dashboard)
     */
    public function index(): View
    {
        $idSppg = $this->resolveSppgId();
        $this->ensureSampleData($idSppg);

        $totalMenuActive = Menu::where('id_sppg', $idSppg)->where('is_active', true)->count();
        $totalMenuInactive = Menu::where('id_sppg', $idSppg)->where('is_active', false)->count();

        $totalRecipeActive = Recipe::whereHas('menu', fn ($q) => $q->where('id_sppg', $idSppg))->where('status', 'active')->count();
        $totalRecipeInactive = Recipe::whereHas('menu', fn ($q) => $q->where('id_sppg', $idSppg))->where('status', '!=', 'active')->count();

        $menus = Menu::where('id_sppg', $idSppg)->orderBy('id')->limit(5)->get();
        $recipes = Recipe::with(['menu', 'items.material', 'nutritions.nutritionType'])
            ->whereHas('menu', fn ($q) => $q->where('id_sppg', $idSppg))
            ->orderBy('id')
            ->limit(5)
            ->get();

        $allMaterials = Material::where('id_sppg', $idSppg)->where('is_active', true)->orderBy('nama')->get();
        $allMenus = Menu::where('id_sppg', $idSppg)->where('is_active', true)->orderBy('nama')->get();
        $allNutritionTypes = NutritionType::orderBy('id')->get();

        return view('staff.menu-resep.index', compact(
            'totalMenuActive',
            'totalMenuInactive',
            'totalRecipeActive',
            'totalRecipeInactive',
            'menus',
            'recipes',
            'allMaterials',
            'allMenus',
            'allNutritionTypes'
        ));
    }

    /**
     * Halaman Utama Data Resep (Sesuai Gambar / Mockup Lengkap)
     */
    public function recipes(Request $request): View
    {
        $idSppg = $this->resolveSppgId();
        $this->ensureSampleData($idSppg);

        $query = Recipe::with(['menu', 'items.material', 'nutritions.nutritionType'])
            ->whereHas('menu', fn ($q) => $q->where('id_sppg', $idSppg));

        if ($request->filled('q')) {
            $q = trim((string) $request->query('q'));
            $query->where(function ($w) use ($q) {
                $w->whereHas('menu', fn ($m) => $m->where('nama', 'ilike', "%{$q}%"))
                    ->orWhereHas('items.material', fn ($mat) => $mat->where('nama', 'ilike', "%{$q}%"));
            });
        }

        if ($request->filled('porsi')) {
            $query->where('ukuran_porsi', $request->query('porsi'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->query('status'));
        }

        $recipes = $query->orderBy('id')->paginate(10)->withQueryString();

        $allMaterials = Material::where('id_sppg', $idSppg)->where('is_active', true)->orderBy('nama')->get();
        $allMenus = Menu::where('id_sppg', $idSppg)->where('is_active', true)->orderBy('nama')->get();
        $allNutritionTypes = NutritionType::orderBy('id')->get();

        $totalRecipeActive = Recipe::whereHas('menu', fn ($q) => $q->where('id_sppg', $idSppg))->where('status', 'active')->count();
        $totalRecipeInactive = Recipe::whereHas('menu', fn ($q) => $q->where('id_sppg', $idSppg))->where('status', '!=', 'active')->count();

        return view('staff.menu-resep.recipes', compact(
            'recipes',
            'allMaterials',
            'allMenus',
            'allNutritionTypes',
            'totalRecipeActive',
            'totalRecipeInactive'
        ));
    }

    /**
     * Detail Resep via JSON untuk Drawer dan Modal Edit
     */
    public function showRecipe(int $id): JsonResponse
    {
        $idSppg = $this->resolveSppgId();

        $recipe = Recipe::with(['menu', 'items.material', 'nutritions.nutritionType'])
            ->whereHas('menu', fn ($q) => $q->where('id_sppg', $idSppg))
            ->findOrFail($id);

        $isUsedInBatch = $recipe->isUsedInBatch();

        return response()->json([
            'id' => $recipe->id,
            'kode' => $recipe->formatted_id,
            'menu_id' => $recipe->menu_id,
            'menu_kode' => $recipe->menu?->formatted_id ?? '-',
            'nama_menu' => $recipe->menu?->nama ?? '-',
            'ukuran_porsi' => $recipe->ukuran_porsi,
            'ukuran_porsi_label' => ucfirst($recipe->ukuran_porsi),
            'version' => $recipe->version,
            'status' => $recipe->status,
            'is_used_in_batch' => $isUsedInBatch,
            'bahan_list' => $recipe->bahan_list,
            'items' => $recipe->items->map(fn ($item) => [
                'id' => $item->id,
                'material_id' => $item->material_id,
                'material_nama' => $item->material?->nama ?? '-',
                'qty_per_porsi' => (float) $item->qty_per_porsi,
                'satuan' => $item->satuan,
            ]),
            'nutritions' => $recipe->nutritions->map(fn ($n) => [
                'id' => $n->id,
                'nutrition_type_id' => $n->nutrition_type_id,
                'nama' => $n->nutritionType?->nama ?? '-',
                'nilai' => (float) $n->nilai,
                'satuan' => $n->nutritionType?->satuan ?? '',
            ]),
        ]);
    }

    /**
     * Simpan Data Resep Baru
     */
    public function storeRecipe(Request $request): RedirectResponse
    {
        $idSppg = $this->resolveSppgId();

        $validated = $request->validate([
            'menu_id' => ['required', 'exists:menus,id'],
            'ukuran_porsi' => ['required', 'in:besar,kecil'],
            'status' => ['required', 'in:active,archived'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.material_id' => ['required', 'distinct', 'exists:materials,id'],
            'items.*.qty_per_porsi' => ['required', 'numeric', 'gt:0'],
            'nutritions' => ['nullable', 'array'],
            'nutritions.*.nutrition_type_id' => ['nullable', 'distinct', 'exists:nutrition_types,id'],
            'nutritions.*.nilai' => ['nullable', 'numeric', 'gte:0'],
        ], [
            'items.*.material_id.distinct' => 'Bahan tidak boleh dipilih ganda dalam satu resep.',
            'items.*.qty_per_porsi.gt' => 'Takaran bahan harus berupa angka lebih besar dari 0.',
            'nutritions.*.nutrition_type_id.distinct' => 'Jenis gizi tidak boleh dipilih ganda dalam satu resep.',
            'nutritions.*.nilai.gte' => 'Nilai gizi harus berupa angka lebih besar atau sama dengan 0.',
        ]);

        Menu::where('id_sppg', $idSppg)->findOrFail($validated['menu_id']);

        DB::transaction(function () use ($validated, $idSppg) {
            // Logika: Hanya 1 resep active per (menu, ukuran_porsi)
            if ($validated['status'] === 'active') {
                Recipe::where('menu_id', $validated['menu_id'])
                    ->where('ukuran_porsi', $validated['ukuran_porsi'])
                    ->where('status', 'active')
                    ->update(['status' => 'archived']);
            }

            $recipe = Recipe::create([
                'menu_id' => $validated['menu_id'],
                'ukuran_porsi' => $validated['ukuran_porsi'],
                'version' => 1,
                'status' => $validated['status'],
            ]);

            // Simpan Bahan (recipe_items)
            foreach ($validated['items'] as $item) {
                $material = Material::findOrFail($item['material_id']);
                RecipeItem::create([
                    'recipe_id' => $recipe->id,
                    'material_id' => $material->id,
                    'qty_per_porsi' => $item['qty_per_porsi'],
                    'satuan' => $material->satuan,
                ]);
            }

            // Simpan Nilai Gizi (recipe_nutritions)
            if (! empty($validated['nutritions'])) {
                foreach ($validated['nutritions'] as $nut) {
                    if (! empty($nut['nutrition_type_id']) && isset($nut['nilai']) && $nut['nilai'] !== '') {
                        RecipeNutrition::create([
                            'recipe_id' => $recipe->id,
                            'nutrition_type_id' => $nut['nutrition_type_id'],
                            'nilai' => $nut['nilai'],
                        ]);
                    }
                }
            }

            // Audit Trail
            AuditTrail::catat('create', $recipe, null, [
                'menu' => $recipe->menu?->nama,
                'porsi' => $recipe->ukuran_porsi,
                'status' => $recipe->status,
                'bahan' => $recipe->bahan_list,
            ], null, $idSppg);
        });

        return back()->with('success', 'Data Resep baru berhasil disimpan.');
    }

    /**
     * Perbarui Data Resep
     * Aturan bisnis: Resep yang sudah dipakai batch tidak boleh diedit langsung.
     */
    public function updateRecipe(Request $request, int $id): RedirectResponse
    {
        $idSppg = $this->resolveSppgId();

        $recipe = Recipe::with(['menu', 'items.material', 'nutritions.nutritionType'])
            ->whereHas('menu', fn ($q) => $q->where('id_sppg', $idSppg))
            ->findOrFail($id);

        if ($recipe->isUsedInBatch()) {
            return back()->with('error', "Resep ini sudah pernah digunakan dalam batch produksi dan tidak dapat diubah secara langsung. Silakan gunakan tombol 'Buat Versi Baru'.");
        }

        $validated = $request->validate([
            'ukuran_porsi' => ['required', 'in:besar,kecil'],
            'status' => ['required', 'in:active,archived'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.material_id' => ['required', 'distinct', 'exists:materials,id'],
            'items.*.qty_per_porsi' => ['required', 'numeric', 'gt:0'],
            'nutritions' => ['nullable', 'array'],
            'nutritions.*.nutrition_type_id' => ['nullable', 'distinct', 'exists:nutrition_types,id'],
            'nutritions.*.nilai' => ['nullable', 'numeric', 'gte:0'],
        ], [
            'items.*.material_id.distinct' => 'Bahan tidak boleh dipilih ganda dalam satu resep.',
            'items.*.qty_per_porsi.gt' => 'Takaran bahan harus berupa angka lebih besar dari 0.',
            'nutritions.*.nutrition_type_id.distinct' => 'Jenis gizi tidak boleh dipilih ganda dalam satu resep.',
            'nutritions.*.nilai.gte' => 'Nilai gizi harus berupa angka lebih besar atau sama dengan 0.',
        ]);

        DB::transaction(function () use ($recipe, $validated, $idSppg) {
            $oldValues = [
                'porsi' => $recipe->ukuran_porsi,
                'status' => $recipe->status,
                'bahan' => $recipe->bahan_list,
            ];

            if ($validated['status'] === 'active') {
                Recipe::where('menu_id', $recipe->menu_id)
                    ->where('ukuran_porsi', $validated['ukuran_porsi'])
                    ->where('id', '!=', $recipe->id)
                    ->where('status', 'active')
                    ->update(['status' => 'archived']);
            }

            $recipe->ukuran_porsi = $validated['ukuran_porsi'];
            $recipe->status = $validated['status'];
            $recipe->save();

            // Sync Bahan
            $recipe->items()->delete();
            foreach ($validated['items'] as $item) {
                $material = Material::findOrFail($item['material_id']);
                RecipeItem::create([
                    'recipe_id' => $recipe->id,
                    'material_id' => $material->id,
                    'qty_per_porsi' => $item['qty_per_porsi'],
                    'satuan' => $material->satuan,
                ]);
            }

            // Sync Gizi
            $recipe->nutritions()->delete();
            if (! empty($validated['nutritions'])) {
                foreach ($validated['nutritions'] as $nut) {
                    if (! empty($nut['nutrition_type_id']) && isset($nut['nilai']) && $nut['nilai'] !== '') {
                        RecipeNutrition::create([
                            'recipe_id' => $recipe->id,
                            'nutrition_type_id' => $nut['nutrition_type_id'],
                            'nilai' => $nut['nilai'],
                        ]);
                    }
                }
            }

            $recipe->load(['items.material', 'nutritions.nutritionType']);

            $newValues = [
                'porsi' => $recipe->ukuran_porsi,
                'status' => $recipe->status,
                'bahan' => $recipe->bahan_list,
            ];

            AuditTrail::catat('update', $recipe, $oldValues, $newValues, null, $idSppg);
        });

        return back()->with('success', 'Data Resep berhasil diperbarui.');
    }

    /**
     * Buat Versi Baru Resep (version + 1)
     * Resep lama diarsipkan, resep baru dibuat sebagai active dengan menyalin/memperbarui bahan dan gizi.
     */
    public function createVersion(Request $request, int $id): RedirectResponse
    {
        $idSppg = $this->resolveSppgId();

        $oldRecipe = Recipe::with(['menu', 'items', 'nutritions'])
            ->whereHas('menu', fn ($q) => $q->where('id_sppg', $idSppg))
            ->findOrFail($id);

        $validated = $request->validate([
            'items' => ['required', 'array', 'min:1'],
            'items.*.material_id' => ['required', 'distinct', 'exists:materials,id'],
            'items.*.qty_per_porsi' => ['required', 'numeric', 'gt:0'],
            'nutritions' => ['nullable', 'array'],
            'nutritions.*.nutrition_type_id' => ['nullable', 'distinct', 'exists:nutrition_types,id'],
            'nutritions.*.nilai' => ['nullable', 'numeric', 'gte:0'],
        ], [
            'items.*.material_id.distinct' => 'Bahan tidak boleh dipilih ganda.',
            'items.*.qty_per_porsi.gt' => 'Takaran bahan harus berupa angka lebih besar dari 0.',
            'nutritions.*.nutrition_type_id.distinct' => 'Jenis gizi tidak boleh dipilih ganda.',
            'nutritions.*.nilai.gte' => 'Nilai gizi harus berupa angka lebih besar atau sama dengan 0.',
        ]);

        DB::transaction(function () use ($oldRecipe, $validated, $idSppg) {
            // 1. Arsipkan resep lama
            $oldRecipe->status = 'archived';
            $oldRecipe->save();

            // 2. Buat resep baru dengan version + 1
            $newRecipe = Recipe::create([
                'menu_id' => $oldRecipe->menu_id,
                'ukuran_porsi' => $oldRecipe->ukuran_porsi,
                'version' => $oldRecipe->version + 1,
                'status' => 'active',
            ]);

            // 3. Simpan bahan baru
            foreach ($validated['items'] as $item) {
                $material = Material::findOrFail($item['material_id']);
                RecipeItem::create([
                    'recipe_id' => $newRecipe->id,
                    'material_id' => $material->id,
                    'qty_per_porsi' => $item['qty_per_porsi'],
                    'satuan' => $material->satuan,
                ]);
            }

            // 4. Simpan gizi baru
            if (! empty($validated['nutritions'])) {
                foreach ($validated['nutritions'] as $nut) {
                    if (! empty($nut['nutrition_type_id']) && isset($nut['nilai']) && $nut['nilai'] !== '') {
                        RecipeNutrition::create([
                            'recipe_id' => $newRecipe->id,
                            'nutrition_type_id' => $nut['nutrition_type_id'],
                            'nilai' => $nut['nilai'],
                        ]);
                    }
                }
            }

            AuditTrail::catat('create_version', $newRecipe, null, [
                'menu' => $oldRecipe->menu?->nama,
                'porsi' => $newRecipe->ukuran_porsi,
                'versi_baru' => $newRecipe->version,
                'versi_lama' => $oldRecipe->version,
            ], null, $idSppg);
        });

        return back()->with('success', "Versi baru resep (v" . ($oldRecipe->version + 1) . ") berhasil dibuat dan diaktifkan.");
    }

    /**
     * Ubah status resep: Aktifkan / Arsipkan
     */
    public function toggleRecipeStatus(int $id): RedirectResponse
    {
        $idSppg = $this->resolveSppgId();
        $recipe = Recipe::whereHas('menu', fn ($q) => $q->where('id_sppg', $idSppg))->findOrFail($id);

        DB::transaction(function () use ($recipe, $idSppg) {
            $oldStatus = $recipe->status;

            if ($recipe->status === 'active') {
                $recipe->status = 'archived';
            } else {
                // Arsipkan resep aktif lain pada menu dan porsi yang sama
                Recipe::where('menu_id', $recipe->menu_id)
                    ->where('ukuran_porsi', $recipe->ukuran_porsi)
                    ->where('status', 'active')
                    ->where('id', '!=', $recipe->id)
                    ->update(['status' => 'archived']);

                $recipe->status = 'active';
            }

            $recipe->save();

            AuditTrail::catat('toggle_status', $recipe, ['status' => $oldStatus], ['status' => $recipe->status], null, $idSppg);
        });

        $statusText = $recipe->status === 'active' ? 'diaktifkan' : 'diarsipkan';

        return back()->with('success', "Status resep {$recipe->formatted_id} berhasil {$statusText}.");
    }

    /**
     * Tambah Jenis Gizi Baru via Pop-up / AJAX
     */
    public function storeNutritionType(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'nama' => ['required', 'string', 'max:100', 'unique:nutrition_types,nama'],
            'satuan' => ['required', 'in:kkal,g,mg'],
        ], [
            'nama.unique' => 'Jenis gizi ini sudah terdaftar.',
            'satuan.in' => 'Satuan harus salah satu dari: kkal, g, mg.',
        ]);

        $type = NutritionType::create($validated);

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $type->id,
                'nama' => $type->nama,
                'satuan' => $type->satuan,
            ],
            'message' => 'Jenis gizi baru berhasil ditambahkan.',
        ]);
    }

    /**
     * Halaman Lengkap Data Menu
     */
    public function menus(Request $request): View
    {
        $idSppg = $this->resolveSppgId();
        $this->ensureSampleData($idSppg);

        $query = Menu::where('id_sppg', $idSppg);

        if ($request->filled('q')) {
            $q = trim((string) $request->query('q'));
            $numericId = ltrim(preg_replace('/[^0-9]/', '', $q), '0');

            $query->where(function ($w) use ($q, $numericId) {
                $w->where('nama', 'ilike', "%{$q}%")
                    ->orWhere('deskripsi', 'ilike', "%{$q}%");

                if (! empty($numericId)) {
                    $w->orWhere('id', (int) $numericId);
                }
            });
        }

        if ($request->filled('kategori')) {
            $query->where('kategori', $request->query('kategori'));
        }

        if ($request->filled('status')) {
            $status = $request->query('status');
            if ($status === 'active') {
                $query->where('is_active', true);
            } elseif ($status === 'inactive') {
                $query->where('is_active', false);
            }
        }

        $menus = $query->orderBy('id')->paginate(10)->withQueryString();

        $totalMenuActive = Menu::where('id_sppg', $idSppg)->where('is_active', true)->count();
        $totalMenuInactive = Menu::where('id_sppg', $idSppg)->where('is_active', false)->count();

        return view('staff.menu-resep.menus', compact('menus', 'totalMenuActive', 'totalMenuInactive'));
    }

    /**
     * Simpan Data Menu Baru
     */
    public function storeMenu(Request $request): RedirectResponse
    {
        $idSppg = $this->resolveSppgId();

        $validated = $request->validate([
            'nama' => ['required', 'string', 'max:150'],
            'kategori' => ['required', 'in:makanan_siap_santap,paket_siap_konsumsi,pangan_siap_olah'],
            'deskripsi' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $validated['id_sppg'] = $idSppg;
        $validated['is_active'] = $request->boolean('is_active', true);

        DB::transaction(function () use ($validated, $idSppg) {
            $menu = Menu::create($validated);

            AuditTrail::catat('create', $menu, null, [
                'nama' => $menu->nama,
                'kategori' => $menu->kategori_label,
                'is_active' => $menu->is_active ? 'Aktif' : 'Tidak Aktif',
            ], null, $idSppg);
        });

        return back()->with('success', 'Data Menu baru berhasil ditambahkan.');
    }

    /**
     * Perbarui Data Menu
     */
    public function updateMenu(Request $request, int $id): RedirectResponse
    {
        $idSppg = $this->resolveSppgId();
        $menu = Menu::where('id_sppg', $idSppg)->findOrFail($id);

        $validated = $request->validate([
            'nama' => ['required', 'string', 'max:150'],
            'kategori' => ['required', 'in:makanan_siap_santap,paket_siap_konsumsi,pangan_siap_olah'],
            'deskripsi' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);

        DB::transaction(function () use ($menu, $validated, $idSppg) {
            $oldValues = [
                'nama' => $menu->nama,
                'kategori' => $menu->kategori_label,
                'deskripsi' => $menu->deskripsi,
                'is_active' => $menu->is_active ? 'Aktif' : 'Tidak Aktif',
            ];

            $menu->update($validated);

            $newValues = [
                'nama' => $menu->nama,
                'kategori' => $menu->kategori_label,
                'deskripsi' => $menu->deskripsi,
                'is_active' => $menu->is_active ? 'Aktif' : 'Tidak Aktif',
            ];

            AuditTrail::catat('update', $menu, $oldValues, $newValues, null, $idSppg);
        });

        return back()->with('success', 'Data Menu berhasil diperbarui.');
    }

    /**
     * Ubah status aktif/nonaktif menu
     */
    public function toggleMenuStatus(int $id): RedirectResponse
    {
        $idSppg = $this->resolveSppgId();
        $menu = Menu::where('id_sppg', $idSppg)->findOrFail($id);

        DB::transaction(function () use ($menu, $idSppg) {
            $oldStatus = $menu->is_active ? 'Aktif' : 'Tidak Aktif';
            $menu->is_active = ! $menu->is_active;
            $menu->save();

            $newStatus = $menu->is_active ? 'Aktif' : 'Tidak Aktif';
            AuditTrail::catat('toggle_status', $menu, ['status' => $oldStatus], ['status' => $newStatus], null, $idSppg);
        });

        return back()->with('success', "Status menu {$menu->nama} berhasil diubah.");
    }

    /**
     * Resolusi ID SPPG pengguna yang sedang login
     */
    private function resolveSppgId(): int
    {
        $user = Auth::guard('staff')->user() ?: Auth::user();
        if (! $user) {
            abort(401, 'Silakan login terlebih dahulu.');
        }

        $idSppg = $user->id_sppg
            ?? DB::table(Keys::SPPG_TABLE)->where(Keys::SPPG_OWNER, $user->getKey())->value(Keys::SPPG_PK);

        if (! $idSppg) {
            $idSppg = DB::table(Keys::SPPG_TABLE)->orderBy(Keys::SPPG_PK)->value(Keys::SPPG_PK);
        }

        abort_unless($idSppg, 403, 'Akun ini tidak terhubung ke SPPG mana pun.');

        return (int) $idSppg;
    }

    /**
     * Menjamin data sample sesuai gambar tersedia jika belum ada data di database
     */
    private function ensureSampleData(int $idSppg): void
    {
        $materialsData = [
            ['kode' => 'BRS', 'nama' => 'Beras putih', 'satuan' => 'kg', 'kategori' => 'Bahan Pokok'],
            ['kode' => 'TMP', 'nama' => 'Tempe', 'satuan' => 'kg', 'kategori' => 'Lauk Nabati'],
            ['kode' => 'BYM', 'nama' => 'Sayur Bayam', 'satuan' => 'kg', 'kategori' => 'Sayuran'],
            ['kode' => 'AYM', 'nama' => 'Daging Ayam', 'satuan' => 'kg', 'kategori' => 'Lauk Hewani'],
            ['kode' => 'MNY', 'nama' => 'Minyak Goreng', 'satuan' => 'liter', 'kategori' => 'Bumbu & Minyak'],
        ];

        $matMap = [];
        foreach ($materialsData as $m) {
            $row = Material::firstOrCreate(
                ['id_sppg' => $idSppg, 'kode' => $m['kode']],
                ['nama' => $m['nama'], 'satuan' => $m['satuan'], 'kategori' => $m['kategori'], 'min_stock' => 5, 'is_active' => true]
            );
            $matMap[$m['kode']] = $row->id;
        }

        if (Menu::where('id_sppg', $idSppg)->count() === 0) {
            $m1 = Menu::create([
                'id_sppg' => $idSppg,
                'nama' => 'Nasi Tempe Orek Sayur Bayam',
                'kategori' => 'makanan_siap_santap',
                'deskripsi' => 'Nasi putih, tempe orek manis gurih, sayur bayam bening',
                'is_active' => true,
            ]);

            $m2 = Menu::create([
                'id_sppg' => $idSppg,
                'nama' => 'Nasi Ayam Goreng Bayam',
                'kategori' => 'makanan_siap_santap',
                'deskripsi' => 'Nasi putih, ayam goreng, sayur bayam bening',
                'is_active' => true,
            ]);

            $sampleList = [
                ['nama' => 'Nasi Ayam Goreng Sayur Bayam', 'kategori' => 'makanan_siap_santap', 'deskripsi' => 'Nasi putih, ayam goreng, sayur bayam bening'],
                ['nama' => 'Paket Bento Ayam Teriyaki', 'kategori' => 'paket_siap_konsumsi', 'deskripsi' => 'Nasi putih, ayam fillet teriyaki, tumis brokoli wortel'],
                ['nama' => 'Nasi Daging Sapi Lada Hitam', 'kategori' => 'makanan_siap_santap', 'deskripsi' => 'Nasi putih, daging sapi lada hitam, sayur capcay'],
                ['nama' => 'Paket Sayur Sop Komplit Siap Olah', 'kategori' => 'pangan_siap_olah', 'deskripsi' => 'Potongan wortel, kubis, kentang, buncis, dan bumbu kaldu sop'],
                ['nama' => 'Nasi Ikan Gurame Bakar Madu', 'kategori' => 'makanan_siap_santap', 'deskripsi' => 'Nasi putih, ikan gurame bakar madu, lalapan dan sambal manis'],
                ['nama' => 'Paket Sayur Asem & Tahu Tempe', 'kategori' => 'pangan_siap_olah', 'deskripsi' => 'Bahan sayur asem mentah, labu siam, jagung, kacang panjang, bumbu siap masak'],
                ['nama' => 'Nasi Opor Ayam Telur Rebus', 'kategori' => 'makanan_siap_santap', 'deskripsi' => 'Nasi putih, opor ayam kuah santan gurih, telur rebus, kerupuk'],
            ];

            foreach ($sampleList as $s) {
                Menu::firstOrCreate(
                    ['id_sppg' => $idSppg, 'nama' => $s['nama']],
                    ['kategori' => $s['kategori'], 'deskripsi' => $s['deskripsi'], 'is_active' => true]
                );
            }

            // Resep
            $r1 = Recipe::create([
                'menu_id' => $m1->id,
                'ukuran_porsi' => 'kecil',
                'version' => 1,
                'status' => 'active',
            ]);

            if (isset($matMap['BRS'])) {
                RecipeItem::create(['recipe_id' => $r1->id, 'material_id' => $matMap['BRS'], 'qty_per_porsi' => 0.09, 'satuan' => 'kg']);
            }
            if (isset($matMap['TMP'])) {
                RecipeItem::create(['recipe_id' => $r1->id, 'material_id' => $matMap['TMP'], 'qty_per_porsi' => 0.05, 'satuan' => 'kg']);
            }
            if (isset($matMap['BYM'])) {
                RecipeItem::create(['recipe_id' => $r1->id, 'material_id' => $matMap['BYM'], 'qty_per_porsi' => 0.04, 'satuan' => 'kg']);
            }

            // Gizi awal
            $energiType = NutritionType::where('nama', 'Energi')->first();
            $proteinType = NutritionType::where('nama', 'Protein')->first();
            $lemakType = NutritionType::where('nama', 'Lemak')->first();

            if ($energiType) {
                RecipeNutrition::create(['recipe_id' => $r1->id, 'nutrition_type_id' => $energiType->id, 'nilai' => 550]);
            }
            if ($proteinType) {
                RecipeNutrition::create(['recipe_id' => $r1->id, 'nutrition_type_id' => $proteinType->id, 'nilai' => 22]);
            }
            if ($lemakType) {
                RecipeNutrition::create(['recipe_id' => $r1->id, 'nutrition_type_id' => $lemakType->id, 'nilai' => 14]);
            }

            $r2 = Recipe::create([
                'menu_id' => $m2->id,
                'ukuran_porsi' => 'besar',
                'version' => 1,
                'status' => 'active',
            ]);

            if (isset($matMap['BRS'])) {
                RecipeItem::create(['recipe_id' => $r2->id, 'material_id' => $matMap['BRS'], 'qty_per_porsi' => 0.12, 'satuan' => 'kg']);
            }
            if (isset($matMap['AYM'])) {
                RecipeItem::create(['recipe_id' => $r2->id, 'material_id' => $matMap['AYM'], 'qty_per_porsi' => 0.10, 'satuan' => 'kg']);
            }
            if (isset($matMap['BYM'])) {
                RecipeItem::create(['recipe_id' => $r2->id, 'material_id' => $matMap['BYM'], 'qty_per_porsi' => 0.05, 'satuan' => 'kg']);
            }

            if ($energiType) {
                RecipeNutrition::create(['recipe_id' => $r2->id, 'nutrition_type_id' => $energiType->id, 'nilai' => 680]);
            }
            if ($proteinType) {
                RecipeNutrition::create(['recipe_id' => $r2->id, 'nutrition_type_id' => $proteinType->id, 'nilai' => 32]);
            }
            if ($lemakType) {
                RecipeNutrition::create(['recipe_id' => $r2->id, 'nutrition_type_id' => $lemakType->id, 'nilai' => 18]);
            }
        }
    }
}
