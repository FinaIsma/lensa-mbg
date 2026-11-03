@extends('layouts.staff')

@section('title', 'Penerimaan Bahan - Staff SPPG')

@section('content')
<div class="space-y-6 pb-8">
    {{-- Page Title Section --}}
    <div class="page-title-section">
        <h1 class="page-title">Penerimaan Bahan</h1>
        <p class="page-subtitle">Kelola penerimaan barang fisik dari pemasok dan pencatatan lot bahan baku pangan.</p>
    </div>

    {{-- 4 Stat Cards Grid --}}
    <div class="stat-cards-grid">
        {{-- Card 1: Total Penerimaan --}}
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
                <span class="stat-label">Total Penerimaan</span>
                <span class="stat-value">{{ number_format($stats['total_penerimaan'], 0, ',', '.') }}</span>
                <span class="stat-subtext">Surat Penerimaan</span>
            </div>
        </div>

        {{-- Card 2: Menunggu Konfirmasi (Draft) --}}
        <div class="stat-card">
            <div class="stat-icon-wrapper icon-yellow">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10"></circle>
                    <polyline points="12 6 12 12 14 14"></polyline>
                </svg>
            </div>
            <div class="stat-content">
                <span class="stat-label">Menunggu Konfirmasi</span>
                <span class="stat-value">{{ number_format($stats['menunggu_cek'], 0, ',', '.') }}</span>
                <span class="stat-subtext">Draft Penerimaan</span>
            </div>
        </div>

        {{-- Card 3: Terkonfirmasi --}}
        <div class="stat-card">
            <div class="stat-icon-wrapper icon-green">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                    <polyline points="22 4 12 14.01 9 11.01"></polyline>
                </svg>
            </div>
            <div class="stat-content">
                <span class="stat-label">Terkonfirmasi</span>
                <span class="stat-value">{{ number_format($stats['terkonfirmasi'], 0, ',', '.') }}</span>
                <span class="stat-subtext">Masuk ke Stok Lot</span>
            </div>
        </div>

        {{-- Card 4: Total Item Diterima --}}
        <div class="stat-card">
            <div class="stat-icon-wrapper icon-purple">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path>
                    <polyline points="3.27 6.96 12 12.01 20.73 6.96"></polyline>
                    <line x1="12" y1="22.08" x2="12" y2="12"></line>
                </svg>
            </div>
            <div class="stat-content">
                <span class="stat-label">Bahan Diterima</span>
                <span class="stat-value">{{ number_format($stats['total_item_masuk'], 0, ',', '.') }}</span>
                <span class="stat-subtext">Baris Item Masuk</span>
            </div>
        </div>
    </div>

    {{-- Unified Table Card: Filter Bar + Table --}}
    <div class="table-card">
        <form method="GET" action="{{ route('staff.receivings.index') }}" class="filter-bar-form" style="display: flex; align-items: center; justify-content: space-between; gap: 14px; flex-wrap: wrap;">
            {{-- Filter Bagian Kiri: Search, Status, Tanggal, Reset --}}
            <div style="display: flex; align-items: center; gap: 10px; flex: 1; flex-wrap: wrap;">
                {{-- Search Box --}}
                <div class="search-input-box" style="width: 260px; min-width: 180px; flex: 1;">
                    <svg class="search-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="11" cy="11" r="8"></circle>
                        <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                    </svg>
                    <input type="text" name="q" value="{{ $q }}" placeholder="Cari penerimaan, PO, pemasok" class="input-search">
                </div>

                {{-- Dropdown Status Filter --}}
                <div class="select-dropdown-box" style="min-width: 155px; width: auto;">
                    <select name="status" class="select-status-verif" onchange="this.form.submit()" style="padding-right: 2.25rem;">
                        <option value="">Semua Status</option>
                        <option value="draft" {{ $status === 'draft' ? 'selected' : '' }}>Draft</option>
                        <option value="confirmed" {{ $status === 'confirmed' ? 'selected' : '' }}>Terkonfirmasi</option>
                        <option value="voided" {{ $status === 'voided' ? 'selected' : '' }}>Dibatalkan</option>
                    </select>
                    <svg class="chevron-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="6 9 12 15 18 9"></polyline>
                    </svg>
                </div>

                {{-- Filter Rentang Tanggal Popover --}}
                <div class="date-filter-dropdown" style="position: relative;">
                    <button type="button" class="date-filter-btn" onclick="toggleDatePopover(event)" id="datePickerBtn">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="color: #64748B;">
                            <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                            <line x1="16" y1="2" x2="16" y2="6"></line>
                            <line x1="8" y1="2" x2="8" y2="6"></line>
                            <line x1="3" y1="10" x2="21" y2="10"></line>
                        </svg>
                        <span id="datePickerBtnLabel">
                            @if($dateFrom && $dateTo)
                                {{ \Carbon\Carbon::parse($dateFrom)->format('d M') }} - {{ \Carbon\Carbon::parse($dateTo)->format('d M Y') }}
                            @elseif($dateFrom)
                                Dari {{ \Carbon\Carbon::parse($dateFrom)->format('d M Y') }}
                            @elseif($dateTo)
                                Sampai {{ \Carbon\Carbon::parse($dateTo)->format('d M Y') }}
                            @else
                                Semua Tanggal
                            @endif
                        </span>
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="color: #64748B;">
                            <polyline points="6 9 12 15 18 9"></polyline>
                        </svg>
                    </button>

                    {{-- Popover Dropdown Rentang Tanggal --}}
                    <div id="datePopover" class="date-popover-box">
                        <div style="font-size: 13.5px; font-weight: 700; color: #071E49; margin-bottom: 12px;">Rentang Tanggal Terima</div>
                        <div style="margin-bottom: 10px;">
                            <label style="display: block; font-size: 12px; font-weight: 600; color: #64748B; margin-bottom: 4px;">Dari Tanggal</label>
                            <input type="date" id="popoverDateFrom" name="date_from" value="{{ $dateFrom }}" class="form-input-field" style="height: 38px; font-size: 13px;">
                        </div>
                        <div style="margin-bottom: 14px;">
                            <label style="display: block; font-size: 12px; font-weight: 600; color: #64748B; margin-bottom: 4px;">Sampai Tanggal</label>
                            <input type="date" id="popoverDateTo" name="date_to" value="{{ $dateTo }}" class="form-input-field" style="height: 38px; font-size: 13px;">
                        </div>
                        <div style="display: flex; gap: 8px; justify-content: flex-end;">
                            <button type="button" class="btn-cancel-action" style="padding: 6px 12px; font-size: 12.5px;" onclick="clearDateFilter()">Kosongkan</button>
                            <button type="submit" class="btn-pesan-action" style="padding: 6px 16px; font-size: 12.5px;">Terapkan</button>
                        </div>
                    </div>
                </div>

                {{-- Reset Filter Button --}}
                @if(!empty($q) || !empty($status) || !empty($dateFrom) || !empty($dateTo))
                    <a href="{{ route('staff.receivings.index') }}" class="btn-reset-filter" title="Reset filter" style="height: 42px; display: inline-flex; align-items: center; gap: 6px; padding: 0 14px; border: 1px solid #CBD5E1; border-radius: 9px; font-size: 13.5px; color: #64748B; text-decoration: none;">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <line x1="18" y1="6" x2="6" y2="18"></line>
                            <line x1="6" y1="6" x2="18" y2="18"></line>
                        </svg>
                        <span>Reset</span>
                    </a>
                @endif
            </div>

            {{-- Tombol Tambah di KANAN TABEL (Sesuai Gaya Tabel Lainnya) --}}
            <div style="margin-left: auto;">
                <button type="button" class="btn-tambah-staff" onclick="openCreateModal()">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                        <line x1="12" y1="5" x2="12" y2="19"></line>
                        <line x1="5" y1="12" x2="19" y2="12"></line>
                    </svg>
                    <span>Catat Penerimaan</span>
                </button>
            </div>
        </form>

        {{-- Table Section --}}
        <div class="table-responsive" style="margin-top: 14px;">
            <table class="staff-table data-table">
                <thead>
                    <tr>
                        <th style="min-width: 160px; font-weight: 700; color: #071E49;">No. Penerimaan</th>
                        <th style="min-width: 150px; font-weight: 700; color: #071E49;">Nota Pembelian</th>
                        <th style="min-width: 180px; font-weight: 700; color: #071E49;">Pemasok</th>
                        <th style="min-width: 140px; font-weight: 700; color: #071E49;">Waktu Terima</th>
                        <th style="min-width: 120px; font-weight: 700; color: #071E49;">Item Barang</th>
                        <th style="min-width: 140px; font-weight: 700; color: #071E49;">Status</th>
                        <th style="width: 110px; text-align: center; font-weight: 700; color: #071E49;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($receivings as $item)
                        <tr>
                            <td class="font-medium text-navy">
                                <span style="font-weight: 600;">{{ $item->receiving_no }}</span>
                            </td>
                            <td>
                                <span style="color: #0F172A; font-weight: 500;">{{ $item->purchase?->purchase_no ?? '-' }}</span>
                            </td>
                            <td>{{ $item->purchase?->supplier?->nama ?? '-' }}</td>
                            <td>{{ $item->received_at ? $item->received_at->format('d-m-Y H:i') : '-' }}</td>
                            <td>{{ $item->items->count() }} Bahan</td>
                            <td>
                                <div style="display: inline-flex; align-items: center; gap: 6px;">
                                    <span class="badge-status {{ $item->status_badge_class }}">
                                        <span class="status-dot"></span>
                                        {{ $item->status_label }}
                                    </span>
                                    @if ($item->is_locked)
                                        <span title="Dokumen Terkunci" style="color: #64748B; display: inline-flex;">
                                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                                <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                                                <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                                            </svg>
                                        </span>
                                    @endif
                                </div>
                            </td>
                            <td>
                                <div class="action-buttons-group" style="justify-content: center;">
                                    {{-- Tombol Detail (Mata): Pop up di Sebelah Kiri --}}
                                    <a href="{{ route('staff.receivings.show', $item->id) }}" 
                                       class="btn-action-icon" 
                                       data-receiving-detail-id="{{ $item->id }}"
                                       onclick="return openReceivingDetailFromLink(event, {{ $item->id }});" 
                                       title="Lihat Detail Penerimaan">
                                        <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="pointer-events: none;">
                                            <circle cx="12" cy="12" r="3"></circle>
                                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                        </svg>
                                    </a>

                                    {{-- Tombol Barcode (jika terkonfirmasi) --}}
                                    @if ($item->isConfirmed())
                                        <a href="{{ route('staff.receivings.barcode-all', $item->id) }}" target="_blank" class="btn-action-icon" title="Cetak Semua Barcode" style="color: #16A34A; border-color: #BBF7D0;">
                                            <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <path d="M3 5h2v14H3zM7 5h1v14H7zM10 5h2v14h-2zM14 5h1v14h-1zM17 5h3v14h-3zM21 5h1v14h-1z"></path>
                                            </svg>
                                        </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7">
                                <div style="padding: 48px 20px; text-align: center; color: #64748B;">
                                    <svg width="44" height="44" viewBox="0 0 24 24" fill="none" stroke="#94A3B8" stroke-width="1.6" style="margin: 0 auto 12px; display: block;">
                                        <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path>
                                        <polyline points="3.27 6.96 12 12.01 20.73 6.96"></polyline>
                                        <line x1="12" y1="22.08" x2="12" y2="12"></line>
                                    </svg>
                                    <p style="font-size: 15px; font-weight: 600; color: #071E49; margin-bottom: 4px;">
                                        Belum ada data penerimaan
                                    </p>
                                    <p style="font-size: 13px; color: #64748B; margin-bottom: 18px;">
                                        Belum ada barang fisik yang diterima. Catat penerimaan bahan pertama Anda.
                                    </p>
                                    <button type="button" class="btn-tambah-staff" onclick="openCreateModal()" style="display: inline-flex;">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                            <line x1="12" y1="5" x2="12" y2="19"></line>
                                            <line x1="5" y1="12" x2="19" y2="12"></line>
                                        </svg>
                                        <span>Catat Penerimaan</span>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if ($receivings->hasPages() || $receivings->total() > 0)
            <div class="table-pagination-footer">
                <div class="pagination-info">
                    Menampilkan {{ $receivings->firstItem() ?? 0 }}-{{ $receivings->lastItem() ?? 0 }} dari {{ $receivings->total() }} data
                </div>
                <div class="pagination-links">
                    {{ $receivings->appends(request()->query())->onEachSide(1)->links('pagination::bootstrap-4') }}
                </div>
            </div>
        @endif
    </div>
