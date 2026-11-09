{{-- ==========================================================================
     1. MODAL CATAT PENGIRIMAN BARU
     ========================================================================== --}}
<div class="pengiriman-modal-backdrop" id="modalCatatPengiriman" role="dialog" aria-modal="true" aria-labelledby="modalCatatTitle">
    <div class="pengiriman-modal-dialog">
        <div class="pengiriman-modal-header">
            <h3 class="pengiriman-modal-title" id="modalCatatTitle">Catat Pengiriman Makanan</h3>
            <button type="button" class="pengiriman-modal-close" onclick="closeModalCatat()" aria-label="Tutup modal">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
            </button>
        </div>

        <form action="{{ route('staff.pengiriman.store') }}" method="POST" enctype="multipart/form-data" id="formCatatPengiriman">
            @csrf
            <div class="pengiriman-modal-body">
                {{-- Pilih Batch Produksi --}}
                <div class="pengiriman-form-group">
                    <label for="catat_batch_id" class="pengiriman-form-label">
                        Batch Produksi <span class="required">*</span>
                    </label>
                    <select name="production_batch_id" id="catat_batch_id" class="pengiriman-form-select" required onchange="onBatchChange(this)">
                        <option value="">-- Pilih Batch Produksi Siap Kirim --</option>
                        @foreach ($batches as $batch)
                            <option value="{{ $batch->id }}" {{ old('production_batch_id') == $batch->id ? 'selected' : '' }}>
                                {{ $batch->batch_code }} - {{ $batch->menu?->nama ?? 'Menu' }} (Sisa: {{ number_format($batch->sisa_porsi_siap_kirim, 0, ',', '.') }} porsi)
                            </option>
                        @endforeach
                    </select>
                    <span class="pengiriman-form-help">Hanya menampilkan batch dengan status Ready/Distributed yang memiliki sisa porsi.</span>
                </div>

                {{-- Summary Kuota Batch (Dinamis AJAX) --}}
                <div class="batch-summary-box" id="catat_batch_summary" style="display: none;">
                    <div class="text-xs font-semibold text-slate-700" id="sum_batch_menu">-</div>
                    <div class="batch-summary-grid">
                        <div class="batch-summary-item">
                            <span class="batch-summary-label">Target Batch</span>
                            <span class="batch-summary-val" id="sum_target_porsi">-</span>
                        </div>
                        <div class="batch-summary-item">
                            <span class="batch-summary-label">Hasil Masak (Aktual)</span>
                            <span class="batch-summary-val" id="sum_actual_porsi">-</span>
                        </div>
                        <div class="batch-summary-item">
                            <span class="batch-summary-label">Sisa Siap Kirim</span>
                            <span class="batch-summary-val" id="sum_sisa_porsi" style="color: #10B981;">-</span>
                        </div>
                    </div>
                </div>

                {{-- Pilih Tujuan Distribusi --}}
                <div class="pengiriman-form-group">
                    <label for="catat_destination_id" class="pengiriman-form-label">
                        Tujuan Distribusi <span class="required">*</span>
                    </label>
                    <select name="destination_id" id="catat_destination_id" class="pengiriman-form-select" required>
                        <option value="">-- Pilih Sekolah / Lokasi Tujuan --</option>
                        @foreach ($destinations as $dest)
                            <option value="{{ $dest->id }}" {{ old('destination_id') == $dest->id ? 'selected' : '' }}>
                                {{ $dest->nama }} ({{ $dest->tipe_label }} - Target: {{ number_format($dest->jumlah_penerima, 0, ',', '.') }} Porsi)
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Jumlah Porsi & Waktu Pengiriman (2 Kolom) --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="pengiriman-form-group">
                        <label for="catat_portions_sent" class="pengiriman-form-label">
                            Jumlah Porsi Dikirim <span class="required">*</span>
                        </label>
                        <input type="number" name="portions_sent" id="catat_portions_sent" class="pengiriman-form-input" min="1" placeholder="Cth: 150" required value="{{ old('portions_sent') }}">
                        <div class="text-xs text-red-600 font-medium mt-1" id="catat_quota_warning" style="display: none;"></div>
                    </div>

                    <div class="pengiriman-form-group">
                        <label for="catat_distributed_at" class="pengiriman-form-label">
                            Waktu Berangkat / Pengiriman <span class="required">*</span>
                        </label>
                        <input type="datetime-local" name="distributed_at" id="catat_distributed_at" class="pengiriman-form-input" required value="{{ old('distributed_at') }}">
                    </div>
                </div>

                {{-- Status Awal Pengiriman --}}
                <div class="pengiriman-form-group">
                    <label class="pengiriman-form-label">
                        Status Awal Pengiriman <span class="required">*</span>
                    </label>
                    <div class="pengiriman-radio-group">
                        <label class="pengiriman-radio-item">
                            <input type="radio" name="status" value="planned" checked>
                            <span>Direncanakan (Planned)</span>
                        </label>
                        <label class="pengiriman-radio-item">
                            <input type="radio" name="status" value="sent">
                            <span>Langsung Berangkat (Sent)</span>
                        </label>
                    </div>
                </div>

                {{-- Upload Bukti Foto (Opsional) --}}
                <div class="pengiriman-form-group">
                    <label for="catat_evidence_file" class="pengiriman-form-label">
                        Foto Bukti Pengiriman (Opsional)
                    </label>
                    <input type="file" name="evidence_file" id="catat_evidence_file" class="pengiriman-form-input" accept="image/*">
                    <span class="pengiriman-form-help">Format file: JPG, PNG, WEBP (Maksimal 5MB).</span>
                </div>

                {{-- Catatan Pengiriman --}}
                <div class="pengiriman-form-group">
                    <label for="catat_notes" class="pengiriman-form-label">Keterangan / Catatan Driver (Opsional)</label>
                    <textarea name="notes" id="catat_notes" rows="2" class="pengiriman-form-textarea" placeholder="Cth: Dikirim menggunakan armada box pendingin No. B 1234 CD">{{ old('notes') }}</textarea>
                </div>
            </div>

            <div class="pengiriman-modal-footer">
                <button type="button" class="btn-secondary" onclick="closeModalCatat()">Batal</button>
                <button type="submit" class="btn-primary">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="20 6 9 17 4 12"></polyline>
                    </svg>
                    <span>Simpan Pengiriman</span>
                </button>
            </div>
        </form>
    </div>
