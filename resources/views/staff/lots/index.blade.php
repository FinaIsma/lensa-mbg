@extends('layouts.staff')

@section('title', 'Lot Bahan Baku & Stok - Staff SPPG')

@push('styles')
<style>
    /* ================= TAB NAVIGASI UTAMA ================= */
    .lot-main-tabs-nav {
        display: flex;
        gap: 12px;
        border-bottom: 2px solid #E2E8F0;
        margin-bottom: 24px;
    }

    .lot-nav-tab {
        display: inline-flex;
        align-items: center;
        gap: 10px;
        padding: 12px 20px;
        font-size: 14.5px;
        font-weight: 600;
        color: #64748B;
        background: transparent;
        border: none;
        border-bottom: 3px solid transparent;
        cursor: pointer;
        transition: all 0.2s ease;
        text-decoration: none;
        margin-bottom: -2px;
    }

    .lot-nav-tab:hover {
        color: #071E49;
    }

    .lot-nav-tab.active {
        color: #042A70;
        font-weight: 700;
        border-bottom-color: #042A70;
    }

    .tab-badge-pill {
        font-size: 11px;
        font-weight: 700;
        background: #DCFCE7;
        color: #15803D;
        padding: 2px 9px;
        border-radius: 9999px;
    }

    .tab-count-pill {
        font-size: 11px;
        font-weight: 700;
        background: #F1F5F9;
        color: #475569;
        padding: 2px 9px;
        border-radius: 9999px;
    }

    /* ================= TAB 1: STASIUN PEMINDAI (SCAN STATION) ================= */
    .scan-station-card {
        background: #FFFFFF;
        border: 1px solid #E2E8F0;
        border-radius: 16px;
        box-shadow: 0 4px 18px rgba(0, 0, 0, 0.04);
        padding: 24px;
        margin-bottom: 24px;
    }

    .scan-station-grid {
        display: grid;
        grid-template-columns: 440px 1fr;
        gap: 28px;
        align-items: center;
    }

    @media (max-width: 980px) {
        .scan-station-grid {
            grid-template-columns: 1fr;
        }
    }

    /* Live Video Kamera Bersih Tanpa Kotakan */
    .live-camera-box {
        position: relative;
        background: #0F172A;
        border-radius: 14px;
        overflow: hidden;
        min-height: 260px;
        max-height: 290px;
        height: 275px;
        box-shadow: inset 0 0 16px rgba(0, 0, 0, 0.5);
    }

    #embedded-camera-feed {
        position: absolute;
        inset: 0;
        width: 100% !important;
        height: 100% !important;
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 1;
    }

    #embedded-camera-feed video {
        width: 100% !important;
        height: 100% !important;
        object-fit: cover !important;
        border-radius: 14px;
    }

    #camera-placeholder-msg {
        position: absolute;
        inset: 0;
        width: 100%;
        height: 100%;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        text-align: center;
        padding: 24px;
        box-sizing: border-box;
        z-index: 2;
    }

    /* Hilangkan semua shaded-region/box bawaan html5-qrcode */
    #embedded-camera-feed #qr-shaded-region,
    #embedded-camera-feed svg,
    #embedded-camera-feed div[id*="shaded_region"],
    #embedded-camera-feed [id*="scan_region"] svg {
        display: none !important;
        box-shadow: none !important;
        border: none !important;
    }

    .live-pulse-dot {
        width: 9px;
        height: 9px;
        border-radius: 50%;
        background-color: #22C55E;
        box-shadow: 0 0 0 rgba(34, 197, 94, 0.4);
        animation: pulseLiveDot 1.8s infinite;
    }

    .live-pulse-dot.inactive {
        background-color: #94A3B8;
        box-shadow: none;
        animation: none;
    }

    @keyframes pulseLiveDot {
        0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(34, 197, 94, 0.7); }
        70% { transform: scale(1.05); box-shadow: 0 0 0 8px rgba(34, 197, 94, 0); }
        100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(34, 197, 94, 0); }
    }

    /* Status Pill Badge di Header Kamera */
    .camera-status-pill {
        font-size: 11.5px;
        font-weight: 600;
        padding: 3px 10px;
        border-radius: 9999px;
        transition: all 0.2s ease;
    }
    .camera-status-pill.standby {
        background: #F1F5F9;
        color: #64748B;
    }
    .camera-status-pill.active {
        background: #DCFCE7;
        color: #15803D;
    }
    .camera-status-pill.searching {
        background: #FEF3C7;
        color: #B45309;
    }
    .camera-status-pill.success {
        background: #DCFCE7;
        color: #16A34A;
    }

    /* Dropdown Pilih Perangkat Kamera dengan Garis Tepi Jelas */
    .camera-device-selector-wrapper {
        position: relative;
        margin-top: 12px;
        display: flex;
        align-items: center;
        width: 100%;
    }

    .camera-device-icon {
        position: absolute;
        left: 12px;
        color: #64748B;
        pointer-events: none;
        z-index: 2;
    }

    .camera-device-chevron {
        position: absolute;
        right: 12px;
        color: #64748B;
        pointer-events: none;
        z-index: 2;
    }

    .camera-device-select {
        width: 100%;
        height: 40px;
        padding: 8px 34px 8px 36px;
        background-color: #FFFFFF;
        border: 1.5px solid #CBD5E1 !important;
        border-radius: 10px;
        font-size: 12.5px;
        font-weight: 500;
        color: #0F172A;
        outline: none;
        cursor: pointer;
        appearance: none;
        -webkit-appearance: none;
        -moz-appearance: none;
        transition: all 0.15s ease;
        box-shadow: 0 1px 2px rgba(0, 0, 0, 0.04);
        box-sizing: border-box;
    }

    .camera-device-select:hover {
        border-color: #94A3B8 !important;
        background-color: #F8FAFC;
    }

    .camera-device-select:focus {
        border-color: #042A70 !important;
        box-shadow: 0 0 0 3px rgba(4, 42, 112, 0.1) !important;
        background-color: #FFFFFF;
    }

    /* Tombol Kembar Aksi Kamera & Upload Foto QR (Ukuran & Proporsi Identik 100%) */
    .btn-camera-scan-action,
    .btn-upload-scan-action {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        height: 42px;
        min-height: 42px;
        max-height: 42px;
        padding: 0 18px;
        border-radius: 10px;
        font-size: 13.5px;
        font-weight: 600;
        font-family: inherit;
        line-height: 1;
        cursor: pointer;
        box-sizing: border-box;
        white-space: nowrap;
        transition: all 0.2s ease;
        margin: 0;
        text-decoration: none;
    }

    .btn-camera-scan-action {
        background-color: #042A70;
        color: #FFFFFF;
        border: 1.5px solid #042A70;
        box-shadow: 0 2px 6px rgba(4, 42, 112, 0.15);
    }

    .btn-camera-scan-action:hover {
        background-color: #071E49;
        border-color: #071E49;
        color: #FFFFFF;
        transform: translateY(-1px);
        box-shadow: 0 4px 10px rgba(7, 30, 73, 0.2);
    }

    .btn-upload-scan-action {
        background-color: #FFFFFF;
        color: #042A70;
        border: 1.5px solid #CBD5E1;
        user-select: none;
    }

    .btn-upload-scan-action:hover {
        background-color: #F8FAFC;
        border-color: #94A3B8;
        color: #071E49;
        transform: translateY(-1px);
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.04);
    }

    .btn-camera-scan-action svg,
    .btn-upload-scan-action svg {
        width: 18px;
        height: 18px;
        flex-shrink: 0;
    }

    /* Input Manual Box yang Rapi & Proporsional */
    .manual-input-box {
        display: flex;
        align-items: center;
        background: #FFFFFF;
        border: 1.5px solid #CBD5E1;
        border-radius: 10px;
        padding: 4px 6px 4px 14px;
        gap: 10px;
        transition: all 0.2s ease;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.03);
    }

    .manual-input-box:focus-within {
        border-color: #042A70;
        box-shadow: 0 0 0 3px rgba(4, 42, 112, 0.12);
    }

    .manual-input-control {
        flex: 1;
        border: none;
        outline: none;
        background: transparent;
        font-size: 13.5px;
        color: #0F172A;
        font-family: inherit;
        padding: 8px 4px;
    }

    .manual-input-control::placeholder {
        color: #94A3B8;
    }

    .btn-manual-search {
        padding: 9px 20px;
        background-color: #042A70;
        color: #FFFFFF;
        border: none;
        border-radius: 7px;
        font-size: 13px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.15s ease;
        white-space: nowrap;
    }

    .btn-manual-search:hover {
        background-color: #071E49;
    }

    /* Card Hasil Inspeksi Data Bahan */
    .inspection-result-card {
        background: #FFFFFF;
        border: 1.5px solid #CBD5E1;
        border-radius: 16px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
        overflow: hidden;
        transition: all 0.3s ease;
    }

    .inspection-result-card.detected {
        border-color: #86EFAC;
        box-shadow: 0 4px 24px rgba(34, 197, 94, 0.12);
        animation: cardFadeIn 0.35s cubic-bezier(0.16, 1, 0.3, 1) forwards;
    }

    @keyframes cardFadeIn {
        from {
            opacity: 0;
            transform: translateY(8px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    /* Card Animasi Loading Saat Scan / Input Manual Berhasil */
    .loading-state-card {
        background: #FFFFFF;
        border: 1.5px solid #CBD5E1;
        border-radius: 16px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
        overflow: hidden;
        animation: cardFadeIn 0.3s ease forwards;
    }

    @keyframes spinLoader {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }

    .inspection-spinner {
        width: 52px;
        height: 52px;
        border: 3.5px solid #E2E8F0;
        border-top-color: #042A70;
        border-right-color: #0284C7;
        border-radius: 50%;
        animation: spinLoader 0.75s cubic-bezier(0.68, -0.55, 0.27, 1.55) infinite;
    }

    .spin-icon {
        animation: spinLoader 0.8s linear infinite;
        display: inline-block;
    }

    @keyframes shimmerAnim {
        0% { background-position: -600px 0; }
        100% { background-position: 600px 0; }
    }

    .skeleton-shimmer {
        background: linear-gradient(90deg, #F1F5F9 0%, #E2E8F0 50%, #F1F5F9 100%);
        background-size: 600px 100%;
        animation: shimmerAnim 1.4s infinite ease-in-out;
        border-radius: 8px;
    }

    .placeholder-state-card {
        background: #FFFFFF;
        border: 2px dashed #CBD5E1;
        border-radius: 16px;
        padding: 50px 24px;
        text-align: center;
        color: #64748B;
    }

    /* ================= TAB 2: TABEL MASTER DATA (100% PERSIS SEPERTI GAMBAR 1 / SPPG MANAGEMENT) ================= */
    .table-card {
        background-color: #FFFFFF;
        border-radius: 16px;
        padding: 26px;
        border: 1px solid #F1F5F9;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.02);
        margin-bottom: 28px;
    }

    .table-filter-bar-form {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 16px;
        margin-bottom: 24px;
        flex-wrap: wrap;
    }

    .table-search-box {
        position: relative;
        width: 380px;
        max-width: 100%;
        flex: 1;
        min-width: 240px;
    }

    .table-search-box .search-icon {
        position: absolute;
        left: 14px;
        top: 50%;
        transform: translateY(-50%);
        color: #94A3B8;
        pointer-events: none;
        width: 18px;
        height: 18px;
    }

    .table-search-box input {
        width: 100%;
        height: 42px;
        box-sizing: border-box;
        padding: 10px 14px 10px 42px;
        border: 1px solid #E2E8F0;
        border-radius: 10px;
        font-size: 13.5px;
        color: #0F172A;
        outline: none;
        background-color: #FFFFFF;
        transition: border-color 0.15s ease;
    }

    .table-search-box input:focus {
        border-color: #3B82F6;
    }

    .table-search-box input::placeholder {
        color: #94A3B8;
    }

    .table-select-box {
        position: relative;
    }

    .table-select-box select {
        appearance: none;
        -webkit-appearance: none;
        height: 42px;
        padding: 10px 38px 10px 16px;
        border: 1px solid #E2E8F0;
        border-radius: 10px;
        font-size: 13px;
        font-weight: 500;
        color: #1E293B;
        background-color: #FFFFFF;
        cursor: pointer;
        outline: none;
        transition: border-color 0.15s ease;
    }

    .table-select-box select:focus {
        border-color: #3B82F6;
    }

    .table-select-box .chevron-icon {
        position: absolute;
        right: 12px;
        top: 50%;
        transform: translateY(-50%);
        color: #64748B;
        pointer-events: none;
    }

    /* Tombol Hapus Filter Persis Gambar 1 */
    .btn-reset-filter {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        height: 42px;
        padding: 0 16px;
        border: 1px solid #071E49 !important;
        border-radius: 10px !important;
        font-size: 13px;
        font-weight: 600;
        color: #FFFFFF !important;
        background-color: #071E49 !important;
        text-decoration: none;
        cursor: pointer;
        box-shadow: 0 2px 6px rgba(7, 30, 73, 0.12) !important;
        transition: all 0.15s ease;
        white-space: nowrap;
        box-sizing: border-box;
    }

    .btn-reset-filter:hover {
        background-color: #042A70 !important;
        border-color: #042A70 !important;
        color: #FFFFFF !important;
        transform: translateY(-1px);
        box-shadow: 0 4px 10px rgba(7, 30, 73, 0.2) !important;
    }

    .btn-reset-filter:active {
        transform: scale(0.98);
    }

    /* Tabel Bersih Berlatar Putih Tanpa Kotak Border Ganda */
    .table-responsive {
        width: 100%;
        overflow-x: auto;
        border: none !important;
        border-radius: 0 !important;
        box-shadow: none !important;
    }

    .staff-table.data-table {
        width: 100%;
        border-collapse: collapse;
        text-align: left;
        background-color: transparent !important;
    }

    .staff-table.data-table thead th {
        background: transparent !important;
        background-color: transparent !important;
        padding: 14px 16px;
        font-size: 13px;
        font-weight: 700;
        color: #071E49;
        border-bottom: 1.5px solid #E2E8F0 !important;
        border-top: none !important;
        border-left: none !important;
        border-right: none !important;
        letter-spacing: -0.01em;
        white-space: nowrap;
        text-align: left;
    }

    .staff-table.data-table tbody td {
        padding: 14px 16px;
        font-size: 13.5px;
        color: #071E49;
        border-bottom: 1px solid #F1F5F9 !important;
        border-top: none !important;
        border-left: none !important;
        border-right: none !important;
        vertical-align: middle;
        background-color: transparent;
    }

    .staff-table.data-table tbody tr:hover td,
    .staff-table.data-table tbody tr:hover {
        background-color: #F8FAFC !important;
    }

    /* Action Buttons Group & Icon Button */
    .action-buttons-group {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
    }

    .btn-action-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 34px;
        height: 34px;
        border-radius: 8px;
        border: 1px solid #E2E8F0;
        background-color: #FFFFFF;
        color: #071E49;
        transition: all 0.15s ease;
        cursor: pointer;
        text-decoration: none;
    }

    .btn-action-icon:hover {
        background-color: #F8FAFC;
        border-color: #CBD5E1;
        color: #042A70;
        transform: translateY(-1px);
    }

    /* Pagination Footer (Sesuai Standar Admin & Kepala SPPG) */
    .table-pagination-footer {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding-top: 18px;
        margin-top: 14px;
        border-top: 1px solid #E2E8F0;
        font-size: 13px;
        color: #64748B;
        flex-wrap: wrap;
        gap: 12px;
    }

    .pagination-info {
        font-size: 13px;
        color: #64748B;
        font-weight: 500;
    }

    .pagination-links nav {
        display: flex;
        align-items: center;
    }

    .pagination-links ul.pagination {
        display: flex;
        list-style: none;
        gap: 4px;
        align-items: center;
        margin: 0;
        padding: 0;
    }

    .pagination-links li.page-item .page-link {
        min-width: 32px;
        height: 32px;
        padding: 0 8px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border: 1px solid transparent;
        border-radius: 6px;
        color: #64748B;
        font-size: 13px;
        font-weight: 500;
        text-decoration: none;
        background-color: #FFFFFF;
        transition: all 0.15s ease;
    }

    .pagination-links li.page-item:first-child .page-link,
    .pagination-links li.page-item:last-child .page-link {
        border: 1px solid #E2E8F0;
        border-radius: 6px;
    }

    .pagination-links li.page-item.active .page-link {
        background-color: #071E49 !important;
        color: #FFFFFF !important;
        font-weight: 700;
        border-color: #071E49 !important;
    }

    .pagination-links li.page-item:not(.active):not(.disabled) .page-link:hover {
        background-color: #F1F5F9;
        color: #071E49;
    }

    .pagination-links li.page-item.disabled .page-link {
        color: #CBD5E1;
        cursor: not-allowed;
        background-color: #FFFFFF;
        border-color: #F1F5F9;
    }

    /* Status Badges & Dots Persis Gambar 1 */
    .badge-status {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
        white-space: nowrap;
    }

    .status-dot {
        width: 6px;
        height: 6px;
        border-radius: 50%;
        background-color: currentColor;
    }

    .badge-status.badge-received,
    .badge-status.badge-safe {
        background-color: #DCFCE7 !important;
        color: #16A34A !important;
    }

    .badge-status.badge-warning,
    .badge-status.badge-near_expired {
        background-color: #FEF3C7 !important;
        color: #D97706 !important;
    }

    .badge-status.badge-cancelled,
    .badge-status.badge-expired {
        background-color: #FEE2E2 !important;
        color: #DC2626 !important;
    }
</style>
@endpush

@section('content')
<div class="space-y-6 pb-8">
    {{-- Page Title Section --}}
    <div class="page-title-section">
        <h1 class="page-title">Lot Bahan Baku & Stok</h1>
        <p class="page-subtitle">Pusat pemeriksaan fisik bahan pangan via pemindai QR code / barcode, masa kedaluwarsa (FEFO), dan kartu stok mutasi.</p>
    </div>

    {{-- 4 Stat Cards Grid (Tetap Dipertahankan) --}}
    <div class="stat-cards-grid">
        {{-- Card 1: Total Lot --}}
        <div class="stat-card">
            <div class="stat-icon-wrapper icon-blue">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="3" y="3" width="7" height="7"></rect>
                    <rect x="14" y="3" width="7" height="7"></rect>
                    <rect x="14" y="14" width="7" height="7"></rect>
                    <rect x="3" y="14" width="7" height="7"></rect>
                </svg>
            </div>
            <div class="stat-content">
                <span class="stat-label">Total Lot Terbit</span>
                <span class="stat-value">{{ number_format($stats['total_lots'], 0, ',', '.') }}</span>
                <span class="stat-subtext">Batch Bahan Masuk</span>
            </div>
        </div>

        {{-- Card 2: Stok Tersedia --}}
        <div class="stat-card">
            <div class="stat-icon-wrapper icon-green">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                    <polyline points="22 4 12 14.01 9 11.01"></polyline>
                </svg>
            </div>
            <div class="stat-content">
                <span class="stat-label">Stok Tersedia</span>
                <span class="stat-value">{{ number_format($stats['stok_tersedia'], 0, ',', '.') }}</span>
                <span class="stat-subtext">Lot Siap Olah (FEFO)</span>
            </div>
        </div>

        {{-- Card 3: Mendekati Kedaluwarsa --}}
        <div class="stat-card">
            <div class="stat-icon-wrapper icon-yellow">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10"></circle>
                    <polyline points="12 6 12 12 14 14"></polyline>
                </svg>
            </div>
            <div class="stat-content">
                <span class="stat-label">Mendekati Kedaluwarsa</span>
                <span class="stat-value">{{ number_format($stats['mendekati_kedaluwarsa'], 0, ',', '.') }}</span>
                <span class="stat-subtext">≤ 7 Hari Mendatang</span>
            </div>
        </div>

        {{-- Card 4: Sudah Kedaluwarsa --}}
        <div class="stat-card">
            <div class="stat-icon-wrapper" style="background-color: #FEE2E2; color: #DC2626;">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path>
                    <line x1="12" y1="9" x2="12" y2="13"></line>
                    <line x1="12" y1="17" x2="12.01" y2="17"></line>
                </svg>
            </div>
            <div class="stat-content">
                <span class="stat-label">Kedaluwarsa Berisi</span>
                <span class="stat-value">{{ number_format($stats['kedaluwarsa'], 0, ',', '.') }}</span>
                <span class="stat-subtext">Perlu Dicatat Limbah</span>
            </div>
        </div>
    </div>

    {{-- NAVIGASI SISTEM TAB UTAMA --}}
    <div class="lot-main-tabs-nav">
        <button type="button" id="tab-btn-scan" class="lot-nav-tab {{ $activeTab !== 'table' ? 'active' : '' }}" onclick="switchMainTab('scan')">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <rect x="3" y="3" width="7" height="7"></rect>
                <rect x="14" y="3" width="7" height="7"></rect>
                <rect x="14" y="14" width="7" height="7"></rect>
                <rect x="3" y="14" width="7" height="7"></rect>
            </svg>
            <span>Pindai & Periksa Lot</span>
        </button>

        <button type="button" id="tab-btn-table" class="lot-nav-tab {{ $activeTab === 'table' ? 'active' : '' }}" onclick="switchMainTab('table')">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <line x1="8" y1="6" x2="21" y2="6"></line>
                <line x1="8" y1="12" x2="21" y2="12"></line>
                <line x1="8" y1="18" x2="21" y2="18"></line>
                <line x1="3" y1="6" x2="3.01" y2="6"></line>
                <line x1="3" y1="12" x2="3.01" y2="12"></line>
                <line x1="3" y1="18" x2="3.01" y2="18"></line>
            </svg>
            <span>Daftar Semua Lot (Master Data)</span>
        </button>
    </div>

    {{-- ======================================================== --}}
    {{-- TAB 1: PINDAI & PERIKSA LOT (FOKUS SCANNER & HASIL DATA) --}}
    {{-- ======================================================== --}}
    <div id="main-tab-content-scan" style="display: {{ $activeTab !== 'table' ? 'block' : 'none' }};">
        
        {{-- KOTAK PEMINDAI UTAMA ("SCAN BARANG YANG INGIN DIPERIKSA") --}}
        <div class="scan-station-card">
            <div class="scan-station-grid">
                {{-- Kolom Kiri: Kamera Live Viewport --}}
                <div>
                    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 10px;">
                        <div style="display: flex; align-items: center; gap: 8px; font-size: 13.5px; font-weight: 700; color: #071E49;">
                            <span id="live-indicator-dot" class="live-pulse-dot inactive"></span>
                            <span>Kamera Pemindai Live</span>
                        </div>
                        <span id="camera-live-badge" class="camera-status-pill standby">Kamera Siaga</span>
                    </div>

                    <div class="live-camera-box">
                        <div id="embedded-camera-feed"></div>

                        {{-- Placeholder saat kamera belum menyala --}}
                        <div id="camera-placeholder-msg">
                            <svg width="44" height="44" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" style="margin: 0 auto 10px; display: block; color: #64748B;">
                                <path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"></path>
                                <circle cx="12" cy="13" r="4"></circle>
                            </svg>
                            <p style="font-size: 14px; font-weight: 600; color: #CBD5E1; margin-bottom: 4px;">Kamera Siaga (Area Penuh)</p>
                            <p style="font-size: 12px; color: #94A3B8; max-width: 290px; margin: 0 auto; line-height: 1.4;">Klik tombol "Nyalakan Kamera" untuk memindai QR code stiker bahan.</p>
                        </div>
                    </div>

                    {{-- Dropdown Pilih Perangkat Kamera dengan Garis Tepi & Ikon Rapi --}}
                    <div class="camera-device-selector-wrapper">
                        <svg class="camera-device-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"></path>
                            <circle cx="12" cy="13" r="4"></circle>
                        </svg>
                        <select id="camera-select-dropdown" class="camera-device-select" onchange="changeCameraDevice(this.value)" title="Pilih perangkat kamera pemindai">
                            <option value="">Deteksi Perangkat Kamera...</option>
                        </select>
                        <svg class="camera-device-chevron" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="6 9 12 15 18 9"></polyline>
                        </svg>
                    </div>
                </div>

                {{-- Kolom Kanan: Instruksi, Input Manual, & Kontrol --}}
                <div style="display: flex; flex-direction: column; gap: 16px;">
                    <div>
                        <div style="display: inline-flex; align-items: center; gap: 6px; background: #E0F2FE; color: #0369A1; font-size: 11.5px; font-weight: 700; padding: 3px 10px; border-radius: 6px; text-transform: uppercase; margin-bottom: 8px;">
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                <circle cx="12" cy="12" r="10"></circle>
                                <line x1="12" y1="16" x2="12" y2="12"></line>
                                <line x1="12" y1="8" x2="12.01" y2="8"></line>
                            </svg>
                            <span>Stasiun Pemeriksaan Lot</span>
                        </div>
                        <h2 style="font-size: 20px; font-weight: 800; color: #071E49; margin: 0 0 6px 0;">
                            SCAN Barang yang Ingin Diperiksa
                        </h2>
                        <p style="font-size: 13.5px; color: #64748B; margin: 0; line-height: 1.5;">
                            Arahkan stiker <strong>QR Code</strong> pada kemasan bahan ke kamera, tembak menggunakan scanner USB, atau masukkan kode lot secara manual.
                        </p>
                    </div>

                    {{-- Tombol Kontrol Kamera & Upload Foto (Ukuran Presisi Kembar Identik) --}}
                    <div style="display: flex; flex-wrap: wrap; gap: 10px; align-items: center;">
                        <button type="button" id="btn-toggle-camera" onclick="toggleLiveCamera()" class="btn-camera-scan-action">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"></path>
                                <circle cx="12" cy="13" r="4"></circle>
                            </svg>
                            <span id="btn-toggle-camera-text">Nyalakan Kamera</span>
                        </button>

                        <label class="btn-upload-scan-action" title="Unggah gambar QR code dari galeri/komputer">
                            <input type="file" id="file-barcode-input" accept="image/*" style="display: none;" onchange="handleFileScan(this)">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <rect x="3" y="3" width="18" height="18" rx="2"></rect>
                                <circle cx="8.5" cy="8.5" r="1.5"></circle>
                                <polyline points="21 15 16 10 5 21"></polyline>
                            </svg>
                            <span>Upload Foto QR</span>
                        </label>
                    </div>

                    {{-- Input Manual (Sesuai Permintaan User) --}}
                    <div style="margin-top: 4px;">
                        <label for="barcode-search-input" style="display: block; font-size: 13px; font-weight: 700; color: #071E49; margin-bottom: 6px;">
                            Input Manual
                        </label>
                        <div class="manual-input-box">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#64748B" stroke-width="2" style="flex-shrink: 0;">
                                <path d="M4 7V4h16v3M4 17v3h16v-3M9 12h6"></path>
                            </svg>
                            <input type="text" 
                                   id="barcode-search-input" 
                                   class="manual-input-control" 
                                   placeholder="Ketik atau tempel kode lot (contoh: LOT-SPPG01-20261102-005)..." 
                                   autocomplete="off">
                            <button type="button" id="btn-manual-search" onclick="performManualLookup()" class="btn-manual-search">
                                <span>Periksa</span>
                            </button>
                        </div>
                    </div>

                    <div style="font-size: 12px; color: #64748B; background: #F8FAFC; border: 1px solid #E2E8F0; padding: 10px 14px; border-radius: 8px;">
                        💡 <strong>Sistem Otomatis:</strong> Saat kode lot terdeteksi, sistem langsung menampilkan status kelayakan bahan (FEFO), sisa stok gudang, dan buku mutasi kartu stok.
                    </div>
                </div>
            </div>
        </div>

        {{-- AREA HASIL PEMERIKSAAN BAHAN --}}
        
        {{-- State 1: Belum Ada Bahan yang Dipindai (Placeholder Elegan) --}}
        <div id="empty-inspection-state" class="placeholder-state-card">
            <div style="width: 68px; height: 68px; border-radius: 50%; background: #F1F5F9; color: #042A70; display: flex; align-items: center; justify-content: center; margin: 0 auto 16px;">
                <svg width="34" height="34" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                    <rect x="3" y="3" width="7" height="7"></rect>
                    <rect x="14" y="3" width="7" height="7"></rect>
                    <rect x="14" y="14" width="7" height="7"></rect>
                    <rect x="3" y="14" width="7" height="7"></rect>
                </svg>
            </div>
            <h3 style="font-size: 17px; font-weight: 700; color: #071E49; margin-bottom: 6px;">
                Siap Memeriksa Bahan Pangan
            </h3>
            <p style="font-size: 13.5px; color: #64748B; max-width: 520px; margin: 0 auto 14px; line-height: 1.5;">
                Silakan nyalakan kamera pemindai di atas atau masukkan kode lot pada kotak Input Manual. Data lengkap barang akan langsung muncul di sini.
            </p>
            <button type="button" onclick="switchMainTab('table')" style="background: none; border: none; font-size: 13px; font-weight: 600; color: #042A70; cursor: pointer; text-decoration: underline;">
                Atau lihat semua daftar stok di Tab Rekap &rarr;
            </button>
        </div>

        {{-- State 2: Sedang Memproses / Mengambil Data Lot (Animasi Loading Tematik) --}}
        <div id="loading-inspection-state" class="loading-state-card" style="display: none;">
            <div style="padding: 36px 24px 28px; text-align: center; border-bottom: 1.5px solid #F1F5F9;">
                <div style="position: relative; width: 62px; height: 62px; margin: 0 auto 16px; display: flex; align-items: center; justify-content: center;">
                    <div class="inspection-spinner"></div>
                    <div style="position: absolute; color: #042A70;">
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                            <rect x="3" y="3" width="7" height="7"></rect>
                            <rect x="14" y="3" width="7" height="7"></rect>
                            <rect x="14" y="14" width="7" height="7"></rect>
                            <rect x="3" y="14" width="7" height="7"></rect>
                        </svg>
                    </div>
                </div>

                <div style="display: inline-flex; align-items: center; gap: 8px; background: #EFF6FF; border: 1px solid #BFDBFE; color: #1D4ED8; font-size: 11.5px; font-weight: 700; padding: 4px 14px; border-radius: 9999px; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 10px;">
                    <span class="live-pulse-dot" style="width: 8px; height: 8px; background: #2563EB;"></span>
                    <span>Memproses Kode Lot</span>
                </div>

                <h3 style="font-size: 18px; font-weight: 800; color: #071E49; margin: 0 0 8px 0;">
                    Mengambil Data & Kartu Stok Bahan...
                </h3>
                <p style="font-size: 13.5px; color: #64748B; margin: 0; display: flex; align-items: center; justify-content: center; gap: 8px; flex-wrap: wrap;">
                    <span>Sedang memuat data untuk kode:</span>
                    <span id="loading-lot-code-preview" style="font-family: monospace; font-size: 13px; font-weight: 700; color: #042A70; background: #F1F5F9; padding: 3px 10px; border-radius: 6px; border: 1px solid #CBD5E1;">
                        ...
                    </span>
                </p>
            </div>

            {{-- Skeleton Shimmer Preview Placeholder --}}
            <div style="padding: 24px;">
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 16px; background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 12px; padding: 18px; margin-bottom: 22px;">
                    <div style="display: flex; flex-direction: column; gap: 8px;">
                        <div class="skeleton-shimmer" style="height: 12px; width: 55%;"></div>
                        <div class="skeleton-shimmer" style="height: 20px; width: 85%;"></div>
                    </div>
                    <div style="display: flex; flex-direction: column; gap: 8px;">
                        <div class="skeleton-shimmer" style="height: 12px; width: 50%;"></div>
                        <div class="skeleton-shimmer" style="height: 20px; width: 90%;"></div>
                    </div>
                    <div style="display: flex; flex-direction: column; gap: 8px;">
                        <div class="skeleton-shimmer" style="height: 12px; width: 60%;"></div>
                        <div class="skeleton-shimmer" style="height: 20px; width: 75%;"></div>
                    </div>
                    <div style="display: flex; flex-direction: column; gap: 8px;">
                        <div class="skeleton-shimmer" style="height: 12px; width: 65%;"></div>
                        <div class="skeleton-shimmer" style="height: 20px; width: 70%;"></div>
                    </div>
                </div>

                <div style="display: flex; flex-direction: column; gap: 10px;">
                    <div class="skeleton-shimmer" style="height: 16px; width: 260px;"></div>
                    <div class="skeleton-shimmer" style="height: 70px; width: 100%;"></div>
                </div>
            </div>
        </div>

        {{-- State 3: Bahan Berhasil Dipindai (Tampilan Data Lengkap Lapang) --}}
        <div id="detected-inspection-state" class="inspection-result-card detected" style="display: none;">
            {{-- Header Hasil Pemeriksaan --}}
            <div style="padding: 20px 24px; border-bottom: 1.5px solid #E2E8F0; background: #FFFFFF; display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 14px;">
                <div>
                    <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 4px;">
                        <span id="insp-lot-barcode" style="font-family: monospace; font-size: 20px; font-weight: 800; color: #042A70;">LOT-...</span>
                        <span id="insp-status-badge" class="badge-status badge-safe" style="font-size: 12px; padding: 2px 10px;">Tersedia</span>
                        <span id="insp-expiry-badge" class="badge-status badge-received" style="font-size: 12px; padding: 2px 10px;">Aman</span>
                    </div>
                    <div style="font-size: 16px; font-weight: 700; color: #0F172A;" id="insp-material-title">Bahan Pangan</div>
                </div>

                <div>
                    <button type="button" onclick="resetInspectionState()" class="btn-action-icon" style="width: auto; padding: 9px 18px; font-size: 13px; font-weight: 600; display: inline-flex; align-items: center; gap: 7px; background: #F8FAFC; border: 1.5px solid #CBD5E1; border-radius: 9px; color: #071E49; cursor: pointer; transition: all 0.15s ease;" title="Pindai lot bahan lainnya">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"></path>
                            <polyline points="3 3 3 8 8 8"></polyline>
                        </svg>
                        <span>Pindai Bahan Lain</span>
                    </button>
                </div>
            </div>

            {{-- Body Hasil Pemeriksaan: Ringkasan Informasi Bahan & Kelayakan FEFO --}}
            <div style="padding: 24px;">

                {{-- Grid Ringkasan Asal Bahan & Validasi FEFO --}}
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 16px; background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 12px; padding: 18px; margin-bottom: 24px;">
                    <div>
                        <span style="font-size: 11.5px; color: #64748B; display: block;">No. Lot Kemasan Pabrik</span>
                        <span id="insp-lot-number" style="font-size: 14.5px; font-weight: 700; color: #0F172A;">-</span>
                    </div>
                    <div>
                        <span style="font-size: 11.5px; color: #64748B; display: block;">Pemasok</span>
                        <span id="insp-supplier" style="font-size: 14px; font-weight: 600; color: #0F172A;">-</span>
                    </div>
                    <div>
                        <span style="font-size: 11.5px; color: #64748B; display: block;">Waktu Terima Fisik</span>
                        <span id="insp-received-date" style="font-size: 14px; font-weight: 600; color: #0F172A;">-</span>
                    </div>
                    <div>
                        <span style="font-size: 11.5px; color: #64748B; display: block;">Tanggal Kedaluwarsa</span>
                        <span id="insp-expiry-date" style="font-size: 15px; font-weight: 800; color: #DC2626;">-</span>
                    </div>
                    <div>
                        <span style="font-size: 11.5px; color: #64748B; display: block;">Sisa Stok Saat Ini</span>
                        <span id="insp-qty-info" style="font-size: 16px; font-weight: 800; color: #071E49;">0 / 0 kg</span>
                    </div>
                    <div>
                        <span style="font-size: 11.5px; color: #64748B; display: block;">Harga Satuan PO</span>
                        <span id="insp-unit-cost" style="font-size: 14.5px; font-weight: 700; color: #071E49;">Rp 0</span>
                    </div>
                    <div>
                        <span style="font-size: 11.5px; color: #64748B; display: block;">Dokumen Penerimaan</span>
                        <span id="insp-receiving-no" style="font-size: 13.5px; font-weight: 600; color: #042A70;">-</span>
                    </div>
                    <div>
                        <span style="font-size: 11.5px; color: #64748B; display: block;">Dokumen Pembelian (PO)</span>
                        <span id="insp-purchase-no" style="font-size: 13.5px; font-weight: 600; color: #042A70;">-</span>
                    </div>
                </div>

                {{-- Riwayat Mutasi Kartu Stok --}}
                <div style="margin-top: 10px;">
                    <h4 style="font-size: 15px; font-weight: 700; color: #071E49; margin-bottom: 12px; display: flex; align-items: center; gap: 8px;">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <line x1="12" y1="20" x2="12" y2="10"></line>
                            <line x1="18" y1="20" x2="18" y2="4"></line>
                            <line x1="6" y1="20" x2="6" y2="16"></line>
                        </svg>
                        <span>Buku Mutasi Masuk & Keluar (Kartu Stok)</span>
                    </h4>

                    <div class="table-responsive" style="max-height: 280px; overflow-y: auto;">
                        <table class="staff-table data-table" style="font-size: 13px;">
                            <thead>
                                <tr>
                                    <th style="padding: 10px 14px;">Waktu Mutasi</th>
                                    <th style="padding: 10px 14px;">Tipe Transaksi</th>
                                    <th style="padding: 10px 14px; text-align: right;">Masuk / Keluar</th>
                                    <th style="padding: 10px 14px; text-align: right;">Saldo Berjalan</th>
                                    <th style="padding: 10px 14px;">Pencatat</th>
                                </tr>
                            </thead>
                            <tbody id="insp-movements-tbody">
                                {{-- Diisi via JavaScript saat scan/lookup --}}
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        {{-- Hidden sandbox div untuk decoding file --}}
        <div id="file-decode-sandbox" style="display: none;"></div>
    </div>

    {{-- ======================================================== --}}
    {{-- TAB 2: REKAP & DAFTAR SEMUA LOT (WHITE CARD SEPERTI LAIN) --}}
    {{-- ======================================================== --}}
    <div id="main-tab-content-table" style="display: {{ $activeTab === 'table' ? 'block' : 'none' }};">
        <div class="table-card">
            {{-- Filter Bar Form --}}
            <form method="GET" action="{{ route('staff.lots.index') }}" id="filter-lots-form" class="table-filter-bar-form">
                <input type="hidden" name="tab" value="table">
                
                {{-- Input Pencarian --}}
                <div class="table-search-box">
                    <svg class="search-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="11" cy="11" r="8"></circle>
                        <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                    </svg>
                    <input type="text" 
                           name="q" 
                           value="{{ $q }}" 
                           placeholder="Cari barcode lot, no. kemasan, bahan, pemasok..." 
                           autocomplete="off">
                </div>

                {{-- Filter Dropdowns & Reset --}}
                <div style="display: flex; flex-wrap: wrap; gap: 10px; align-items: center;">
                    <div class="table-select-box">
                        <select name="material_id" onchange="document.getElementById('filter-lots-form').submit()">
                            <option value="">Semua Bahan</option>
                            @foreach ($materials as $m)
                                <option value="{{ $m->id }}" {{ (string) $materialId === (string) $m->id ? 'selected' : '' }}>
                                    {{ $m->nama }}
                                </option>
                            @endforeach
                        </select>
                        <svg class="chevron-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polyline points="6 9 12 15 18 9"></polyline>
                        </svg>
                    </div>

                    <div class="table-select-box">
                        <select name="status" onchange="document.getElementById('filter-lots-form').submit()">
                            <option value="">Semua Status</option>
                            <option value="available" {{ $status === 'available' ? 'selected' : '' }}>Tersedia</option>
                            <option value="depleted" {{ $status === 'depleted' ? 'selected' : '' }}>Habis</option>
                            <option value="voided" {{ $status === 'voided' ? 'selected' : '' }}>Dibatalkan</option>
                        </select>
                        <svg class="chevron-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polyline points="6 9 12 15 18 9"></polyline>
                        </svg>
                    </div>

                    <div class="table-select-box">
                        <select name="expiry" onchange="document.getElementById('filter-lots-form').submit()">
                            <option value="">Semua Kondisi</option>
                            <option value="safe" {{ $expiryFilter === 'safe' ? 'selected' : '' }}>Aman</option>
                            <option value="near_expired" {{ $expiryFilter === 'near_expired' ? 'selected' : '' }}>Mendekati (≤7 hr)</option>
                            <option value="expired" {{ $expiryFilter === 'expired' ? 'selected' : '' }}>Kedaluwarsa</option>
                        </select>
                        <svg class="chevron-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polyline points="6 9 12 15 18 9"></polyline>
                        </svg>
                    </div>

                    <a href="{{ route('staff.lots.index', ['tab' => 'table']) }}" 
                       class="btn-reset-filter" 
                       title="Hapus semua filter pencarian">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"></path>
                            <polyline points="3 3 3 8 8 8"></polyline>
                        </svg>
                        <span>Hapus Filter</span>
                    </a>
                </div>
            </form>

            {{-- Tabel Data Lot Bersih Berlatar Putih --}}
            <div class="table-responsive">
                <table class="staff-table data-table">
                    <thead>
                        <tr>
                            <th style="min-width: 150px;">Barcode / QR</th>
                            <th style="min-width: 180px;">Bahan & Lot</th>
                            <th style="min-width: 140px;">Pemasok</th>
                            <th style="min-width: 140px;">Kedaluwarsa</th>
                            <th style="min-width: 130px;">Sisa / Awal</th>
                            <th style="min-width: 110px;">Status</th>
                            <th style="width: 100px; text-align: center;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($lots as $item)
                            <tr id="lot-row-{{ $item->id }}">
                                {{-- Barcode / QR --}}
                                <td class="font-medium text-navy">
                                    <a href="javascript:void(0)" onclick="inspectLotFromTable({{ $item->id }})" style="text-decoration: none; color: #042A70; font-weight: 700; display: inline-flex; align-items: center; gap: 6px;" title="Klik untuk memeriksa di pemindai">
                                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="color: #64748B;">
                                            <rect x="3" y="3" width="7" height="7"></rect>
                                            <rect x="14" y="3" width="7" height="7"></rect>
                                            <rect x="14" y="14" width="7" height="7"></rect>
                                            <circle cx="17.5" cy="17.5" r="2.5"></circle>
                                        </svg>
                                        <span>{{ $item->barcode }}</span>
                                    </a>
                                </td>

                                {{-- Bahan & Nomor Lot Kemasan --}}
                                <td>
                                    <div style="font-weight: 600; color: #0F172A;">{{ $item->material?->nama ?? '-' }}</div>
                                    <div style="font-size: 11.5px; color: #64748B;">No: {{ $item->lot_number }}</div>
                                </td>

                                {{-- Pemasok --}}
                                <td style="color: #334155; font-size: 12.5px;">{{ $item->supplier?->nama ?? '-' }}</td>

                                {{-- Tanggal Kedaluwarsa --}}
                                <td>
                                    <div style="display: inline-flex; flex-direction: column; gap: 2px;">
                                        <span style="font-weight: 600; font-size: 12.5px; color: {{ $item->isExpired() ? '#DC2626' : '#0F172A' }};">
                                            {{ $item->expiry_date ? $item->expiry_date->format('d-m-Y') : '-' }}
                                        </span>
                                        <span class="badge-status {{ $item->expiry_badge_class }}" style="padding: 2px 7px; font-size: 10.5px;">
                                            <span class="status-dot"></span>
                                            {{ $item->expiry_status_label }}
                                        </span>
                                    </div>
                                </td>

                                {{-- Sisa / Stok Awal --}}
                                <td>
                                    <div style="font-weight: 700; color: #071E49;">
                                        {{ number_format($item->current_qty, 2, ',', '.') }} {{ $item->material?->satuan ?? 'kg' }}
                                    </div>
                                    <div style="font-size: 11px; color: #94A3B8;">
                                        dari {{ number_format($item->initial_qty, 2, ',', '.') }}
                                    </div>
                                </td>

                                {{-- Status --}}
                                <td>
                                    <span class="badge-status {{ $item->status_badge_class }}">
                                        <span class="status-dot"></span>
                                        {{ $item->status_label }}
                                    </span>
                                </td>

                                {{-- Aksi --}}
                                <td style="text-align: center;">
                                    <div class="action-buttons-group">
                                        <button type="button" class="btn-action-icon" onclick="inspectLotFromTable({{ $item->id }})" title="Periksa di Stasiun Pindai">
                                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <circle cx="11" cy="11" r="8"></circle>
                                                <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                                            </svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7">
                                    <div style="padding: 44px 20px; text-align: center; color: #64748B;">
                                        <p style="font-size: 15px; font-weight: 600; color: #071E49; margin-bottom: 4px;">Belum ada lot bahan baku</p>
                                        <p style="font-size: 13px; color: #64748B;">Lot diterbitkan otomatis saat penerimaan fisik dikonfirmasi.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            @if ($lots->hasPages() || $lots->total() > 0)
                <div class="table-pagination-footer">
                    <div class="pagination-info">
                        Menampilkan {{ $lots->firstItem() ?? 0 }}-{{ $lots->lastItem() ?? 0 }} dari {{ $lots->total() }} data
                    </div>
                    <div class="pagination-links">
                        {{ $lots->appends(array_merge(request()->query(), ['tab' => 'table']))->fragment('main-tab-content-table')->onEachSide(1)->links('pagination::bootstrap-4') }}
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

@section('modals')
{{-- Modal Alert Data Lot Tidak Ditemukan Sesuai Tema Lensa MBG --}}
<div class="modal-backdrop" id="lotNotFoundModal" role="dialog" aria-modal="true" aria-labelledby="lotNotFoundTitle">
    <div class="modal-dialog" style="max-width: 440px; text-align: center; position: relative; padding: 32px 28px 26px; border-radius: 18px; box-shadow: 0 20px 45px rgba(7, 30, 73, 0.22);">
        <button type="button" class="modal-close-corner" onclick="closeLotNotFoundModal()" title="Tutup" aria-label="Tutup">
            &times;
        </button>

        {{-- Icon Peringatan Tematik --}}
        <div style="width: 62px; height: 62px; border-radius: 50%; background-color: #FEF2F2; color: #DC2626; display: flex; align-items: center; justify-content: center; margin: 0 auto 16px; border: 4px solid #FEE2E2;">
            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="11" cy="11" r="8"></circle>
                <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                <line x1="8" y1="8" x2="14" y2="14"></line>
                <line x1="14" y1="8" x2="8" y2="14"></line>
            </svg>
        </div>

        {{-- Judul Modal --}}
        <h3 id="lotNotFoundTitle" style="font-size: 18px; font-weight: 700; color: #071E49; margin: 0 0 6px 0;">
            Data Lot Tidak Ditemukan
        </h3>

        {{-- Pesan Keterangan --}}
        <p id="lotNotFoundMessage" style="font-size: 13.5px; color: #64748B; margin: 0 0 14px 0; line-height: 1.5;">
            Barcode atau QR Code yang Anda pindai tidak terdaftar pada unit SPPG ini.
        </p>

        {{-- Preview Kode yang Dipindai --}}
        <div style="background: #F8FAFC; border: 1.5px dashed #CBD5E1; border-radius: 10px; padding: 10px 14px; margin-bottom: 22px; text-align: left;">
            <span style="display: block; font-size: 11px; font-weight: 700; color: #64748B; text-transform: uppercase; letter-spacing: 0.03em; margin-bottom: 3px;">
                Kode yang Dipindai:
            </span>
            <div id="lotNotFoundScannedCode" style="font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace; font-size: 12.5px; color: #0F172A; font-weight: 600; word-break: break-all; max-height: 70px; overflow-y: auto;">
                -
            </div>
        </div>

        {{-- Tombol Aksi --}}
        <div style="display: flex; gap: 10px; justify-content: center;">
            <button type="button" onclick="closeLotNotFoundModal()" class="btn-manual-search" style="padding: 10px 26px; font-size: 13.5px; font-weight: 600; border-radius: 9px; cursor: pointer;">
                Coba Pindai Ulang
            </button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
{{-- Library QR Code Generator (qrcode.js) & HTML5-QRCode Scanner --}}
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
<script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>

<script>
    let liveScannerInstance = null;
    let isCameraRunning = false;
    let cameraDevices = [];
    let selectedCameraId = null;

    // State Inspeksi
    let currentInspectedLotId = null;

    // Helper update status pill badge di header kamera
    function setCameraBadge(type, text) {
        const badge = document.getElementById('camera-live-badge');
        if (!badge) return;
        badge.className = `camera-status-pill ${type}`;
        badge.innerText = text;
    }

    // 1. NAVIGASI SISTEM TAB UTAMA
    function switchMainTab(tab) {
        const btnScan = document.getElementById('tab-btn-scan');
        const btnTable = document.getElementById('tab-btn-table');
        const contentScan = document.getElementById('main-tab-content-scan');
        const contentTable = document.getElementById('main-tab-content-table');

        if (tab === 'scan') {
            btnScan.classList.add('active');
            btnTable.classList.remove('active');
            contentScan.style.display = 'block';
            contentTable.style.display = 'none';
        } else {
            btnTable.classList.add('active');
            btnScan.classList.remove('active');
            contentTable.style.display = 'block';
            contentScan.style.display = 'none';
        }

        // Sinkronkan preferensi tab ke URL browser tanpa reload halaman
        try {
            const url = new URL(window.location.href);
            url.searchParams.set('tab', tab);
            window.history.replaceState({}, '', url.toString());
        } catch(e) {}
    }

    // 2. DUKUNGAN SCANNER USB & PENCARIAN MANUAL
    const searchInput = document.getElementById('barcode-search-input');
    if (searchInput) {
        searchInput.addEventListener('keydown', async function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                await performManualLookup();
            }
        });
    }

    async function performManualLookup() {
        const val = searchInput ? searchInput.value.trim() : '';
        if (!val) {
            showLotNotFoundModal('-', 'Silakan masukkan barcode atau nomor lot pada kotak Input Manual terlebih dahulu.');
            if (searchInput) searchInput.focus();
            return;
        }
        await lookupAndShowLot(val);
    }

    // 3. DETEKSI KAMERA & INISIALISASI
    document.addEventListener('DOMContentLoaded', async function() {
        if (typeof Html5Qrcode !== 'undefined') {
            try {
                cameraDevices = await Html5Qrcode.getCameras();
                const dropdown = document.getElementById('camera-select-dropdown');
                dropdown.innerHTML = '';

                if (cameraDevices && cameraDevices.length > 0) {
                    cameraDevices.forEach((cam, idx) => {
                        const opt = document.createElement('option');
                        opt.value = cam.id;
                        opt.text = cam.label || `Kamera ${idx + 1}`;
                        dropdown.appendChild(opt);
                    });

                    // Prioritaskan kamera belakang jika ada, atau kamera pertama (webcam)
                    const back = cameraDevices.find(c => /back|rear|environment|belakang/i.test(c.label));
                    selectedCameraId = back ? back.id : cameraDevices[0].id;
                    dropdown.value = selectedCameraId;
                    setCameraBadge('standby', 'Kamera Siaga');
                } else {
                    dropdown.innerHTML = '<option value="">Tidak ada kamera terdeteksi</option>';
                    setCameraBadge('standby', 'Tidak Ada Kamera');
                }
            } catch(e) {
                console.warn("Gagal deteksi kamera awal:", e);
                setCameraBadge('standby', 'Izin Belum Aktif');
            }
        }
    });

    // Toggle Nyalakan / Matikan Kamera Live
    async function toggleLiveCamera() {
        if (isCameraRunning) {
            await stopLiveCamera();
        } else {
            await startLiveCamera();
        }
    }

    async function startLiveCamera() {
        const btnToggle = document.getElementById('btn-toggle-camera-text');
        const dot = document.getElementById('live-indicator-dot');
        const placeholder = document.getElementById('camera-placeholder-msg');

        setCameraBadge('searching', 'Menghubungkan...');

        try {
            if (typeof Html5Qrcode === 'undefined') {
                setCameraBadge('standby', 'Library Error');
                return;
            }

            if (!selectedCameraId && cameraDevices.length > 0) {
                selectedCameraId = cameraDevices[0].id;
            }

            if (!liveScannerInstance) {
                liveScannerInstance = new Html5Qrcode("embedded-camera-feed");
            }

            // Area penuh tanpa batasan kotakan!
            const config = {
                fps: 20,
                aspectRatio: 1.333
            };

            await liveScannerInstance.start(
                selectedCameraId || { facingMode: "user" },
                config,
                (decodedText, decodedResult) => {
                    handleSuccessfulScan(decodedText);
                },
                (errorMessage) => {
                    // Frame scan aktif...
                }
            );

            isCameraRunning = true;
            btnToggle.innerText = 'Matikan Kamera';
            dot.className = 'live-pulse-dot';
            placeholder.style.display = 'none';
            setCameraBadge('active', 'Kamera Aktif');
        } catch (err) {
            console.error("Gagal menyalakan kamera live:", err);
            isCameraRunning = false;
            btnToggle.innerText = 'Nyalakan Kamera';
            dot.className = 'live-pulse-dot inactive';
            placeholder.style.display = 'flex';
            setCameraBadge('standby', 'Gagal Buka Kamera');
            alert('Tidak dapat mengakses kamera: ' + (err.message || err));
        }
    }

    async function stopLiveCamera() {
        if (liveScannerInstance && isCameraRunning) {
            try {
                await liveScannerInstance.stop();
            } catch(e) {}
        }
        isCameraRunning = false;
        document.getElementById('btn-toggle-camera-text').innerText = 'Nyalakan Kamera';
        document.getElementById('live-indicator-dot').className = 'live-pulse-dot inactive';
        document.getElementById('camera-placeholder-msg').style.display = 'flex';
        setCameraBadge('standby', 'Kamera Siaga');
    }

    async function changeCameraDevice(newCameraId) {
        if (!newCameraId) return;
        selectedCameraId = newCameraId;
        if (isCameraRunning) {
            await stopLiveCamera();
            await startLiveCamera();
        }
    }

    // 4. DETEKSI HASIL SCAN BERHASIL
    let isProcessingScan = false;
    async function handleSuccessfulScan(codeText) {
        if (isProcessingScan) return;
        isProcessingScan = true;

        // Beep audio feedback
        try {
            const ctx = new (window.AudioContext || window.webkitAudioContext)();
            const osc = ctx.createOscillator();
            osc.type = 'sine';
            osc.frequency.setValueAtTime(880, ctx.currentTime);
            osc.connect(ctx.destination);
            osc.start();
            osc.stop(ctx.currentTime + 0.15);
        } catch(e) {}

        await lookupAndShowLot(codeText);

        setTimeout(() => {
            isProcessingScan = false;
        }, 2000);
    }

    // 5. SCAN DARI UPLOAD FILE FOTO
    async function handleFileScan(inputEl) {
        if (!inputEl.files || inputEl.files.length === 0) return;
        const file = inputEl.files[0];
        setCameraBadge('searching', 'Membaca Foto...');

        try {
            const fileReaderScanner = new Html5Qrcode("file-decode-sandbox");
            const decodedText = await fileReaderScanner.scanFile(file, true);
            fileReaderScanner.clear();

            await handleSuccessfulScan(decodedText);
            setCameraBadge('success', 'Foto Terbaca!');
        } catch (err) {
            console.error("Gagal baca QR dari file:", err);
            showLotNotFoundModal('File Gambar Tidak Terbaca', 'Gambar QR Code tidak dapat dikenali. Pastikan foto memiliki pencahayaan cukup terang, fokus jelas, dan gambar kode tidak terpotong.');
            setCameraBadge('standby', isCameraRunning ? 'Kamera Aktif' : 'Kamera Siaga');
        }
    }

    // Modal Alert Tematik "Data Lot Tidak Ditemukan"
    function showLotNotFoundModal(scannedCode, customMessage) {
        const modal = document.getElementById('lotNotFoundModal');
        const codeEl = document.getElementById('lotNotFoundScannedCode');
        const msgEl = document.getElementById('lotNotFoundMessage');

        if (codeEl) {
            codeEl.innerText = scannedCode || '-';
        }
        if (msgEl && customMessage) {
            msgEl.innerText = customMessage;
        }

        if (modal) {
            modal.classList.add('open');
            document.body.style.overflow = 'hidden';
        }
    }

    function closeLotNotFoundModal() {
        const modal = document.getElementById('lotNotFoundModal');
        if (modal) {
            modal.classList.remove('open');
            document.body.style.overflow = '';
        }
        setCameraBadge('standby', isCameraRunning ? 'Kamera Aktif' : 'Kamera Siaga');
        isProcessingScan = false;
        const searchInput = document.getElementById('barcode-search-input');
        if (searchInput) {
            searchInput.focus();
            searchInput.select();
        }
    }

    // Helper Tampilkan & Sembunyikan Animasi Loading di Area Hasil Inspeksi
    function showInspectionLoading(code) {
        const emptyEl = document.getElementById('empty-inspection-state');
        if (emptyEl) emptyEl.style.display = 'none';

        const detectedPanel = document.getElementById('detected-inspection-state');
        if (detectedPanel) detectedPanel.style.display = 'none';

        const loadingEl = document.getElementById('loading-inspection-state');
        if (loadingEl) {
            const previewEl = document.getElementById('loading-lot-code-preview');
            if (previewEl) previewEl.innerText = code || '...';
            loadingEl.style.display = 'block';
            loadingEl.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        }

        const btn = document.getElementById('btn-manual-search');
        if (btn) {
            btn.disabled = true;
            btn.style.opacity = '0.85';
            btn.innerHTML = `
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" class="spin-icon">
                    <path d="M21 12a9 9 0 1 1-6.219-8.56"></path>
                </svg>
                <span>Memuat...</span>
            `;
        }
    }

    function hideInspectionLoading() {
        const loadingEl = document.getElementById('loading-inspection-state');
        if (loadingEl) loadingEl.style.display = 'none';

        const btn = document.getElementById('btn-manual-search');
        if (btn) {
            btn.disabled = false;
            btn.style.opacity = '1';
            btn.innerHTML = `<span>Periksa</span>`;
        }
    }

    // 6. LOOKUP API & TAMPILKAN PANEL HASIL INSPEKSI
    async function lookupAndShowLot(code) {
        if (!code || !code.trim()) {
            showLotNotFoundModal('-', 'Silakan ketik atau pindai kode lot bahan terlebih dahulu.');
            return;
        }

        const rawCode = code.trim();
        const cleanCode = encodeURIComponent(rawCode);
        setCameraBadge('searching', 'Mencari data...');
        showInspectionLoading(rawCode);

        try {
            const res = await fetch(`/staff/lot/barcode/${cleanCode}?code=${cleanCode}`, {
                headers: { 'Accept': 'application/json' }
            });
            const data = await res.json().catch(() => null);

            if (data && data.success && data.lot_id) {
                setCameraBadge('success', '✓ Ditemukan');
                await loadLotInspectionData(data.lot_id);
            } else {
                hideInspectionLoading();
                const emptyEl = document.getElementById('empty-inspection-state');
                if (emptyEl) emptyEl.style.display = 'block';
                setCameraBadge('standby', isCameraRunning ? 'Kamera Aktif' : 'Kamera Siaga');
                const errMsg = (data && data.message) 
                    ? data.message 
                    : `Data lot tidak ditemukan pada unit SPPG ini. Pastikan label QR Code atau Barcode yang dipindai sudah benar.`;
                showLotNotFoundModal(rawCode, errMsg);
            }
        } catch (err) {
            console.error(err);
            hideInspectionLoading();
            const emptyEl = document.getElementById('empty-inspection-state');
            if (emptyEl) emptyEl.style.display = 'block';
            setCameraBadge('standby', isCameraRunning ? 'Kamera Aktif' : 'Kamera Siaga');
            showLotNotFoundModal(rawCode, 'Terjadi kendala saat memeriksa kode lot ke server.');
        }
    }

    // Load Data Lengkap ke Panel Inspeksi Tab 1
    async function loadLotInspectionData(lotId) {
        try {
            const loadingEl = document.getElementById('loading-inspection-state');
            if (!loadingEl || loadingEl.style.display === 'none') {
                showInspectionLoading('ID Lot #' + lotId);
            }

            const res = await fetch(`/staff/lot/${lotId}?format=json`, {
                headers: { 'Accept': 'application/json' }
            });
            const data = await res.json();
            if (!data || !data.success) {
                hideInspectionLoading();
                const emptyEl = document.getElementById('empty-inspection-state');
                if (emptyEl) emptyEl.style.display = 'block';
                return;
            }

            const l = data.lot;
            currentInspectedLotId = l.id;

            // Pastikan aktif di Tab 1
            switchMainTab('scan');

            // Sembunyikan loading & placeholder, tampilkan panel data hasil inspeksi
            hideInspectionLoading();
            const emptyEl = document.getElementById('empty-inspection-state');
            if (emptyEl) emptyEl.style.display = 'none';

            const detectedPanel = document.getElementById('detected-inspection-state');
            if (detectedPanel) detectedPanel.style.display = 'block';

            // Isi data header
            const barcodeEl = document.getElementById('insp-lot-barcode');
            if (barcodeEl) barcodeEl.innerText = l.barcode || '-';

            const matTitleEl = document.getElementById('insp-material-title');
            if (matTitleEl) matTitleEl.innerText = `${l.material_nama} (${l.material_kategori})`;

            const statusBadge = document.getElementById('insp-status-badge');
            if (statusBadge) {
                statusBadge.innerText = l.status_label;
                statusBadge.className = `badge-status ${l.status_badge_class}`;
            }

            const expBadge = document.getElementById('insp-expiry-badge');
            if (expBadge) {
                expBadge.innerText = l.expiry_status_label;
                expBadge.className = `badge-status ${l.expiry_badge_class}`;
            }

            // Isi grid info
            const lotNumEl = document.getElementById('insp-lot-number');
            if (lotNumEl) lotNumEl.innerText = l.lot_number || '-';

            const suppEl = document.getElementById('insp-supplier');
            if (suppEl) suppEl.innerText = l.supplier_nama || '-';

            const recDateEl = document.getElementById('insp-received-date');
            if (recDateEl) recDateEl.innerText = l.received_date || '-';

            const expDateEl = document.getElementById('insp-expiry-date');
            if (expDateEl) expDateEl.innerText = `${l.expiry_date} (${l.expiry_status_label})`;

            const qtyInfoEl = document.getElementById('insp-qty-info');
            if (qtyInfoEl) {
                qtyInfoEl.innerText = `${Number(l.current_qty).toLocaleString('id-ID')} / ${Number(l.initial_qty).toLocaleString('id-ID')} ${l.material_satuan}`;
            }

            const unitCostEl = document.getElementById('insp-unit-cost');
            if (unitCostEl) unitCostEl.innerText = l.formatted_unit_cost || '-';

            const recNoEl = document.getElementById('insp-receiving-no');
            if (recNoEl) recNoEl.innerText = l.receiving_no || '-';

            const purNoEl = document.getElementById('insp-purchase-no');
            if (purNoEl) purNoEl.innerText = l.purchase_no || '-';

            // Render Kartu Stok Movements
            const tbody = document.getElementById('insp-movements-tbody');
            if (tbody) {
                tbody.innerHTML = '';

                if (data.movements && data.movements.length > 0) {
                    data.movements.forEach(m => {
                        const isIN = m.direction === 'IN';
                        const tr = document.createElement('tr');
                        tr.innerHTML = `
                            <td style="padding: 10px 14px; color: #475569;">${m.created_at}</td>
                            <td style="padding: 10px 14px; font-weight: 600;">
                                <span class="badge-status ${isIN ? 'badge-safe' : 'badge-cancelled'}" style="padding: 2px 8px; font-size: 11px;">
                                    ${m.movement_type}
                                </span>
                            </td>
                            <td style="padding: 10px 14px; text-align: right; font-weight: 700; color: ${isIN ? '#16A34A' : '#DC2626'};">
                                ${isIN ? '+' : '-'}${Number(m.qty).toLocaleString('id-ID')} ${l.material_satuan}
                            </td>
                            <td style="padding: 10px 14px; text-align: right; font-weight: 800; color: #071E49;">
                                ${Number(m.balance_after).toLocaleString('id-ID')} ${l.material_satuan}
                            </td>
                            <td style="padding: 10px 14px; color: #64748B;">${m.creator_name}</td>
                        `;
                        tbody.appendChild(tr);
                    });
                } else {
                    tbody.innerHTML = `<tr><td colspan="5" style="text-align: center; padding: 24px; color: #94A3B8;">Belum ada mutasi stok tercatat untuk lot ini.</td></tr>`;
                }
            }

            // Smooth scroll ke panel hasil
            if (detectedPanel) {
                detectedPanel.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }

        } catch (err) {
            console.error("Gagal load data inspeksi:", err);
            hideInspectionLoading();
            const emptyEl = document.getElementById('empty-inspection-state');
            if (emptyEl) emptyEl.style.display = 'block';
        }
    }

    // Reset Panel Hasil Inspeksi untuk Scan Ulang
    function resetInspectionState() {
        currentInspectedLotId = null;
        hideInspectionLoading();
        const detectedPanel = document.getElementById('detected-inspection-state');
        if (detectedPanel) detectedPanel.style.display = 'none';
        const emptyEl = document.getElementById('empty-inspection-state');
        if (emptyEl) emptyEl.style.display = 'block';
        if (searchInput) {
            searchInput.value = '';
            searchInput.focus();
        }
        setCameraBadge('standby', isCameraRunning ? 'Kamera Aktif' : 'Kamera Siaga');
    }

    // Pindah dari Tabel Tab 2 ke Pemindai Tab 1
    function inspectLotFromTable(lotId) {
        switchMainTab('scan');
        showInspectionLoading('ID Lot #' + lotId);
        loadLotInspectionData(lotId);
    }
</script>
@endpush
