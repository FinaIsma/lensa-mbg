@extends('layouts.staff')

@section('title', 'Dashboard Operasional - Staff SPPG')

@section('content')
<div class="space-y-7 pb-8">
    {{-- Page Title & Subtitle --}}
    <div class="page-title-section">
        <h1 class="page-title">
            Selamat datang, {{ $user->nama ?? 'Staff' }} di Dashboard Operasional SPPG
        </h1>
        <p class="page-subtitle">
            Ringkasan data dan aktivitas operasional SPPG secara real-time.
        </p>
    </div>

    {{-- 4 Stat Cards Grid --}}
    <div class="stat-cards-grid">
        {{-- Card 1: Total Menu --}}
        <div class="stat-card">
            <div class="stat-icon-wrapper icon-blue">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
                    <line x1="9" y1="9" x2="15" y2="9"></line>
                    <line x1="9" y1="13" x2="15" y2="13"></line>
                    <line x1="9" y1="17" x2="11" y2="17"></line>
                </svg>
            </div>
            <div class="stat-content">
                <span class="stat-label">Total Menu</span>
                <span class="stat-value">{{ number_format($stats['total_menu'], 0, ',', '.') }}</span>
                <span class="stat-subtext">Menu Terdaftar</span>
            </div>
        </div>

        {{-- Card 2: Total Resep --}}
        <div class="stat-card">
            <div class="stat-icon-wrapper icon-green">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                    <polyline points="14 2 14 8 20 8"></polyline>
                    <line x1="16" y1="13" x2="8" y2="13"></line>
                    <line x1="16" y1="17" x2="8" y2="17"></line>
                </svg>
            </div>
            <div class="stat-content">
                <span class="stat-label">Total Resep</span>
                <span class="stat-value">{{ number_format($stats['total_resep'], 0, ',', '.') }}</span>
                <span class="stat-subtext">Resep Terdaftar</span>
            </div>
        </div>

        {{-- Card 3: PO Draft (Tugas Hari Ini) --}}
        <div class="stat-card">
            <div class="stat-icon-wrapper icon-yellow">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10"></circle>
                    <polyline points="12 6 12 12 14 14"></polyline>
                </svg>
            </div>
            <div class="stat-content">
                <span class="stat-label">PO Draft (Tugas)</span>
                <span class="stat-value">{{ number_format($stats['po_draft_count'], 0, ',', '.') }}</span>
                <span class="stat-subtext">Perlu Diselesaikan</span>
            </div>
        </div>

        {{-- Card 4: Menunggu Barang --}}
        <div class="stat-card">
            <div class="stat-icon-wrapper icon-purple">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"></path>
                    <line x1="3" y1="6" x2="21" y2="6"></line>
                    <path d="M16 10a4 4 0 0 1-8 0"></path>
                </svg>
            </div>
            <div class="stat-content">
                <span class="stat-label">Menunggu Barang</span>
                <span class="stat-value">{{ number_format($stats['po_waiting_count'], 0, ',', '.') }}</span>
                <span class="stat-subtext">PO Dipesan/Parsial</span>
            </div>
        </div>
    </div>

    {{-- Two Columns Section: Kartu Tugas Hari Ini (PO Draft) & Pembelian Menunggu Barang --}}
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(360px, 1fr)); gap: 24px;">
        {{-- Kartu Tugas Hari Ini: PO Draft --}}
        <div class="detail-card" style="margin-bottom: 0;">
            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 16px;">
                <div style="display: flex; align-items: center; gap: 10px;">
                    <div style="width: 34px; height: 34px; border-radius: 8px; background-color: #FEF3C7; color: #D97706; display: flex; align-items: center; justify-content: center;">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="10"></circle>
                            <polyline points="12 6 12 12 14 14"></polyline>
                        </svg>
                    </div>
                    <h3 style="font-size: 16px; font-weight: 700; color: #071E49; margin: 0;">Tugas Hari Ini: PO Draft</h3>
                </div>
                <a href="{{ route('staff.purchases.index', ['status' => 'draft']) }}" style="font-size: 12.5px; font-weight: 600; color: #0284C7; text-decoration: none;">
                    Lihat Semua &rarr;
                </a>
            </div>

            @if($draftPurchases->isEmpty())
                <div style="padding: 24px; text-align: center; color: #64748B; font-size: 13.5px;">
                    <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="#CBD5E1" stroke-width="1.8" style="margin: 0 auto 8px; display: block;">
                        <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                        <polyline points="22 4 12 14.01 9 11.01"></polyline>
                    </svg>
                    Tidak ada draft PO yang tertunda saat ini.
                </div>
            @else
                <div class="space-y-2">
                    @foreach($draftPurchases as $dp)
                        <div style="display: flex; align-items: center; justify-content: space-between; padding: 10px 14px; background-color: #F8FAFC; border-radius: 10px; border: 1px solid #F1F5F9;">
                            <div>
                                <a href="{{ route('staff.purchases.index', ['detail_id' => $dp->id]) }}" style="font-size: 13.5px; font-weight: 600; color: #071E49; text-decoration: none;">
                                    {{ $dp->purchase_no }}
                                </a>
                                <div style="font-size: 12px; color: #64748B; margin-top: 2px;">
                                    {{ $dp->supplier->nama ?? '-' }} &bull; {{ $dp->total_items_count }} item
                                </div>
                            </div>
                            <div style="display: flex; align-items: center; gap: 8px;">
                                <a href="{{ route('staff.purchases.index', ['edit_id' => $dp->id]) }}" class="btn-action-icon" style="width: 30px; height: 30px;" title="Lanjutkan Draft">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                                        <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                                    </svg>
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- Kartu Pembelian Menunggu Barang --}}
        <div class="detail-card" style="margin-bottom: 0;">
            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 16px;">
                <div style="display: flex; align-items: center; gap: 10px;">
                    <div style="width: 34px; height: 34px; border-radius: 8px; background-color: #E0F2FE; color: #0284C7; display: flex; align-items: center; justify-content: center;">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"></path>
                            <line x1="3" y1="6" x2="21" y2="6"></line>
                            <path d="M16 10a4 4 0 0 1-8 0"></path>
                        </svg>
                    </div>
                    <h3 style="font-size: 16px; font-weight: 700; color: #071E49; margin: 0;">Pembelian Menunggu Barang</h3>
                </div>
                <a href="{{ route('staff.purchases.index', ['status' => 'ordered']) }}" style="font-size: 12.5px; font-weight: 600; color: #0284C7; text-decoration: none;">
                    Lihat Semua &rarr;
                </a>
            </div>

            @if($waitingPurchases->isEmpty())
                <div style="padding: 24px; text-align: center; color: #64748B; font-size: 13.5px;">
                    <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="#CBD5E1" stroke-width="1.8" style="margin: 0 auto 8px; display: block;">
                        <circle cx="12" cy="12" r="10"></circle>
                        <line x1="12" y1="8" x2="12" y2="12"></line>
                        <line x1="12" y1="16" x2="12.01" y2="16"></line>
                    </svg>
                    Belum ada pesanan pembelian yang menunggu pengiriman barang.
                </div>
            @else
                <div class="space-y-2">
                    @foreach($waitingPurchases as $wp)
                        <div style="display: flex; align-items: center; justify-content: space-between; padding: 10px 14px; background-color: #F8FAFC; border-radius: 10px; border: 1px solid #F1F5F9;">
                            <div>
                                <a href="{{ route('staff.purchases.index', ['detail_id' => $wp->id]) }}" style="font-size: 13.5px; font-weight: 600; color: #071E49; text-decoration: none;">
                                    {{ $wp->purchase_no }}
                                </a>
                                <div style="font-size: 12px; color: #64748B; margin-top: 2px;">
                                    {{ $wp->supplier->nama ?? '-' }} &bull; {{ $wp->formatted_total_cost }}
                                </div>
                            </div>
                            <div>
                                <span class="badge-status {{ $wp->status_badge_class }}" style="font-size: 11px; padding: 2px 8px;">
                                    <span class="status-dot"></span>
                                    <span>{{ $wp->status_label }}</span>
                                </span>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    {{-- Main Quick Action Container --}}
    <div class="table-card" style="padding: 32px 28px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 20px;">
        <div>
            <h3 style="font-size: 17px; font-weight: 700; color: #071E49; margin-bottom: 4px;">Pengadaan & Manajemen Pangan</h3>
            <p style="font-size: 13.5px; color: #64748B; margin: 0;">Akses cepat untuk membuat nota pembelian baru atau mengelola resep menu makanan.</p>
        </div>
        <div style="display: flex; gap: 12px; flex-wrap: wrap;">
            <a href="{{ route('staff.purchases.index', ['action' => 'create']) }}" class="btn-tambah-staff" style="text-decoration: none;">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                    <line x1="12" y1="5" x2="12" y2="19"></line>
                    <line x1="5" y1="12" x2="19" y2="12"></line>
                </svg>
                <span>Catat Pembelian Baru</span>
            </a>
            <a href="{{ route('staff.menu-resep.index') }}" class="btn-edit-action" style="text-decoration: none;">
                <span>Kelola Menu & Resep</span>
            </a>
        </div>
    </div>
</div>
@endsection