</div>

{{-- ==========================================================================
     2. MODAL UBAH PENGIRIMAN (PLANNED)
     ========================================================================== --}}
<div class="pengiriman-modal-backdrop" id="modalEditPengiriman" role="dialog" aria-modal="true" aria-labelledby="modalEditTitle">
    <div class="pengiriman-modal-dialog">
        <div class="pengiriman-modal-header">
            <h3 class="pengiriman-modal-title" id="modalEditTitle">Ubah Alokasi Pengiriman</h3>
            <button type="button" class="pengiriman-modal-close" onclick="closeModalEdit()" aria-label="Tutup modal">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
            </button>
        </div>

        <form action="" method="POST" id="formEditPengiriman">
            @csrf
            @method('PUT')
            <div class="pengiriman-modal-body">
                <div class="p-3 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-700 flex items-center justify-between">
                    <div>
                        <span class="text-slate-500">Batch Produksi:</span>
                        <strong class="text-slate-800 ml-1" id="edit_batch_code_label">-</strong>
                    </div>
                    <div class="text-emerald-600 font-semibold" id="edit_max_label">-</div>
                </div>

                {{-- Pilih Tujuan --}}
                <div class="pengiriman-form-group">
                    <label for="edit_destination_id" class="pengiriman-form-label">
                        Tujuan Distribusi <span class="required">*</span>
                    </label>
                    <select name="destination_id" id="edit_destination_id" class="pengiriman-form-select" required>
                        @foreach ($destinations as $dest)
                            <option value="{{ $dest->id }}">
                                {{ $dest->nama }} ({{ $dest->tipe_label }} - Kuota: {{ number_format($dest->jumlah_penerima, 0, ',', '.') }} Porsi)
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Porsi & Waktu --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="pengiriman-form-group">
                        <label for="edit_portions_sent" class="pengiriman-form-label">
                            Jumlah Porsi Dikirim <span class="required">*</span>
                        </label>
                        <input type="number" name="portions_sent" id="edit_portions_sent" class="pengiriman-form-input" min="1" required>
                    </div>

                    <div class="pengiriman-form-group">
                        <label for="edit_distributed_at" class="pengiriman-form-label">
                            Waktu Pengiriman <span class="required">*</span>
                        </label>
                        <input type="datetime-local" name="distributed_at" id="edit_distributed_at" class="pengiriman-form-input" required>
                    </div>
                </div>
            </div>

            <div class="pengiriman-modal-footer">
                <button type="button" class="btn-secondary" onclick="closeModalEdit()">Batal</button>
                <button type="submit" class="btn-primary">
                    <span>Simpan Perubahan</span>
                </button>
            </div>
        </form>
    </div>
</div>

{{-- ==========================================================================
     3. MODAL TANDAI TERKIRIM (PLANNED -> SENT)
     ========================================================================== --}}
