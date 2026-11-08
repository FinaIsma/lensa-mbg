{{-- =====================================================================
     1. PANEL DETAIL TUJUAN (SLIDE-OVER DRAWER)
     ===================================================================== --}}
<div class="tujuan-drawer-backdrop" id="drawerDetailTujuan">
    <div class="tujuan-drawer-dialog">
        {{-- Header Drawer --}}
        <div class="tujuan-drawer-header">
            <div style="display: flex; align-items: center; gap: 10px;">
                <div class="tujuan-icon-box icon-blue" style="width: 38px; height: 38px;">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
                        <circle cx="12" cy="10" r="3"></circle>
                    </svg>
                </div>
                <div>
                    <h3 class="tujuan-drawer-title">Detail Tujuan Distribusi</h3>
                    <span style="font-size: 12px; color: #64748B;">Informasi lokasi penerima manfaat &amp; riwayat distribusi</span>
                </div>
            </div>
            <button type="button" class="tujuan-drawer-close" onclick="closeDetailPanel()" aria-label="Tutup Panel">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
            </button>
        </div>

        {{-- Body Drawer --}}
        <div class="tujuan-drawer-body">
            {{-- Entity Header Card --}}
            <div class="drawer-entity-card">
                <div>
                    <h4 id="detail_nama" class="drawer-entity-name">-</h4>
                    <span id="detail_created_at" style="font-size: 12px; color: #64748B;">Terdaftar: -</span>
                </div>
                <div id="detail_tipe_chip">
                    <span class="badge-tipe badge-tipe-sekolah">Sekolah</span>
                </div>
            </div>

            {{-- 2 Statistik Mini --}}
            <div class="drawer-metrics-grid">
                <div class="drawer-metric-box">
                    <span class="drawer-metric-label">Jumlah Pengiriman</span>
                    <span id="detail_jumlah_pengiriman" class="drawer-metric-val">0 Kali</span>
                    <span style="font-size: 11px; color: #94A3B8; margin-top: 2px;">Selain draft &amp; batal</span>
                </div>

                <div class="drawer-metric-box">
                    <span class="drawer-metric-label">Total Porsi Terkirim</span>
                    <span id="detail_total_porsi" class="drawer-metric-val" style="color: #071E49;">0 Porsi</span>
                    <span style="font-size: 11px; color: #94A3B8; margin-top: 2px;">Akumulasi penerimaan</span>
                </div>
            </div>

            {{-- Info Kontak & Alamat --}}
            <div class="drawer-info-card">
                <div class="drawer-info-item">
                    <span class="drawer-info-label">Target Jumlah Penerima</span>
                    <span id="detail_jumlah_penerima" class="drawer-info-value" style="font-weight: 700; color: #071E49;">-</span>
                </div>
                <div class="drawer-info-item" style="border-top: 1px solid #E2E8F0; padding-top: 10px;">
                    <span class="drawer-info-label">Alamat Lengkap</span>
                    <span id="detail_alamat" class="drawer-info-value">-</span>
                </div>
            </div>

            {{-- Riwayat Distribusi --}}
            <div>
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
                    <h5 style="font-size: 14px; font-weight: 700; color: #071E49; margin: 0;">Riwayat Pengiriman</h5>
                    <span id="detail_dist_count" style="font-size: 12px; color: #64748B;">-</span>
                </div>
                <div class="drawer-history-table-wrapper">
                    <table class="drawer-history-table">
                        <thead>
                            <tr>
                                <th>Batch</th>
                                <th>Menu</th>
                                <th style="text-align: center;">Porsi</th>
                                <th>Waktu Kirim</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody id="detail_dist_tbody">
                            <tr>
                                <td colspan="5" style="text-align: center; padding: 20px 0; color: #94A3B8; font-size: 13px;">
                                    Memuat data riwayat...
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Footer Drawer --}}
        <div class="tujuan-drawer-footer">
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
     2. MODAL TAMBAH TUJUAN
     ===================================================================== --}}
