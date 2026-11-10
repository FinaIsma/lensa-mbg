{{-- Modal Catat Hasil Produksi Baru --}}
<div id="modalAddHasil" class="modal-backdrop modal-backdrop-custom" style="display: none; position: fixed; inset: 0; background: rgba(15, 23, 42, 0.55); backdrop-filter: blur(4px); z-index: 99999; align-items: center; justify-content: center; padding: 16px;">
    <div class="modal-dialog" style="background: #FFFFFF; border-radius: 16px; width: 100%; max-width: 620px; max-height: 90vh; overflow-y: auto; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);">
        <form method="POST" action="{{ route('staff.hasil.store') }}" enctype="multipart/form-data" id="formAddHasil">
            @csrf

            {{-- Modal Header --}}
            <div style="display: flex; align-items: center; justify-content: space-between; padding: 16px 24px; border-bottom: 1px solid #F1F5F9;">
                <h3 style="font-size: 18px; font-weight: 700; color: #071E49; margin: 0;">Catat Hasil Produksi</h3>
                <button type="button" onclick="closeAddHasilModal()" style="background: none; border: none; color: #94A3B8; cursor: pointer; padding: 4px; border-radius: 8px; transition: all 0.2s;">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="18" y1="6" x2="6" y2="18"></line>
                        <line x1="6" y1="6" x2="18" y2="18"></line>
                    </svg>
                </button>
            </div>

            {{-- Modal Body --}}
            <div style="padding: 18px 24px; display: flex; flex-direction: column; gap: 14px;">
                {{-- 1. Pilih Batch --}}
                <div>
                    <label for="hasil_batch_select" style="display: block; font-size: 13px; font-weight: 600; color: #071E49; margin-bottom: 6px;">
                        Batch Produksi <span style="color: #EF4444;">*</span>
                    </label>
                    <select name="production_batch_id" id="hasil_batch_select" required class="form-select" onchange="handleHasilBatchChange(this)" style="width: 100%; height: 42px; font-size: 13px; border-radius: 8px; border: 1px solid #CBD5E1; padding: 0 12px; background: #FFFFFF;">
                        <option value="">-- Pilih Batch --</option>
                        @forelse ($eligibleBatches as $eBatch)
                            <option value="{{ $eBatch->id }}"
                                data-target="{{ $eBatch->target_porsi }}"
                                data-menu="{{ $eBatch->menu?->nama ?? '-' }}"
                                data-kategori="{{ $eBatch->menu?->kategori_label ?? '-' }}"
                                data-tgl="{{ $eBatch->production_date ? $eBatch->production_date->format('d/m/Y') : '-' }}"
                                data-porsi="{{ ucfirst($eBatch->recipe?->ukuran_porsi ?? 'Standar') }}"
                                data-steps="{{ $eBatch->processingSteps->count() }}">
                                {{ $eBatch->batch_code }} - {{ $eBatch->menu?->nama }} ({{ number_format($eBatch->target_porsi, 0, ',', '.') }} ps)
                            </option>
                        @empty
                            <option value="" disabled>Belum ada batch yang siap dicatat</option>
                        @endforelse
                    </select>
                </div>

                {{-- Batch Info Card --}}
                <div id="batch_info_box" class="hasil-batch-summary-card" style="display: none;">
                    <div class="hasil-batch-summary-grid">
                        <div class="hasil-batch-summary-item">
                            <span>Menu</span>
                            <strong id="info_menu">-</strong>
                        </div>
                        <div class="hasil-batch-summary-item">
                            <span>Target</span>
                            <strong class="is-accent" id="info_target">-</strong>
                        </div>
                        <div class="hasil-batch-summary-item">
                            <span>Tanggal</span>
                            <strong id="info_tgl">-</strong>
                        </div>
                        <div class="hasil-batch-summary-item">
                            <span>Ukuran</span>
                            <strong id="info_porsi">-</strong>
                        </div>
                    </div>
                </div>

                {{-- 2. Porsi Siap Kirim --}}
                <div>
                    <label for="input_portions_ready" style="display: block; font-size: 13px; font-weight: 600; color: #071E49; margin-bottom: 6px;">
                        Porsi Siap Kirim <span style="color: #EF4444;">*</span>
                    </label>
                    <div style="position: relative;">
                        <input type="number" name="portions_ready" id="input_portions_ready" required min="0" step="1" placeholder="0" oninput="calculateHasilSusut()" class="form-input" style="width: 100%; height: 42px; font-size: 14px; font-weight: 600; border-radius: 8px; border: 1px solid #CBD5E1; padding: 0 70px 0 12px; box-sizing: border-box;">
                        <span style="position: absolute; right: 12px; top: 50%; transform: translateY(-50%); font-size: 13px; color: #64748B; font-weight: 600; pointer-events: none;">Porsi</span>
                    </div>

                    {{-- Dynamic Warning: Melebihi target --}}
                    <div id="warning_excess" class="hasil-modal-alert is-warning">
                        <div class="hasil-modal-alert-row">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="flex-shrink: 0; color: #D97706;">
                                <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path>
                                <line x1="12" y1="9" x2="12" y2="13"></line>
                                <line x1="12" y1="17" x2="12.01" y2="17"></line>
                            </svg>
                            <div>
                                Melebihi target (+<span id="warn_excess_diff">0</span> porsi). Susut tercatat: <strong>0 porsi</strong>.
                            </div>
                        </div>
                    </div>

                    {{-- Dynamic Info Otomatis: Susut --}}
                    <div id="info_susut_box" class="hasil-modal-alert is-info">
                        <div class="hasil-modal-alert-row">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="flex-shrink: 0;">
                                <circle cx="12" cy="12" r="10"></circle>
                                <line x1="12" y1="16" x2="12" y2="12"></line>
                                <line x1="12" y1="8" x2="12.01" y2="8"></line>
                            </svg>
                            <div id="susut_message_text">
                                Susut: <strong id="susut_count_val" style="color: #DC2626;">0 porsi</strong>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- 3. Kondisi Makanan --}}
                <div>
                    <label for="input_condition" style="display: block; font-size: 13px; font-weight: 600; color: #071E49; margin-bottom: 6px;">
                        Kondisi Makanan <span style="color: #EF4444;">*</span>
                    </label>
                    <input type="text" name="reported_condition" id="input_condition" required placeholder="Contoh: Baik, hangat, siap kirim" class="form-input" style="width: 100%; height: 42px; font-size: 13px; border-radius: 8px; border: 1px solid #CBD5E1; padding: 0 12px; box-sizing: border-box;">

                    {{-- Quick Suggestion Chips --}}
                    <div style="display: flex; gap: 6px; flex-wrap: wrap; margin-top: 6px;">
                        <button type="button" class="quick-chip-btn" onclick="setConditionText('Baik, sesuai standar resep')">
                            + Baik &amp; sesuai resep
                        </button>
                        <button type="button" class="quick-chip-btn" onclick="setConditionText('Hangat, kemasan higienis')">
                            + Hangat &amp; higienis
                        </button>
                        <button type="button" class="quick-chip-btn" onclick="setConditionText('Baik, siap distribusi')">
                            + Siap distribusi
                        </button>
                    </div>
                </div>

                {{-- 4. Catatan (Opsional) --}}
                <div>
                    <label for="hasil_note_input" style="display: block; font-size: 13px; font-weight: 600; color: #071E49; margin-bottom: 6px;">
                        Catatan <span style="font-size: 11.5px; font-weight: 400; color: #94A3B8;">(Opsional)</span>
                    </label>
                    <textarea name="note" id="hasil_note_input" rows="2" placeholder="Catatan tambahan..." class="form-input" style="width: 100%; font-size: 13px; border-radius: 8px; border: 1px solid #CBD5E1; padding: 8px 12px; box-sizing: border-box; resize: vertical;"></textarea>
                </div>

                {{-- 5. Bukti Foto --}}
                <div>
                    <label style="display: block; font-size: 13px; font-weight: 600; color: #071E49; margin-bottom: 6px;">
                        Foto Bukti <span style="color: #EF4444;">*</span>
                    </label>
                    <div id="dropzone_hasil" class="hasil-upload-dropzone">
                        <input type="file" name="evidence_file" id="evidence_file_input" required accept="image/*" onchange="previewEvidenceImage(this)" style="display: none;">
                        <label for="evidence_file_input" class="hasil-upload-label">
                            <div class="hasil-upload-icon">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"></path>
                                    <circle cx="12" cy="13" r="4"></circle>
                                </svg>
                            </div>
                            <div>
                                <span style="font-size: 13px; font-weight: 600; color: #042A70;">Unggah foto porsi</span>
                                <div style="font-size: 11px; color: #94A3B8; margin-top: 2px;">JPG, PNG, WEBP · Maks. 5MB</div>
                            </div>
                        </label>
                        <div id="image_preview_box" style="display: none; margin-top: 10px; position: relative;">
                            <img id="preview_img" src="" alt="Preview Bukti" style="max-height: 120px; border-radius: 8px; border: 1px solid #CBD5E1; object-fit: cover;">
                            <button type="button" onclick="clearEvidencePreview()" style="position: absolute; top: -8px; right: calc(50% - 70px); background: #EF4444; color: #FFFFFF; border: none; border-radius: 50%; width: 22px; height: 22px; cursor: pointer; font-size: 12px; display: flex; align-items: center; justify-content: center;">&times;</button>
                        </div>
                    </div>

                    {{-- Checkbox Jadikan Publik --}}
                    <div style="margin-top: 10px; display: flex; align-items: center; gap: 8px;">
                        <input type="checkbox" name="is_public" id="is_public_checkbox" value="1" style="width: 16px; height: 16px; accent-color: #042A70; cursor: pointer;">
                        <label for="is_public_checkbox" style="font-size: 12.5px; color: #334155; cursor: pointer;">
                            Jadikan foto ini publik
                        </label>
                    </div>
                </div>
            </div>

            {{-- Modal Footer --}}
            <div style="display: flex; align-items: center; justify-content: flex-end; gap: 10px; padding: 14px 24px; border-top: 1px solid #F1F5F9; background: #F8FAFC; border-radius: 0 0 16px 16px;">
                <button type="button" onclick="closeAddHasilModal()" class="btn-secondary" style="padding: 9px 18px; font-size: 13px;">
                    Batal
                </button>
                <button type="submit" class="btn-primary" style="padding: 9px 20px; font-size: 13px;">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                        <polyline points="20 6 9 17 4 12"></polyline>
                    </svg>
                    <span>Simpan Hasil</span>
                </button>
            </div>
        </form>
    </div>
