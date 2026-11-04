{{-- =====================================================================
     1. PANEL DETAIL PEMASOK (SLIDE-OVER DRAWER)
     ===================================================================== --}}
<div class="pemasok-drawer-backdrop" id="drawerDetailPemasok">
    <div class="pemasok-drawer-dialog">
        {{-- Header Drawer --}}
        <div class="pemasok-drawer-header">
            <div style="display: flex; align-items: center; gap: 10px;">
                <div class="pemasok-icon-box icon-blue" style="width: 38px; height: 38px;">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                        <circle cx="12" cy="7" r="4"></circle>
                    </svg>
                </div>
                <div>
                    <h3 class="pemasok-drawer-title">Detail Pemasok</h3>
                    <span style="font-size: 12px; color: #64748B;">Informasi lengkap vendor bahan pangan</span>
                </div>
            </div>
            <button type="button" class="pemasok-modal-close" onclick="closeDetailPanel()" aria-label="Tutup Panel">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
            </button>
        </div>

        {{-- Body Drawer --}}
        <div class="pemasok-drawer-body">
            {{-- Entity Header Card --}}
            <div class="drawer-entity-card">
                <div>
                    <h4 id="detail_nama" class="drawer-entity-name">-</h4>
                    <span id="detail_created_at" style="font-size: 12px; color: #64748B;">Terdaftar: -</span>
                </div>
                <div id="detail_status_chip">
                    <span class="chip-status chip-aktif">
                        <span class="chip-dot"></span>
                        Aktif
                    </span>
                </div>
            </div>

            {{-- Info Kontak & Alamat --}}
            <div class="drawer-info-card">
                <div class="drawer-info-item">
                    <span class="drawer-info-label">Kontak</span>
                    <span id="detail_kontak" class="drawer-info-value">-</span>
                </div>
                <div class="drawer-info-item" style="border-top: 1px solid #F1F5F9; padding-top: 10px;">
                    <span class="drawer-info-label">Alamat</span>
                    <span id="detail_alamat" class="drawer-info-value">-</span>
                </div>
            </div>

            {{-- 2 Statistik Mini --}}
            <div class="drawer-metrics-grid">
                <div class="drawer-metric-box">
                    <span class="drawer-metric-label">Jumlah Pembelian</span>
                    <div style="display: flex; align-items: baseline; gap: 4px; margin-top: 4px;">
                        <span id="detail_jumlah_pembelian" class="drawer-metric-val">0</span>
                        <span style="font-size: 12px; color: #64748B;">PO</span>
                    </div>
                    <span style="font-size: 11px; color: #94A3B8; margin-top: 2px;">Selain draft & cancelled</span>
                </div>

                <div class="drawer-metric-box">
                    <span class="drawer-metric-label">Total Nilai</span>
                    <span id="detail_total_nilai" class="drawer-metric-val" style="color: #071E49; font-size: 17px; margin-top: 4px;">Rp 0</span>
                    <span style="font-size: 11px; color: #94A3B8; margin-top: 2px;">Akumulasi nilai transaksi</span>
                </div>
            </div>

            {{-- Riwayat PO --}}
            <div class="drawer-section">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
                    <h5 style="font-size: 13.5px; font-weight: 700; color: #071E49; margin: 0;">Riwayat PO</h5>
                    <span id="detail_po_count" style="font-size: 12px; color: #64748B;">-</span>
                </div>
                <div class="drawer-po-table-wrapper">
                    <table class="drawer-po-table">
                        <thead>
                            <tr>
                                <th>Nomor</th>
                                <th>Tanggal</th>
                                <th>Status</th>
                                <th style="text-align: right;">Total</th>
                            </tr>
                        </thead>
                        <tbody id="detail_po_tbody">
                            <tr>
                                <td colspan="4" style="text-align: center; padding: 20px 0; color: #94A3B8; font-size: 13px;">
                                    Memuat data riwayat...
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Footer Drawer --}}
        <div class="pemasok-drawer-footer">
            <button type="button" class="btn-modal-cancel" onclick="closeDetailPanel()">Tutup</button>
            <button type="button" id="detail_btn_edit" class="btn-modal-submit" style="display: inline-flex; align-items: center; gap: 6px;">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M12 20h9"></path>
                    <path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"></path>
                </svg>
                <span>Ubah Data</span>
            </button>
        </div>
    </div>
</div>

{{-- =====================================================================
     2. MODAL TAMBAH PEMASOK
     ===================================================================== --}}
