@extends('layouts.staff')

@section('title', 'Pembelian Bahan - Staff SPPG')

@section('content')
<div class="space-y-6 pb-8">
    {{-- Page Title Section --}}
    <div class="page-title-section">
        <h1 class="page-title">Pembelian Bahan</h1>
        <p class="page-subtitle">Kelola dan catat nota belanja bahan baku pangan untuk SPPG ini.</p>
    </div>

    {{-- 4 Stat Cards Grid Sesuai Mockup --}}
    <div class="stat-cards-grid">
        {{-- Card 1: Total Pembelian --}}
        <div class="stat-card">
            <div class="stat-icon-wrapper icon-blue">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                    <polyline points="14 2 14 8 20 8"></polyline>
                    <line x1="16" y1="13" x2="8" y2="13"></line>
                    <line x1="16" y1="17" x2="8" y2="17"></line>
                    <polyline points="10 9 9 9 8 9"></polyline>
                </svg>
            </div>
            <div class="stat-content">
                <span class="stat-label">Total Pembelian</span>
                <span class="stat-value">{{ number_format($stats['total_pembelian'], 0, ',', '.') }}</span>
                <span class="stat-subtext">Nota Belanja</span>
            </div>
        </div>

        {{-- Card 2: Menunggu Cek --}}
        <div class="stat-card">
            <div class="stat-icon-wrapper icon-yellow">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10"></circle>
                    <polyline points="12 6 12 12 14 14"></polyline>
                </svg>
            </div>
            <div class="stat-content">
                <span class="stat-label">Menunggu Cek</span>
                <span class="stat-value">{{ number_format($stats['menunggu_cek'], 0, ',', '.') }}</span>
                <span class="stat-subtext">Belum Diterima</span>
            </div>
        </div>

        {{-- Card 3: Selesai Diterima --}}
        <div class="stat-card">
            <div class="stat-icon-wrapper icon-green">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                    <polyline points="22 4 12 14.01 9 11.01"></polyline>
                </svg>
            </div>
            <div class="stat-content">
                <span class="stat-label">Selesai Diterima</span>
                <span class="stat-value">{{ number_format($stats['selesai_diterima'], 0, ',', '.') }}</span>
                <span class="stat-subtext">Masuk Gudang</span>
            </div>
        </div>

        {{-- Card 4: Total Belanja --}}
        <div class="stat-card">
            <div class="stat-icon-wrapper icon-purple">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"></path>
                    <line x1="3" y1="6" x2="21" y2="6"></line>
                    <path d="M16 10a4 4 0 0 1-8 0"></path>
                </svg>
            </div>
            <div class="stat-content">
                <span class="stat-label">Total Belanja</span>
                <span class="stat-value" style="font-size: 21px;">{{ $stats['formatted_total_belanja'] }}</span>
                <span class="stat-subtext">Target Porsi</span>
            </div>
        </div>
    </div>

    {{-- Unified Table Card: Filter Bar + Table Bersatu Sesuai Mockup --}}
    <div class="table-card">
        <form method="GET" action="{{ route('staff.purchases.index') }}" class="filter-bar-form">
            {{-- Search Box --}}
            <div class="search-input-box" style="flex: 1; max-width: 460px;">
                <svg class="search-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="11" cy="11" r="8"></circle>
                    <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                </svg>
                <input type="text" name="q" value="{{ $q }}" placeholder="Cari nota, pemasok" class="input-search">
            </div>

            <div class="filter-actions-group">
                {{-- Dropdown Status Filter --}}
                <div class="select-dropdown-box" style="min-width: 170px;">
                    <select name="status" class="select-status-verif" onchange="this.form.submit()" style="padding-right: 2.25rem;">
                        <option value="">Semua Status</option>
                        <option value="draft" {{ $status === 'draft' ? 'selected' : '' }}>Draft</option>
                        <option value="ordered" {{ $status === 'ordered' ? 'selected' : '' }}>Menunggu Cek</option>
                        <option value="received_partial" {{ $status === 'received_partial' ? 'selected' : '' }}>Sebagian Diterima</option>
                        <option value="received" {{ $status === 'received' ? 'selected' : '' }}>Selesai</option>
                        <option value="cancelled" {{ $status === 'cancelled' ? 'selected' : '' }}>Dibatalkan</option>
                    </select>
                    <svg class="chevron-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="6 9 12 15 18 9"></polyline>
                    </svg>
                </div>

                {{-- Action Button: Catat Pembelian (Pop-up di Bagian Tengah, teks tanpa tanda +) --}}
                <button type="button" class="btn-tambah-staff" onclick="openAddPurchaseModal()">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                        <line x1="12" y1="5" x2="12" y2="19"></line>
                        <line x1="5" y1="12" x2="19" y2="12"></line>
                    </svg>
                    <span>Catat Pembelian</span>
                </button>

                @if(!empty($q) || !empty($status))
                    <a href="{{ route('staff.purchases.index') }}" class="btn-reset-filter" title="Reset filter">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <line x1="18" y1="6" x2="6" y2="18"></line>
                            <line x1="6" y1="6" x2="18" y2="18"></line>
                        </svg>
                        <span>Reset</span>
                    </a>
                @endif
            </div>
        </form>

        {{-- Table Container --}}
        <div class="table-responsive">
            <table class="staff-table data-table">
                <thead>
                    <tr>
                        <th style="min-width: 150px; font-weight: 700; color: #071E49;">No. Nota</th>
                        <th style="min-width: 180px; font-weight: 700; color: #071E49;">Pemasok</th>
                        <th style="min-width: 120px; font-weight: 700; color: #071E49;">Tanggal</th>
                        <th style="min-width: 130px; font-weight: 700; color: #071E49;">Item Belanja</th>
                        <th style="min-width: 140px; font-weight: 700; color: #071E49;">Total Biaya</th>
                        <th style="min-width: 140px; font-weight: 700; color: #071E49;">Status</th>
                        <th style="width: 100px; text-align: center; font-weight: 700; color: #071E49;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($purchases as $purchase)
                        <tr>
                            {{-- No. Nota --}}
                            <td style="font-weight: 600; color: #071E49;">
                                {{ $purchase->purchase_no }}
                            </td>

                            {{-- Pemasok --}}
                            <td style="color: #334155;">
                                {{ $purchase->supplier->nama ?? '-' }}
                            </td>

                            {{-- Tanggal --}}
                            <td style="color: #334155;">
                                {{ $purchase->purchase_date ? $purchase->purchase_date->format('d-m-Y') : '-' }}
                            </td>

                            {{-- Item Belanja --}}
                            <td style="color: #334155;">
                                {{ $purchase->total_items_count }} Bahan
                            </td>

                            {{-- Total Biaya --}}
                            <td style="font-weight: 600; color: #071E49;">
                                {{ $purchase->formatted_total_cost }}
                            </td>

                            {{-- Status Badge --}}
                            <td>
                                <span class="badge-status {{ $purchase->status_badge_class }}">
                                    <span class="status-dot"></span>
                                    <span>{{ $purchase->status_label }}</span>
                                </span>
                            </td>

                            {{-- Aksi --}}
                            <td style="text-align: center;">
                                <div class="action-buttons-group">
                                    {{-- Lihat Detail (Pop-up di Sebelah Kanan) --}}
                                    <a href="{{ route('staff.purchases.show', $purchase->id) }}" 
                                       class="btn-action-icon" 
                                       data-purchase-detail-id="{{ $purchase->id }}"
                                       onclick="return openPurchaseDetailFromLink(event, {{ $purchase->id }});" 
                                       title="Lihat Detail Nota">
                                        <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="pointer-events: none;">
                                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                            <circle cx="12" cy="12" r="3"></circle>
                                        </svg>
                                    </a>

                                    {{-- Edit (Pop-up Form di Bagian Tengah) --}}
                                    @if($purchase->canBeEdited())
                                        <button type="button" class="btn-action-icon" onclick="openEditPurchaseModal({{ $purchase->id }})" title="Ubah Draft Nota">
                                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                                                <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                                            </svg>
                                        </button>
                                    @else
                                        <span class="btn-action-icon disabled" title="Tidak dapat diedit setelah dipesan">
                                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                                                <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                                            </svg>
                                        </span>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7">
                                <div style="padding: 48px 20px; text-align: center; color: #64748B;">
                                    <svg width="44" height="44" viewBox="0 0 24 24" fill="none" stroke="#94A3B8" stroke-width="1.6" style="margin: 0 auto 12px; display: block;">
                                        <path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"></path>
                                        <line x1="3" y1="6" x2="21" y2="6"></line>
                                        <path d="M16 10a4 4 0 0 1-8 0"></path>
                                    </svg>
                                    <p style="font-size: 15px; font-weight: 600; color: #071E49; margin-bottom: 4px;">
                                        Belum ada pembelian. Buat pembelian pertama
                                    </p>
                                    <p style="font-size: 13px; color: #64748B; margin-bottom: 18px;">
                                        Catat nota pengadaan bahan baku pangan untuk kebutuhan operasional SPPG.
                                    </p>
                                    <button type="button" class="btn-tambah-staff" onclick="openAddPurchaseModal()" style="display: inline-flex;">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                            <line x1="12" y1="5" x2="12" y2="19"></line>
                                            <line x1="5" y1="12" x2="19" y2="12"></line>
                                        </svg>
                                        <span>Catat Pembelian Pertama</span>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination & Footer --}}
        @if($purchases->hasPages() || $purchases->total() > 0)
            <div class="table-pagination-footer" style="display: flex; align-items: center; justify-content: space-between; padding: 18px 24px; border-top: 1px solid #F1F5F9; font-size: 13px; color: #64748B;">
                <div>
                    Menampilkan {{ $purchases->firstItem() ?? 0 }}-{{ $purchases->lastItem() ?? 0 }} dari {{ $purchases->total() }} data
                </div>
                <div>
                    {{ $purchases->links('pagination::tailwind') }}
                </div>
            </div>
        @endif
    </div>