</div>
@endsection

@section('modals')
{{-- ==========================================================================
     1. DETAIL PENERIMAAN BAHAN: POP UP DI SEBELAH KANAN (RIGHT DRAWER)
     ========================================================================== --}}
<div class="purchase-drawer-backdrop" id="receivingDrawerBackdrop" onclick="closeReceivingDetailDrawer()"></div>

<aside class="purchase-drawer" id="receivingDetailDrawer" role="dialog" aria-modal="true" aria-label="Detail Penerimaan Bahan">
    <div class="purchase-drawer-header">
        <div>
            <h2 class="purchase-drawer-title">Detail Penerimaan Bahan</h2>
            <p class="purchase-drawer-subtitle">Informasi verifikasi fisik kedatangan barang dan lot terbit</p>
        </div>
        <div style="display: flex; align-items: center; gap: 8px;">
            <a id="btnDrawerOpenFullPage" href="#" class="btn-action-icon" style="width: 34px; height: 34px;" title="Buka di Laman Penuh">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path>
                    <polyline points="15 3 21 3 21 9"></polyline>
                    <line x1="10" y1="14" x2="21" y2="3"></line>
                </svg>
            </a>
            <button type="button" class="modal-close-corner" onclick="closeReceivingDetailDrawer()" title="Tutup" aria-label="Tutup" style="position: static;">
                &times;
            </button>
        </div>
    </div>

    <div class="purchase-drawer-scroll" id="drawerBody">
        {{-- Card 1: Informasi Kedatangan --}}
        <div class="detail-card">
            <h3 class="detail-card-title" style="margin-bottom: 20px;">Informasi Kedatangan</h3>

            <div class="detail-grid-2col-exact">
                {{-- Kolom Kiri: 3 baris teratur --}}
                <div>
                    <div class="detail-exact-row">
                        <span class="detail-exact-label">No. Penerimaan</span>
                        <span class="detail-exact-colon">:</span>
                        <span class="detail-exact-value" id="detailReceivingNo" style="font-weight: 700; color: #071E49;">-</span>
                    </div>
                    <div class="detail-exact-row">
                        <span class="detail-exact-label">Nota Pembelian</span>
                        <span class="detail-exact-colon">:</span>
                        <span class="detail-exact-value" id="detailPurchaseNo" style="font-weight: 600; color: #071E49;">-</span>
                    </div>
                    <div class="detail-exact-row">
                        <span class="detail-exact-label">Petugas</span>
                        <span class="detail-exact-colon">:</span>
                        <span class="detail-exact-value" id="detailReceiverName">-</span>
                    </div>
                </div>

                {{-- Kolom Kanan: 3 baris teratur sejajar --}}
                <div>
                    <div class="detail-exact-row">
                        <span class="detail-exact-label">Status</span>
                        <span class="detail-exact-colon">:</span>
                        <div class="detail-exact-value">
                            <span id="detailBadgeStatus" class="badge-status badge-received" style="margin: 0;">
                                <span class="status-dot"></span>
                                <span id="detailBadgeText">Terkonfirmasi</span>
                            </span>
                        </div>
                    </div>
                    <div class="detail-exact-row">
                        <span class="detail-exact-label">Waktu Terima</span>
                        <span class="detail-exact-colon">:</span>
                        <span class="detail-exact-value" id="detailReceivedAt">-</span>
                    </div>
                    <div class="detail-exact-row">
                        <span class="detail-exact-label">Konfirmasi</span>
                        <span class="detail-exact-colon">:</span>
                        <span class="detail-exact-value" id="detailConfirmedAt">-</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Card 2: Pemeriksaan Fisik Barang Datang --}}
        <div class="detail-card">
            <h3 class="detail-card-title">Pemeriksaan Fisik Barang Datang</h3>
            <div class="table-responsive">
                <table class="detail-items-table">
                    <thead>
                        <tr>
                            <th style="width: 36px;">No</th>
                            <th>Nama Bahan</th>
                            <th style="width: 80px;">Dipesan</th>
                            <th style="width: 80px;">Diterima</th>
                            <th style="width: 110px;">Tgl Expired</th>
                            <th>Kondisi Fisik</th>
                        </tr>
                    </thead>
                    <tbody id="detailItemsTableBody">
                        {{-- Diisi melalui AJAX --}}
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Card 3: Barcode Lot yang terbit --}}
        <div class="detail-card" id="detailBarcodeCard">
            <h3 class="detail-card-title">Barcode Lot yang terbit</h3>
            <div id="detailBarcodeList">
                {{-- Diisi melalui AJAX --}}
            </div>
        </div>

        {{-- Card 4: Foto Bukti Kedatangan --}}
        <div class="detail-card">
            <h3 class="detail-card-title">Foto Bukti Kedatangan</h3>
            <div class="evidence-gallery-grid" id="detailEvidenceGallery">
                {{-- Diisi melalui AJAX --}}
            </div>
        </div>
    </div>

    {{-- Footer Actions di Drawer Sesuai Mockup --}}
    <div class="purchase-drawer-footer" style="justify-content: flex-end; gap: 14px;">
        <button type="button" id="btnDrawerVoid" class="btn-mockup-batal" onclick="confirmVoidFromDrawer()">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="10"></circle>
                <line x1="15" y1="9" x2="9" y2="15"></line>
                <line x1="9" y1="9" x2="15" y2="15"></line>
            </svg>
            <span>Batalkan Dokumen</span>
        </button>

        <a id="btnDrawerPrintAll" href="#" target="_blank" class="btn-mockup-cetak-all">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                <polyline points="14 2 14 8 20 8"></polyline>
                <line x1="16" y1="13" x2="8" y2="13"></line>
                <line x1="16" y1="17" x2="8" y2="17"></line>
            </svg>
            <span>Cetak Semua Barcode</span>
        </a>

        <button type="button" id="btnDrawerConfirm" class="btn-mockup-confirm" onclick="triggerConfirmDraftFromDrawer()" style="display: none;">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                <polyline points="20 6 9 17 4 12"></polyline>
            </svg>
            <span>Konfirmasi Penerimaan</span>
        </button>
    </div>