<div class="pemasok-modal-backdrop" id="modalTambahPemasok">
    <div class="pemasok-modal-dialog">
        <div class="pemasok-modal-header">
            <h3 class="pemasok-modal-title">Tambah Pemasok Baru</h3>
            <button type="button" class="pemasok-modal-close" onclick="closeModalTambahPemasok()" aria-label="Tutup">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
            </button>
        </div>

        @php
            $storeRoute = Route::has('staff.suppliers.store') ? route('staff.suppliers.store') : url('/staff/pemasok');
        @endphp
        <form id="formTambahPemasok" action="{{ $storeRoute }}" method="POST">
            @csrf
            <div class="pemasok-modal-body">
                <div class="pemasok-form-group">
                    <label class="pemasok-form-label" for="tambah_nama">
                        Nama Pemasok <span class="req">*</span>
                    </label>
                    <input type="text" name="nama" id="tambah_nama" class="pemasok-form-input" placeholder="Contoh: CV Tani Makmur" required>
                </div>

                <div class="pemasok-form-group">
                    <label class="pemasok-form-label" for="tambah_kontak">
                        Kontak (No. HP / Telepon) <span class="req">*</span>
                    </label>
                    <input type="text" name="kontak" id="tambah_kontak" class="pemasok-form-input" placeholder="Contoh: 081255782666" required>
                </div>

                <div class="pemasok-form-group">
                    <label class="pemasok-form-label" for="tambah_alamat">
                        Alamat Lengkap <span class="req">*</span>
                    </label>
                    <textarea name="alamat" id="tambah_alamat" class="pemasok-form-textarea" placeholder="Contoh: Jl. Pasar Induk No. 34" rows="3" required></textarea>
                </div>

                <div class="pemasok-form-group">
                    <label class="pemasok-form-label" for="tambah_status">
                        Status <span class="req">*</span>
                    </label>
                    <div class="pemasok-select-wrapper">
                        <select name="status" id="tambah_status" class="pemasok-select" required>
                            <option value="active" selected>Aktif</option>
                            <option value="inactive">Nonaktif</option>
                        </select>
                        <svg class="pemasok-select-chevron" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polyline points="6 9 12 15 18 9"></polyline>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="pemasok-modal-footer">
                <button type="button" class="btn-modal-cancel" onclick="closeModalTambahPemasok()">Batal</button>
                <button type="submit" class="btn-modal-submit">Simpan Pemasok</button>
            </div>
        </form>
    </div>
</div>

{{-- =====================================================================
     3. MODAL EDIT PEMASOK (UBAH)
     ===================================================================== --}}
<div class="pemasok-modal-backdrop" id="modalEditPemasok">
    <div class="pemasok-modal-dialog">
        <div class="pemasok-modal-header">
            <h3 class="pemasok-modal-title">Ubah Data Pemasok</h3>
            <button type="button" class="pemasok-modal-close" onclick="closeModalEditPemasok()" aria-label="Tutup">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
            </button>
        </div>

        <form id="formEditPemasok" action="" method="POST">
            @csrf
            @method('PUT')
            <input type="hidden" name="id" id="edit_pemasok_id">

            <div class="pemasok-modal-body">
                <div class="pemasok-form-group">
                    <label class="pemasok-form-label" for="edit_nama">
                        Nama Pemasok <span class="req">*</span>
                    </label>
                    <input type="text" name="nama" id="edit_nama" class="pemasok-form-input" required>
                </div>

                <div class="pemasok-form-group">
                    <label class="pemasok-form-label" for="edit_kontak">
                        Kontak (No. HP / Telepon) <span class="req">*</span>
                    </label>
                    <input type="text" name="kontak" id="edit_kontak" class="pemasok-form-input" required>
                </div>

                <div class="pemasok-form-group">
                    <label class="pemasok-form-label" for="edit_alamat">
                        Alamat Lengkap <span class="req">*</span>
                    </label>
                    <textarea name="alamat" id="edit_alamat" class="pemasok-form-textarea" rows="3" required></textarea>
                </div>

                <div class="pemasok-form-group">
                    <label class="pemasok-form-label" for="edit_status">
                        Status <span class="req">*</span>
                    </label>
                    <div class="pemasok-select-wrapper">
                        <select name="status" id="edit_status" class="pemasok-select" required>
                            <option value="active">Aktif</option>
                            <option value="inactive">Nonaktif</option>
                        </select>
                        <svg class="pemasok-select-chevron" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polyline points="6 9 12 15 18 9"></polyline>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="pemasok-modal-footer">
                <button type="button" class="btn-modal-cancel" onclick="closeModalEditPemasok()">Batal</button>
                <button type="submit" class="btn-modal-submit">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>
