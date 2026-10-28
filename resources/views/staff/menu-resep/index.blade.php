@extends('layouts.staff')

@section('title', 'Data Menu dan Resep')

@section('content')
<div class="menu-resep-page">
    {{-- Header Section --}}
    <div class="page-title-section">
        <h1 class="page-title">Data Menu dan Resep</h1>
        <p class="page-subtitle">Kelola data vendor penyedia bahan baku pangan untuk SPPG ini.</p>
    </div>

    {{-- 4 Stat Cards --}}
    <div class="stat-cards-grid">
        <div class="stat-card">
            <div class="stat-icon-wrapper icon-green">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="20 6 9 17 4 12"></polyline>
                </svg>
            </div>
            <div class="stat-content">
                <span class="stat-label">Total Menu</span>
                <span class="stat-value">{{ number_format($totalMenuActive, 0, ',', '.') }}</span>
                <span class="stat-subtext">Menu Aktif</span>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon-wrapper icon-red">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
            </div>
            <div class="stat-content">
                <span class="stat-label">Total Menu</span>
                <span class="stat-value">{{ number_format($totalMenuInactive, 0, ',', '.') }}</span>
                <span class="stat-subtext">Menu Tidak Aktif</span>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon-wrapper icon-green">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="20 6 9 17 4 12"></polyline>
                </svg>
            </div>
            <div class="stat-content">
                <span class="stat-label">Total Resep</span>
                <span class="stat-value">{{ number_format($totalRecipeActive, 0, ',', '.') }}</span>
                <span class="stat-subtext">Resep Aktif</span>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon-wrapper icon-red">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
            </div>
            <div class="stat-content">
                <span class="stat-label">Total Resep</span>
                <span class="stat-value">{{ number_format($totalRecipeInactive, 0, ',', '.') }}</span>
                <span class="stat-subtext">Resep Tidak Aktif</span>
            </div>
        </div>
    </div>

    {{-- Section 1: Data Menu --}}
    <div class="section-card">
        <div class="section-card-header">
            <h2 class="section-card-title">Data Menu</h2>
            <div class="header-actions">
                <button type="button" class="btn-outline-primary" onclick="openModal('modalAddMenu')">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="12" y1="5" x2="12" y2="19"></line>
                        <line x1="5" y1="12" x2="19" y2="12"></line>
                    </svg>
                    <span>Tambah Menu</span>
                </button>
                <a href="{{ route('staff.menus.index') }}" class="btn-dark">
                    <span>Lihat Selengkapnya</span>
                </a>
            </div>
        </div>

        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th class="col-id">ID</th>
                        <th>Nama</th>
                        <th>Kategori</th>
                        <th>Deskripsi</th>
                        <th>Status</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($menus as $menu)
                        <tr>
                            <td class="col-id">{{ $menu->formatted_id }}</td>
                            <td class="col-name">{{ $menu->nama }}</td>
                            <td>
                                <span style="display: inline-block; padding: 2px 8px; border-radius: 6px; background: #F1F5F9; font-size: 12px; font-weight: 500; color: #334155;">
                                    {{ $menu->kategori_label }}
                                </span>
                            </td>
                            <td class="col-desc">{{ $menu->deskripsi ?? '-' }}</td>
                            <td>
                                @if ($menu->is_active)
                                    <span class="badge badge-active">
                                        <span class="badge-dot"></span>
                                        <span>Active</span>
                                    </span>
                                @else
                                    <span class="badge badge-inactive">
                                        <span class="badge-dot"></span>
                                        <span>Inactive</span>
                                    </span>
                                @endif
                            </td>
                            <td class="text-center">
                                <button type="button" class="btn-action-edit"
                                    data-edit-menu
                                    data-id="{{ $menu->id }}"
                                    data-nama="{{ $menu->nama }}"
                                    data-kategori="{{ $menu->kategori }}"
                                    data-deskripsi="{{ $menu->deskripsi }}"
                                    data-active="{{ $menu->is_active ? '1' : '0' }}"
                                    title="Edit Menu">
                                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                                        <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                                    </svg>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center" style="padding: 30px; color: #64748B;">
                                Belum ada data menu.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Section 2: Data Resep --}}
    <div class="section-card">
        <div class="section-card-header">
            <h2 class="section-card-title">Data Resep</h2>
            <div class="header-actions">
                <button type="button" class="btn-outline-primary" onclick="openModal('modalAddRecipe')">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="12" y1="5" x2="12" y2="19"></line>
                        <line x1="5" y1="12" x2="19" y2="12"></line>
                    </svg>
                    <span>Tambah Resep</span>
                </button>
                <a href="{{ route('staff.recipes.index') }}" class="btn-dark">
                    <span>Lihat Selengkapnya</span>
                </a>
            </div>
        </div>

        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th class="col-id">ID</th>
                        <th>Nama Resep</th>
                        <th>Porsi</th>
                        <th>Version</th>
                        <th>Bahan</th>
                        <th>Status</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($recipes as $recipe)
                        @php
                            $itemsJson = $recipe->items->map(function ($it) {
                                return [
                                    'id' => $it->id,
                                    'material_id' => $it->material_id,
                                    'material_nama' => $it->material?->nama ?? '-',
                                    'qty_per_porsi' => (float) $it->qty_per_porsi,
                                    'satuan' => $it->satuan,
                                ];
                            })->values();
                        @endphp
                        <tr>
                            <td class="col-id">{{ $recipe->formatted_id }}</td>
                            <td class="col-name">{{ $recipe->menu?->nama ?? 'Resep #' . $recipe->id }}</td>
                            <td>{{ ucfirst($recipe->ukuran_porsi) }}</td>
                            <td>{{ $recipe->version }}</td>
                            <td class="col-bahan">{{ $recipe->bahan_list }}</td>
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
                                <button type="button" class="btn-action-detail"
                                    data-detail-recipe
                                    data-id="{{ $recipe->id }}"
                                    data-kode="{{ $recipe->formatted_id }}"
                                    data-menu="{{ $recipe->menu?->nama ?? '-' }}"
                                    data-porsi="{{ ucfirst($recipe->ukuran_porsi) }}"
                                    data-version="{{ $recipe->version }}"
                                    data-status="{{ $recipe->status }}"
                                    data-items="{{ json_encode($itemsJson) }}">
                                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8S1 12 1 12z"></path>
                                        <circle cx="12" cy="12" r="3"></circle>
                                    </svg>
                                    <span>Detail</span>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center" style="padding: 30px; color: #64748B;">
                                Belum ada data resep.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@section('modals')
{{-- Modal Edit Menu --}}
<div class="modal-backdrop" id="modalEditMenu">
    <div class="modal-dialog">
        <form id="formEditMenu" method="POST" action="">
            @csrf
            @method('PUT')
            <div class="modal-header">
                <h3 class="modal-title">Edit Data Menu</h3>
                <button type="button" class="modal-close-btn" onclick="closeModal('modalEditMenu')">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                </button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label class="form-label" for="editMenuNama">Nama Menu <span style="color:#DC2626">*</span></label>
                    <input type="text" id="editMenuNama" name="nama" class="form-input" required>
                </div>
                <div class="form-group">
                    <label class="form-label" for="editMenuKategori">Kategori <span style="color:#DC2626">*</span></label>
                    <select id="editMenuKategori" name="kategori" class="form-select" required>
                        @foreach (\App\Models\Menu::KATEGORI as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label" for="editMenuDeskripsi">Deskripsi</label>
                    <textarea id="editMenuDeskripsi" name="deskripsi" class="form-textarea" placeholder="Rincian lauk dan sayur"></textarea>
                </div>
                <div class="form-group">
                    <label class="form-label" for="editMenuStatus">Status Menu</label>
                    <select id="editMenuStatus" name="is_active" class="form-select">
                        <option value="1">Aktif (Active)</option>
                        <option value="0">Tidak Aktif (Inactive)</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-secondary" onclick="closeModal('modalEditMenu')">Batal</button>
                <button type="submit" class="btn-primary">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>

{{-- Modal Tambah Menu --}}
<div class="modal-backdrop" id="modalAddMenu">
    <div class="modal-dialog">
        <form method="POST" action="{{ route('staff.menus.store') }}">
            @csrf
            <div class="modal-header">
                <h3 class="modal-title">Tambah Menu Baru</h3>
                <button type="button" class="modal-close-btn" onclick="closeModal('modalAddMenu')">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                </button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label class="form-label" for="addMenuNama">Nama Menu <span style="color:#DC2626">*</span></label>
                    <input type="text" id="addMenuNama" name="nama" class="form-input" placeholder="Contoh: Nasi Rendang Sayur Singkong" required>
                </div>
                <div class="form-group">
                    <label class="form-label" for="addMenuKategori">Kategori <span style="color:#DC2626">*</span></label>
                    <select id="addMenuKategori" name="kategori" class="form-select" required>
                        @foreach (\App\Models\Menu::KATEGORI as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label" for="addMenuDeskripsi">Deskripsi</label>
                    <textarea id="addMenuDeskripsi" name="deskripsi" class="form-textarea" placeholder="Nasi putih, rendang daging, sayur daun singkong rebus"></textarea>
                </div>
                <div class="form-group">
                    <label class="form-label" for="addMenuStatus">Status Menu</label>
                    <select id="addMenuStatus" name="is_active" class="form-select">
                        <option value="1" selected>Aktif (Active)</option>
                        <option value="0">Tidak Aktif (Inactive)</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-secondary" onclick="closeModal('modalAddMenu')">Batal</button>
                <button type="submit" class="btn-primary">Simpan Menu</button>
            </div>
        </form>
    </div>
</div>

{{-- Modal Detail Resep --}}
<div class="modal-backdrop" id="modalDetailRecipe">
    <div class="modal-dialog" style="max-width: 620px;">
        <div class="modal-header">
            <h3 class="modal-title">Detail Resep Bahan</h3>
            <button type="button" class="modal-close-btn" onclick="closeModal('modalDetailRecipe')">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
            </button>
        </div>
        <div class="modal-body">
            <div class="detail-info-grid">
                <div class="detail-item">
                    <dt>Kode Resep</dt>
                    <dd id="detailRecipeKode">-</dd>
                </div>
                <div class="detail-item">
                    <dt>Menu Masakan</dt>
                    <dd id="detailRecipeMenu">-</dd>
                </div>
                <div class="detail-item">
                    <dt>Ukuran Porsi</dt>
                    <dd id="detailRecipePorsi">-</dd>
                </div>
                <div class="detail-item">
                    <dt>Status & Versi</dt>
                    <dd style="display: flex; align-items: center; gap: 8px;">
                        <span id="detailRecipeStatus" class="badge badge-active"><span class="badge-dot"></span> Active</span>
                        <span style="font-size: 12px; color: #64748B;">v<span id="detailRecipeVersion">1</span></span>
                    </dd>
                </div>
            </div>

            <h4 style="font-size: 14px; font-weight: 700; color: #071E49; margin-bottom: 8px;">Komposisi Bahan Baku (Multi-Item)</h4>
            <div class="table-responsive">
                <table class="ingredient-table">
                    <thead>
                        <tr>
                            <th style="width: 40px;">No</th>
                            <th>Nama Bahan</th>
                            <th style="width: 140px;">Takaran per Porsi</th>
                            <th style="width: 80px;">Satuan</th>
                        </tr>
                    </thead>
                    <tbody id="detailRecipeTableBody">
                    </tbody>
                </table>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn-primary" onclick="closeModal('modalDetailRecipe')">Tutup</button>
        </div>
    </div>
</div>

{{-- Modal Tambah Resep --}}
<div class="modal-backdrop" id="modalAddRecipe">
    <div class="modal-dialog" style="max-width: 640px;">
        <form method="POST" action="{{ route('staff.recipes.store') }}">
            @csrf
            <div class="modal-header">
                <h3 class="modal-title">Tambah Resep Baru</h3>
                <button type="button" class="modal-close-btn" onclick="closeModal('modalAddRecipe')">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                </button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label class="form-label" for="addRecipeMenuId">Pilih Menu <span style="color:#DC2626">*</span></label>
                    <select id="addRecipeMenuId" name="menu_id" class="form-select" required>
                        <option value="">-- Pilih Menu --</option>
                        @foreach ($allMenus as $m)
                            <option value="{{ $m->id }}">{{ $m->formatted_id }} - {{ $m->nama }}</option>
                        @endforeach
                    </select>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px;">
                    <div class="form-group">
                        <label class="form-label" for="addRecipePorsi">Ukuran Porsi <span style="color:#DC2626">*</span></label>
                        <select id="addRecipePorsi" name="ukuran_porsi" class="form-select" required>
                            <option value="kecil" selected>Kecil (PAUD / SD)</option>
                            <option value="besar">Besar (SMP / Umum)</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="addRecipeStatus">Status Resep</label>
                        <select id="addRecipeStatus" name="status" class="form-select">
                            <option value="active" selected>Active</option>
                            <option value="archived">Archived</option>
                        </select>
                    </div>
                </div>

                <div style="display: flex; align-items: center; justify-content: space-between; margin-top: 10px; margin-bottom: 8px;">
                    <label class="form-label" style="margin: 0;">Bahan-bahan Resep (Multi-Item) <span style="color:#DC2626">*</span></label>
                    <button type="button" class="btn-outline-primary" id="btnAddIngredient" style="padding: 4px 10px; font-size: 12px;">
                        Tambah Bahan
                    </button>
                </div>

                <div id="ingredientListContainer" style="display: flex; flex-direction: column; gap: 8px;">
                    <div class="ingredient-form-row" style="display: grid; grid-template-columns: 2fr 1.2fr auto; gap: 8px; align-items: center;">
                        <select name="items[0][material_id]" class="form-select" required>
                            <option value="">-- Pilih Bahan --</option>
                            @foreach ($allMaterials as $mat)
                                <option value="{{ $mat->id }}">{{ $mat->nama }} ({{ $mat->satuan }})</option>
                            @endforeach
                        </select>
                        <input type="number" step="0.0001" min="0.0001" name="items[0][qty_per_porsi]" class="form-input" placeholder="Takaran/porsi" required>
                        <button type="button" class="btn-remove-ingredient" style="color: #DC2626; border: none; background: transparent; padding: 6px; cursor: pointer;">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
                        </button>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-secondary" onclick="closeModal('modalAddRecipe')">Batal</button>
                <button type="submit" class="btn-primary">Simpan Resep</button>
            </div>
        </form>
    </div>
</div>

<template id="ingredientRowTemplate">
    <div class="ingredient-form-row" style="display: grid; grid-template-columns: 2fr 1.2fr auto; gap: 8px; align-items: center;">
        <select name="items[__INDEX__][material_id]" class="form-select" required>
            <option value="">-- Pilih Bahan --</option>
            @foreach ($allMaterials as $mat)
                <option value="{{ $mat->id }}">{{ $mat->nama }} ({{ $mat->satuan }})</option>
            @endforeach
        </select>
        <input type="number" step="0.0001" min="0.0001" name="items[__INDEX__][qty_per_porsi]" class="form-input" placeholder="Takaran/porsi" required>
        <button type="button" class="btn-remove-ingredient" style="color: #DC2626; border: none; background: transparent; padding: 6px; cursor: pointer;">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
        </button>
    </div>
</template>
@endsection
