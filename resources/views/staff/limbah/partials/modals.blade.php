{{-- Modal Catat Limbah & Surplus Baru --}}
<div id="modalAddLimbah" class="limbah-modal-backdrop" style="display: none;">
    <div class="limbah-modal-dialog">
        <form method="POST" action="{{ route('staff.limbah.store') }}" enctype="multipart/form-data" id="formAddLimbah">
            @csrf

            {{-- Header --}}
            <div style="display: flex; align-items: center; justify-content: space-between; padding: 20px 24px; border-bottom: 1px solid #F1F5F9;">
                <div>
                    <h3 style="font-size: 18px; font-weight: 700; color: #071E49; margin: 0;">Catat Limbah & Surplus</h3>
                    <p style="font-size: 12.5px; color: #64748B; margin: 3px 0 0;">Dokumentasikan susut olah, sisa konsumsi, surplus porsi, atau pembuangan bahan.</p>
                </div>
                <button type="button" onclick="closeAddLimbahModal()" style="background: none; border: none; color: #94A3B8; cursor: pointer; padding: 4px; border-radius: 8px;">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="18" y1="6" x2="6" y2="18"></line>
                        <line x1="6" y1="6" x2="18" y2="18"></line>
                    </svg>
                </button>
            </div>

            {{-- Body --}}
            <div style="padding: 20px 24px; display: flex; flex-direction: column; gap: 16px;">
                {{-- 1. Pilihan Tipe Limbah --}}
                <div>
                    <label style="display: block; font-size: 13px; font-weight: 600; color: #071E49; margin-bottom: 6px;">
                        Tipe Limbah / Surplus <span style="color: #EF4444;">*</span>
                    </label>
                    <select name="record_type" id="limbah_type_select" required onchange="handleLimbahTypeChange(this.value)" class="form-select" style="width: 100%; height: 42px; font-size: 13px; border-radius: 8px; border: 1px solid #CBD5E1; padding: 0 12px; background: #FFFFFF; font-weight: 600;">
                        <option value="PRODUCTION_LOSS">PRODUCTION_LOSS (Susut Pengolahan Porsi)</option>
                        <option value="FOOD_WASTE">FOOD_WASTE (Makanan Sisa Pasca Distribusi)</option>
                        <option value="SURPLUS">SURPLUS (Porsi Matang Berlebih)</option>
                        <option value="MATERIAL_DISPOSAL">MATERIAL_DISPOSAL (Bahan Baku Dibuang / Rusak)</option>
                    </select>
                </div>

                {{-- 2. Field Dinamis: Batch (Untuk tipe Porsi) --}}
                <div id="sec_batch">
                    <label style="display: block; font-size: 13px; font-weight: 600; color: #071E49; margin-bottom: 6px;">
                        Pilih Batch Produksi <span style="color: #EF4444;">*</span>
                    </label>
                    <select name="production_batch_id" id="limbah_batch_select" onchange="handleLimbahBatchChange(this)" class="form-select" style="width: 100%; height: 42px; font-size: 13px; border-radius: 8px; border: 1px solid #CBD5E1; padding: 0 12px; background: #FFFFFF;">
                        <option value="">-- Pilih Batch Produksi --</option>
                        @foreach ($eligibleBatches as $b)
                            <option value="{{ $b->id }}" data-code="{{ $b->batch_code }}" data-actual="{{ $b->actual_porsi ?? 0 }}" data-target="{{ $b->target_porsi }}">
                                {{ $b->batch_code }} - {{ $b->menu?->nama }} (Siap: {{ number_format($b->actual_porsi ?? 0, 0, ',', '.') }} ps) [{{ $b->status_label }}]
                            </option>
                        @endforeach
                    </select>
                    <span style="font-size: 11.5px; color: #64748B; margin-top: 4px; display: block;">
                        Hanya menampilkan batch yang berstatus <em>ready</em>, <em>distributed</em>, atau <em>completed</em>.
                    </span>
                </div>

                {{-- 3. Field Dinamis: Pengiriman (Khusus FOOD_WASTE, Opsional) --}}
                <div id="sec_distribution" style="display: none;">
                    <label style="display: block; font-size: 13px; font-weight: 600; color: #071E49; margin-bottom: 6px;">
                        Tujuan Pengiriman Terkait <span style="font-size: 11px; font-weight: 400; color: #94A3B8;">(Opsional)</span>
                    </label>
                    <select name="distribution_id" id="limbah_distribution_select" class="form-select" style="width: 100%; height: 42px; font-size: 13px; border-radius: 8px; border: 1px solid #CBD5E1; padding: 0 12px; background: #FFFFFF;">
                        <option value="">-- Semua / Tanpa Pengiriman Spesifik --</option>
                    </select>
                    <span style="font-size: 11.5px; color: #64748B; margin-top: 4px; display: block;">
                        Pilih tujuan distribusi jika sisa makanan berasal dari sekolah/penerima tertentu.
                    </span>
                </div>

                {{-- 4. Field Dinamis: Lot Bahan (Khusus MATERIAL_DISPOSAL) --}}
                <div id="sec_lot" style="display: none;">
                    <label style="display: block; font-size: 13px; font-weight: 600; color: #071E49; margin-bottom: 6px;">
                        Pilih Lot Bahan Baku <span style="color: #EF4444;">*</span>
                    </label>
                    <select name="material_lot_id" id="limbah_lot_select" onchange="handleLimbahLotChange(this)" class="form-select" style="width: 100%; height: 42px; font-size: 13px; border-radius: 8px; border: 1px solid #CBD5E1; padding: 0 12px; background: #FFFFFF;">
                        <option value="">-- Pilih Lot Bahan (Masih Ada Stok) --</option>
                        @foreach ($eligibleLots as $l)
                            <option value="{{ $l->id }}" data-satuan="{{ $l->material?->satuan ?? 'unit' }}" data-qty="{{ $l->current_qty }}">
                                {{ $l->lot_number }} ({{ $l->barcode }}) - {{ $l->material?->nama }} [Sisa: {{ (float) $l->current_qty }} {{ $l->material?->satuan }}]
                            </option>
                        @endforeach
                    </select>
                    <span style="font-size: 11.5px; color: #64748B; margin-top: 4px; display: block;">
                        Hanya menampilkan lot bahan yang masih memiliki sisa stok (current_qty > 0).
                    </span>
                </div>

                {{-- Dynamic Hint Box --}}
                <div id="limbah_hint_box" class="limbah-helper-box" style="display: none; background: #EFF6FF; border-color: #BFDBFE; color: #1E40AF;">
                    <div id="limbah_hint_text">Petunjuk batas isian</div>
                </div>

                {{-- 5. Jumlah (Qty) dengan Satuan Dinamis --}}
                <div>
                    <label style="display: block; font-size: 13px; font-weight: 600; color: #071E49; margin-bottom: 6px;">
                        Jumlah Qty <span style="color: #EF4444;">*</span>
                    </label>
                    <div style="position: relative;">
                        <input type="number" name="qty" id="limbah_qty_input" required min="0.001" step="any" placeholder="Masukkan jumlah..." class="form-input" style="width: 100%; height: 42px; font-size: 14px; font-weight: 600; border-radius: 8px; border: 1px solid #CBD5E1; padding: 0 80px 0 12px; box-sizing: border-box;">
                        <span id="limbah_unit_label" style="position: absolute; right: 12px; top: 50%; transform: translateY(-50%); font-size: 13px; color: #64748B; font-weight: 600; pointer-events: none;">Porsi</span>
                    </div>
                </div>

                {{-- 6. Alasan --}}
                <div>
                    <label style="display: block; font-size: 13px; font-weight: 600; color: #071E49; margin-bottom: 6px;">
                        Penyebab / Alasan <span style="color: #EF4444;">*</span>
                    </label>
                    <textarea name="reason" rows="3" required placeholder="Jelaskan alasan atau penyebab susut, sisa, surplus, atau pembuangan bahan..." class="form-input" style="width: 100%; font-size: 13px; border-radius: 8px; border: 1px solid #CBD5E1; padding: 8px 12px; box-sizing: border-box; resize: vertical;"></textarea>
                </div>

                {{-- 7. Waktu Pencatatan --}}
                <div>
                    <label style="display: block; font-size: 13px; font-weight: 600; color: #071E49; margin-bottom: 6px;">
                        Waktu Pencatatan
                    </label>
                    <input type="datetime-local" name="recorded_at" value="{{ now()->format('Y-m-d\TH:i') }}" class="form-input" style="width: 100%; height: 40px; font-size: 13px; border-radius: 8px; border: 1px solid #CBD5E1; padding: 0 12px; box-sizing: border-box;">
                </div>

                {{-- 8. Bukti Foto --}}
                <div>
                    <label style="display: block; font-size: 13px; font-weight: 600; color: #071E49; margin-bottom: 6px;">
                        Foto Bukti Fisik <span style="font-size: 11.5px; font-weight: 400; color: #64748B;">(Disarankan)</span>
                    </label>
                    <div class="limbah-upload-dropzone">
                        <input type="file" name="evidence_file" id="limbah_evidence_file_input" accept="image/*" onchange="previewLimbahEvidence(this)" style="display: none;">
                        <label for="limbah_evidence_file_input" style="cursor: pointer; display: flex; flex-direction: column; align-items: center; gap: 8px;">
                            <div style="width: 44px; height: 44px; border-radius: 50%; background: #EFF6FF; color: #0284C7; display: flex; align-items: center; justify-content: center;">
                                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"></path>
                                    <circle cx="12" cy="13" r="4"></circle>
                                </svg>
                            </div>
                            <div>
                                <span style="font-size: 13px; font-weight: 600; color: #071E49;">Pilih file foto bukti</span>
                                <span style="font-size: 12px; color: #64748B;"> atau seret ke sini</span>
                            </div>
                            <span style="font-size: 11px; color: #94A3B8;">Format JPG, PNG, WEBP maks. 5MB</span>
                        </label>
                        <div id="limbah_image_preview_box" style="display: none; margin-top: 12px; position: relative;">
                            <img id="limbah_preview_img" src="" alt="Preview Bukti" style="max-height: 140px; border-radius: 8px; border: 1px solid #CBD5E1; object-fit: cover;">
                            <button type="button" onclick="clearLimbahEvidence()" style="position: absolute; top: -8px; right: calc(50% - 80px); background: #EF4444; color: #FFFFFF; border: none; border-radius: 50%; width: 22px; height: 22px; cursor: pointer; font-size: 12px; display: flex; align-items: center; justify-content: center;">&times;</button>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Footer --}}
            <div style="display: flex; align-items: center; justify-content: flex-end; gap: 10px; padding: 16px 24px; border-top: 1px solid #F1F5F9; background: #F8FAFC; border-radius: 0 0 16px 16px;">
                <button type="button" onclick="closeAddLimbahModal()" class="btn-secondary" style="padding: 9px 18px; font-size: 13px;">
                    Batal
                </button>
                <button type="submit" class="btn-primary" style="padding: 9px 20px; font-size: 13px;">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                        <polyline points="20 6 9 17 4 12"></polyline>
                    </svg>
                    <span>Simpan Catatan</span>
                </button>
            </div>
        </form>
    </div>