<div class="pengiriman-modal-backdrop" id="modalSentPengiriman" role="dialog" aria-modal="true" aria-labelledby="modalSentTitle">
    <div class="pengiriman-modal-dialog">
        <div class="pengiriman-modal-header">
            <h3 class="pengiriman-modal-title" id="modalSentTitle">Memberangkatkan Pengiriman</h3>
            <button type="button" class="pengiriman-modal-close" onclick="closeModalSent()" aria-label="Tutup modal">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
            </button>
        </div>

        <form action="" method="POST" enctype="multipart/form-data" id="formSentPengiriman">
            @csrf
            <div class="pengiriman-modal-body">
                <div class="p-3 bg-blue-50 border border-blue-100 rounded-xl flex items-center justify-between text-xs text-blue-900">
                    <div>
                        <span class="text-blue-600">Tujuan:</span>
                        <strong class="ml-1" id="sent_dest_label">-</strong>
                    </div>
                    <div>
                        <span class="text-blue-600">Porsi:</span>
                        <strong class="ml-1" id="sent_portions_label">-</strong>
                    </div>
                </div>

                <div class="pengiriman-form-group">
                    <label for="sent_distributed_at" class="pengiriman-form-label">
                        Waktu Keberangkatan Armada <span class="required">*</span>
                    </label>
                    <input type="datetime-local" name="distributed_at" id="sent_distributed_at" class="pengiriman-form-input" required>
                </div>

                <div class="pengiriman-form-group">
                    <label for="sent_evidence_file" class="pengiriman-form-label">
                        Foto Bukti Keberangkatan / Armada (Opsional)
                    </label>
                    <input type="file" name="evidence_file" id="sent_evidence_file" class="pengiriman-form-input" accept="image/*">
                </div>

                <div class="pengiriman-form-group">
                    <label for="sent_notes" class="pengiriman-form-label">Catatan (Opsional)</label>
                    <textarea name="notes" id="sent_notes" rows="2" class="pengiriman-form-textarea" placeholder="Cth: Berangkat tepat waktu dengan kendaraan box"></textarea>
                </div>
            </div>

            <div class="pengiriman-modal-footer">
                <button type="button" class="btn-secondary" onclick="closeModalSent()">Batal</button>
                <button type="submit" class="btn-primary" style="background-color: #2563EB;">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="22" y1="2" x2="11" y2="13"></line>
                        <polygon points="22 2 15 22 11 13 2 9 22 2"></polygon>
                    </svg>
                    <span>Konfirmasi Berangkat</span>
                </button>
            </div>
        </form>
    </div>
</div>

{{-- ==========================================================================
     4. MODAL TANDAI DITERIMA (SENT -> RECEIVED)
     ========================================================================== --}}
<div class="pengiriman-modal-backdrop" id="modalReceivedPengiriman" role="dialog" aria-modal="true" aria-labelledby="modalRecvTitle">
    <div class="pengiriman-modal-dialog">
        <div class="pengiriman-modal-header">
            <h3 class="pengiriman-modal-title" id="modalRecvTitle">Konfirmasi Penerimaan Makanan</h3>
            <button type="button" class="pengiriman-modal-close" onclick="closeModalReceived()" aria-label="Tutup modal">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
            </button>
        </div>

        <form action="" method="POST" enctype="multipart/form-data" id="formReceivedPengiriman">
            @csrf
            <div class="pengiriman-modal-body">
                <div class="p-3 bg-emerald-50 border border-emerald-100 rounded-xl flex items-center justify-between text-xs text-emerald-900">
                    <div>
                        <span class="text-emerald-700">Tujuan Penerima:</span>
                        <strong class="ml-1" id="recv_dest_label">-</strong>
                    </div>
                    <div>
                        <span class="text-emerald-700">Jumlah:</span>
                        <strong class="ml-1" id="recv_portions_label">-</strong>
                    </div>
                </div>

                <div class="pengiriman-form-group">
                    <label for="recv_evidence_file" class="pengiriman-form-label">
                        Foto Bukti Serah Terima / Tanda Tangan (Opsional)
                    </label>
                    <input type="file" name="evidence_file" id="recv_evidence_file" class="pengiriman-form-input" accept="image/*">
                    <span class="pengiriman-form-help">Unggah foto saat makanan diserahkan kepada pihak sekolah/posyandu.</span>
                </div>

                <div class="pengiriman-form-group">
                    <label for="recv_notes" class="pengiriman-form-label">Catatan Penerimaan (Opsional)</label>
                    <textarea name="notes" id="recv_notes" rows="2" class="pengiriman-form-textarea" placeholder="Cth: Diterima langsung oleh kepala sekolah dalam kondisi hangat dan baik"></textarea>
                </div>
            </div>

            <div class="pengiriman-modal-footer">
                <button type="button" class="btn-secondary" onclick="closeModalReceived()">Batal</button>
                <button type="submit" class="btn-primary" style="background-color: #16A34A;">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="20 6 9 17 4 12"></polyline>
                    </svg>
                    <span>Konfirmasi Telah Diterima</span>
                </button>
            </div>
        </form>
    </div>
