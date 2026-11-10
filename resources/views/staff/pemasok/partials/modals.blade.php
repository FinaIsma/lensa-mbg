{{-- =====================================================================
     1. PANEL DETAIL PEMASOK (SLIDE-OVER DRAWER)
     ===================================================================== --}}
<div class="pemasok-drawer-backdrop" id="pemasokDrawerBackdrop" onclick="closeDetailPanel()"></div>
<aside class="purchase-drawer" id="drawerDetailPemasok" role="dialog" aria-modal="true" aria-label="Detail Pemasok">
    {{-- Header Drawer --}}
    <div class="purchase-drawer-header">
        <div>
            <h2 class="purchase-drawer-title">Detail Pemasok</h2>
            <p class="purchase-drawer-subtitle">Informasi lengkap vendor bahan pangan</p>
        </div>
        <button type="button" class="modal-close-corner" onclick="closeDetailPanel()" aria-label="Tutup Panel" title="Tutup">
            &times;
        </button>
    </div>

    {{-- Body Drawer (Scrollable) --}}
    <div class="purchase-drawer-scroll">
        {{-- Card 1: Informasi Pemasok --}}
        <div class="detail-card">
            <div class="pemasok-drawer-card-header">
                <h3 class="detail-card-title" style="margin-bottom: 0;">Informasi Pemasok</h3>
                <div id="detail_status_chip">
                    <span class="badge-status badge-draft">
                        <span class="status-dot"></span>
                        Memuat...
                    </span>
                </div>
            </div>

            <div class="detail-grid-2col-exact">
                <div class="pemasok-col-group">
                    <div class="detail-exact-row">
                        <span class="detail-exact-label">Nama Pemasok</span>
                        <span class="detail-exact-colon">:</span>
                        <span class="detail-exact-value" id="detail_nama" style="font-weight: 700;">-</span>
                    </div>
                    <div class="detail-exact-row">
                        <span class="detail-exact-label">Kontak</span>
                        <span class="detail-exact-colon">:</span>
                        <span class="detail-exact-value" id="detail_kontak">-</span>
                    </div>
                    <div class="detail-exact-row">
                        <span class="detail-exact-label">Alamat</span>
                        <span class="detail-exact-colon">:</span>
                        <span class="detail-exact-value" id="detail_alamat">-</span>
                    </div>
                </div>

                <div class="pemasok-col-group">
                    <div class="detail-exact-row">
                        <span class="detail-exact-label">Terdaftar</span>
                        <span class="detail-exact-colon">:</span>
                        <span class="detail-exact-value" id="detail_created_at">-</span>
                    </div>
                    <div class="detail-exact-row">
                        <span class="detail-exact-label">Jumlah Pembelian</span>
                        <span class="detail-exact-colon">:</span>
                        <span class="detail-exact-value" id="detail_jumlah_pembelian" style="font-weight: 700;">0 PO</span>
                    </div>
                    <div class="detail-exact-row">
                        <span class="detail-exact-label">Total Nilai</span>
                        <span class="detail-exact-colon">:</span>
                        <span class="detail-exact-value" id="detail_total_nilai" style="font-weight: 700;">Rp 0</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Card 2: Riwayat PO --}}
        <div class="detail-card">
            <div class="pemasok-drawer-card-header" style="margin-bottom: 12px;">
                <h3 class="detail-card-title" style="margin-bottom: 0;">Riwayat PO</h3>
                <span id="detail_po_count" class="pemasok-po-count">-</span>
            </div>

            <div class="table-responsive">
                <table class="detail-items-table">
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
                            <td colspan="4" class="drawer-po-empty">
                                Memuat data riwayat...
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Footer Drawer --}}
    <div class="purchase-drawer-footer">
        <button type="button" class="btn-edit-action" onclick="closeDetailPanel()">Tutup</button>
        <button type="button" id="detail_btn_edit" class="btn-pesan-action">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M12 20h9"></path>
                <path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"></path>
            </svg>
            <span>Ubah Data</span>
        </button>
    </div>
</aside>

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