</div>

{{-- Modal Catat Susut (Limbah tipe PRODUCTION_LOSS) --}}
<div id="modalCatatSusut" class="modal-backdrop modal-backdrop-custom" style="display: none; position: fixed; inset: 0; background: rgba(15, 23, 42, 0.55); backdrop-filter: blur(4px); z-index: 99999; align-items: center; justify-content: center; padding: 16px;">
    <div class="modal-dialog" style="background: #FFFFFF; border-radius: 16px; width: 100%; max-width: 520px; max-height: 90vh; overflow-y: auto; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);">
        <form method="POST" action="{{ route('staff.hasil.susut.store') }}" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="production_batch_id" id="susut_batch_id_input">

            {{-- Header --}}
            <div style="display: flex; align-items: center; justify-content: space-between; padding: 18px 22px; border-bottom: 1px solid #F1F5F9;">
                <div style="display: flex; align-items: center; gap: 10px;">
                    <div style="width: 36px; height: 36px; border-radius: 10px; background: #FEE2E2; color: #DC2626; display: flex; align-items: center; justify-content: center;">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polyline points="3 6 5 6 21 6"></polyline>
                            <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                        </svg>
                    </div>
                    <div>
                        <h3 style="font-size: 16px; font-weight: 700; color: #071E49; margin: 0;">Catat Susut Produksi</h3>
                        <span style="font-size: 11.5px; color: #64748B;">Limbah Tipe: <strong>PRODUCTION_LOSS</strong></span>
                    </div>
                </div>
                <button type="button" onclick="closeCatatSusutModal()" style="background: none; border: none; color: #94A3B8; cursor: pointer; padding: 4px;">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="18" y1="6" x2="6" y2="18"></line>
                        <line x1="6" y1="6" x2="18" y2="18"></line>
                    </svg>
                </button>
            </div>

            {{-- Body --}}
            <div style="padding: 20px 22px; display: flex; flex-direction: column; gap: 14px;">
                <div style="background: #FFFBEB; border: 1px solid #FDE68A; border-radius: 8px; padding: 10px 14px; font-size: 12.5px; color: #92400E;">
                    Mencatat data susut batch <strong id="susut_batch_code_display">-</strong> ke pembukuan limbah SPPG untuk pertanggungjawaban porsi.
                </div>

                {{-- Jumlah Susut --}}
                <div>
                    <label style="display: block; font-size: 12.5px; font-weight: 600; color: #071E49; margin-bottom: 5px;">
                        Jumlah Porsi Susut <span style="color: #EF4444;">*</span>
                    </label>
                    <div style="position: relative;">
                        <input type="number" name="qty" id="susut_qty_input" required min="0.1" step="0.1" class="form-input" style="width: 100%; height: 40px; font-size: 14px; font-weight: 600; border-radius: 8px; border: 1px solid #CBD5E1; padding: 0 70px 0 12px; box-sizing: border-box;">
                        <span style="position: absolute; right: 12px; top: 50%; transform: translateY(-50%); font-size: 12.5px; color: #64748B; font-weight: 600;">Porsi</span>
                    </div>
                </div>

                {{-- Alasan Susut --}}
                <div>
                    <label style="display: block; font-size: 12.5px; font-weight: 600; color: #071E49; margin-bottom: 5px;">
                        Penyebab / Keterangan Susut <span style="color: #EF4444;">*</span>
                    </label>
                    <textarea name="reason" rows="3" required placeholder="Contoh: Kerak nasi tebal di dasar panci, tumpahan saat packaging, atau potongan rusak..." class="form-input" style="width: 100%; font-size: 13px; border-radius: 8px; border: 1px solid #CBD5E1; padding: 8px 12px; box-sizing: border-box; resize: vertical;"></textarea>
                </div>

                {{-- Bukti Foto Susut (Opsional) --}}
                <div>
                    <label style="display: block; font-size: 12.5px; font-weight: 600; color: #071E49; margin-bottom: 5px;">
                        Foto Bukti Limbah / Sisa <span style="font-size: 11px; font-weight: 400; color: #94A3B8;">(Opsional)</span>
                    </label>
                    <div class="hasil-upload-dropzone">
                        <input type="file" name="evidence_file" id="susut_evidence_file_input" accept="image/*" onchange="previewEvidenceImage(this)" style="display: none;">
                        <label for="susut_evidence_file_input" style="cursor: pointer; display: flex; flex-direction: column; align-items: center; gap: 6px;">
                            <div style="width: 38px; height: 38px; border-radius: 50%; background: #EFF6FF; color: #0284C7; display: flex; align-items: center; justify-content: center;">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"></path>
                                    <circle cx="12" cy="13" r="4"></circle>
                                </svg>
                            </div>
                            <div>
                                <span style="font-size: 12.5px; font-weight: 600; color: #071E49;">Pilih file foto bukti</span>
                                <span style="font-size: 11.5px; color: #64748B;"> atau seret ke sini</span>
                            </div>
                            <span style="font-size: 11px; color: #94A3B8;">Format JPG, PNG, WEBP maks. 5MB</span>
                        </label>
                    </div>
                </div>
            </div>

            {{-- Footer --}}
            <div style="display: flex; align-items: center; justify-content: flex-end; gap: 10px; padding: 14px 22px; border-top: 1px solid #F1F5F9; background: #F8FAFC; border-radius: 0 0 16px 16px;">
                <button type="button" onclick="closeCatatSusutModal()" class="btn-secondary" style="padding: 8px 16px; font-size: 12.5px;">
                    Tutup
                </button>
                <button type="submit" class="btn-primary" style="background-color: #DC2626; border-color: #DC2626; padding: 8px 18px; font-size: 12.5px;">
                    Simpan Data Limbah
                </button>
            </div>
        </form>
    </div>