</div>

{{-- Modal Ajukan Koreksi Pembatalan (Void) --}}
<div id="modalKoreksiLimbah" class="limbah-modal-backdrop" style="display: none;">
    <div class="limbah-modal-dialog modal-sm">
        <form method="POST" action="{{ route('staff.limbah.koreksi') }}">
            @csrf
            <input type="hidden" name="waste_record_id" id="koreksi_record_id">

            {{-- Header --}}
            <div style="display: flex; align-items: center; justify-content: space-between; padding: 18px 22px; border-bottom: 1px solid #F1F5F9;">
                <div>
                    <h3 style="font-size: 16px; font-weight: 700; color: #071E49; margin: 0;">Ajukan Koreksi Pembatalan</h3>
                    <p style="font-size: 12px; color: #64748B; margin: 2px 0 0;">Catatan limbah salah catat akan di-voided setelah disetujui Kepala SPPG.</p>
                </div>
                <button type="button" onclick="closeKoreksiLimbahModal()" style="background: none; border: none; color: #94A3B8; cursor: pointer; padding: 4px;">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="18" y1="6" x2="6" y2="18"></line>
                        <line x1="6" y1="6" x2="18" y2="18"></line>
                    </svg>
                </button>
            </div>

            {{-- Body --}}
            <div style="padding: 18px 22px; display: flex; flex-direction: column; gap: 14px;">
                <div id="koreksi_record_info" style="background: #FFFBEB; border: 1px solid #FDE68A; border-radius: 8px; padding: 10px 14px; font-size: 12.5px; color: #92400E;">
                    Catatan limbah ini akan diajukan untuk dibatalkan.
                </div>

                <div>
                    <label style="display: block; font-size: 12.5px; font-weight: 600; color: #071E49; margin-bottom: 5px;">
                        Alasan Pengajuan Koreksi <span style="color: #EF4444;">*</span>
                    </label>
                    <textarea name="reason" rows="3" required placeholder="Tuliskan alasan detail mengapa catatan ini salah dan perlu dibatalkan..." class="form-input" style="width: 100%; font-size: 13px; border-radius: 8px; border: 1px solid #CBD5E1; padding: 8px 12px; box-sizing: border-box; resize: vertical;"></textarea>
                    <span style="font-size: 11px; color: #64748B; margin-top: 3px; display: block;">
                        Untuk pembuangan bahan baku, persetujuan pembatalan akan otomatis membalikkan sisa stok lot (VOID_REVERSAL).
                    </span>
                </div>
            </div>

            {{-- Footer --}}
            <div style="display: flex; align-items: center; justify-content: flex-end; gap: 10px; padding: 14px 22px; border-top: 1px solid #F1F5F9; background: #F8FAFC; border-radius: 0 0 16px 16px;">
                <button type="button" onclick="closeKoreksiLimbahModal()" class="btn-secondary" style="padding: 8px 16px; font-size: 12.5px;">
                    Batal
                </button>
                <button type="submit" class="btn-primary" style="background-color: #D97706; border-color: #D97706; padding: 8px 18px; font-size: 12.5px;">
                    Kirim Pengajuan Koreksi
                </button>
            </div>
        </form>
    </div>
