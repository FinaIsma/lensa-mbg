@extends('layouts.staff')

@section('title', 'Data Menu')

@section('content')
<div class="menu-resep-page">
    {{-- Header Sesuai Mockup --}}
    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 24px;">
        <div style="display: flex; align-items: center; gap: 14px;">
            <a href="{{ route('staff.menu-resep.index') }}" class="btn-back" title="Kembali ke Menu & Resep">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="15 18 9 12 15 6"></polyline>
                </svg>
            </a>
            <div>
                <h1 class="page-title" style="margin: 0; font-size: 24px; font-weight: 700; color: #071E49;">Data Menu</h1>
                <p class="page-subtitle" style="margin: 2px 0 0; font-size: 13px; color: #64748B;">Kelola data menu untuk SPPG ini</p>
            </div>
        </div>
    </div>

    {{-- Filter Bar Satu Baris Sesuai Mockup --}}
    <div class="section-card" style="padding: 16px 20px; margin-bottom: 22px;">
        <form method="GET" action="{{ route('staff.menus.index') }}" style="display: flex; gap: 14px; align-items: center; flex-wrap: wrap;">
            <div style="flex: 1; min-width: 220px; position: relative;">
                <input type="text" name="q" value="{{ request('q') }}" placeholder="Cari ID, Nama Menu" class="form-input" style="padding-left: 38px;">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#94A3B8" stroke-width="2" style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); pointer-events: none;">
                    <circle cx="11" cy="11" r="8"></circle>
                    <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                </svg>
            </div>
            <div style="width: 200px;">
                <select name="kategori" class="form-select" onchange="this.form.submit()">
                    <option value="">Semua Kategori</option>
                    @foreach (\App\Models\Menu::KATEGORI as $key => $label)
                        <option value="{{ $key }}" {{ request('kategori') === $key ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div style="width: 170px;">
                <select name="status" class="form-select" onchange="this.form.submit()">
                    <option value="">Semua Status</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                </select>
            </div>
            <button type="button" class="btn-primary" onclick="openAddMenuModal()" style="display: inline-flex; align-items: center; gap: 8px; background-color: #071E49; border-radius: 9px; padding: 10px 18px; font-weight: 600; cursor: pointer; white-space: nowrap;">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                <span>Tambah Menu</span>
            </button>
            @if(request()->hasAny(['q', 'kategori', 'status']))
                <a href="{{ route('staff.menus.index') }}" class="btn-secondary" style="padding: 10px 14px; border-radius: 9px; text-decoration: none;">Reset</a>
            @endif
        </form>
    </div>

    {{-- Tabel Data Menu Sesuai Mockup --}}
    <div class="section-card">
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th class="col-id" style="width: 85px;">ID</th>
                        <th style="min-width: 180px;">Nama</th>
                        <th style="min-width: 160px;">Kategori</th>
                        <th>Deskripsi</th>
                        <th style="width: 110px;">Status</th>
                        <th class="text-center" style="width: 80px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($menus as $menu)
                        @php
                            $menuPayload = [
                                'id' => $menu->id,
                                'formatted_id' => $menu->formatted_id,
                                'nama' => $menu->nama,
                                'kategori' => $menu->kategori,
                                'deskripsi' => $menu->deskripsi,
                                'is_active' => (bool) $menu->is_active,
                            ];
                        @endphp
                        <tr>
                            <td class="col-id" style="font-weight: 700; color: #2563EB;">{{ $menu->formatted_id }}</td>
                            <td class="col-name" style="font-weight: 600; color: #071E49;">{{ $menu->nama }}</td>
                            <td>
                                <span style="display: inline-block; padding: 3px 10px; border-radius: 6px; background: #F1F5F9; font-size: 12.5px; font-weight: 500; color: #334155;">
                                    {{ $menu->kategori_label }}
                                </span>
                            </td>
                            <td class="col-desc" style="color: #475569; font-size: 13px;">{{ $menu->deskripsi ?: '-' }}</td>
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
                                    onclick="openEditMenuModal({{ json_encode($menuPayload) }})"
                                    title="Ubah Data Menu">
                                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                                        <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                                    </svg>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center" style="padding: 36px 20px; color: #64748B;">
                                <div style="display: flex; flex-direction: column; align-items: center; gap: 8px;">
                                    <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="#CBD5E1" stroke-width="1.5">
                                        <circle cx="12" cy="12" r="10"></circle>
                                        <line x1="8" y1="12" x2="16" y2="12"></line>
                                    </svg>
                                    <span>Tidak ada data menu yang sesuai filter pencarian.</span>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination Footer Sesuai Mockup --}}
        <div class="table-pagination-footer">
            <span class="pagination-info-text">
                Menampilkan {{ $menus->firstItem() ?? 0 }}-{{ $menus->lastItem() ?? 0 }} dari {{ $menus->total() }} data
            </span>
            <div>
                {{ $menus->links() }}
            </div>
        </div>
    </div>