</div>

{{-- Modal Preview Foto Bukti --}}
<div id="modalPreviewFoto" class="modal-backdrop modal-backdrop-custom" style="display: none; position: fixed; inset: 0; background: rgba(15, 23, 42, 0.75); backdrop-filter: blur(4px); z-index: 99999; align-items: center; justify-content: center; padding: 16px;" onclick="closePreviewModal(event)">
    <div style="background: #FFFFFF; border-radius: 14px; max-width: 600px; width: 100%; overflow: hidden; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);" onclick="event.stopPropagation()">
        <div style="display: flex; align-items: center; justify-content: space-between; padding: 14px 18px; border-bottom: 1px solid #F1F5F9;">
            <div style="font-size: 14px; font-weight: 700; color: #071E49;" id="preview_modal_title">Foto Bukti Porsi</div>
            <button type="button" onclick="closePreviewModal()" style="background: none; border: none; color: #94A3B8; cursor: pointer; padding: 4px;">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
            </button>
        </div>
        <div style="padding: 16px; text-align: center; background: #0F172A;">
            <img id="preview_modal_img" src="" alt="Bukti Porsi" style="max-height: 480px; max-width: 100%; border-radius: 8px; object-fit: contain;">
        </div>
        <div style="padding: 12px 18px; font-size: 12px; color: #64748B; background: #F8FAFC; display: flex; justify-content: space-between; align-items: center;">
            <span id="preview_modal_desc">-</span>
            <span id="preview_modal_public_badge"></span>
        </div>
    </div>
</div>