</aside>

{{-- ==========================================================================
     2. FORM TAMBAH CATATAN PENERIMAAN: POP UP DI BAGIAN TENGAH (CENTER MODAL)
     ========================================================================== --}}
<div class="modal-backdrop" id="createReceivingModal">
    <div class="modal-dialog modal-purchase-dialog" style="max-width: 880px; position: relative;">
        <div class="modal-header" style="position: relative; padding: 22px 26px 16px; border-bottom: 1px solid #F1F5F9;">
            <div style="text-align: left;">
                <h3 class="modal-title" style="font-size: 20px; font-weight: 700; color: #071E49; margin: 0;">Tambah Catatan Penerimaan</h3>
                <p class="modal-subtitle" style="font-size: 13px; color: #64748B; margin-top: 4px; text-align: left;">
                    Catat fisik barang yang datang untuk diperiksa dan diterbitkan lot stoknya
                </p>
            </div>
            <button type="button" class="modal-close-corner" onclick="closeCreateModal()" title="Tutup" aria-label="Tutup">
                &times;
            </button>
        </div>

        <form id="createReceivingForm" method="POST" action="{{ route('staff.receivings.store') }}" enctype="multipart/form-data" onsubmit="event.preventDefault(); submitReceivingForm('confirm');">
            @csrf
            <input type="hidden" name="action" id="formActionField" value="confirm">

            <div class="modal-body" style="padding: 22px 26px; max-height: calc(85vh - 160px); overflow-y: auto; display: flex; flex-direction: column; gap: 20px;">
                {{-- Bagian 1: Informasi Kedatangan --}}
                <div class="detail-card" style="border: 1.5px solid #E2E8F0; padding: 20px 24px;">
                    <h4 style="font-size: 15px; font-weight: 700; color: #071E49; margin-bottom: 16px; text-align: left;">
                        Informasi Kedatangan
                    </h4>
                    <div class="form-header-grid">
                        <div>
                            <div class="form-inline-row">
                                <label class="form-inline-label">No. Penerimaan :</label>
                                <div style="display: flex; align-items: center; gap: 8px; flex: 1;">
                                    <input type="text" value="{{ $autoReceivingNo }}" class="form-input-mockup" readonly style="background-color: #F8FAFC; max-width: 190px;">
                                    <span style="font-size: 12.5px; color: #64748B; white-space: nowrap;">(Otomatis)</span>
                                </div>
                            </div>

                            <div class="form-inline-row">
                                <label class="form-inline-label">Nota Pembelian :</label>
                                <div class="select-dropdown-box" style="flex: 1; width: 100%;">
                                    <select name="purchase_id" id="selectPurchaseId" class="select-status-verif form-input-mockup" style="height: 38px; width: 100%; padding-right: 2.25rem;" onchange="onPurchaseChanged(this.value)" required>
                                        <option value="">-- Pilih Nota Pembelian (PO) --</option>
                                        @foreach ($receivablePurchases as $po)
                                            <option value="{{ $po->id }}">{{ $po->purchase_no }} - {{ $po->supplier?->nama ?? 'Pemasok' }}</option>
                                        @endforeach
                                    </select>
                                    <svg class="chevron-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="top: 11px;">
                                        <polyline points="6 9 12 15 18 9"></polyline>
                                    </svg>
                                </div>
                            </div>
                        </div>

                        <div>
                            <div class="form-inline-row form-inline-row-right">
                                <label class="form-inline-label">Waktu Terima :</label>
                                <input type="datetime-local" name="received_at" id="inputReceivedAt" value="{{ $defaultReceivedAt }}" class="form-input-mockup" style="flex: 1;" required>
                            </div>

                            <div class="form-inline-row form-inline-row-right">
                                <label class="form-inline-label">Petugas :</label>
                                <input type="text" value="{{ $currentStaff?->nama ?? 'Staff' }} (Staff SPPG)" class="form-input-mockup" readonly style="background-color: #F8FAFC; flex: 1;">
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Bagian 2: Pemeriksaan Fisik Barang Datang --}}
                <div class="detail-card" style="border: 1.5px solid #E2E8F0; padding: 18px 20px;">
                    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 14px;">
                        <h4 style="font-size: 15px; font-weight: 700; color: #071E49; margin-bottom: 0; text-align: left;">
                            Pemeriksaan Fisik Barang Datang
                        </h4>
                        <span id="itemsNotice" style="font-size: 12.5px; color: #64748B;">Pilih nota pembelian di atas untuk memuat item</span>
                    </div>

                    <div class="table-responsive">
                        <table class="items-form-table">
                            <thead>
                                <tr>
                                    <th style="width: 32px;">No</th>
                                    <th style="min-width: 160px;">Nama Bahan</th>
                                    <th style="width: 100px;">Qty Dipesan</th>
                                    <th style="width: 120px;">Qty Diterima</th>
                                    <th style="width: 140px;">Tgl Expired</th>
                                    <th style="min-width: 150px;">Kondisi Fisik</th>
                                    <th style="width: 44px; text-align: center;">Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="createItemsTableBody">
                                <tr>
                                    <td colspan="7" style="text-align: center; padding: 24px; color: #94A3B8; font-size: 13.5px;">
                                        Belum ada item dipilih. Silakan pilih Nota Pembelian terlebih dahulu.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- Bagian 3: Dokumentasi Bukti --}}
                <div class="detail-card" style="border: 1.5px solid #E2E8F0; padding: 18px 20px;">
                    <h4 style="font-size: 15px; font-weight: 700; color: #071E49; margin-bottom: 6px; text-align: left;">
                        Dokumentasi Bukti
                    </h4>
                    <p style="font-size: 13px; font-weight: 500; color: #071E49; margin-bottom: 12px; text-align: left;">Foto Barang/Timbangan</p>

                    <div class="evidence-dropzone-mockup" onclick="document.getElementById('evidenceFileInput').click()">
                        <input type="file" id="evidenceFileInput" name="evidence_files[]" multiple accept="image/jpeg,image/png,image/jpg" style="display: none;" onchange="handleEvidenceFiles(this.files)">
                        <button type="button" class="btn-upload-picker-mockup">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
                                <circle cx="8.5" cy="8.5" r="1.5"></circle>
                                <polyline points="21 15 16 10 5 21"></polyline>
                            </svg>
                            <span>Pilih File / Unggah Foto Bukti</span>
                        </button>
                        <span style="font-size: 13px; color: #475569;">Format : JPG, PNG (maks 5MB)</span>
                    </div>

                    <div id="evidencePreviewContainer" class="evidence-preview-list"></div>
                </div>
            </div>

            {{-- Modal Action Buttons Sesuai Mockup --}}
            <div style="display: flex; align-items: center; justify-content: flex-end; gap: 14px; padding: 16px 26px; border-top: 1px solid #E2E8F0; background-color: #FFFFFF;">
                <button type="button" class="btn-mockup-batal" onclick="closeCreateModal()">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="10"></circle>
                        <line x1="15" y1="9" x2="9" y2="15"></line>
                        <line x1="9" y1="9" x2="15" y2="15"></line>
                    </svg>
                    <span>Batal</span>
                </button>

                <button type="button" class="btn-edit-action" onclick="submitReceivingForm('draft')" style="border-radius: 9px; padding: 9px 18px;">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path>
                        <polyline points="17 21 17 13 7 13 7 21"></polyline>
                        <polyline points="7 3 7 8 15 8"></polyline>
                    </svg>
                    <span>Simpan Draft</span>
                </button>

                <button type="button" class="btn-mockup-confirm" onclick="submitReceivingForm('confirm')">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                        <polyline points="22 4 12 14.01 9 11.01"></polyline>
                    </svg>
                    <span>Simpan & Konfirmasi</span>
                </button>
            </div>
        </form>
    </div>