</div>

{{-- ==========================================================================
     5. MODAL BATALKAN PENGIRIMAN (PLANNED -> CANCELLED)
     ========================================================================== --}}
<div class="pengiriman-modal-backdrop" id="modalCancelPengiriman" role="dialog" aria-modal="true" aria-labelledby="modalCancelTitle">
    <div class="pengiriman-modal-dialog">
        <div class="pengiriman-modal-header">
            <h3 class="pengiriman-modal-title text-red-600" id="modalCancelTitle">Batalkan Rencana Pengiriman</h3>
            <button type="button" class="pengiriman-modal-close" onclick="closeModalCancel()" aria-label="Tutup modal">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
            </button>
        </div>

        <form action="" method="POST" id="formCancelPengiriman">
            @csrf
            <div class="pengiriman-modal-body">
                <div class="p-3 bg-red-50 border border-red-100 rounded-xl text-xs text-red-800">
                    <div>Membatalkan alokasi pengiriman ke: <strong id="cancel_dest_label">-</strong></div>
                    <div class="mt-1 text-slate-500">Porsi dari pengiriman ini akan otomatis dikembalikan ke sisa kuota batch terkait.</div>
                </div>

                <div class="pengiriman-form-group">
                    <label for="cancellation_reason" class="pengiriman-form-label">
                        Alasan Pembatalan <span class="required">*</span>
                    </label>
                    <textarea name="cancellation_reason" id="cancellation_reason" rows="3" class="pengiriman-form-textarea" placeholder="Jelaskan alasan pembatalan (minimal 5 karakter)..." required></textarea>
                </div>
            </div>

            <div class="pengiriman-modal-footer">
                <button type="button" class="btn-secondary" onclick="closeModalCancel()">Kembali</button>
                <button type="submit" class="btn-primary" style="background-color: #DC2626;">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="10"></circle>
                        <line x1="15" y1="9" x2="9" y2="15"></line>
                        <line x1="9" y1="9" x2="15" y2="15"></line>
                    </svg>
                    <span>Ya, Batalkan Pengiriman</span>
                </button>
            </div>
        </form>
    </div>
</div>

{{-- ==========================================================================
     6. SLIDE-OVER DRAWER DETAIL PENGIRIMAN
     ========================================================================== --}}