<div class="tujuan-modal-backdrop" id="modalTambahTujuan">
    <div class="tujuan-modal-dialog">
        <div class="tujuan-modal-header">
            <h3 class="tujuan-modal-title">Tambah Tujuan Distribusi</h3>
            <button type="button" class="tujuan-modal-close" onclick="closeModalTambah()" aria-label="Tutup Modal">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
            </button>
        </div>

        <form id="formTambahTujuan" action="{{ route('staff.destinations.store') }}" method="POST">
            @csrf
            <div class="tujuan-modal-body">
                <div class="tujuan-form-group">
                    <label class="tujuan-form-label" for="tambah_nama">
                        Nama Tujuan / Lokasi <span class="required">*</span>
                    </label>
                    <input type="text" id="tambah_nama" name="nama" class="tujuan-form-input" placeholder="Contoh: SDN 1 Sukamaju" required maxlength="150" value="{{ old('nama') }}">
                    <span class="tujuan-form-help">Nama sekolah, posyandu, atau lembaga penerima manfaat MBG.</span>
                </div>

                <div class="tujuan-form-group">
                    <label class="tujuan-form-label" for="tambah_tipe">
                        Tipe Tujuan <span class="required">*</span>
                    </label>
                    <select id="tambah_tipe" name="tipe" class="tujuan-form-select" required>
                        <option value="sekolah" {{ old('tipe') === 'sekolah' ? 'selected' : '' }}>Sekolah (SD/SMP/SMA/PAUD)</option>
                        <option value="posyandu" {{ old('tipe') === 'posyandu' ? 'selected' : '' }}>Posyandu</option>
                        <option value="lainnya" {{ old('tipe') === 'lainnya' ? 'selected' : '' }}>Lainnya / Panti Asuhan</option>
                    </select>
                </div>

                <div class="tujuan-form-group">
                    <label class="tujuan-form-label" for="tambah_jumlah_penerima">
                        Target Jumlah Penerima (Porsi / Siswa) <span class="required">*</span>
                    </label>
                    <input type="number" id="tambah_jumlah_penerima" name="jumlah_penerima" class="tujuan-form-input" placeholder="Contoh: 320" required min="0" step="1" value="{{ old('jumlah_penerima', 0) }}">
                    <span class="tujuan-form-help">Digunakan sebagai acuan peringatan jika porsi pengiriman melebihi kuota.</span>
                </div>

                <div class="tujuan-form-group">
                    <label class="tujuan-form-label" for="tambah_alamat">
                        Alamat Lengkap
                    </label>
                    <textarea id="tambah_alamat" name="alamat" rows="3" class="tujuan-form-textarea" placeholder="Jalan, kelurahan, kecamatan atau patokan lokasi penerima...">{{ old('alamat') }}</textarea>
                </div>
            </div>

            <div class="tujuan-modal-footer">
                <button type="button" class="btn-modal-cancel" onclick="closeModalTambah()">Batal</button>
                <button type="submit" class="btn-modal-submit">Simpan Tujuan</button>
            </div>
        </form>
    </div>
</div>

{{-- =====================================================================
     3. MODAL UBAH TUJUAN
     ===================================================================== --}}
