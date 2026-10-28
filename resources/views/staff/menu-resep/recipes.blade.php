@extends('layouts.staff')

@section('title', 'Data Resep')

@section('content')
<div class="menu-resep-page">
    {{-- Header Halaman Sesuai Mockup Panel Kiri --}}
    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 24px; gap: 16px; flex-wrap: wrap;">
        <div style="display: flex; align-items: center; gap: 14px;">
            <a href="{{ route('staff.menu-resep.index') }}" class="btn-back" title="Kembali ke Halaman Menu & Resep">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="15 18 9 12 15 6"></polyline>
                </svg>
            </a>
            <div>
                <h1 class="page-title" style="margin: 0; font-size: 24px; font-weight: 700; color: #071E49;">Data Resep</h1>
                <p class="page-subtitle" style="margin: 2px 0 0; font-size: 13px; color: #64748B;">Kelola data resep untuk SPPG ini</p>
            </div>
        </div>
        <button type="button" class="btn-primary" onclick="openAddRecipeModal()" style="display: inline-flex; align-items: center; gap: 8px; background-color: #071E49; border-radius: 10px; padding: 10px 18px; font-weight: 600; cursor: pointer;">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
            <span>Tambah Resep</span>
        </button>
    </div>

    {{-- Filter Bar --}}
    <div class="section-card" style="padding: 16px 20px; margin-bottom: 22px;">
        <form method="GET" action="{{ route('staff.recipes.index') }}" style="display: flex; gap: 14px; align-items: center; flex-wrap: wrap;">
            <div style="flex: 1; min-width: 220px; position: relative;">
                <input type="text" name="q" value="{{ request('q') }}" placeholder="Cari nama resep atau bahan..." class="form-input" style="padding-left: 38px;">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#94A3B8" stroke-width="2" style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); pointer-events: none;">
                    <circle cx="11" cy="11" r="8"></circle>
                    <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                </svg>
            </div>
            <div style="width: 170px;">
                <select name="porsi" class="form-select" onchange="this.form.submit()">
                    <option value="">Semua Porsi</option>
                    <option value="kecil" {{ request('porsi') === 'kecil' ? 'selected' : '' }}>Porsi Kecil</option>
                    <option value="besar" {{ request('porsi') === 'besar' ? 'selected' : '' }}>Porsi Besar</option>
                </select>
            </div>
            <div style="width: 170px;">
                <select name="status" class="form-select" onchange="this.form.submit()">
                    <option value="">Semua Status</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                    <option value="archived" {{ request('status') === 'archived' ? 'selected' : '' }}>Archived</option>
                </select>
            </div>
            <button type="submit" class="btn-primary" style="padding: 10px 18px; border-radius: 9px;">Cari</button>
            @if(request()->hasAny(['q', 'porsi', 'status']))
                <a href="{{ route('staff.recipes.index') }}" class="btn-secondary" style="padding: 10px 14px; border-radius: 9px; text-decoration: none;">Reset</a>
            @endif
        </form>
    </div>

    {{-- Tabel Data Resep (Panel 1) --}}
    <div class="section-card">
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th class="col-id" style="width: 85px;">ID</th>
                        <th style="min-width: 180px;">Nama Resep</th>
                        <th style="width: 110px;">Porsi</th>
                        <th style="width: 90px;">Version</th>
                        <th>Bahan</th>
                        <th style="width: 110px;">Status</th>
                        <th class="text-center" style="width: 130px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($recipes as $recipe)
                        <tr>
                            <td class="col-id" style="font-weight: 700; color: #2563EB;">{{ $recipe->formatted_id }}</td>
                            <td class="col-name" style="font-weight: 600; color: #071E49;">{{ $recipe->menu?->nama ?? 'Resep #' . $recipe->id }}</td>
                            <td>
                                <span style="font-weight: 500; color: #334155;">{{ ucfirst($recipe->ukuran_porsi) }}</span>
                            </td>
                            <td>
                                <span style="display: inline-block; padding: 2px 8px; border-radius: 6px; background: #F1F5F9; font-size: 12px; font-weight: 600; color: #475569;">
                                    v{{ $recipe->version }}
                                </span>
                            </td>
                            <td class="col-bahan" style="color: #475569; font-size: 13px;">{{ $recipe->bahan_list ?: '-' }}</td>
                            <td>
                                @if ($recipe->status === 'active')
                                    <span class="badge badge-active">
                                        <span class="badge-dot"></span>
                                        <span>Active</span>
                                    </span>
                                @else
                                    <span class="badge badge-inactive">
                                        <span class="badge-dot"></span>
                                        <span>Archived</span>
                                    </span>
                                @endif
                            </td>
                            <td class="text-center">
                                <div style="display: inline-flex; align-items: center; gap: 8px;">
                                    <button type="button" class="btn-action-detail" onclick="openDetailDrawer({{ $recipe->id }})" title="Lihat Detail Resep">
                                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8S1 12 1 12z"></path>
                                            <circle cx="12" cy="12" r="3"></circle>
                                        </svg>
                                        <span>Detail</span>
                                    </button>
                                    <button type="button" class="btn-action-edit" onclick="openEditRecipeModal({{ $recipe->id }})" title="Ubah Resep">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                                            <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                                        </svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center" style="padding: 36px 20px; color: #64748B;">
                                <div style="display: flex; flex-direction: column; align-items: center; gap: 8px;">
                                    <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="#CBD5E1" stroke-width="1.5">
                                        <circle cx="12" cy="12" r="10"></circle>
                                        <line x1="8" y1="12" x2="16" y2="12"></line>
                                    </svg>
                                    <span>Tidak ada data resep yang sesuai dengan filter pencarian.</span>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div style="margin-top: 22px;">
            {{ $recipes->links() }}
        </div>
    </div>