<div class="pengiriman-drawer-backdrop" id="drawerDetailPengiriman" onclick="if(event.target === this) closeDrawerDetail()">
    <div class="pengiriman-drawer-dialog">
        <div class="pengiriman-drawer-header">
            <div>
                <h3 class="pengiriman-drawer-title">Detail Pengiriman</h3>
                <span class="text-xs text-slate-500" id="drawer_shipment_id">#KRM-0000</span>
            </div>
            <div class="flex items-center gap-3">
                <span id="drawer_status_badge" class="status-chip badge-planned">Direncanakan</span>
                <button type="button" class="pengiriman-drawer-close" onclick="closeDrawerDetail()" aria-label="Tutup drawer">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="18" y1="6" x2="6" y2="18"></line>
                        <line x1="6" y1="6" x2="18" y2="18"></line>
                    </svg>
                </button>
            </div>
        </div>

        {{-- Loading Indicator --}}
        <div id="drawer_loading" class="flex-1 flex flex-col items-center justify-center p-8 text-slate-400">
            <svg class="animate-spin h-8 w-8 text-[#071E49] mb-3" viewBox="0 0 24 24" fill="none">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
            </svg>
            <span class="text-xs font-medium">Memuat data pengiriman...</span>
        </div>

        {{-- Drawer Content --}}
        <div class="pengiriman-drawer-body" id="drawer_content" style="display: none;">
            {{-- Timeline 3 Tahap --}}
            <div class="pengiriman-timeline">
                <div class="timeline-step" id="timeline_step_1">
                    <div class="timeline-node">1</div>
                    <span class="timeline-label">Direncanakan</span>
                </div>
                <div class="timeline-step" id="timeline_step_2">
                    <div class="timeline-node">2</div>
                    <span class="timeline-label">Dalam Kirim</span>
                </div>
                <div class="timeline-step" id="timeline_step_3">
                    <div class="timeline-node">3</div>
                    <span class="timeline-label">Diterima</span>
                </div>
            </div>

            {{-- Card Info Tujuan --}}
            <div class="p-4 bg-slate-50 border border-slate-200 rounded-xl flex flex-col gap-2">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Lokasi Tujuan</span>
                    <span class="text-xs bg-blue-100 text-blue-700 font-semibold px-2 py-0.5 rounded-full" id="drawer_dest_type">-</span>
                </div>
                <div class="text-base font-bold text-[#071E49]" id="drawer_dest_name">-</div>
                <div class="text-xs text-slate-600 flex items-start gap-1">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="flex-shrink-0 mt-0.5 text-slate-400">
                        <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
                        <circle cx="12" cy="10" r="3"></circle>
                    </svg>
                    <span id="drawer_dest_address">-</span>
                </div>
                <div class="text-xs font-medium text-slate-500 mt-1" id="drawer_dest_target">- Porsi Target</div>
            </div>

            {{-- Card Info Batch & Porsi --}}
            <div class="p-4 bg-slate-50 border border-slate-200 rounded-xl flex flex-col gap-3">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Batch Produksi</span>
                    <span class="text-xs font-bold text-slate-700" id="drawer_batch_code">-</span>
                </div>
                <div class="text-sm font-bold text-[#071E49]" id="drawer_menu_name">-</div>

                <div class="p-3 bg-white border border-slate-200 rounded-lg flex items-center justify-between">
                    <span class="text-xs text-slate-600 font-medium">Porsi Dikirimkan:</span>
                    <strong class="text-base text-emerald-600" id="drawer_portions_sent">- Porsi</strong>
                </div>

                {{-- Progress Bar Kuota Batch --}}
                <div class="flex flex-col gap-1.5 mt-1">
                    <div class="flex justify-between text-xs text-slate-600">
                        <span>Alokasi Batch</span>
                        <span id="drawer_batch_pct" class="font-semibold text-slate-800">0%</span>
                    </div>
                    <div class="w-full bg-slate-200 rounded-full h-2 overflow-hidden">
                        <div id="drawer_batch_progress_bar" class="bg-[#071E49] h-2 rounded-full transition-all duration-300" style="width: 0%;"></div>
                    </div>
                    <div class="grid grid-cols-4 gap-1 text-[11px] text-slate-500 text-center mt-1">
                        <div>Target: <b id="drawer_batch_target">-</b></div>
                        <div>Hasil: <b id="drawer_batch_actual">-</b></div>
                        <div>Teralokasi: <b id="drawer_batch_allocated">-</b></div>
                        <div>Sisa: <b id="drawer_batch_sisa" class="text-emerald-600">-</b></div>
                    </div>
                </div>
            </div>

            {{-- Info Petugas & Waktu --}}
            <div class="grid grid-cols-3 gap-2.5 text-xs">
                <div class="p-3 bg-white border border-slate-200 rounded-xl flex flex-col gap-1">
                    <span class="text-slate-500 font-medium">Petugas Pencatat</span>
                    <strong class="text-slate-800 truncate" id="drawer_recorder_name">-</strong>
                </div>
                <div class="p-3 bg-white border border-slate-200 rounded-xl flex flex-col gap-1">
                    <span class="text-slate-500 font-medium">Waktu Berangkat</span>
                    <strong class="text-slate-800" id="drawer_distributed_at">-</strong>
                </div>
                <div class="p-3 bg-white border border-slate-200 rounded-xl flex flex-col gap-1">
                    <span class="text-slate-500 font-medium">Waktu Dibuat</span>
                    <strong class="text-slate-800" id="drawer_created_at">-</strong>
                </div>
            </div>

            {{-- Galeri Foto Bukti --}}
            <div class="flex flex-col gap-2">
                <span class="text-xs font-semibold text-slate-700 uppercase tracking-wider">Foto Bukti Pengiriman</span>
                <div id="drawer_evidences_list" class="evidence-gallery-grid"></div>
                <div id="drawer_no_evidence" class="text-xs text-slate-400 italic p-3 bg-slate-50 border border-dashed border-slate-200 rounded-lg text-center" style="display: none;">
                    Belum ada bukti foto yang diunggah untuk pengiriman ini.
                </div>
            </div>
        </div>

        {{-- Drawer Footer Actions --}}
        <div class="pengiriman-drawer-footer">
            <button type="button" class="btn-act btn-act-detail" onclick="closeDrawerDetail()">Tutup</button>
            <div id="drawer_footer_actions" class="flex items-center gap-2"></div>
        </div>
    </div>
</div>