<div class="tujuan-modal-backdrop" id="modalEditTujuan">
    <div class="tujuan-modal-dialog">
        <div class="tujuan-modal-header">
            <h3 class="tujuan-modal-title">Ubah Tujuan Distribusi</h3>
            <button type="button" class="tujuan-modal-close" onclick="closeModalEdit()" aria-label="Tutup Modal">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
            </button>
        </div>

        <form id="formEditTujuan" action="" method="POST">
            @csrf
            @method('PUT')
            <input type="hidden" id="edit_id" name="id" value="">

            <div class="tujuan-modal-body">
                <div class="tujuan-form-group">
                    <label class="tujuan-form-label" for="edit_nama">
                        Nama Tujuan / Lokasi <span class="required">*</span>
                    </label>
                    <input type="text" id="edit_nama" name="nama" class="tujuan-form-input" required maxlength="150">
                </div>

                <div class="tujuan-form-group">
                    <label class="tujuan-form-label" for="edit_tipe">
                        Tipe Tujuan <span class="required">*</span>
                    </label>
                    <select id="edit_tipe" name="tipe" class="tujuan-form-select" required>
                        <option value="sekolah">Sekolah (SD/SMP/SMA/PAUD)</option>
                        <option value="posyandu">Posyandu</option>
                        <option value="lainnya">Lainnya / Panti Asuhan</option>
                    </select>
                </div>

                <div class="tujuan-form-group">
                    <label class="tujuan-form-label" for="edit_jumlah_penerima">
                        Target Jumlah Penerima (Porsi / Siswa) <span class="required">*</span>
                    </label>
                    <input type="number" id="edit_jumlah_penerima" name="jumlah_penerima" class="tujuan-form-input" required min="0" step="1">
                    <span class="tujuan-form-help">Digunakan sebagai acuan peringatan jika porsi pengiriman melebihi kuota.</span>
                </div>

                <div class="tujuan-form-group">
                    <label class="tujuan-form-label" for="edit_alamat">
                        Alamat Lengkap
                    </label>
                    <textarea id="edit_alamat" name="alamat" rows="3" class="tujuan-form-textarea" placeholder="Jalan, kelurahan, kecamatan atau patokan lokasi..."></textarea>
                </div>
            </div>

            <div class="tujuan-modal-footer">
                <button type="button" class="btn-modal-cancel" onclick="closeModalEdit()">Batal</button>
                <button type="submit" class="btn-modal-submit">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>

{{-- =====================================================================
     4. MODAL KONFIRMASI HAPUS TUJUAN
     ===================================================================== --}}
<div class="tujuan-modal-backdrop" id="modalDeleteTujuan">
    <div class="tujuan-modal-dialog" style="max-width: 440px;">
        <div class="tujuan-modal-header" style="background-color: #FEF2F2; border-bottom-color: #FEE2E2;">
            <div style="display: flex; align-items: center; gap: 10px;">
                <div style="width: 34px; height: 34px; border-radius: 50%; background: #FEE2E2; color: #DC2626; display: flex; align-items: center; justify-content: center;">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="10"></circle>
                        <line x1="12" y1="8" x2="12" y2="12"></line>
                        <line x1="12" y1="16" x2="12.01" y2="16"></line>
                    </svg>
                </div>
                <h3 class="tujuan-modal-title" style="color: #991B1B;">Hapus Tujuan Distribusi</h3>
            </div>
            <button type="button" class="tujuan-modal-close" onclick="closeModalDelete()" aria-label="Tutup Modal">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
            </button>
        </div>

        <form id="formDeleteTujuan" action="" method="POST">
            @csrf
            @method('DELETE')
            <div class="tujuan-modal-body">
                <p style="margin: 0; font-size: 14px; color: #334155; line-height: 1.5;">
                    Apakah Anda yakin ingin menghapus tujuan distribusi <strong id="delete_target_name" style="color: #0F172A;"></strong>?
                </p>
                <div style="background-color: #FFFBEB; border: 1px solid #FDE68A; border-radius: 8px; padding: 12px; margin-top: 10px; font-size: 12.5px; color: #92400E;">
                    Catatan: Tujuan yang sudah memiliki riwayat pengiriman tidak dapat dihapus untuk menjaga integritas data penelusuran.
                </div>
            </div>

            <div class="tujuan-modal-footer">
                <button type="button" class="btn-modal-cancel" onclick="closeModalDelete()">Batal</button>
                <button type="submit" class="btn-modal-delete">Ya, Hapus</button>
            </div>
        </form>
    </div>
</div>