</div>

{{-- ==========================================================================
     3. POP UP NOTIFIKASI / PERINGATAN (DI BAGIAN TENGAH)
     ========================================================================== --}}
<div class="modal-backdrop" id="appWarningModal">
    <div class="modal-dialog" style="max-width: 440px; text-align: left; position: relative; padding: 26px 26px 22px;">
        <button type="button" class="modal-close-corner" onclick="closeAppWarningModal()" title="Tutup" aria-label="Tutup">
            &times;
        </button>

        <div style="display: flex; align-items: flex-start; gap: 14px; margin-bottom: 16px;">
            <div id="appWarningIconWrap" style="width: 42px; height: 42px; border-radius: 50%; background-color: #FEF2F2; color: #DC2626; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                    <circle cx="12" cy="12" r="10"></circle>
                    <line x1="12" y1="8" x2="12" y2="12"></line>
                    <line x1="12" y1="16" x2="12.01" y2="16"></line>
                </svg>
            </div>
            <div>
                <h3 id="appWarningTitle" style="font-size: 17px; font-weight: 700; color: #071E49; margin: 0 0 4px 0; text-align: left;">Perhatian</h3>
                <p id="appWarningMessage" style="font-size: 13.5px; color: #64748B; margin: 0; line-height: 1.5; text-align: left;"></p>
            </div>
        </div>

        <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 20px;">
            <button type="button" id="appWarningConfirmBtn" class="btn-pesan-action" style="padding: 8px 20px; font-size: 13.5px;" onclick="closeAppWarningModal()">
                Mengerti
            </button>
        </div>
    </div>
