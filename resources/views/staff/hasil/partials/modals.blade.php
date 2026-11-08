{{-- Modal Catat Hasil Produksi Baru --}}
<div id="modalAddHasil" class="modal-backdrop modal-backdrop-custom" style="display: none; position: fixed; inset: 0; background: rgba(15, 23, 42, 0.55); backdrop-filter: blur(4px); z-index: 99999; align-items: center; justify-content: center; padding: 16px;">
    <div class="modal-dialog" style="background: #FFFFFF; border-radius: 16px; width: 100%; max-width: 620px; max-height: 90vh; overflow-y: auto; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);">
        <form method="POST" action="{{ route('staff.hasil.store') }}" enctype="multipart/form-data" id="formAddHasil">
            @csrf

            {{-- Modal Header --}}
            <div style="display: flex; align-items: center; justify-content: space-between; padding: 20px 24px; border-bottom: 1px solid #F1F5F9;">
                <div>
                    <h3 style="font-size: 18px; font-weight: 700; color: #071E49; margin: 0;">Catat Hasil Produksi</h3>
                    <p style="font-size: 12.5px; color: #64748B; margin: 3px 0 0;">Verifikasi porsi siap saji dan dokumentasikan bukti sebelum distribusi.</p>
                </div>
                <button type="button" onclick="closeAddHasilModal()" style="background: none; border: none; color: #94A3B8; cursor: pointer; padding: 4px; border-radius: 8px; transition: all 0.2s;">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="18" y1="6" x2="6" y2="18"></line>
                        <line x1="6" y1="6" x2="18" y2="18"></line>
                    </svg>
                </button>
            </div>

            {{-- Modal Body --}}
            <div style="padding: 20px 24px; display: flex; flex-direction: column; gap: 16px;">
                {{-- 1. Pilih Batch --}}
                <div>
                    <label style="display: block; font-size: 13px; font-weight: 600; color: #071E49; margin-bottom: 6px;">
                        Pilih Batch Produksi <span style="color: #EF4444;">*</span>
                    </label>
                    <select name="production_batch_id" id="hasil_batch_select" required class="form-select" onchange="handleHasilBatchChange(this)" style="width: 100%; height: 42px; font-size: 13px; border-radius: 8px; border: 1px solid #CBD5E1; padding: 0 12px; background: #FFFFFF;">
                        <option value="">-- Pilih Batch Pengolahan Selesai --</option>
                        @forelse ($eligibleBatches as $eBatch)
                            <option value="{{ $eBatch->id }}"
                                data-target="{{ $eBatch->target_porsi }}"
                                data-menu="{{ $eBatch->menu?->nama ?? '-' }}"
                                data-kategori="{{ $eBatch->menu?->kategori_label ?? '-' }}"
                                data-tgl="{{ $eBatch->production_date ? $eBatch->production_date->format('d/m/Y') : '-' }}"
                                data-porsi="{{ ucfirst($eBatch->recipe?->ukuran_porsi ?? 'Standar') }}"
                                data-steps="{{ $eBatch->processingSteps->count() }}">
                                {{ $eBatch->batch_code }} - {{ $eBatch->menu?->nama }} (Target: {{ number_format($eBatch->target_porsi, 0, ',', '.') }} ps)
                            </option>
                        @empty
                            <option value="" disabled>Tidak ada batch yang siap dicatat (semua tahap harus done)</option>
                        @endforelse
                    </select>
                    <span style="font-size: 11.5px; color: #64748B; margin-top: 4px; display: block;">
                        Hanya menampilkan batch <em>processing</em> yang semua tahapan kerjanya sudah selesai (done).
                    </span>
                </div>

                {{-- Batch Info Card (Ringkasan Batch Hanya Baca) --}}
                <div id="batch_info_box" style="display: none; background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 10px; padding: 14px 16px;">
                    <div style="font-size: 12px; font-weight: 700; color: #071E49; margin-bottom: 8px; text-transform: uppercase; letter-spacing: 0.03em;">
                        Ringkasan Batch (Hanya Baca)
                    </div>
                    <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 10px; font-size: 12.5px;">
                        <div>
                            <span style="color: #64748B;">Menu Resep:</span>
                            <div style="font-weight: 600; color: #071E49;" id="info_menu">-</div>
                        </div>
                        <div>
                            <span style="color: #64748B;">Target Produksi:</span>
                            <div style="font-weight: 700; color: #0284C7;" id="info_target">-</div>
                        </div>
                        <div>
                            <span style="color: #64748B;">Tgl Produksi:</span>
                            <div style="font-weight: 500; color: #334155;" id="info_tgl">-</div>
                        </div>
                        <div>
                            <span style="color: #64748B;">Ukuran Porsi:</span>
                            <div style="font-weight: 500; color: #334155;" id="info_porsi">-</div>
                        </div>
                        <div style="grid-column: span 2; padding-top: 4px; border-top: 1px dashed #E2E8F0;">
                            <span style="color: #64748B;">Status Pengolahan:</span>
                            <div style="font-weight: 600; color: #15803D; display: flex; align-items: center; gap: 4px;">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                    <polyline points="20 6 9 17 4 12"></polyline>
                                </svg>
                                <span id="info_steps_done">Semua tahap pengolahan selesai (Done)</span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- 2. portions_ready --}}
                <div>
                    <label style="display: block; font-size: 13px; font-weight: 600; color: #071E49; margin-bottom: 6px;">
                        Jumlah Porsi Siap Kirim (portions_ready) <span style="color: #EF4444;">*</span>
                    </label>
                    <div style="position: relative;">
                        <input type="number" name="portions_ready" id="input_portions_ready" required min="0" step="1" placeholder="Contoh: 490" oninput="calculateHasilSusut()" class="form-input" style="width: 100%; height: 42px; font-size: 14px; font-weight: 600; border-radius: 8px; border: 1px solid #CBD5E1; padding: 0 70px 0 12px; box-sizing: border-box;">
                        <span style="position: absolute; right: 12px; top: 50%; transform: translateY(-50%); font-size: 13px; color: #64748B; font-weight: 600; pointer-events: none;">Porsi</span>
                    </div>

                    {{-- Dynamic Warning: Melebihi target [BARU: Bukan blokir, hanya peringatan] --}}
                    <div id="warning_excess" style="display: none; margin-top: 8px; background: #FFFBEB; border: 1px solid #FDE68A; border-radius: 8px; padding: 10px 12px; font-size: 12px; color: #92400E;">
                        <div style="display: flex; align-items: flex-start; gap: 8px;">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="flex-shrink: 0; margin-top: 1px; color: #D97706;">
                                <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path>
                                <line x1="12" y1="9" x2="12" y2="13"></line>
                                <line x1="12" y1="17" x2="12.01" y2="17"></line>
                            </svg>
                            <div>
                                <strong>Peringatan Porsi Melebihi Target:</strong> Porsi siap kirim (<span id="warn_ready_val">0</span> porsi) melebihi target awal (<span id="warn_target_val">0</span> porsi) sebanyak <strong>+<span id="warn_excess_diff">0</span> porsi</strong>. Data tetap dapat disimpan dan susut tercatat 0 porsi.
                            </div>
                        </div>
                    </div>

                    {{-- Dynamic Info Otomatis: Susut --}}
                    <div id="info_susut_box" style="display: none; margin-top: 8px; background: #EFF6FF; border: 1px solid #BFDBFE; border-radius: 8px; padding: 10px 12px; font-size: 12px; color: #1E40AF;">
                        <div style="display: flex; align-items: flex-start; gap: 8px;">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="flex-shrink: 0; margin-top: 1px;">
                                <circle cx="12" cy="12" r="10"></circle>
                                <line x1="12" y1="16" x2="12" y2="12"></line>
                                <line x1="12" y1="8" x2="12.01" y2="8"></line>
                            </svg>
                            <div id="susut_message_text">
                                <strong>Info Otomatis Susut:</strong> Ditemukan susut sebanyak <strong id="susut_count_val" style="color: #DC2626;">0</strong> porsi. Setelah disimpan, Anda dapat langsung mencatat susut ini ke formulir Limbah tipe <em>PRODUCTION_LOSS</em>.
                            </div>
                        </div>
                    </div>
                </div>

                {{-- 3. reported_condition --}}
                <div>
                    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 6px;">
                        <label style="font-size: 13px; font-weight: 600; color: #071E49;">
                            Kondisi Makanan Siap Saji (reported_condition) <span style="color: #EF4444;">*</span>
                        </label>
                        <span style="font-size: 11px; color: #64748B;">Teks bebas, bukan hasil uji lab</span>
                    </div>
                    <input type="text" name="reported_condition" id="input_condition" required placeholder="Contoh: Baik, rasa gurih, suhu hangat dan siap saji" class="form-input" style="width: 100%; height: 42px; font-size: 13px; border-radius: 8px; border: 1px solid #CBD5E1; padding: 0 12px; box-sizing: border-box;">

                    {{-- Quick Suggestion Chips --}}
                    <div style="display: flex; gap: 6px; flex-wrap: wrap; margin-top: 6px;">
                        <button type="button" onclick="setConditionText('Kondisi baik, rasa dan aroma sesuai standar resep')" style="background: #F1F5F9; border: 1px solid #E2E8F0; padding: 2px 8px; border-radius: 6px; font-size: 11px; color: #475569; cursor: pointer;">
                            + Baik & standar resep
                        </button>
                        <button type="button" onclick="setConditionText('Suhu hangat, kemasan higienis, porsi lengkap')" style="background: #F1F5F9; border: 1px solid #E2E8F0; padding: 2px 8px; border-radius: 6px; font-size: 11px; color: #475569; cursor: pointer;">
                            + Suhu hangat & higienis
                        </button>
                        <button type="button" onclick="setConditionText('Kualitas prima, tertata rapi siap distribusi')" style="background: #F1F5F9; border: 1px solid #E2E8F0; padding: 2px 8px; border-radius: 6px; font-size: 11px; color: #475569; cursor: pointer;">
                            + Prima siap kirim
                        </button>
                    </div>
                </div>

                {{-- 4. Catatan (Opsional) --}}
                <div>
                    <label style="display: block; font-size: 13px; font-weight: 600; color: #071E49; margin-bottom: 6px;">
                        Catatan Tambahan <span style="font-size: 11.5px; font-weight: 400; color: #94A3B8;">(Opsional)</span>
                    </label>
                    <textarea name="note" rows="2" placeholder="Catatan wadah kemasan, nomor box penyimpanan, dll..." class="form-input" style="width: 100%; font-size: 13px; border-radius: 8px; border: 1px solid #CBD5E1; padding: 8px 12px; box-sizing: border-box; resize: vertical;"></textarea>
                </div>

                {{-- 5. Bukti Foto --}}
                <div>
                    <label style="display: block; font-size: 13px; font-weight: 600; color: #071E49; margin-bottom: 6px;">
                        Foto Bukti Porsi Siap Kirim <span style="color: #EF4444;">*</span>
                    </label>
                    <div style="border: 2px dashed #CBD5E1; border-radius: 10px; padding: 16px; text-align: center; background: #F8FAFC; transition: all 0.2s;" id="dropzone_hasil">
                        <input type="file" name="evidence_file" id="evidence_file_input" required accept="image/*" onchange="previewEvidenceImage(this)" style="display: none;">
                        <label for="evidence_file_input" style="cursor: pointer; display: flex; flex-direction: column; align-items: center; gap: 8px;">
                            <div style="width: 42px; height: 42px; border-radius: 50%; background: #EFF6FF; color: #0284C7; display: flex; align-items: center; justify-content: center;">
                                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"></path>
                                    <circle cx="12" cy="13" r="4"></circle>
                                </svg>
                            </div>
                            <div>
                                <span style="font-size: 13px; font-weight: 600; color: #042A70;">Pilih file foto porsi</span>
                                <span style="font-size: 12px; color: #64748B;"> atau seret ke sini</span>
                            </div>
                            <span style="font-size: 11px; color: #94A3B8;">Format JPG, PNG, WEBP maks. 5MB (Minimal 1 foto)</span>
                        </label>
                        <div id="image_preview_box" style="display: none; margin-top: 12px; position: relative;">
                            <img id="preview_img" src="" alt="Preview Bukti" style="max-height: 140px; border-radius: 8px; border: 1px solid #CBD5E1; object-fit: cover;">
                            <button type="button" onclick="clearEvidencePreview()" style="position: absolute; top: -8px; right: calc(50% - 80px); background: #EF4444; color: #FFFFFF; border: none; border-radius: 50%; width: 22px; height: 22px; cursor: pointer; font-size: 12px; display: flex; align-items: center; justify-content: center;">&times;</button>
                        </div>
                    </div>

                    {{-- Checkbox Jadikan Publik --}}
                    <div style="margin-top: 10px; display: flex; align-items: center; gap: 8px;">
                        <input type="checkbox" name="is_public" id="is_public_checkbox" value="1" style="width: 16px; height: 16px; accent-color: #042A70; cursor: pointer;">
                        <label for="is_public_checkbox" style="font-size: 12.5px; color: #334155; cursor: pointer;">
                            Jadikan foto ini publik (opsional ditampilkan di galeri transparansi SPPG)
                        </label>
                    </div>
                </div>
            </div>

            {{-- Modal Footer --}}
            <div style="display: flex; align-items: center; justify-content: flex-end; gap: 10px; padding: 16px 24px; border-top: 1px solid #F1F5F9; background: #F8FAFC; border-radius: 0 0 16px 16px;">
                <button type="button" onclick="closeAddHasilModal()" class="btn-secondary" style="padding: 9px 18px; font-size: 13px;">
                    Batal
                </button>
                <button type="submit" class="btn-primary" style="padding: 9px 20px; font-size: 13px;">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                        <polyline points="20 6 9 17 4 12"></polyline>
                    </svg>
                    <span>Simpan Hasil Produksi</span>
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


