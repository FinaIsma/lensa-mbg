{{-- =====================================================================
     1. PANEL DETAIL BAHAN BAKU (SLIDE-OVER DRAWER)
     ===================================================================== --}}
<div class="bahan-drawer-backdrop" id="drawerDetailBahan">
    <div class="bahan-drawer-dialog">
        {{-- Header Drawer --}}
        <div class="bahan-drawer-header">
            <div style="display: flex; align-items: center; gap: 10px;">
                <div class="bahan-icon-box icon-blue" style="width: 38px; height: 38px;">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path>
                        <polyline points="3.27 6.96 12 12.01 20.73 6.96"></polyline>
                        <line x1="12" y1="22.08" x2="12" y2="12"></line>
                    </svg>
                </div>
                <div>
                    <h3 class="bahan-drawer-title">Detail Bahan Baku</h3>
                    <span style="font-size: 12px; color: #64748B;">Informasi spesifikasi bahan & riwayat lot persediaan</span>
                </div>
            </div>
            <button type="button" class="bahan-modal-close" onclick="closeDetailPanelBahan()" aria-label="Tutup Panel">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
            </button>
        </div>

        {{-- Body Drawer --}}
        <div class="bahan-drawer-body">
            {{-- Entity Header Card --}}
            <div class="drawer-entity-card">
                <div>
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <span id="detail_bahan_kode" style="font-weight: 700; font-size: 13px; color: #071E49; background-color: #EEF2F6; padding: 2px 8px; border-radius: 4px;">-</span>
                        <h4 id="detail_bahan_nama" class="drawer-entity-name" style="margin: 0;">-</h4>
                    </div>
                    <span id="detail_bahan_kategori_satuan" style="font-size: 12.5px; color: #64748B; margin-top: 4px; display: block;">Kategori: - | Satuan: -</span>
                </div>
                <div style="display: flex; flex-direction: column; align-items: flex-end; gap: 6px;">
                    <div id="detail_bahan_aktif_chip">
                        <span class="chip-status chip-aktif">
                            <span class="chip-dot"></span>
                            Aktif
                        </span>
                    </div>
                    <div id="detail_bahan_indikator_chip">
                        <span class="chip-status chip-aman">
                            <span class="chip-dot"></span>
                            Aman
                        </span>
                    </div>
                </div>
            </div>

            {{-- 2 Statistik Mini Persediaan --}}
            <div class="drawer-metrics-grid">
                <div class="drawer-metric-box">
                    <span class="drawer-metric-label">Stok Total</span>
                    <div style="display: flex; align-items: baseline; gap: 4px; margin-top: 4px;">
                        <span id="detail_bahan_total_stok" class="drawer-metric-val" style="color: #071E49; font-size: 18px;">0</span>
                    </div>
                    <span style="font-size: 11px; color: #94A3B8; margin-top: 2px;">Lot available yang belum kedaluwarsa</span>
                </div>

                <div class="drawer-metric-box">
                    <span class="drawer-metric-label">Stok Minimum</span>
                    <div style="display: flex; align-items: baseline; gap: 4px; margin-top: 4px;">
                        <span id="detail_bahan_min_stock" class="drawer-metric-val" style="color: #475569; font-size: 18px;">0</span>
                    </div>
                    <span style="font-size: 11px; color: #94A3B8; margin-top: 2px;">Batas pemicu status peringatan</span>
                </div>
            </div>

            {{-- Riwayat / Daftar Lot Bahan --}}
            <div class="drawer-section">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
                    <h5 style="font-size: 13.5px; font-weight: 700; color: #071E49; margin: 0;">Riwayat Lot Bahan</h5>
                    <span id="detail_bahan_lot_count" style="font-size: 12px; color: #64748B;">-</span>
                </div>
                <div class="drawer-lot-table-wrapper">
                    <table class="drawer-lot-table">
                        <thead>
                            <tr>
                                <th>No. Lot</th>
                                <th>Pemasok</th>
                                <th>Tgl Terima</th>
                                <th>Kedaluwarsa</th>
                                <th style="text-align: right;">Sisa Qty</th>
                                <th style="text-align: center;">Status</th>
                            </tr>
                        </thead>
                        <tbody id="detail_bahan_lot_tbody">
                            <tr>
                                <td colspan="6" style="text-align: center; color: #94A3B8; padding: 24px 0;">Memuat riwayat lot...</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Footer Drawer --}}
        <div class="bahan-drawer-footer">
            <button type="button" class="btn-drawer-close" onclick="closeDetailPanelBahan()">Tutup</button>
            <button type="button" id="btnEditFromDrawer" class="btn-drawer-edit" onclick="openModalEditBahanFromDetail()">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M12 20h9"></path>
                    <path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"></path>
                </svg>
                <span>Ubah Bahan</span>
            </button>
        </div>
    </div>
</div>

{{-- =====================================================================
     2. MODAL TAMBAH BAHAN BAKU
     ===================================================================== --}}