</div>

{{-- ==========================================================================
     4. POP UP KONFIRMASI PEMBATALAN DOKUMEN (DI BAGIAN TENGAH)
     ========================================================================== --}}
<div class="modal-backdrop" id="voidConfirmModal">
    <div class="modal-dialog" style="max-width: 450px; text-align: left; position: relative; padding: 26px 26px 22px;">
        <button type="button" class="modal-close-corner" onclick="closeVoidModal()" title="Tutup" aria-label="Tutup">
            &times;
        </button>

        <form id="voidForm" method="POST" action="">
            @csrf
            <div style="display: flex; align-items: flex-start; gap: 14px; margin-bottom: 16px;">
                <div style="width: 42px; height: 42px; border-radius: 50%; background-color: #FEF2F2; color: #DC2626; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                        <circle cx="12" cy="12" r="10"></circle>
                        <line x1="15" y1="9" x2="9" y2="15"></line>
                        <line x1="9" y1="9" x2="15" y2="15"></line>
                    </svg>
                </div>
                <div>
                    <h3 style="font-size: 17px; font-weight: 700; color: #071E49; margin: 0 0 4px 0; text-align: left;">Batalkan Dokumen Penerimaan?</h3>
                    <p style="font-size: 13.5px; color: #64748B; margin: 0; line-height: 1.5; text-align: left;">
                        Dokumen penerimaan ini akan berstatus <strong style="color: #DC2626;">Dibatalkan (Voided)</strong>. Penerimaan draft yang dibatalkan tidak akan memengaruhi stok.
                    </p>
                </div>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 20px;">
                <button type="button" class="btn-cancel-action" style="padding: 8px 18px;" onclick="closeVoidModal()">
                    Batal
                </button>
                <button type="submit" class="btn-batal-doc" style="background-color: #DC2626; color: #FFFFFF; padding: 8px 20px;">
                    Ya, Batalkan Dokumen
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    let activeReceivingId = null;
    let isSubmitting = false;

    // Center Warning Modal
    function showAppWarning(message, title = 'Perhatian') {
        document.getElementById('appWarningTitle').innerText = title;
        document.getElementById('appWarningMessage').innerText = message;
        document.getElementById('appWarningModal').classList.add('open');
        document.body.style.overflow = 'hidden';
    }

    function closeAppWarningModal() {
        document.getElementById('appWarningModal').classList.remove('open');
        const hasOpen = document.querySelector('.modal-backdrop.open');
        if (!hasOpen) document.body.style.overflow = '';
    }

    // Center Add Modal
    function openCreateModal() {
        document.getElementById('createReceivingModal').classList.add('open');
        document.body.style.overflow = 'hidden';
    }

    function closeCreateModal() {
        document.getElementById('createReceivingModal').classList.remove('open');
        const hasOpen = document.querySelector('.modal-backdrop.open');
        if (!hasOpen) document.body.style.overflow = '';
    }

    // Left Drawer Detail (Pop up di Sebelah Kiri)
    window.openReceivingDetailFromLink = function(e, receivingId) {
        if (e && (e.ctrlKey || e.metaKey || e.button === 1)) {
            return true; // Biarkan browser membuka tab baru jika Ctrl/Meta/Middle click
        }
        if (e) {
            e.preventDefault();
            e.stopPropagation();
        }
        window.openReceivingDetailDrawer(receivingId);
        return false;
    };

    window.openReceivingDetailDrawer = function(receivingId) {
        try {
            activeReceivingId = receivingId;
            const drawer = document.getElementById('receivingDetailDrawer');
            const backdrop = document.getElementById('receivingDrawerBackdrop');

            if (!drawer || !backdrop) {
                console.warn('Elemen drawer tidak ditemukan, mengalihkan ke halaman penuh.');
                window.location.href = `/staff/penerimaan/${receivingId}`;
                return;
            }

            // Tampilkan backdrop dan drawer di sebelah kiri SEGERA
            drawer.classList.add('open');
            backdrop.classList.add('open');
            document.body.style.overflow = 'hidden';

            const btnFull = document.getElementById('btnDrawerOpenFullPage');
            if (btnFull) btnFull.href = `/staff/penerimaan/${receivingId}`;

            // Helper aman untuk set teks
            const setSafe = (id, val) => {
                const el = document.getElementById(id);
                if (el) el.innerText = val;
            };

            setSafe('detailReceivingNo', 'Memuat...');
            setSafe('detailPurchaseNo', '-');
            setSafe('detailReceiverName', '-');
            setSafe('detailReceivedAt', '-');
            setSafe('detailConfirmedAt', '-');
            setSafe('detailSupplierName', '-');
            setSafe('detailBadgeText', 'Memuat...');

            const tbody = document.getElementById('detailItemsTableBody');
            if (tbody) tbody.innerHTML = '<tr><td colspan="6" style="text-align: center; color: #94A3B8; padding: 24px;">Memuat rincian item...</td></tr>';
            
            const barcodeList = document.getElementById('detailBarcodeList');
            if (barcodeList) barcodeList.innerHTML = '';
            
            const gallery = document.getElementById('detailEvidenceGallery');
            if (gallery) gallery.innerHTML = '';

            // Fetch detail data dengan format JSON
            fetch(`/staff/penerimaan/${receivingId}?format=json`, {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
                .then(res => {
                    if (!res.ok) throw new Error('HTTP ' + res.status);
                    return res.json();
                })
                .then(data => {
                    if (data.success) {
                        populateDrawerData(data);
                    } else {
                        showAppWarning('Gagal memuat detail penerimaan: ' + (data.message || 'Error'));
                    }
                })
                .catch(err => {
                    console.error('Fetch error:', err);
                    showAppWarning('Terjadi kendala saat memuat detail penerimaan: ' + err.message);
                });
        } catch (err) {
            console.error('Crash in openReceivingDetailDrawer:', err);
            window.location.href = `/staff/penerimaan/${receivingId}`;
        }
    };

    window.closeReceivingDetailDrawer = function() {
        const backdrop = document.getElementById('receivingDrawerBackdrop');
        const drawer = document.getElementById('receivingDetailDrawer');
        if (backdrop) backdrop.classList.remove('open');
        if (drawer) drawer.classList.remove('open');
        const hasOpen = document.querySelector('.modal-backdrop.open, .purchase-drawer.open');
        if (!hasOpen) document.body.style.overflow = '';
    };

    // Alias backward-compatibility
    window.openDetailDrawerFromLink = window.openReceivingDetailFromLink;
    window.openDetailDrawer = window.openReceivingDetailDrawer;
    window.closeDetailDrawer = window.closeReceivingDetailDrawer;

    function populateDrawerData(data) {
        if (!data || !data.receiving) return;
        const rcv = data.receiving;
        
        const setSafe = (id, val) => {
            const el = document.getElementById(id);
            if (el) el.innerText = (val !== null && val !== undefined && val !== '') ? val : '-';
        };

        const btnFull = document.getElementById('btnDrawerOpenFullPage');
        if (btnFull) btnFull.href = `/staff/penerimaan/${rcv.id}`;

        setSafe('detailReceivingNo', rcv.receiving_no);
        setSafe('detailPurchaseNo', rcv.purchase_no + ' (' + rcv.supplier_name + ')');
        setSafe('detailReceiverName', rcv.receiver_name);
        setSafe('detailReceivedAt', rcv.received_at);
        setSafe('detailConfirmedAt', rcv.confirmed_at !== '-' ? rcv.confirmed_at : '-');
        setSafe('detailSupplierName', rcv.supplier_name);

        // Badge Status
        const badge = document.getElementById('detailBadgeStatus');
        if (badge) {
            badge.className = 'badge-status ' + (rcv.status_badge_class || 'badge-ordered');
        }
        setSafe('detailBadgeText', rcv.status_label || rcv.status);

        // Items Table
        const tbody = document.getElementById('detailItemsTableBody');
        if (tbody) {
            tbody.innerHTML = '';
            (data.items || []).forEach(item => {
                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td>${item.no}</td>
                    <td style="font-weight: 600; color: #071E49;">${item.material_name}</td>
                    <td>${item.qty_ordered} ${item.satuan}</td>
                    <td style="font-weight: 600; color: #16A34A;">${item.qty_received} ${item.satuan}</td>
                    <td>${item.expiry_date}</td>
                    <td style="color: #475569;">${item.kondisi_fisik}</td>
                `;
                tbody.appendChild(tr);
            });
        }

        // Barcode Lot Section
        const barcodeList = document.getElementById('detailBarcodeList');
        const barcodeCard = document.getElementById('detailBarcodeCard');

        if (barcodeCard && barcodeList) {
            barcodeList.innerHTML = '';
            if (rcv.status === 'confirmed') {
                barcodeCard.style.display = 'block';
                (data.items || []).forEach(item => {
                    const row = document.createElement('div');
                    row.className = 'barcode-lot-row';
                    row.innerHTML = `
                        <div class="barcode-lot-left">
                            <span style="color: #071E49; font-size: 16px;">•</span>
                            <span class="barcode-lot-name">${item.material_name}</span>
                            <span style="color: #071E49; font-weight: 600;">:</span>
                            <span class="lot-badge-pill">${item.barcode || item.lot_number || '-'}</span>
                        </div>
                        ${item.lot_id ? `
                            <a href="/staff/penerimaan/${rcv.id}/barcode/${item.lot_id}" target="_blank" class="btn-mockup-cetak-lot">
                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                                    <polyline points="14 2 14 8 20 8"></polyline>
                                    <line x1="16" y1="13" x2="8" y2="13"></line>
                                    <line x1="16" y1="17" x2="8" y2="17"></line>
                                </svg>
                                <span>Cetak</span>
                            </a>
                        ` : ''}
                    `;
                    barcodeList.appendChild(row);
                });
            } else {
                barcodeCard.style.display = 'block';
                barcodeList.innerHTML = `<p style="font-size: 13.5px; color: #64748B; margin: 0;">Barcode lot akan diterbitkan otomatis setelah penerimaan dikonfirmasi.</p>`;
            }
        }

        // Evidences
        const gallery = document.getElementById('detailEvidenceGallery');
        if (gallery) {
            gallery.innerHTML = '';
            const evList = data.evidences || [];
            const totalBoxes = Math.max(3, evList.length);

            for (let i = 0; i < totalBoxes; i++) {
                const box = document.createElement('div');
                box.className = 'evidence-photo-box';
                if (i < evList.length) {
                    const ev = evList[i];
                    box.innerHTML = `<img src="${ev.url}" alt="${ev.description || 'Foto Bukti'}" class="evidence-photo-img" title="Klik untuk membuka ukuran penuh" onclick="window.open('${ev.url}', '_blank')">`;
                }
                gallery.appendChild(box);
            }
        }

        // Action Buttons in Drawer
        const btnPrintAll = document.getElementById('btnDrawerPrintAll');
        const btnVoid = document.getElementById('btnDrawerVoid');
        const btnConfirm = document.getElementById('btnDrawerConfirm');

        if (btnPrintAll) {
            btnPrintAll.href = `/staff/penerimaan/${rcv.id}/barcode`;
        }

        if (rcv.status === 'confirmed') {
            if (btnPrintAll) btnPrintAll.style.display = 'inline-flex';
            if (btnConfirm) btnConfirm.style.display = 'none';
            if (btnVoid) {
                btnVoid.style.display = 'inline-flex';
                btnVoid.innerHTML = `
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="10"></circle>
                        <line x1="15" y1="9" x2="9" y2="15"></line>
                        <line x1="9" y1="9" x2="15" y2="15"></line>
                    </svg>
                    <span>Batalkan Dokumen</span>
                `;
            }
        } else if (rcv.status === 'draft') {
            if (btnPrintAll) btnPrintAll.style.display = 'none';
            if (btnConfirm) btnConfirm.style.display = 'inline-flex';
            if (btnVoid) {
                btnVoid.style.display = 'inline-flex';
                btnVoid.innerHTML = `
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="10"></circle>
                        <line x1="15" y1="9" x2="9" y2="15"></line>
                        <line x1="9" y1="9" x2="15" y2="15"></line>
                    </svg>
                    <span>Batalkan Draft</span>
                `;
            }
        } else {
            if (btnPrintAll) btnPrintAll.style.display = 'none';
            if (btnConfirm) btnConfirm.style.display = 'none';
            if (btnVoid) btnVoid.style.display = 'none';
        }
    }

    // Toggle Date Range Popover
    function toggleDatePopover(e) {
        e.stopPropagation();
        const popover = document.getElementById('datePopover');
        popover.classList.toggle('open');
    }

    function clearDateFilter() {
        document.getElementById('popoverDateFrom').value = '';
        document.getElementById('popoverDateTo').value = '';
        document.querySelector('.filter-bar-form').submit();
    }

    // Close Date Popover on outside click
    document.addEventListener('click', function(e) {
        const popover = document.getElementById('datePopover');
        const btn = document.getElementById('datePickerBtn');
        if (popover && popover.classList.contains('open')) {
            if (!popover.contains(e.target) && !btn.contains(e.target)) {
                popover.classList.remove('open');
            }
        }
    });

    // PO Changed -> Fetch Items
    function onPurchaseChanged(purchaseId) {
        const tbody = document.getElementById('createItemsTableBody');
        const notice = document.getElementById('itemsNotice');

        if (!purchaseId) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="7" style="text-align: center; padding: 24px; color: #94A3B8; font-size: 13.5px;">
                        Belum ada item dipilih. Silakan pilih Nota Pembelian terlebih dahulu.
                    </td>
                </tr>
            `;
            notice.innerText = 'Pilih nota pembelian di atas untuk memuat item';
            return;
        }

        notice.innerText = 'Memuat item dari nota pembelian...';

        fetch(`/staff/penerimaan/po/${purchaseId}/items`)
            .then(res => res.json())
            .then(data => {
                if (data.success && data.items && data.items.length > 0) {
                    tbody.innerHTML = '';
                    const defaultExpDate = getDefaultExpiryDate();

                    data.items.forEach((item, idx) => {
                        const tr = document.createElement('tr');
                        tr.id = `item-row-${idx}`;
                        tr.innerHTML = `
                            <td style="color: #071E49; font-weight: 500;">${idx + 1}</td>
                            <td>
                                <input type="hidden" name="items[${idx}][purchase_item_id]" value="${item.purchase_item_id}">
                                <input type="hidden" name="items[${idx}][material_id]" value="${item.material_id}">
                                <input type="text" value="${item.material_name}" class="form-input-mockup" readonly style="background-color: #FFFFFF; font-weight: 500;">
                            </td>
                            <td>
                                <span style="font-weight: 500; color: #071E49; font-size: 13.5px; white-space: nowrap;">${item.qty_remaining} ${item.satuan}</span>
                            </td>
                            <td>
                                <div style="display: flex; align-items: center; gap: 6px;">
                                    <input type="number" step="any" min="0.001" max="${item.qty_remaining}" name="items[${idx}][qty_received]" value="${item.qty_remaining}" class="form-input-mockup" style="width: 70px; text-align: center;" required>
                                    <span style="font-size: 13px; color: #071E49; font-weight: 500;">${item.satuan}</span>
                                </div>
                            </td>
                            <td>
                                <input type="date" name="items[${idx}][expiry_date_input]" value="${defaultExpDate}" class="form-input-mockup" required>
                            </td>
                            <td>
                                <input type="text" name="items[${idx}][kondisi_dilaporkan]" value="Segar, Dingin" placeholder="Segar, Dingin" class="form-input-mockup">
                            </td>
                            <td style="text-align: center;">
                                <button type="button" class="btn-delete-row-mockup" onclick="removeCreateItemRow(${idx})" title="Hapus baris ini">
                                    <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="#DC2626" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <polyline points="3 6 5 6 21 6"></polyline>
                                        <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                                    </svg>
                                </button>
                            </td>
                        `;
                        tbody.appendChild(tr);
                    });

                    notice.innerText = `${data.items.length} item siap diterima`;
                } else {
                    tbody.innerHTML = `
                        <tr>
                            <td colspan="7" style="text-align: center; padding: 24px; color: #D97706; font-size: 13.5px;">
                                Semua item pada Nota Pembelian ini sudah diterima lengkap.
                            </td>
                        </tr>
                    `;
                    notice.innerText = 'Tidak ada sisa item yang belum diterima';
                }
            })
            .catch(err => {
                showAppWarning('Gagal memuat item dari Nota Pembelian terpilih.');
            });
    }

    function removeCreateItemRow(idx) {
        const row = document.getElementById(`item-row-${idx}`);
        if (row) {
            row.remove();
        }
        const tbody = document.getElementById('createItemsTableBody');
        const rows = tbody.querySelectorAll('tr');
        if (rows.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="7" style="text-align: center; padding: 24px; color: #DC2626; font-size: 13.5px;">
                        Semua baris dihapus. Silakan pilih kembali Nota Pembelian.
                    </td>
                </tr>
            `;
        }
    }

    function getDefaultExpiryDate() {
        const d = new Date();
        d.setDate(d.getDate() + 14);
        const year = d.getFullYear();
        const month = String(d.getMonth() + 1).padStart(2, '0');
        const day = String(d.getDate()).padStart(2, '0');
        return `${year}-${month}-${day}`;
    }

    // Handle Uploaded File Previews
    function handleEvidenceFiles(files) {
        const container = document.getElementById('evidencePreviewContainer');
        container.innerHTML = '';

        if (!files || files.length === 0) return;

        Array.from(files).forEach((file, index) => {
            if (!file.type.startsWith('image/')) return;

            const reader = new FileReader();
            reader.onload = function(e) {
                const item = document.createElement('div');
                item.className = 'evidence-preview-item';
                item.innerHTML = `
                    <img src="${e.target.result}" alt="Preview">
                `;
                container.appendChild(item);
            };
            reader.readAsDataURL(file);
        });
    }

    // Submit Form
    function submitReceivingForm(action) {
        if (isSubmitting) return;

        const form = document.getElementById('createReceivingForm');
        document.getElementById('formActionField').value = action;

        // Validasi PO
        const purchaseId = document.getElementById('selectPurchaseId').value;
        if (!purchaseId) {
            showAppWarning('Pilih Nota Pembelian terlebih dahulu sebelum menyimpan.', 'Form Belum Lengkap');
            return;
        }

        // Validasi minimal 1 baris item
        const tbody = document.getElementById('createItemsTableBody');
        const inputItems = tbody.querySelectorAll('input[name*="[purchase_item_id]"]');
        if (inputItems.length === 0) {
            showAppWarning('Daftar pemeriksaan fisik barang tidak boleh kosong.', 'Item Belum Dipilih');
            return;
        }

        // Validasi konfirmasi: wajib foto bukti
        if (action === 'confirm') {
            const fileInput = document.getElementById('evidenceFileInput');
            if (!fileInput.files || fileInput.files.length === 0) {
                showAppWarning('Lampirkan minimal satu foto nota atau fisik barang saat konfirmasi penerimaan.', 'Foto Bukti Wajib');
                return;
            }
        }

        isSubmitting = true;
        form.submit();
    }

    // Void from Drawer
    function confirmVoidFromDrawer() {
        if (!activeReceivingId) return;

        const form = document.getElementById('voidForm');
        form.action = `/staff/penerimaan/${activeReceivingId}/void`;

        document.getElementById('voidConfirmModal').classList.add('open');
        document.body.style.overflow = 'hidden';
    }

    function closeVoidModal() {
        document.getElementById('voidConfirmModal').classList.remove('open');
        const hasOpen = document.querySelector('.modal-backdrop.open');
        if (!hasOpen) document.body.style.overflow = '';
    }

    function triggerConfirmDraftFromDrawer() {
        if (!activeReceivingId) return;

        fetch(`/staff/penerimaan/${activeReceivingId}/confirm`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json'
            }
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                window.location.reload();
            } else {
                showAppWarning(data.message || 'Gagal mengonfirmasi draft penerimaan.');
            }
        })
        .catch(err => {
            showAppWarning('Terjadi kendala saat mengonfirmasi dokumen.');
        });
    }

    // Close on ESC
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeCreateModal();
            closeReceivingDetailDrawer();
            closeAppWarningModal();
            closeVoidModal();
            const datePop = document.getElementById('datePopover');
            if (datePop) datePop.classList.remove('open');
        }
    });

    // Delegasi klik tombol detail
    document.addEventListener('click', function(e) {
        const btn = e.target.closest('[data-receiving-detail-id], [data-receiving-detail-btn]');
        if (btn) {
            if (!e.ctrlKey && !e.metaKey && e.button === 0) {
                e.preventDefault();
                e.stopPropagation();
                const rcvId = btn.getAttribute('data-receiving-detail-id') || btn.getAttribute('data-receiving-detail-btn');
                if (rcvId) window.openReceivingDetailDrawer(rcvId);
            }
        }
    });

    // Auto-open form tambah penerimaan jika parameter purchase_id ada (dari modul Pembelian)
    document.addEventListener('DOMContentLoaded', function() {
        const urlParams = new URLSearchParams(window.location.search);
        const purchaseIdParam = urlParams.get('purchase_id') || '{{ $selectedPurchaseId ?? "" }}';
        if (purchaseIdParam) {
            openCreateModal();
            const selectPo = document.getElementById('selectPurchaseId');
            if (selectPo) {
                selectPo.value = purchaseIdParam;
                onPurchaseChanged(purchaseIdParam);
            }
        }
    });
</script>
@endpush