</div>
@endsection

@section('modals')
{{-- ==========================================================================
     PANEL 2: RIGHT DRAWER DETAIL DATA RESEP (SLIDE-OVER)
     ========================================================================== --}}
<div class="recipe-drawer-backdrop" id="recipeDrawerBackdrop" onclick="closeRecipeDrawer()"></div>
<aside class="recipe-drawer" id="recipeDrawer" role="dialog" aria-modal="true" aria-label="Detail Data Resep" aria-hidden="true" tabindex="-1">
    <div class="recipe-drawer-scroll">
        {{-- Header Drawer --}}
        <div class="recipe-drawer-header">
            <span class="drawer-pretitle">Detail Data Resep</span>
            <button type="button" class="drawer-close-btn" onclick="closeRecipeDrawer()" aria-label="Tutup">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
            </button>
        </div>

        {{-- Hero Status & Judul Resep --}}
        <div class="drawer-hero-info">
            <div>
                <span id="drawerBadgeStatus" class="badge badge-active">
                    <span class="badge-dot"></span>
                    <span id="drawerBadgeStatusText">Active</span>
                </span>
            </div>
            <h2 id="drawerRecipeTitle" class="drawer-recipe-title">Memuat data...</h2>
        </div>

        {{-- Card 1: Informasi Resep --}}
        <div class="drawer-card">
            <h3 class="drawer-card-title">Informasi Resep</h3>
            <div class="drawer-info-grid">
                <div class="drawer-info-item">
                    <span class="info-label">ID Resep</span>
                    <span class="info-value" id="drawerIdResep">-</span>
                </div>
                <div class="drawer-info-item">
                    <span class="info-label">ID Menu</span>
                    <span class="info-value" id="drawerIdMenu">-</span>
                </div>
                <div class="drawer-info-item" style="grid-column: span 2;">
                    <span class="info-label">Menu Masakan</span>
                    <span class="info-value" id="drawerNamaMenu">-</span>
                </div>
                <div class="drawer-info-item">
                    <span class="info-label">Ukuran Porsi</span>
                    <span class="info-value" id="drawerPorsi">-</span>
                </div>
                <div class="drawer-info-item">
                    <span class="info-label">Versi Resep</span>
                    <span class="info-value" id="drawerVersion">-</span>
                </div>
            </div>
        </div>

        {{-- Card 2: Bahan Baku (per Porsi) --}}
        <div class="drawer-card">
            <h3 class="drawer-card-title">Bahan (per Porsi)</h3>
            <div id="drawerBahanList" class="drawer-item-list">
                <div style="font-size: 13px; color: #94A3B8;">Memuat bahan...</div>
            </div>
        </div>

        {{-- Card 3: Kandungan Gizi --}}
        <div class="drawer-card">
            <h3 class="drawer-card-title">Kandungan Gizi</h3>
            <div id="drawerGiziList" class="drawer-item-list">
                <div style="font-size: 13px; color: #94A3B8;">Memuat gizi...</div>
            </div>
        </div>

        {{-- Footer Actions Drawer --}}
        <div class="drawer-footer">
            <form id="formDrawerToggleStatus" method="POST" action="" style="flex: 1;">
                @csrf
                <button type="submit" id="btnDrawerToggleStatus" class="btn-drawer-danger">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                    <span id="btnDrawerToggleStatusText">Arsipkan</span>
                </button>
            </form>
            <button type="button" id="btnDrawerEdit" class="btn-drawer-edit" onclick="editFromDrawer()" style="flex: 1;">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 20h9"></path><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"></path></svg>
                <span>Edit</span>
            </button>
        </div>
    </div>
