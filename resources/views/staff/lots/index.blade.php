@extends('layouts.staff')

@section('title', 'Lot Bahan Baku & Stok - Staff SPPG')

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
                <span class="stat-subtext">&le; 7 Hari Mendatang</span>
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
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#D97706" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink: 0; display: inline-block; vertical-align: -2px; margin-right: 4px;"><path d="M15 14c.2-1 .7-1.7 1.5-2.5 1-.9 1.5-2.2 1.5-3.5A6 6 0 0 0 6 8c0 1 .2 2.2 1.5 3.5.7.7 1.3 1.5 1.5 2.5"></path><path d="M9 18h6"></path><path d="M10 22h4"></path></svg><strong>Sistem Otomatis:</strong> Saat kode lot terdeteksi, sistem langsung menampilkan status kelayakan bahan (FEFO), sisa stok gudang, dan buku mutasi kartu stok.
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
                            <option value="near_expired" {{ $expiryFilter === 'near_expired' ? 'selected' : '' }}>Mendekati (&le; 7 hr)</option>
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
@endpush