<div class="bahan-modal-backdrop" id="modalTambahBahan">
    <div class="bahan-modal-dialog">
        <div class="bahan-modal-header">
            <div>
                <h3 class="bahan-modal-title">Tambah Bahan Baku Baru</h3>
                <span style="font-size: 12.5px; color: #64748B;">Lengkapi informasi data bahan baku pangan untuk SPPG ini</span>
            </div>
            <button type="button" class="bahan-modal-close" onclick="closeModalTambahBahan()" aria-label="Tutup">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
            </button>
        </div>

        @php
            $storeAction = Route::has('staff.materials.store') ? route('staff.materials.store') : url('/staff/bahan');
        @endphp
        <form id="formTambahBahan" action="{{ $storeAction }}" method="POST">
            @csrf
            <div class="bahan-modal-body">
                <div class="bahan-form-row">
                    <div class="bahan-form-group">
                        <label class="bahan-form-label" for="tambah_kode">
                            Kode Bahan <span class="req">*</span>
                        </label>
                        <input type="text" name="kode" id="tambah_kode" class="bahan-form-input" placeholder="Contoh: BRS, AYM" maxlength="30" required style="text-transform: uppercase;">
                        <span class="bahan-form-hint">Kode unik pengenal bahan</span>
                    </div>

                    <div class="bahan-form-group">
                        <label class="bahan-form-label" for="tambah_satuan">
                            Satuan Ukur <span class="req">*</span>
                        </label>
                        <div class="bahan-select-wrapper">
                            <select name="satuan" id="tambah_satuan" class="bahan-select" required>
                                <option value="KG" selected>KG (Kilogram)</option>
                                <option value="Liter">Liter</option>
                                <option value="Gram">Gram</option>
                                <option value="Butir">Butir</option>
                                <option value="Pcs">Pcs</option>
                                <option value="Ikat">Ikat</option>
                            </select>
                            <svg class="bahan-select-chevron" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <polyline points="6 9 12 15 18 9"></polyline>
                            </svg>
                        </div>
                    </div>
                </div>

                <div class="bahan-form-group">
                    <label class="bahan-form-label" for="tambah_nama">
                        Nama Bahan Baku <span class="req">*</span>
                    </label>
                    <input type="text" name="nama" id="tambah_nama" class="bahan-form-input" placeholder="Contoh: Beras putih, Daging ayam fillet" maxlength="150" required>
                </div>

                <div class="bahan-form-row">
                    <div class="bahan-form-group">
                        <label class="bahan-form-label" for="tambah_kategori">
                            Kategori <span class="req">*</span>
                        </label>
                        <div class="bahan-select-wrapper">
                            <select name="kategori" id="tambah_kategori" class="bahan-select" required>
                                <option value="">-- Pilih Kategori --</option>
                                <option value="Karbohidrat">Karbohidrat</option>
                                <option value="Protein hewani">Protein hewani</option>
                                <option value="Protein nabati">Protein nabati</option>
                                <option value="Sayur">Sayur</option>
                                <option value="Buah">Buah</option>
                                <option value="Bumbu & minyak">Bumbu & minyak</option>
                                <option value="Lainnya">Lainnya</option>
                            </select>
                            <svg class="bahan-select-chevron" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <polyline points="6 9 12 15 18 9"></polyline>
                            </svg>
                        </div>
                    </div>

                    <div class="bahan-form-group">
                        <label class="bahan-form-label" for="tambah_min_stock">
                            Batas Stok Minimum <span class="req">*</span>
                        </label>
                        <input type="number" step="any" min="0" name="min_stock" id="tambah_min_stock" class="bahan-form-input" placeholder="Contoh: 50" required>
                        <span class="bahan-form-hint">Pemicu status peringatan "Di bawah minimum"</span>
                    </div>
                </div>

                <div class="bahan-form-group">
                    <label class="bahan-form-label" for="tambah_status">
                        Status Keaktifan
                    </label>
                    <div class="bahan-select-wrapper">
                        <select name="is_active" id="tambah_status" class="bahan-select">
                            <option value="1" selected>Aktif</option>
                            <option value="0">Nonaktif</option>
                        </select>
                        <svg class="bahan-select-chevron" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polyline points="6 9 12 15 18 9"></polyline>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bahan-modal-footer">
                <button type="button" class="btn-modal-cancel" onclick="closeModalTambahBahan()">Batal</button>
                <button type="submit" class="btn-modal-submit">Simpan Bahan</button>
            </div>
        </form>
    </div>
</div>

{{-- =====================================================================
     3. MODAL UBAH BAHAN BAKU
     ===================================================================== --}}