</div>
@endsection

@section('modals')
{{-- ==========================================================================
     1. DETAIL NOTA PEMBELIAN: FORM POP UP DI SEBELAH KANAN (RIGHT DRAWER)
     ========================================================================== --}}
<div class="purchase-drawer-backdrop" id="purchaseDrawerBackdrop" onclick="closePurchaseDetailDrawer()"></div>
<aside class="purchase-drawer" id="purchaseDetailDrawer" role="dialog" aria-modal="true" aria-label="Detail Data Pembelian">
    {{-- Header Drawer --}}
    <div class="purchase-drawer-header">
        <div>
            <h2 class="purchase-drawer-title">Detail Nota Pembelian</h2>
            <p class="purchase-drawer-subtitle">Informasi lengkap nota belanja bahan pangan</p>
        </div>
        <button type="button" class="modal-close-corner" onclick="closePurchaseDetailDrawer()" aria-label="Tutup" title="Tutup">
            &times;
        </button>
    </div>

    {{-- Body Drawer (Scrollable) --}}
    <div class="purchase-drawer-scroll">
        {{-- Card 1: Informasi Nota --}}
        <div class="detail-card">
            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 18px;">
                <h3 class="detail-card-title" style="margin-bottom: 0;">Detail Nota Pembelian</h3>
                <span class="badge-status badge-ordered" id="drawerBadgeStatus">
                    <span class="status-dot"></span>
                    <span id="drawerBadgeStatusText">Memuat...</span>
                </span>
            </div>
            <div class="detail-grid-2col">
                <div>
                    <div class="detail-item-row">
                        <span class="detail-item-label">No. Nota :</span>
                        <span class="detail-item-value" id="drawerNoNota" style="font-weight: 600; color: #071E49;">-</span>
                    </div>
                    <div class="detail-item-row">
                        <span class="detail-item-label">Tanggal :</span>
                        <span class="detail-item-value" id="drawerTanggal">-</span>
                    </div>
                    <div class="detail-item-row">
                        <span class="detail-item-label">Toko :</span>
                        <span class="detail-item-value" id="drawerToko">-</span>
                    </div>
                    <div class="detail-item-row">
                        <span class="detail-item-label">Alamat :</span>
                        <span class="detail-item-value" id="drawerAlamat">-</span>
                    </div>
                </div>
                <div>
                    <div class="detail-item-row">
                        <span class="detail-item-label">Dicatat Oleh :</span>
                        <span class="detail-item-value" id="drawerDicatatOleh">-</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Card 2: Rincian Bahan yang Dibeli --}}
        <div class="detail-card">
            <h3 class="detail-card-title">Rincian Bahan yang Dibeli</h3>
            <div class="table-responsive">
                <table class="detail-items-table">
                    <thead>
                        <tr id="drawerTableHeaders">
                            <th style="width: 40px;">No</th>
                            <th>Nama Bahan</th>
                            <th>Jumlah</th>
                            <th>Harga / Satuan</th>
                            <th style="text-align: right;">Total</th>
                        </tr>
                    </thead>
                    <tbody id="drawerItemsTableBody">
                        <tr>
                            <td colspan="5" style="text-align: center; color: #94A3B8;">Memuat item belanja...</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="detail-total-bar">
                <div class="detail-total-text" id="drawerTotalBelanja">
                    Total Belanja : Rp 0
                </div>
            </div>
        </div>

        {{-- Card 3: Status Penerimaan Gudang --}}
        <div class="detail-card">
            <h3 class="detail-card-title">Status Penerimaan Gudang:</h3>
            <div id="drawerReceivingStatusContainer">
                <p class="warehouse-status-empty">
                    Fisik barang belum dikonfirmasi di menu Penerimaan.
                </p>
            </div>
        </div>
    </div>

    {{-- Footer Drawer Actions --}}
    <div class="purchase-drawer-footer">
        {{-- Cetak --}}
        <a id="drawerBtnPrint" href="#" target="_blank" class="btn-cetak">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                <polyline points="14 2 14 8 20 8"></polyline>
                <line x1="16" y1="13" x2="8" y2="13"></line>
                <line x1="16" y1="17" x2="8" y2="17"></line>
            </svg>
            <span>Cetak</span>
        </a>

        {{-- Ubah (hanya draft) --}}
        <button type="button" id="drawerBtnEdit" class="btn-edit-action" style="display: none;" onclick="triggerEditFromDrawer()">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
            </svg>
            <span>Ubah</span>
        </button>

        {{-- Pesan (hanya draft) --}}
        <button type="button" id="drawerBtnOrder" class="btn-pesan-action" style="display: none;" onclick="triggerOrderFromDrawer()">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <line x1="22" y1="2" x2="11" y2="13"></line>
                <polygon points="22 2 15 22 11 13 2 9 22 2"></polygon>
            </svg>
            <span>Pesan</span>
        </button>

        {{-- Batalkan --}}
        <button type="button" id="drawerBtnCancel" class="btn-cancel-action" style="display: none;" onclick="triggerCancelFromDrawer()">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="12" cy="12" r="10"></circle>
                <line x1="15" y1="9" x2="9" y2="15"></line>
                <line x1="9" y1="9" x2="15" y2="15"></line>
            </svg>
            <span>Batalkan</span>
        </button>

        {{-- Catat Penerimaan (jika status ordered atau received_partial) --}}
        <a id="drawerBtnReceive" href="{{ route('staff.receivings.index') }}" class="btn-pesan-action" style="display: none; background-color: #16A34A; text-decoration: none;">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <polyline points="20 6 9 17 4 12"></polyline>
            </svg>
            <span>Catat Penerimaan</span>
        </a>

        {{-- Tutup --}}
        <button type="button" class="btn-edit-action" onclick="closePurchaseDetailDrawer()">
            Tutup
        </button>
    </div>