</div>

{{-- Modal Preview Foto Pop-up --}}
<div id="modalPreviewFoto" class="limbah-modal-backdrop" style="display: none;" onclick="closePreviewModal(event)">
    <div class="limbah-modal-dialog" style="max-width: 600px; padding: 0; overflow: hidden;" onclick="event.stopPropagation()">
        <div style="display: flex; align-items: center; justify-content: space-between; padding: 14px 18px; border-bottom: 1px solid #F1F5F9; background: #F8FAFC;">
            <h4 id="preview_modal_title" style="font-size: 14px; font-weight: 700; color: #071E49; margin: 0;">Foto Bukti</h4>
            <button type="button" onclick="closePreviewModal()" style="background: none; border: none; color: #94A3B8; cursor: pointer; padding: 4px;">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
            </button>
        </div>
        <div style="padding: 16px; text-align: center; background: #0F172A; display: flex; align-items: center; justify-content: center; min-height: 240px;">
            <img id="preview_modal_img" src="" alt="Bukti Fisik" style="max-height: 480px; max-width: 100%; border-radius: 8px; object-fit: contain;">
        </div>
        <div style="padding: 12px 18px; font-size: 12px; color: #64748B; background: #F8FAFC;">
            <span id="preview_modal_desc">-</span>
        </div>
    </div>
</div>