<div class="bahan-modal-backdrop" id="modalEditBahan">
    <div class="bahan-modal-dialog">
        <div class="bahan-modal-header">
            <div>
                <h3 class="bahan-modal-title">Ubah Data Bahan Baku</h3>
                <span style="font-size: 12.5px; color: #64748B;">Perbarui informasi master data bahan pangan</span>
            </div>
            <button type="button" class="bahan-modal-close" onclick="closeModalEditBahan()" aria-label="Tutup">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
            </button>
        </div>

        <form id="formEditBahan" action="" method="POST">
            @csrf
            @method('PUT')
            <input type="hidden" name="id" id="edit_bahan_id">

            <div class="bahan-modal-body">
                <div class="bahan-form-row">
                    <div class="bahan-form-group">
                        <label class="bahan-form-label" for="edit_kode">
                            Kode Bahan <span class="req">*</span>
                        </label>
                        <input type="text" name="kode" id="edit_kode" class="bahan-form-input" maxlength="30" required style="text-transform: uppercase;">
                    </div>

                    <div class="bahan-form-group">
                        <label class="bahan-form-label" for="edit_satuan">
                            Satuan Ukur <span class="req">*</span>
                        </label>
                        <div class="bahan-select-wrapper">
                            <select name="satuan" id="edit_satuan" class="bahan-select" required>
                                <option value="KG">KG (Kilogram)</option>
                                <option value="Liter">Liter</option>
                                <option value="Gram">Gram</option>
                                <option value="Butir">Butir</option>
                                <option value="Pcs">Pcs</option>
                                <option value="Ikat">Ikat</option>
                            </select>
                            <svg class="bahan-select-chevron" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <polyline points="6 9 12 15 18 9"></polyline>
                            </svg>
                        </div>
                    </div>
                </div>

                <div class="bahan-form-group">
                    <label class="bahan-form-label" for="edit_nama">
                        Nama Bahan Baku <span class="req">*</span>
                    </label>
                    <input type="text" name="nama" id="edit_nama" class="bahan-form-input" maxlength="150" required>
                </div>

                <div class="bahan-form-row">
                    <div class="bahan-form-group">
                        <label class="bahan-form-label" for="edit_kategori">
                            Kategori <span class="req">*</span>
                        </label>
                        <div class="bahan-select-wrapper">
                            <select name="kategori" id="edit_kategori" class="bahan-select" required>
                                <option value="Karbohidrat">Karbohidrat</option>
                                <option value="Protein hewani">Protein hewani</option>
                                <option value="Protein nabati">Protein nabati</option>
                                <option value="Sayur">Sayur</option>
                                <option value="Buah">Buah</option>
                                <option value="Bumbu & minyak">Bumbu & minyak</option>
                                <option value="Lainnya">Lainnya</option>
                            </select>
                            <svg class="bahan-select-chevron" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <polyline points="6 9 12 15 18 9"></polyline>
                            </svg>
                        </div>
                    </div>

                    <div class="bahan-form-group">
                        <label class="bahan-form-label" for="edit_min_stock">
                            Batas Stok Minimum <span class="req">*</span>
                        </label>
                        <input type="number" step="any" min="0" name="min_stock" id="edit_min_stock" class="bahan-form-input" required>
                    </div>
                </div>

                <div class="bahan-form-group">
                    <label class="bahan-form-label" for="edit_status">
                        Status Keaktifan
                    </label>
                    <div class="bahan-select-wrapper">
                        <select name="is_active" id="edit_status" class="bahan-select">
                            <option value="1">Aktif</option>
                            <option value="0">Nonaktif</option>
                        </select>
                        <svg class="bahan-select-chevron" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polyline points="6 9 12 15 18 9"></polyline>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bahan-modal-footer">
                <button type="button" class="btn-modal-cancel" onclick="closeModalEditBahan()">Batal</button>
                <button type="submit" class="btn-modal-submit">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>

{{-- =====================================================================
     4. MODAL KONFIRMASI STATUS (TOGGLE STATUS AKTIF / NONAKTIF)
     ===================================================================== --}}
<div class="bahan-modal-backdrop" id="modalToggleStatusBahan">
    <div class="bahan-modal-dialog" style="max-width: 440px;">
        <div class="bahan-modal-header" style="border-bottom: none; padding-bottom: 0;">
            <div style="display: flex; align-items: center; gap: 12px;">
                <div id="toggleStatusIconBox" class="bahan-icon-box icon-red" style="width: 42px; height: 42px;">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                        <circle cx="12" cy="12" r="10"></circle>
                        <line x1="4.93" y1="4.93" x2="19.07" y2="19.07"></line>
                    </svg>
                </div>
                <div>
                    <h3 id="toggleStatusModalTitle" class="bahan-modal-title" style="font-size: 16px;">Nonaktifkan Bahan?</h3>
                    <span id="toggleStatusModalSubtitle" style="font-size: 12.5px; color: #64748B;">Konfirmasi perubahan status</span>
                </div>
            </div>
            <button type="button" class="bahan-modal-close" onclick="closeModalToggleStatusBahan()" aria-label="Tutup">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
            </button>
        </div>

        <form id="formToggleStatusBahan" action="" method="POST">
            @csrf
            <div class="bahan-modal-body" style="padding-top: 14px;">
                <p id="toggleStatusMessage" style="font-size: 13.5px; color: #334155; line-height: 1.5; margin: 0;">
                    Apakah Anda yakin ingin mengubah status bahan ini?
                </p>
            </div>

            <div class="bahan-modal-footer">
                <button type="button" class="btn-modal-cancel" onclick="closeModalToggleStatusBahan()">Batal</button>
                <button type="submit" id="btnToggleStatusSubmit" class="btn-modal-submit" style="background-color: #DC2626;">
                    Ya, Nonaktifkan
                </button>
            </div>
        </form>
    </div>
</div>