</aside>

{{-- ==========================================================================
     2. TAMBAH & UBAH DATA: FORM POP UP DI BAGIAN TENGAH (CENTER MODAL)
     ========================================================================== --}}
<div class="modal-backdrop" id="modalPurchaseForm">
    <div class="modal-dialog modal-purchase-dialog" style="position: relative;">
        <form id="purchaseModalMainForm" method="POST" action="{{ route('staff.purchases.store') }}" onsubmit="event.preventDefault(); submitModalAsDraft();">
            @csrf
            <input type="hidden" name="_method" id="purchaseFormMethod" value="POST">
            <input type="hidden" name="action" id="formActionField" value="draft">

            <div class="modal-header" style="position: relative;">
                <div style="text-align: left;">
                    <h3 class="modal-title" id="purchaseModalTitle">Catat Pembelian Bahan</h3>
                    <p class="modal-subtitle" id="purchaseModalSubtitle" style="font-size: 13px; color: #64748B; margin-top: 3px; text-align: left;">
                        Lengkapi formulir nota belanja bahan baku pangan untuk SPPG ini
                    </p>
                </div>
                <button type="button" class="modal-close-corner" onclick="closePurchaseModal()" aria-label="Tutup" title="Tutup">
                    &times;
                </button>
            </div>

            <div class="modal-body" style="padding: 22px 24px; max-height: calc(85vh - 140px); overflow-y: auto;">
                {{-- Bagian 1: Informasi Nota --}}
                <div style="background-color: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 12px; padding: 18px 20px; margin-bottom: 22px;">
                    <h4 style="font-size: 14.5px; font-weight: 700; color: #071E49; margin-bottom: 14px; text-align: left;">
                        Informasi Nota Pembelian
                    </h4>

                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 16px;">
                        <div>
                            <label class="form-label" style="display: block; font-size: 13px; font-weight: 600; color: #071E49; margin-bottom: 6px; text-align: left;">
                                No. Nota (Otomatis)
                            </label>
                            <input type="text" id="purchaseFormNoNota" value="{{ $autoPurchaseNo }}" readonly class="form-input-field form-input-readonly" style="font-weight: 600; color: #071E49;">
                        </div>

                        <div>
                            <label class="form-label" style="display: block; font-size: 13px; font-weight: 600; color: #071E49; margin-bottom: 6px; text-align: left;">
                                Tanggal Pembelian <span style="color: #DC2626;">*</span>
                            </label>
                            <input type="date" id="purchaseFormDate" name="purchase_date" value="{{ $today }}" required class="form-input-field">
                        </div>

                        <div>
                            <label class="form-label" style="display: block; font-size: 13px; font-weight: 600; color: #071E49; margin-bottom: 6px; text-align: left;">
                                Pemasok / Toko <span style="color: #DC2626;">*</span>
                            </label>
                            <select name="supplier_id" id="modalSupplierSelect" required class="form-input-field" onchange="updateModalSupplierPreview()">
                                <option value="">-- Pilih Pemasok Aktif --</option>
                                @foreach ($suppliers as $s)
                                    <option value="{{ $s->id }}" data-alamat="{{ $s->alamat }}" data-kontak="{{ $s->kontak }}">
                                        {{ $s->nama }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div id="modalSupplierPreview" style="margin-top: 12px; padding: 10px 14px; background-color: #FFFFFF; border-radius: 8px; border: 1px solid #E2E8F0; font-size: 12.5px; color: #475569; display: none; text-align: left;">
                        <div><strong>Alamat:</strong> <span id="modalSupplierAlamatText">-</span></div>
                        <div style="margin-top: 3px;"><strong>Kontak:</strong> <span id="modalSupplierKontakText">-</span></div>
                    </div>
                </div>

                {{-- Bagian 2: Rincian Bahan yang Dibeli --}}
                <div style="background-color: #FFFFFF; border: 1px solid #E2E8F0; border-radius: 12px; padding: 18px 20px;">
                    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px;">
                        <h4 style="font-size: 14.5px; font-weight: 700; color: #071E49; margin: 0; text-align: left;">
                            Rincian Bahan yang Dibeli
                        </h4>
                        <span style="font-size: 12px; color: #64748B;">Satu bahan hanya boleh satu baris</span>
                    </div>

                    <div class="table-responsive">
                        <table class="items-form-table">
                            <thead>
                                <tr>
                                    <th style="min-width: 220px;">Bahan <span style="color: #DC2626;">*</span></th>
                                    <th style="width: 90px;">Satuan</th>
                                    <th style="width: 130px;">Jumlah (Qty) <span style="color: #DC2626;">*</span></th>
                                    <th style="width: 160px;">Harga Satuan (Rp) <span style="color: #DC2626;">*</span></th>
                                    <th style="width: 150px; text-align: right;">Subtotal</th>
                                    <th style="width: 44px; text-align: center;"></th>
                                </tr>
                            </thead>
                            <tbody id="modalItemsTableBody">
                                {{-- Baris dinamis --}}
                            </tbody>
                        </table>
                    </div>

                    {{-- Tombol Tambah Baris Bahan (Teks tanpa tanda +) --}}
                    <button type="button" class="btn-add-item-row" onclick="addNewItemRow()">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                            <line x1="12" y1="5" x2="12" y2="19"></line>
                            <line x1="5" y1="12" x2="19" y2="12"></line>
                        </svg>
                        <span>Tambah Baris Bahan</span>
                    </button>

                    <div class="detail-total-bar" style="margin-top: 18px;">
                        <div class="detail-total-text" style="font-size: 16px;">
                            Total Belanja : <span id="modalGrandTotalDisplay" style="color: #042A70;">Rp 0</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal-footer" style="padding: 16px 24px; border-top: 1px solid #E2E8F0; display: flex; align-items: center; justify-content: flex-end; gap: 12px;">
                <button type="button" class="btn-edit-action" onclick="closePurchaseModal()">
                    Batal
                </button>
                <button type="button" id="btnSubmitDraft" class="btn-edit-action" onclick="submitModalAsDraft()" style="background-color: #FFFFFF; border-color: #CBD5E1;">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path>
                        <polyline points="17 21 17 13 7 13 7 21"></polyline>
                        <polyline points="7 3 7 8 15 8"></polyline>
                    </svg>
                    <span>Simpan Draft</span>
                </button>
                <button type="button" id="btnSubmitOrder" class="btn-pesan-action" onclick="promptModalOrderConfirmation()">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="22" y1="2" x2="11" y2="13"></line>
                        <polygon points="22 2 15 22 11 13 2 9 22 2"></polygon>
                    </svg>
                    <span>Pesan ke Pemasok</span>
                </button>
            </div>
        </form>
    </div>
</div>

{{-- ==========================================================================
     3. POP UP PERINGATAN UMUM DI BAGIAN TENGAH (VALIDASI FORM & PERINGATAN BAHAN)
     ========================================================================== --}}
<div class="modal-backdrop" id="modalWarningCenter" style="z-index: 1090;">
    <div class="modal-dialog" style="max-width: 440px; padding: 26px 28px; border-radius: 18px; position: relative;">
        <button type="button" class="modal-close-corner" onclick="closeModal('modalWarningCenter')" aria-label="Tutup" title="Tutup">
            &times;
        </button>

        <div style="display: flex; align-items: flex-start; gap: 16px;">
            <div style="width: 46px; height: 46px; border-radius: 50%; background-color: #FEF3C7; color: #D97706; display: flex; align-items: center; justify-content: center; flex-shrink: 0; margin-top: 2px;">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                    <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path>
                    <line x1="12" y1="9" x2="12" y2="13"></line>
                    <line x1="12" y1="17" x2="12.01" y2="17"></line>
                </svg>
            </div>
            <div style="text-align: left;">
                <h3 id="modalWarningTitle" style="font-size: 17px; font-weight: 700; color: #071E49; margin: 0 0 6px 0; text-align: left;">
                    Bahan Harus Diisi
                </h3>
                <p id="modalWarningMessage" style="font-size: 13.5px; color: #64748B; line-height: 1.5; margin: 0; text-align: left;">
                    Pastikan semua baris bahan telah dipilih dan jumlah pesanan telah diisi dengan benar.
                </p>
            </div>
        </div>

        <div style="display: flex; justify-content: flex-end; margin-top: 22px;">
            <button type="button" class="btn-pesan-action" onclick="closeModal('modalWarningCenter')" style="padding: 8px 24px;">
                Mengerti
            </button>
        </div>
    </div>
</div>

{{-- ==========================================================================
     4. POP UP KONFIRMASI PESAN KE PEMASOK
     ========================================================================== --}}
<div class="modal-backdrop" id="modalConfirmOrder" style="z-index: 1080;">
    <div class="modal-dialog" style="max-width: 480px; padding: 26px 28px; border-radius: 18px; position: relative;">
        <button type="button" class="modal-close-corner" onclick="closeModal('modalConfirmOrder')" aria-label="Tutup" title="Tutup">
            &times;
        </button>

        <form id="orderFormDirect" method="POST" action="">
            @csrf
            <div style="text-align: left;">
                <h3 style="font-size: 18px; font-weight: 700; color: #071E49; margin: 0 0 10px 0; text-align: left;">
                    Konfirmasi Pesan Bahan ke Pemasok
                </h3>
                <p style="font-size: 13.5px; color: #475569; line-height: 1.5; margin-bottom: 12px; text-align: left;">
                    Apakah Anda yakin ingin memesan nota pembelian <strong id="orderPoNoDisplay">-</strong> ke pemasok?
                </p>
                <div style="background-color: #FEF3C7; border: 1px solid #FDE68A; color: #92400E; padding: 12px 14px; border-radius: 8px; font-size: 12.5px; text-align: left;">
                    <strong>Perhatian:</strong> Setelah dipesan, nota pembelian tidak dapat diubah lagi dan status akan menjadi <em>Menunggu Cek</em>.
                </div>
            </div>

            <div style="display: flex; align-items: center; justify-content: flex-end; gap: 12px; margin-top: 22px;">
                <button type="button" class="btn-edit-action" onclick="closeModal('modalConfirmOrder')">Batal</button>
                <button type="submit" class="btn-pesan-action" id="btnConfirmOrderSubmit">Ya, Pesan Sekarang</button>
            </div>
        </form>
    </div>
</div>

{{-- ==========================================================================
     5. POP UP BATALKAN NOTA PEMBELIAN
     ========================================================================== --}}
<div class="modal-backdrop" id="modalCancelPurchase" style="z-index: 1080;">
    <div class="modal-dialog" style="max-width: 480px; padding: 26px 28px; border-radius: 18px; position: relative;">
        <button type="button" class="modal-close-corner" onclick="closeModal('modalCancelPurchase')" aria-label="Tutup" title="Tutup">
            &times;
        </button>

        <form id="cancelPurchaseForm" method="POST" action="">
            @csrf
            <div style="text-align: left;">
                <h3 style="font-size: 18px; font-weight: 700; color: #DC2626; margin: 0 0 8px 0; text-align: left;">
                    Batalkan Nota Pembelian
                </h3>
                <p style="font-size: 13.5px; color: #475569; line-height: 1.5; margin-bottom: 14px; text-align: left;">
                    Nota pembelian <strong id="cancelPoNoDisplay">-</strong> akan dibatalkan. Masukkan alasan pembatalan:
                </p>

                <div style="text-align: left;">
                    <label for="cancelPurchaseReason" style="display: block; font-size: 13px; font-weight: 600; color: #071E49; margin-bottom: 6px; text-align: left;">
                        Alasan Pembatalan <span style="color: #DC2626;">*</span>
                    </label>
                    <textarea id="cancelPurchaseReason" name="reason" rows="3" required placeholder="Contoh: Stok pemasok kosong / revisi anggaran operasional" class="form-input-field" style="height: auto; padding: 10px 12px;"></textarea>
                    <small style="color: #64748B; font-size: 11.5px; margin-top: 4px; display: block; text-align: left;">
                        Alasan pembatalan akan tercatat secara permanen di audit trail sistem.
                    </small>
                </div>
            </div>

            <div style="display: flex; align-items: center; justify-content: flex-end; gap: 12px; margin-top: 22px;">
                <button type="button" class="btn-edit-action" onclick="closeModal('modalCancelPurchase')">Tutup</button>
                <button type="submit" class="btn-cancel-action" style="background-color: #DC2626; color: #FFFFFF; border-color: #DC2626;">
                    Konfirmasi Pembatalan
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    const materialsData = @json($materials);
    const materialsMap = {};
    materialsData.forEach(m => {
        materialsMap[m.id] = m;
    });

    let activeDrawerData = null;
    let modalRowCounter = 0;
    let isFormSubmitting = false;

    function formatRupiah(number) {
        return 'Rp ' + Number(number).toLocaleString('id-ID');
    }

    function openModal(modalId) {
        const el = document.getElementById(modalId);
        if (el) {
            el.classList.add('open');
            document.body.style.overflow = 'hidden';
        }
    }

    function closeModal(modalId) {
        const el = document.getElementById(modalId);
        if (el) {
            el.classList.remove('open');
            const hasOpen = document.querySelector('.modal-backdrop.open, .purchase-drawer.open');
            if (!hasOpen) {
                document.body.style.overflow = '';
            }
        }
    }

    // Modal Peringatan Khusus di Tengah Layar (Gaya Konsisten, Tanpa Alert Browser)
    function showCenterWarning(title, message) {
        document.getElementById('modalWarningTitle').textContent = title;
        document.getElementById('modalWarningMessage').textContent = message;
        openModal('modalWarningCenter');
    }

    // =========================================================================
    // 1. LOGIKA POP UP DRAWER SEBELAH KANAN (DETAIL NOTA)
    // =========================================================================
    window.openPurchaseDetailFromLink = function(e, purchaseId) {
        if (e && (e.ctrlKey || e.metaKey || e.button === 1)) {
            return true; // Biarkan browser membuka tab baru jika Ctrl/Meta/Middle click
        }
        if (e) {
            e.preventDefault();
            e.stopPropagation();
        }
        openPurchaseDetailDrawer(purchaseId);
        return false;
    };

    async function openPurchaseDetailDrawer(purchaseId) {
        const drawer = document.getElementById('purchaseDetailDrawer');
        const backdrop = document.getElementById('purchaseDrawerBackdrop');

        if (!drawer || !backdrop) return;

        drawer.classList.add('open');
        backdrop.classList.add('open');
        document.body.style.overflow = 'hidden';

        document.getElementById('drawerNoNota').textContent = 'Memuat...';
        document.getElementById('drawerTanggal').textContent = '-';
        document.getElementById('drawerToko').textContent = '-';
        document.getElementById('drawerAlamat').textContent = '-';
        document.getElementById('drawerDicatatOleh').textContent = '-';
        document.getElementById('drawerBadgeStatusText').textContent = 'Memuat...';
        document.getElementById('drawerItemsTableBody').innerHTML = '<tr><td colspan="5" style="text-align: center; color: #94A3B8;">Memuat item belanja...</td></tr>';
        document.getElementById('drawerTotalBelanja').textContent = 'Total Belanja : Rp 0';
        document.getElementById('drawerBtnEdit').style.display = 'none';
        document.getElementById('drawerBtnOrder').style.display = 'none';
        document.getElementById('drawerBtnCancel').style.display = 'none';
        document.getElementById('drawerBtnReceive').style.display = 'none';

        try {
            const res = await fetch(`/staff/pembelian/${purchaseId}`, {
                headers: { 'Accept': 'application/json' }
            });

            if (!res.ok) throw new Error('Gagal memuat detail nota');
            const data = await res.json();
            activeDrawerData = data;

            document.getElementById('drawerNoNota').textContent = data.purchase_no;
            document.getElementById('drawerTanggal').textContent = data.purchase_date;
            document.getElementById('drawerToko').textContent = data.supplier ? data.supplier.nama : '-';
            document.getElementById('drawerAlamat').textContent = data.supplier ? data.supplier.alamat : '-';
            document.getElementById('drawerDicatatOleh').textContent = data.creator_nama;

            const badge = document.getElementById('drawerBadgeStatus');
            badge.className = `badge-status ${data.status_badge_class}`;
            document.getElementById('drawerBadgeStatusText').textContent = data.status_label;

            const isPartialOrReceived = (data.status === 'received_partial' || data.status === 'received');
            const thRow = document.getElementById('drawerTableHeaders');

            if (isPartialOrReceived) {
                thRow.innerHTML = `
                    <th style="width: 35px;">No</th>
                    <th>Nama Bahan</th>
                    <th>Jumlah</th>
                    <th style="color: #16A34A;">Diterima</th>
                    <th style="color: #D97706;">Sisa</th>
                    <th>Harga/Satuan</th>
                    <th style="text-align: right;">Total</th>
                `;
            } else {
                thRow.innerHTML = `
                    <th style="width: 35px;">No</th>
                    <th>Nama Bahan</th>
                    <th>Jumlah</th>
                    <th>Harga / Satuan</th>
                    <th style="text-align: right;">Total</th>
                `;
            }

            let tbodyHtml = '';
            data.items.forEach((item, idx) => {
                if (isPartialOrReceived) {
                    tbodyHtml += `
                        <tr>
                            <td>${idx + 1}</td>
                            <td style="font-weight: 500; color: #071E49;">${item.material_nama}</td>
                            <td>${item.qty_ordered_formatted} ${item.satuan}</td>
                            <td style="color: #16A34A; font-weight: 600;">${item.qty_received_formatted} ${item.satuan}</td>
                            <td style="color: ${item.qty_remaining > 0 ? '#D97706' : '#64748B'}; font-weight: 600;">${item.qty_remaining_formatted} ${item.satuan}</td>
                            <td>${item.formatted_unit_price}</td>
                            <td style="text-align: right; font-weight: 600; color: #071E49;">${item.formatted_subtotal}</td>
                        </tr>
                    `;
                } else {
                    tbodyHtml += `
                        <tr>
                            <td>${idx + 1}</td>
                            <td style="font-weight: 500; color: #071E49;">${item.material_nama}</td>
                            <td>${item.qty_ordered_formatted} ${item.satuan}</td>
                            <td>${item.formatted_unit_price}</td>
                            <td style="text-align: right; font-weight: 600; color: #071E49;">${item.formatted_subtotal}</td>
                        </tr>
                    `;
                }
            });

            document.getElementById('drawerItemsTableBody').innerHTML = tbodyHtml;
            document.getElementById('drawerTotalBelanja').textContent = `Total Belanja : ${data.formatted_total_cost}`;

            const recContainer = document.getElementById('drawerReceivingStatusContainer');
            if (!data.receivings || data.receivings.length === 0) {
                recContainer.innerHTML = '<p class="warehouse-status-empty">Fisik barang belum dikonfirmasi di menu Penerimaan.</p>';
            } else {
                let recHtml = '<div class="table-responsive"><table class="detail-items-table"><thead><tr><th>No. Penerimaan</th><th>Tanggal</th><th>Penerima</th><th>Status</th></tr></thead><tbody>';
                data.receivings.forEach(r => {
                    recHtml += `<tr><td style="font-weight: 600;">${r.receiving_no}</td><td>${r.received_at}</td><td>${r.receiver_nama}</td><td>${r.status}</td></tr>`;
                });
                recHtml += '</tbody></table></div>';
                recContainer.innerHTML = recHtml;
            }

            document.getElementById('drawerBtnPrint').href = data.print_url;

            if (data.can_be_edited) {
                document.getElementById('drawerBtnEdit').style.display = 'inline-flex';
            }
            if (data.can_be_ordered) {
                document.getElementById('drawerBtnOrder').style.display = 'inline-flex';
            }
            if (data.can_be_cancelled) {
                document.getElementById('drawerBtnCancel').style.display = 'inline-flex';
            }
            if (data.can_receive) {
                const btnReceive = document.getElementById('drawerBtnReceive');
                btnReceive.href = `/staff/penerimaan?purchase_id=${data.id}`;
                btnReceive.style.display = 'inline-flex';
            }

        } catch (e) {
            showCenterWarning('Gagal Memuat Detail', 'Terjadi kesalahan saat memuat data nota pembelian.');
            closePurchaseDetailDrawer();
        }
    }

    function closePurchaseDetailDrawer() {
        const drawer = document.getElementById('purchaseDetailDrawer');
        const backdrop = document.getElementById('purchaseDrawerBackdrop');
        if (drawer) drawer.classList.remove('open');
        if (backdrop) backdrop.classList.remove('open');

        const hasOpen = document.querySelector('.modal-backdrop.open');
        if (!hasOpen) document.body.style.overflow = '';
    }

    function triggerEditFromDrawer() {
        if (activeDrawerData) {
            closePurchaseDetailDrawer();
            openEditPurchaseModal(activeDrawerData.id);
        }
    }

    function triggerOrderFromDrawer() {
        if (activeDrawerData) {
            openOrderConfirmationModal(activeDrawerData.order_url, activeDrawerData.purchase_no);
        }
    }

    function triggerCancelFromDrawer() {
        if (activeDrawerData) {
            openCancelPurchaseModal(activeDrawerData.cancel_url, activeDrawerData.purchase_no);
        }
    }

    // =========================================================================
    // 2. LOGIKA POP UP FORM DI BAGIAN TENGAH (TAMBAH & EDIT)
    // =========================================================================
    function openAddPurchaseModal() {
        isFormSubmitting = false;
        resetSubmitButtonStates();

        const form = document.getElementById('purchaseModalMainForm');
        form.action = "{{ route('staff.purchases.store') }}";
        document.getElementById('purchaseFormMethod').value = 'POST';
        document.getElementById('purchaseModalTitle').textContent = 'Catat Pembelian Bahan';
        document.getElementById('purchaseModalSubtitle').textContent = 'Lengkapi formulir nota belanja bahan baku pangan untuk SPPG ini';
        document.getElementById('purchaseFormNoNota').value = "{{ $autoPurchaseNo }}";
        document.getElementById('purchaseFormDate').value = "{{ $today }}";

        const supplierSelect = document.getElementById('modalSupplierSelect');
        supplierSelect.value = '';
        updateModalSupplierPreview();

        document.getElementById('modalItemsTableBody').innerHTML = '';
        modalRowCounter = 0;
        addNewItemRow();

        openModal('modalPurchaseForm');
    }

    async function openEditPurchaseModal(purchaseId) {
        isFormSubmitting = false;
        resetSubmitButtonStates();

        try {
            const res = await fetch(`/staff/pembelian/${purchaseId}`, {
                headers: { 'Accept': 'application/json' }
            });
            if (!res.ok) throw new Error('Gagal memuat data');
            const data = await res.json();

            const form = document.getElementById('purchaseModalMainForm');
            form.action = data.update_url;
            document.getElementById('purchaseFormMethod').value = 'PUT';
            document.getElementById('purchaseModalTitle').textContent = 'Ubah Draft Pembelian Bahan';
            document.getElementById('purchaseModalSubtitle').textContent = 'Perbarui data nota pembelian bahan baku sebelum dipesan ke pemasok';
            document.getElementById('purchaseFormNoNota').value = data.purchase_no;
            document.getElementById('purchaseFormDate').value = data.purchase_date_raw;

            const supplierSelect = document.getElementById('modalSupplierSelect');
            supplierSelect.value = data.supplier ? data.supplier.id : '';
            updateModalSupplierPreview();

            document.getElementById('modalItemsTableBody').innerHTML = '';
            modalRowCounter = 0;
            data.items.forEach(item => {
                addNewItemRow(item.material_id, item.qty_ordered, item.unit_price);
            });

            openModal('modalPurchaseForm');
        } catch (e) {
            showCenterWarning('Gagal Memuat Data', 'Tidak dapat memuat data nota pembelian untuk diubah.');
        }
    }

    function closePurchaseModal() {
        closeModal('modalPurchaseForm');
    }

    function updateModalSupplierPreview() {
        const select = document.getElementById('modalSupplierSelect');
        const selectedOpt = select.options[select.selectedIndex];
        const preview = document.getElementById('modalSupplierPreview');

        if (selectedOpt && selectedOpt.value) {
            document.getElementById('modalSupplierAlamatText').textContent = selectedOpt.getAttribute('data-alamat') || '-';
            document.getElementById('modalSupplierKontakText').textContent = selectedOpt.getAttribute('data-kontak') || '-';
            preview.style.display = 'block';
        } else {
            preview.style.display = 'none';
        }
    }

    function addNewItemRow(materialId = '', qty = '', unitPrice = '') {
        const tbody = document.getElementById('modalItemsTableBody');
        const rowIndex = modalRowCounter++;

        const tr = document.createElement('tr');
        tr.id = `modalItemRow_${rowIndex}`;
        tr.className = 'modal-item-row';

        let optionsHtml = '<option value="">-- Pilih Bahan --</option>';
        materialsData.forEach(m => {
            const selected = (m.id == materialId) ? 'selected' : '';
            optionsHtml += `<option value="${m.id}" ${selected}>${m.nama} (${m.kode})</option>`;
        });

        tr.innerHTML = `
            <td>
                <select name="items[${rowIndex}][material_id]" class="form-input-field modal-material-select" required onchange="handleModalMaterialChange(${rowIndex})">
                    ${optionsHtml}
                </select>
            </td>
            <td>
                <input type="text" id="modalUnit_${rowIndex}" readonly class="form-input-field form-input-readonly" style="text-align: center;" value="">
            </td>
            <td>
                <input type="number" step="0.001" min="0.001" name="items[${rowIndex}][qty_ordered]" value="${qty}" required class="form-input-field modal-qty-input" placeholder="0" oninput="calculateModalRow(${rowIndex})">
            </td>
            <td>
                <input type="number" step="1" min="0" name="items[${rowIndex}][unit_price]" value="${unitPrice}" required class="form-input-field modal-price-input" placeholder="0" oninput="calculateModalRow(${rowIndex})">
            </td>
            <td style="text-align: right; font-weight: 600; color: #071E49;">
                <span id="modalSubtotal_${rowIndex}">Rp 0</span>
            </td>
            <td style="text-align: center;">
                <button type="button" class="btn-delete-row" title="Hapus baris" onclick="removeModalRow(${rowIndex})">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="3 6 5 6 21 6"></polyline>
                        <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                    </svg>
                </button>
            </td>
        `;

        tbody.appendChild(tr);

        if (materialId) {
            handleModalMaterialChange(rowIndex, false);
            calculateModalRow(rowIndex);
        }
    }

    function handleModalMaterialChange(rowIndex, checkDuplicate = true) {
        const row = document.getElementById(`modalItemRow_${rowIndex}`);
        if (!row) return;

        const select = row.querySelector('.modal-material-select');
        const selectedId = select.value;
        const unitInput = document.getElementById(`modalUnit_${rowIndex}`);

        if (!selectedId) {
            unitInput.value = '';
            calculateModalRow(rowIndex);
            return;
        }

        // Cek duplikasi bahan: jika duplikat, tampilkan popup warning di tengah (tanpa alert browser)
        if (checkDuplicate) {
            const allSelects = document.querySelectorAll('.modal-material-select');
            let duplicateFound = false;

            allSelects.forEach(s => {
                if (s !== select && s.value === selectedId) {
                    duplicateFound = true;
                }
            });

            if (duplicateFound) {
                select.value = '';
                unitInput.value = '';
                calculateModalRow(rowIndex);
                showCenterWarning('Bahan Sudah Ada di Daftar', 'Satu bahan hanya boleh dipilih satu baris dalam satu nota pembelian. Silakan pilih bahan lain atau sesuaikan jumlah pada baris yang sudah ada.');
                return;
            }
        }

        if (materialsMap[selectedId]) {
            unitInput.value = materialsMap[selectedId].satuan || '-';
        }

        calculateModalRow(rowIndex);
    }

    function calculateModalRow(rowIndex) {
        const row = document.getElementById(`modalItemRow_${rowIndex}`);
        if (!row) return;

        const qty = parseFloat(row.querySelector('.modal-qty-input').value) || 0;
        const price = parseFloat(row.querySelector('.modal-price-input').value) || 0;
        const subtotal = qty * price;

        document.getElementById(`modalSubtotal_${rowIndex}`).textContent = formatRupiah(subtotal);
        calculateModalGrandTotal();
    }

    function calculateModalGrandTotal() {
        const rows = document.querySelectorAll('.modal-item-row');
        let grandTotal = 0;

        rows.forEach(r => {
            const qty = parseFloat(r.querySelector('.modal-qty-input').value) || 0;
            const price = parseFloat(r.querySelector('.modal-price-input').value) || 0;
            grandTotal += (qty * price);
        });

        document.getElementById('modalGrandTotalDisplay').textContent = formatRupiah(grandTotal);
    }

    function removeModalRow(rowIndex) {
        const rows = document.querySelectorAll('.modal-item-row');
        if (rows.length <= 1) {
            showCenterWarning('Tidak Dapat Menghapus', 'Minimal harus ada 1 baris bahan belanja dalam nota pembelian.');
            return;
        }

        const row = document.getElementById(`modalItemRow_${rowIndex}`);
        if (row) {
            row.remove();
            calculateModalGrandTotal();
        }
    }

    // Validasi Form Menggunakan Pop-Up di Tengah (Bukan Browser Alert)
    function validateModalInputs() {
        const supplierSelect = document.getElementById('modalSupplierSelect');
        if (!supplierSelect.value) {
            showCenterWarning('Pemasok Wajib Dipilih', 'Silakan pilih pemasok / toko terlebih dahulu sebelum menyimpan nota.');
            supplierSelect.focus();
            return false;
        }

        const rows = document.querySelectorAll('.modal-item-row');
        if (rows.length === 0) {
            showCenterWarning('Bahan Belum Ditambahkan', 'Minimal satu baris bahan belanja harus ditambahkan.');
            return false;
        }

        let isValid = true;
        rows.forEach(r => {
            const mat = r.querySelector('.modal-material-select').value;
            const qty = parseFloat(r.querySelector('.modal-qty-input').value) || 0;
            const price = parseFloat(r.querySelector('.modal-price-input').value) || 0;

            if (!mat && isValid) {
                showCenterWarning('Bahan Wajib Dipilih', 'Pastikan semua baris sudah memilih bahan pangan yang akan dibeli.');
                isValid = false;
            } else if (qty <= 0 && isValid) {
                showCenterWarning('Jumlah (Qty) Tidak Valid', 'Jumlah pesanan (Qty) bahan harus lebih besar dari 0.');
                isValid = false;
            } else if (price < 0 && isValid) {
                showCenterWarning('Harga Satuan Tidak Valid', 'Harga satuan bahan tidak boleh bernilai negatif.');
                isValid = false;
            }
        });

        return isValid;
    }

    function resetSubmitButtonStates() {
        const btnDraft = document.getElementById('btnSubmitDraft');
        const btnOrder = document.getElementById('btnSubmitOrder');
        if (btnDraft) {
            btnDraft.disabled = false;
            btnDraft.style.opacity = '1';
            btnDraft.style.cursor = 'pointer';
        }
        if (btnOrder) {
            btnOrder.disabled = false;
            btnOrder.style.opacity = '1';
            btnOrder.style.cursor = 'pointer';
        }
    }

    // Submit Draft dengan Proteksi Double Submit
    function submitModalAsDraft() {
        if (isFormSubmitting) return;
        if (!validateModalInputs()) return;

        isFormSubmitting = true;
        const btnDraft = document.getElementById('btnSubmitDraft');
        if (btnDraft) {
            btnDraft.disabled = true;
            btnDraft.style.opacity = '0.6';
            btnDraft.style.cursor = 'not-allowed';
        }

        document.getElementById('formActionField').value = 'draft';
        document.getElementById('purchaseModalMainForm').submit();
    }

    function promptModalOrderConfirmation() {
        if (!validateModalInputs()) return;
        const noNota = document.getElementById('purchaseFormNoNota').value;
        openOrderConfirmationModal(null, noNota, true);
    }

    // =========================================================================
    // 3. LOGIKA POP UP KONFIRMASI & PEMBATALAN
    // =========================================================================
    let isSubmittingFromCenterModal = false;

    function openOrderConfirmationModal(actionUrl, poNo, fromCenterModal = false) {
        isSubmittingFromCenterModal = fromCenterModal;
        document.getElementById('orderPoNoDisplay').textContent = poNo;

        const form = document.getElementById('orderFormDirect');
        if (actionUrl) {
            form.action = actionUrl;
        }

        form.onsubmit = function(e) {
            if (isSubmittingFromCenterModal) {
                e.preventDefault();
                if (isFormSubmitting) return;
                isFormSubmitting = true;

                const submitBtn = document.getElementById('btnConfirmOrderSubmit');
                if (submitBtn) {
                    submitBtn.disabled = true;
                    submitBtn.style.opacity = '0.6';
                }

                closeModal('modalConfirmOrder');
                document.getElementById('formActionField').value = 'order';
                document.getElementById('purchaseModalMainForm').submit();
            }
        };

        openModal('modalConfirmOrder');
    }

    function openCancelPurchaseModal(cancelUrl, poNo) {
        document.getElementById('cancelPoNoDisplay').textContent = poNo;
        document.getElementById('cancelPurchaseReason').value = '';
        document.getElementById('cancelPurchaseForm').action = cancelUrl;
        openModal('modalCancelPurchase');
    }

    // Auto open drawer atau modal jika ada URL query parameter
    document.addEventListener('DOMContentLoaded', function() {
        const urlParams = new URLSearchParams(window.location.search);
        const detailId = urlParams.get('detail_id');
        const editId = urlParams.get('edit_id');
        const action = urlParams.get('action');

        if (detailId) {
            openPurchaseDetailDrawer(detailId);
        } else if (editId) {
            openEditPurchaseModal(editId);
        } else if (action === 'create') {
            openAddPurchaseModal();
        }
    });

    // Menutup modal dengan klik area backdrop
    window.addEventListener('click', function(e) {
        if (e.target.classList.contains('modal-backdrop')) {
            e.target.classList.remove('open');
            const hasOpen = document.querySelector('.modal-backdrop.open, .purchase-drawer.open');
            if (!hasOpen) document.body.style.overflow = '';
        }
    });
</script>
@endpush