</div>
@endsection

@section('modals')
{{-- ==========================================================================
     POP-UP FORM TAMBAH / UBAH DATA MENU SESUAI MOCKUP PANEL KANAN
     ========================================================================== --}}
<div class="modal-backdrop" id="modalMenuForm">
    <div class="modal-dialog modal-menu-dialog">
        <form id="formMenuMain" method="POST" action="{{ route('staff.menus.store') }}">
            @csrf
            <input type="hidden" name="_method" id="menuFormMethod" value="POST">

            <div class="modal-header" style="border-bottom: none; padding: 22px 24px 10px;">
                <h3 class="modal-title" id="menuModalTitle" style="font-size: 18px; font-weight: 700; color: #071E49;">Tambah/Ubah Data Menu</h3>
                <button type="button" class="modal-close-btn" onclick="closeMenuModal()">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                </button>
            </div>

            <div class="modal-body" style="padding: 10px 24px 18px;">
                {{-- Card 1: Informasi Menu --}}
                <div class="menu-form-card">
                    <h4 class="menu-form-heading">Informasi Menu</h4>
                    <div class="menu-form-grid">
                        <div class="menu-form-row">
                            <label class="menu-field-label" for="menuInputId">ID</label>
                            <input type="text" id="menuInputId" class="form-input form-input-readonly" value="Otomatis" readonly>
                        </div>
                        <div class="menu-form-row">
                            <label class="menu-field-label" for="menuInputNama">Nama <span class="text-danger">*</span></label>
                            <input type="text" id="menuInputNama" name="nama" class="form-input" placeholder="Nama Menu Masakan" required>
                        </div>
                        <div class="menu-form-row">
                            <label class="menu-field-label" for="menuInputKategori">Kategori <span class="text-danger">*</span></label>
                            <select id="menuInputKategori" name="kategori" class="form-select" required>
                                @foreach (\App\Models\Menu::KATEGORI as $key => $label)
                                    <option value="{{ $key }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="menu-form-row">
                            <label class="menu-field-label" for="menuInputStatus">Status</label>
                            <select id="menuInputStatus" name="is_active" class="form-select">
                                <option value="1">Active</option>
                                <option value="0">Inactive</option>
                            </select>
                        </div>
                    </div>
                </div>

                {{-- Card 2: Deskripsi --}}
                <div class="menu-form-card" style="margin-top: 14px;">
                    <h4 class="menu-form-heading">Deskripsi</h4>
                    <div style="margin-top: 4px;">
                        <textarea id="menuInputDeskripsi" name="deskripsi" class="form-textarea" rows="3" placeholder="Nasi putih, ayam goreng, sayur bayam bening"></textarea>
                    </div>
                </div>
            </div>

            <div class="modal-footer" style="padding: 14px 24px 22px; gap: 14px; background: transparent; border-top: none;">
                <button type="button" class="btn-menu-cancel" onclick="closeMenuModal()" style="flex: 1;">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                    <span>Batal</span>
                </button>
                <button type="submit" class="btn-menu-submit" style="flex: 1;">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"></polyline></svg>
                    <span>Simpan</span>
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('styles')
<script>
    window.openAddMenuModal = function () {
        const form = document.getElementById('formMenuMain');
        if (!form) return;

        form.reset();
        form.action = "{{ route('staff.menus.store') }}";
        document.getElementById('menuFormMethod').value = 'POST';

        document.getElementById('menuModalTitle').textContent = 'Tambah/Ubah Data Menu';
        document.getElementById('menuInputId').value = 'Otomatis';
        document.getElementById('menuInputNama').value = '';
        document.getElementById('menuInputKategori').value = 'makanan_siap_santap';
        document.getElementById('menuInputStatus').value = '1';
        document.getElementById('menuInputDeskripsi').value = '';

        window.openModal('modalMenuForm');
    };

    window.openEditMenuModal = function (data) {
        const form = document.getElementById('formMenuMain');
        if (!form) return;

        form.reset();
        form.action = `/staff/menu/${data.id}`;
        document.getElementById('menuFormMethod').value = 'PUT';

        document.getElementById('menuModalTitle').textContent = 'Tambah/Ubah Data Menu';
        document.getElementById('menuInputId').value = data.formatted_id || `MN${data.id}`;
        document.getElementById('menuInputNama').value = data.nama || '';
        document.getElementById('menuInputKategori').value = data.kategori || 'makanan_siap_santap';
        document.getElementById('menuInputStatus').value = data.is_active ? '1' : '0';
        document.getElementById('menuInputDeskripsi').value = data.deskripsi || '';

        window.openModal('modalMenuForm');
    };

    window.closeMenuModal = function () {
        window.closeModal('modalMenuForm');
    };
</script>
@endpush