</aside>

{{-- ==========================================================================
     PANEL 3: MODAL DIALOG TAMBAH / UBAH DATA RESEP
     ========================================================================== --}}
<div class="modal-backdrop" id="modalRecipeForm">
    <div class="modal-dialog modal-recipe-dialog">
        <form id="recipeMainForm" method="POST" action="{{ route('staff.recipes.store') }}" onsubmit="validateAndSubmitRecipe(event)">
            @csrf
            <input type="hidden" name="_method" id="recipeFormMethod" value="POST">
            <input type="hidden" id="recipeEditId" value="">

            <div class="modal-header">
                <div>
                    <h3 class="modal-title" id="recipeModalTitle">Tambah Data Resep</h3>
                    <p class="modal-subtitle" id="recipeModalSubtitle">Lengkapi formulir komposisi bahan dan nilai gizi resep</p>
                </div>
                <button type="button" class="modal-close-btn" onclick="closeRecipeModal()">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                </button>
            </div>

            <div class="modal-body" style="max-height: calc(85vh - 140px); overflow-y: auto;">
                {{-- Alert Peringatan Jika Resep Sudah Dipakai di Batch --}}
                <div id="recipeBatchWarning" class="recipe-batch-alert" style="display: none;">
                    <div class="batch-alert-icon">
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path>
                            <line x1="12" y1="9" x2="12" y2="13"></line>
                            <line x1="12" y1="17" x2="12.01" y2="17"></line>
                        </svg>
                    </div>
                    <div>
                        <strong style="display: block; font-size: 13.5px; margin-bottom: 2px;">Resep Sudah Digunakan dalam Batch Produksi</strong>
                        <p style="margin: 0; font-size: 12.5px; line-height: 1.4;">
                            Sesuai aturan sistem, resep yang pernah digunakan dalam batch tidak boleh diubah langsung. Menyimpan formulir ini akan <strong>membuat Versi Baru (<span id="batchNextVersion">v2</span>)</strong> secara otomatis dan mengarsipkan versi ini.
                        </p>
                    </div>
                </div>

                {{-- Section 1: Informasi Resep --}}
                <div class="form-section-card">
                    <h4 class="form-section-heading" style="margin-bottom: 12px;">Informasi Resep</h4>
                    <div class="form-grid-2">
                        <div class="form-group">
                            <label class="form-label">ID Resep</label>
                            <input type="text" id="recipeFormIdDisplay" class="form-input form-input-readonly" value="Otomatis" readonly>
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="recipeFormMenuId">Menu Masakan <span class="text-danger">*</span></label>
                            <select id="recipeFormMenuId" name="menu_id" class="form-select" required>
                                <option value="">-- Pilih Menu --</option>
                                @foreach ($allMenus as $m)
                                    <option value="{{ $m->id }}">{{ $m->formatted_id }} - {{ $m->nama }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="form-grid-2">
                        <div class="form-group">
                            <label class="form-label" for="recipeFormPorsi">Ukuran Porsi <span class="text-danger">*</span></label>
                            <select id="recipeFormPorsi" name="ukuran_porsi" class="form-select" required>
                                <option value="kecil">Kecil</option>
                                <option value="besar" selected>Besar</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="recipeFormStatus">Status Resep <span class="text-danger">*</span></label>
                            <select id="recipeFormStatus" name="status" class="form-select" required>
                                <option value="active" selected>Active</option>
                                <option value="archived">Archived</option>
                            </select>
                        </div>
                    </div>
                </div>

                {{-- Section 2: Bahan Baku (Multi Baris Berulang) --}}
                <div class="form-section-card">
                    <div class="form-section-header">
                        <div>
                            <h4 class="form-section-heading">Bahan Baku (per Porsi) <span class="text-danger">*</span></h4>
                            <p class="form-section-sub">Pilih bahan aktif, takaran harus angka &gt; 0, satuan terisi otomatis.</p>
                        </div>
                    </div>

                    <div id="recipeItemsContainer" class="repeater-container">
                        <!-- Baris bahan dinamis dirender via JS -->
                    </div>

                    <button type="button" class="btn-repeater-add" id="btnAddIngredientRow" onclick="addIngredientRow()">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                        <span>Tambah Bahan Lain</span>
                    </button>
                </div>

                {{-- Section 3: Kandungan Gizi (Multi Baris Berulang) --}}
                <div class="form-section-card">
                    <div class="form-section-header">
                        <div>
                            <h4 class="form-section-heading">Kandungan Gizi</h4>
                            <p class="form-section-sub">Nilai gizi satu resep per ukuran porsi (angka &ge; 0).</p>
                        </div>
                        <button type="button" class="btn-small-new-nutrition" onclick="openModalAddNutritionType()">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                            <span>Tambah Kategori Gizi</span>
                        </button>
                    </div>

                    <div id="recipeNutritionsContainer" class="repeater-container">
                        <!-- Baris gizi dinamis dirender via JS -->
                    </div>

                    <button type="button" class="btn-repeater-add" id="btnAddNutritionRow" onclick="addNutritionRow()">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                        <span>Tambah Gizi Lain</span>
                    </button>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn-secondary" onclick="closeRecipeModal()">
                    <span>Batal</span>
                </button>
                <button type="submit" class="btn-primary-green" id="btnSubmitRecipe">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"></polyline></svg>
                    <span id="btnSubmitRecipeText">Simpan</span>
                </button>
            </div>
        </form>
    </div>
</div>

{{-- ==========================================================================
     PANEL 4: POP-UP MODAL KECIL TAMBAH KATEGORI / JENIS GIZI BARU
     ========================================================================== --}}
<div class="modal-backdrop-sub" id="modalAddNutritionType" style="display: none;">
    <div class="modal-dialog-sub">
        <form id="formAddNutritionType" onsubmit="submitNutritionType(event)">
            @csrf
            <div class="modal-header" style="padding: 18px 20px;">
                <div>
                    <h3 class="modal-title" style="font-size: 16px;">Tambah Jenis Gizi Baru</h3>
                    <p class="modal-subtitle">Tambah jenis nutrisi ke master data global</p>
                </div>
                <button type="button" class="modal-close-btn" onclick="closeModalAddNutritionType()">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                </button>
            </div>
            <div class="modal-body" style="padding: 20px;">
                <div id="nutritionTypeAlertError" class="alert alert-error" style="display: none; padding: 10px 14px; font-size: 13px; margin-bottom: 14px;"></div>
                <div class="form-group" style="margin-bottom: 16px;">
                    <label class="form-label" for="newNutritionName">Nama Jenis Gizi <span class="text-danger">*</span></label>
                    <input type="text" id="newNutritionName" name="nama" class="form-input" placeholder="Contoh: Vitamin A, Seng, dll" required>
                </div>
                <div class="form-group" style="margin-bottom: 0;">
                    <label class="form-label" for="newNutritionUnit">Satuan <span class="text-danger">*</span></label>
                    <select id="newNutritionUnit" name="satuan" class="form-select" required>
                        <option value="kkal">kkal (Kilokalori)</option>
                        <option value="g" selected>g (Gram)</option>
                        <option value="mg">mg (Miligram)</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer" style="padding: 14px 20px;">
                <button type="button" class="btn-secondary" onclick="closeModalAddNutritionType()" style="padding: 8px 16px;">
                    Batal
                </button>
                <button type="submit" class="btn-primary-green" id="btnSaveNutritionType" style="padding: 8px 18px;">
                    Simpan
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('styles')
@php
    $materialsJson = $allMaterials->map(fn ($m) => [
        'id' => $m->id,
        'nama' => $m->nama,
        'satuan' => $m->satuan,
    ])->values();

    $nutritionTypesJson = $allNutritionTypes->map(fn ($n) => [
        'id' => $n->id,
        'nama' => $n->nama,
        'satuan' => $n->satuan,
    ])->values();
@endphp
<script>
    // Data Master yang Tersedia untuk Repeater JavaScript
    window.AVAILABLE_MATERIALS = @json($materialsJson);
    window.AVAILABLE_NUTRITION_TYPES = @json($nutritionTypesJson);

    window.RECIPE_CONFIG = {
        storeUrl: "{{ route('staff.recipes.store') }}",
        nutritionTypeStoreUrl: "{{ route('staff.nutrition-types.store') }}",
        csrfToken: "{{ csrf_token() }}",
    };
</script>
@endpush
