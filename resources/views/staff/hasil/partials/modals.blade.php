{{-- Modal Catat Hasil Produksi Baru --}}
<div id="modalAddHasil" class="modal-backdrop modal-backdrop-custom" style="display: none; position: fixed; inset: 0; background: rgba(15, 23, 42, 0.6); backdrop-filter: blur(4px); z-index: 99999; align-items: center; justify-content: center; padding: 16px;">
    <div class="modal-dialog" style="background: #FFFFFF; border-radius: 16px; width: 100%; max-width: 820px; max-height: 92vh; overflow-y: auto; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);">
        <form method="POST" action="{{ route('staff.hasil.store') }}" enctype="multipart/form-data" id="formAddHasil">
            @csrf

            {{-- Modal Header --}}
            <div style="display: flex; align-items: center; justify-content: space-between; padding: 18px 24px; border-bottom: 1px solid #F1F5F9; position: sticky; top: 0; background: #FFFFFF; z-index: 10;">
                <div>
                    <h3 style="font-size: 18px; font-weight: 700; color: #071E49; margin: 0;">Catat Hasil Produksi &amp; Uji Fisik Kelayakan Saji</h3>
                    <p style="font-size: 12px; color: #64748B; margin: 3px 0 0;">Pemeriksaan organoleptik, verifikasi ahli gizi, dan penetapan batas waktu kritis konsumsi.</p>
                </div>
                <button type="button" onclick="closeAddHasilModal()" style="background: none; border: none; color: #94A3B8; cursor: pointer; padding: 6px; border-radius: 8px; transition: all 0.2s;" title="Tutup Modal">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="18" y1="6" x2="6" y2="18"></line>
                        <line x1="6" y1="6" x2="18" y2="18"></line>
                    </svg>
                </button>
            </div>

            {{-- Modal Body --}}
            <div style="padding: 20px 24px; display: flex; flex-direction: column; gap: 18px;">
                {{-- 1. Pilih Batch & Kategori Form (Responsive Grid) --}}
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 14px;">
                    <div>
                        <label for="hasil_batch_select" style="display: block; font-size: 13px; font-weight: 600; color: #071E49; margin-bottom: 6px;">
                            Batch Produksi <span style="color: #EF4444;">*</span>
                        </label>
                        <select name="production_batch_id" id="hasil_batch_select" required class="form-select" onchange="handleHasilBatchChange(this)" style="width: 100%; height: 42px; font-size: 13px; border-radius: 8px; border: 1px solid #CBD5E1; padding: 0 12px; background: #FFFFFF;">
                            <option value="">-- Pilih Batch Selesai Masak --</option>
                            @forelse ($eligibleBatches as $eBatch)
                                <option value="{{ $eBatch->id }}"
                                    data-target="{{ $eBatch->target_porsi }}"
                                    data-menu="{{ $eBatch->menu?->nama ?? '-' }}"
                                    data-kategori="{{ $eBatch->menu?->kategori ?? 'makanan_siap_santap' }}"
                                    data-kategori-label="{{ $eBatch->menu?->kategori_label ?? '-' }}"
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

                    <div>
                        <label for="hasil_form_type" style="display: block; font-size: 13px; font-weight: 600; color: #071E49; margin-bottom: 6px;">
                            Kategori Jenis Form QC <span style="color: #EF4444;">*</span>
                        </label>
                        <select name="form_type" id="hasil_form_type" required class="form-select" style="width: 100%; height: 42px; font-size: 13px; border-radius: 8px; border: 1px solid #CBD5E1; padding: 0 12px; background: #FFFFFF;">
                            @foreach ($formTypes as $val => $label)
                                <option value="{{ $val }}" {{ (old('form_type', 'makanan_siap_santap') === $val) ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
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

                {{-- 2. Tenaga Ahli Gizi Penanggung Jawab --}}
                <div style="background: #F0FDF4; border: 1px solid #BBF7D0; border-radius: 12px; padding: 14px 16px;">
                    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 10px;">
                        <label style="font-size: 13px; font-weight: 700; color: #166534; display: flex; align-items: center; gap: 6px; margin: 0;">
                            <span>Tenaga Ahli Gizi Penanggung Jawab QC</span>
                            <span style="color: #EF4444;">*</span>
                        </label>
                        <span style="font-size: 11px; font-weight: 600; color: #15803D; background: #DCFCE7; padding: 2px 8px; border-radius: 9999px;">
                            Verifikator Mutu
                        </span>
                    </div>

                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 12px;">
                        <div>
                            <label for="hasil_nutritionist_select" style="display: block; font-size: 12px; font-weight: 600; color: #166534; margin-bottom: 5px;">
                                Pilih Staf SPPG Terdaftar
                            </label>
                            <select name="nutritionist_id" id="hasil_nutritionist_select" onchange="handleNutritionistSelect(this)" class="form-select" style="width: 100%; height: 40px; font-size: 13px; border-radius: 8px; border: 1px solid #86EFAC; padding: 0 12px; background: #FFFFFF; color: #0F172A;">
                                <option value="">-- Pilih dari Daftar Staf SPPG --</option>
                                @foreach ($staffList as $stf)
                                    <option value="{{ $stf->id }}" data-nama="{{ $stf->nama }}" {{ (old('nutritionist_id', auth('staff')->id()) == $stf->id) ? 'selected' : '' }}>
                                        {{ $stf->nama }} (Staf SPPG)
                                    </option>
                                @endforeach
                                <option value="" data-custom="true">-- Ketik Nama Ahli Gizi Lain (Luar Sistem) --</option>
                            </select>
                        </div>

                        <div>
                            <label for="hasil_nutritionist_name" style="display: block; font-size: 12px; font-weight: 600; color: #166534; margin-bottom: 5px;">
                                Nama Lengkap &amp; Gelar Penanggung Jawab <span style="color: #EF4444;">*</span>
                            </label>
                            <input type="text" name="nutritionist_name" id="hasil_nutritionist_name" value="{{ old('nutritionist_name', auth('staff')->user()?->nama ?? '') }}" required placeholder="Contoh: Fadhilah Ayu, S.Gz" class="form-input" style="width: 100%; height: 40px; font-size: 13px; border-radius: 8px; border: 1px solid #86EFAC; padding: 0 12px; box-sizing: border-box; background: #FFFFFF; color: #0F172A;">
                        </div>
                    </div>
                    <div style="font-size: 11.5px; color: #15803D; margin-top: 8px; line-height: 1.4;">
                        Nama ini akan dicantumkan pada label sertifikasi kelayakan saji batch serta riwayat ketertelusuran publik (QR Code).
                    </div>
                </div>

                {{-- 3. Jam Pengecekan, Estimasi Jam Distribusi & Waktu Kritis Batas Maksimal Dimakan --}}
                <div style="background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 12px; padding: 16px; display: flex; flex-direction: column; gap: 14px;">
                    <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 8px;">
                        <div style="font-size: 13.5px; font-weight: 700; color: #071E49; display: flex; align-items: center; gap: 6px;">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#2563EB" stroke-width="2">
                                <circle cx="12" cy="12" r="10"></circle>
                                <polyline points="12 6 12 12 16 14"></polyline>
                            </svg>
                            <span>Pencatatan Waktu Operasional &amp; Batas Kritis Konsumsi</span>
                        </div>
                        <button type="button" onclick="autoUpdateCriticalTimes(true)" style="background: none; border: none; font-size: 12px; font-weight: 600; color: #2563EB; cursor: pointer; text-decoration: underline; padding: 2px 4px;">
                            Reset ke Waktu Sekarang
                        </button>
                    </div>

                    {{-- Baris 1: 2 Kolom untuk Jam Pengecekan & Estimasi Distribusi --}}
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 14px;">
                        {{-- 1. Jam Pengecekan / Uji Fisik (checked_at) --}}
                        <div>
                            <label for="input_checked_at" style="display: block; font-size: 12.5px; font-weight: 600; color: #1E293B; margin-bottom: 5px;">
                                Jam Uji Fisik (QC / Selesai Masak) <span style="color: #EF4444;">*</span>
                            </label>
                            <input type="datetime-local" name="checked_at" id="input_checked_at" required onchange="onCheckedAtChange(this.value)" value="{{ old('checked_at') }}" class="form-input" style="width: 100%; height: 40px; font-size: 13px; border-radius: 8px; border: 1px solid #CBD5E1; padding: 0 10px; box-sizing: border-box; background: #FFFFFF;">
                            <span style="font-size: 11px; color: #64748B; display: block; margin-top: 4px;">Pemeriksaan organoleptik selesai pengolahan</span>
                        </div>

                        {{-- 2. Estimasi Jam Distribusi (estimated_distribution_at) --}}
                        <div>
                            <label for="input_estimated_distribution_at" style="display: block; font-size: 12.5px; font-weight: 600; color: #1E293B; margin-bottom: 5px;">
                                Estimasi Jam Pengiriman (Distribusi)
                            </label>
                            <input type="datetime-local" name="estimated_distribution_at" id="input_estimated_distribution_at" value="{{ old('estimated_distribution_at') }}" class="form-input" style="width: 100%; height: 40px; font-size: 13px; border-radius: 8px; border: 1px solid #CBD5E1; padding: 0 10px; box-sizing: border-box; background: #FFFFFF;">
                            <span style="font-size: 11px; color: #64748B; display: block; margin-top: 4px;">Perkiraan armada berangkat menuju sekolah sasaran</span>
                        </div>
                    </div>

                    {{-- Baris 2: Waktu Kritis Batas Maksimal Dimakan (Ditaruh Bawahnya agar lega & menonjol) --}}
                    <div style="background: #FEF2F2; border: 1.5px solid #FCA5A5; border-radius: 10px; padding: 14px 16px; display: flex; flex-direction: column; gap: 10px;">
                        <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 8px;">
                            <div>
                                <strong style="font-size: 13px; color: #991B1B; text-transform: uppercase; letter-spacing: 0.3px;">Waktu Kritis: Batas Maksimal Dimakan (Consume By)</strong>
                                <span style="color: #EF4444; font-weight: 700;">*</span>
                            </div>
                            <button type="button" onclick="setConsumeByPlusHours(4)" style="background: #FEE2E2; border: 1px solid #F87171; color: #991B1B; font-size: 11.5px; font-weight: 700; border-radius: 6px; padding: 4px 10px; cursor: pointer; transition: all 0.15s;" title="Set otomatis 4 jam dari jam uji fisik">
                                +4 Jam Otomatis
                            </button>
                        </div>

                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 12px; align-items: center;">
                            <div>
                                <input type="datetime-local" name="consume_by" id="input_consume_by" required value="{{ old('consume_by') }}" class="form-input" style="width: 100%; height: 42px; font-size: 14px; font-weight: 700; color: #991B1B; border-radius: 8px; border: 1.5px solid #EF4444; background: #FFFFFF; padding: 0 12px; box-sizing: border-box;">
                                <span style="font-size: 11px; color: #B91C1C; font-weight: 600; display: block; margin-top: 4px;">Waktu kritis batas konsumsi siswa di sekolah</span>
                            </div>
                            <div style="font-size: 11.5px; color: #7F1D1D; line-height: 1.45; background: rgba(254, 226, 226, 0.6); padding: 8px 12px; border-radius: 8px; border: 1px dashed #FCA5A5;">
                                <em>Standar Keamanan Pangan MBG:</em> Makanan siap santap wajib dikonsumsi <strong>maksimal 4 jam</strong> setelah proses masak untuk mencegah risiko penurunan mutu dan perkembangbiakan bakteri.
                            </div>
                        </div>
                    </div>
                </div>

                {{-- 4. Porsi Siap Kirim --}}
                <div>
                    <label for="input_portions_ready" style="display: block; font-size: 13px; font-weight: 600; color: #071E49; margin-bottom: 6px;">
                        Jumlah Porsi Siap Kirim <span style="color: #EF4444;">*</span>
                    </label>
                    <div style="position: relative;">
                        <input type="number" name="portions_ready" id="input_portions_ready" required min="0" step="1" value="{{ old('portions_ready') }}" placeholder="0" oninput="calculateHasilSusut()" class="form-input" style="width: 100%; height: 42px; font-size: 14px; font-weight: 600; border-radius: 8px; border: 1px solid #CBD5E1; padding: 0 70px 0 12px; box-sizing: border-box;">
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

                {{-- 5. Matrix Uji Fisik (Uji Organoleptik Saji) --}}
                <div style="border: 1px solid #E2E8F0; border-radius: 12px; padding: 16px; background: #FFFFFF;">
                    <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 10px; margin-bottom: 14px;">
                        <div>
                            <div style="font-size: 14px; font-weight: 700; color: #071E49;">Matrix Uji Fisik (Uji Organoleptik Kelayakan Saji)</div>
                            <div style="font-size: 12px; color: #64748B; margin-top: 2px;">Pemeriksaan 6 parameter mutu fisik makanan sebelum didistribusikan</div>
                        </div>
                        <div style="display: flex; align-items: center; gap: 8px;">
                            <span style="font-size: 12.5px; font-weight: 600; color: #1E293B;">Kesimpulan:</span>
                            <select name="physical_test_status" id="physical_test_status" class="form-select" style="height: 34px; font-size: 12px; font-weight: 600; border-radius: 6px; border: 1.5px solid #10B981; color: #065F46; background: #ECFDF5; padding: 0 10px;">
                                <option value="passed" {{ old('physical_test_status', 'passed') === 'passed' ? 'selected' : '' }}>Lolos Uji Fisik (Layak Saji)</option>
                                <option value="warning" {{ old('physical_test_status') === 'warning' ? 'selected' : '' }}>Catatan Khusus (Bersyarat)</option>
                                <option value="failed" {{ old('physical_test_status') === 'failed' ? 'selected' : '' }}>Tidak Lolos (Afkir/Tolak)</option>
                            </select>
                        </div>
                    </div>

                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 12px;">
                        {{-- 1. Rasa --}}
                        <div class="physical-matrix-card" style="border: 1.5px solid #E2E8F0; background: #FFFFFF; border-radius: 10px; padding: 12px 14px; display: flex; flex-direction: column; gap: 8px; box-shadow: 0 1px 2px rgba(0,0,0,0.02);">
                            <div style="display: flex; justify-content: space-between; align-items: center; gap: 8px;">
                                <strong style="font-size: 12.5px; color: #1E293B;">
                                    <span>1. Rasa</span>
                                </strong>
                                <select name="physical_test_matrix[rasa][status]" onchange="syncReportedConditionFromMatrix()" style="font-size: 12px; height: 30px; border-radius: 6px; border: 1px solid #CBD5E1; padding: 0 8px; background: #F8FAFC; color: #0F172A; font-weight: 600; max-width: 175px;">
                                    <option value="sesuai" selected>Sesuai Resep / Gurih</option>
                                    <option value="kurang_bumbu">Kurang Bumbu</option>
                                    <option value="tidak_normal">Tidak Normal / Asam</option>
                                </select>
                            </div>
                            <input type="text" name="physical_test_matrix[rasa][keterangan]" value="Gurih, bumbu meresap, normal khas resep" placeholder="Catatan rasa..." style="width: 100%; height: 34px; font-size: 12px; border: 1px solid #CBD5E1; border-radius: 6px; padding: 0 10px; box-sizing: border-box; background: #FFFFFF; color: #334155;">
                        </div>

                        {{-- 2. Aroma --}}
                        <div class="physical-matrix-card" style="border: 1.5px solid #E2E8F0; background: #FFFFFF; border-radius: 10px; padding: 12px 14px; display: flex; flex-direction: column; gap: 8px; box-shadow: 0 1px 2px rgba(0,0,0,0.02);">
                            <div style="display: flex; justify-content: space-between; align-items: center; gap: 8px;">
                                <strong style="font-size: 12.5px; color: #1E293B;">
                                    <span>2. Aroma / Bau</span>
                                </strong>
                                <select name="physical_test_matrix[aroma][status]" onchange="syncReportedConditionFromMatrix()" style="font-size: 12px; height: 30px; border-radius: 6px; border: 1px solid #CBD5E1; padding: 0 8px; background: #F8FAFC; color: #0F172A; font-weight: 600; max-width: 175px;">
                                    <option value="segar" selected>Segar / Khas Rempah</option>
                                    <option value="kurang_segar">Kurang Tajam / Netral</option>
                                    <option value="apek_asam">Apek / Asam / Basi</option>
                                </select>
                            </div>
                            <input type="text" name="physical_test_matrix[aroma][keterangan]" value="Segar, wangi masakan khas rempah, bebas bau apek" placeholder="Catatan aroma..." style="width: 100%; height: 34px; font-size: 12px; border: 1px solid #CBD5E1; border-radius: 6px; padding: 0 10px; box-sizing: border-box; background: #FFFFFF; color: #334155;">
                        </div>

                        {{-- 3. Warna & Visual --}}
                        <div class="physical-matrix-card" style="border: 1.5px solid #E2E8F0; background: #FFFFFF; border-radius: 10px; padding: 12px 14px; display: flex; flex-direction: column; gap: 8px; box-shadow: 0 1px 2px rgba(0,0,0,0.02);">
                            <div style="display: flex; justify-content: space-between; align-items: center; gap: 8px;">
                                <strong style="font-size: 12.5px; color: #1E293B;">
                                    <span>3. Warna &amp; Visual</span>
                                </strong>
                                <select name="physical_test_matrix[warna][status]" onchange="syncReportedConditionFromMatrix()" style="font-size: 12px; height: 30px; border-radius: 6px; border: 1px solid #CBD5E1; padding: 0 8px; background: #F8FAFC; color: #0F172A; font-weight: 600; max-width: 175px;">
                                    <option value="cerah" selected>Cerah / Menarik</option>
                                    <option value="standar">Standar Masakan</option>
                                    <option value="kusam">Pucat / Ada Benda Asing</option>
                                </select>
                            </div>
                            <input type="text" name="physical_test_matrix[warna][keterangan]" value="Cerah, bersih, tidak pucat, bebas kontaminan asing" placeholder="Catatan warna..." style="width: 100%; height: 34px; font-size: 12px; border: 1px solid #CBD5E1; border-radius: 6px; padding: 0 10px; box-sizing: border-box; background: #FFFFFF; color: #334155;">
                        </div>

                        {{-- 4. Tekstur & Kematangan --}}
                        <div class="physical-matrix-card" style="border: 1.5px solid #E2E8F0; background: #FFFFFF; border-radius: 10px; padding: 12px 14px; display: flex; flex-direction: column; gap: 8px; box-shadow: 0 1px 2px rgba(0,0,0,0.02);">
                            <div style="display: flex; justify-content: space-between; align-items: center; gap: 8px;">
                                <strong style="font-size: 12.5px; color: #1E293B;">
                                    <span>4. Tekstur &amp; Kematangan</span>
                                </strong>
                                <select name="physical_test_matrix[tekstur][status]" onchange="syncReportedConditionFromMatrix()" style="font-size: 12px; height: 30px; border-radius: 6px; border: 1px solid #CBD5E1; padding: 0 8px; background: #F8FAFC; color: #0F172A; font-weight: 600; max-width: 175px;">
                                    <option value="matang" selected>Matang Sempurna / Pas</option>
                                    <option value="agak_lembek">Agak Lembek / Agak Keras</option>
                                    <option value="mentah">Mentah / Rusak Tekstur</option>
                                </select>
                            </div>
                            <input type="text" name="physical_test_matrix[tekstur][keterangan]" value="Matang sempurna, empuk, tidak keras/mentah" placeholder="Catatan tekstur..." style="width: 100%; height: 34px; font-size: 12px; border: 1px solid #CBD5E1; border-radius: 6px; padding: 0 10px; box-sizing: border-box; background: #FFFFFF; color: #334155;">
                        </div>

                        {{-- 5. Suhu Sajian --}}
                        <div class="physical-matrix-card" style="border: 1.5px solid #E2E8F0; background: #FFFFFF; border-radius: 10px; padding: 12px 14px; display: flex; flex-direction: column; gap: 8px; box-shadow: 0 1px 2px rgba(0,0,0,0.02);">
                            <div style="display: flex; justify-content: space-between; align-items: center; gap: 8px;">
                                <strong style="font-size: 12.5px; color: #1E293B;">
                                    <span>5. Suhu Sajian</span>
                                </strong>
                                <select name="physical_test_matrix[suhu][status]" onchange="syncReportedConditionFromMatrix()" style="font-size: 12px; height: 30px; border-radius: 6px; border: 1px solid #CBD5E1; padding: 0 8px; background: #F8FAFC; color: #0F172A; font-weight: 600; max-width: 175px;">
                                    <option value="hangat" selected>Hangat (≥60°C)</option>
                                    <option value="suhu_ruang">Suhu Ruang Aman</option>
                                    <option value="dingin">Dingin / Perlu Hangatkan</option>
                                </select>
                            </div>
                            <input type="text" name="physical_test_matrix[suhu][keterangan]" value="Hangat (≥60°C) higienis" placeholder="Catatan suhu..." style="width: 100%; height: 34px; font-size: 12px; border: 1px solid #CBD5E1; border-radius: 6px; padding: 0 10px; box-sizing: border-box; background: #FFFFFF; color: #334155;">
                        </div>

                        {{-- 6. Kemasan & Higienitas --}}
                        <div class="physical-matrix-card" style="border: 1.5px solid #E2E8F0; background: #FFFFFF; border-radius: 10px; padding: 12px 14px; display: flex; flex-direction: column; gap: 8px; box-shadow: 0 1px 2px rgba(0,0,0,0.02);">
                            <div style="display: flex; justify-content: space-between; align-items: center; gap: 8px;">
                                <strong style="font-size: 12.5px; color: #1E293B;">
                                    <span>6. Kemasan &amp; Higienitas</span>
                                </strong>
                                <select name="physical_test_matrix[kemasan][status]" onchange="syncReportedConditionFromMatrix()" style="font-size: 12px; height: 30px; border-radius: 6px; border: 1px solid #CBD5E1; padding: 0 8px; background: #F8FAFC; color: #0F172A; font-weight: 600; max-width: 175px;">
                                    <option value="rapat" selected>Tertutup Rapat &amp; Bersih</option>
                                    <option value="cukup">Standar Bersih</option>
                                    <option value="bocor">Bocor / Kemasan Kotor</option>
                                </select>
                            </div>
                            <input type="text" name="physical_test_matrix[kemasan][keterangan]" value="Tertutup rapat, baki bersih food-grade" placeholder="Catatan kemasan..." style="width: 100%; height: 34px; font-size: 12px; border: 1px solid #CBD5E1; border-radius: 6px; padding: 0 10px; box-sizing: border-box; background: #FFFFFF; color: #334155;">
                        </div>
                    </div>
                </div>

                {{-- 6. Ringkasan Kondisi Makanan --}}
                <div>
                    <label for="input_condition" style="display: block; font-size: 13px; font-weight: 600; color: #071E49; margin-bottom: 6px;">
                        Ringkasan Kondisi Hasil Uji Kelayakan <span style="color: #EF4444;">*</span>
                    </label>
                    <input type="text" name="reported_condition" id="input_condition" required value="{{ old('reported_condition', 'Lolos Uji Fisik: Matang sempurna, aroma segar, rasa sesuai resep, higienis siap distribusi') }}" placeholder="Contoh: Baik, hangat, siap kirim" class="form-input" style="width: 100%; height: 42px; font-size: 13px; border-radius: 8px; border: 1px solid #CBD5E1; padding: 0 12px; box-sizing: border-box;">

                    {{-- Quick Suggestion Chips --}}
                    <div style="display: flex; gap: 8px; flex-wrap: wrap; margin-top: 8px;">
                        <button type="button" class="quick-chip-btn" onclick="setConditionText('Lolos Uji Fisik: Matang sempurna, aroma segar khas rempah, rasa sesuai standar resep')">
                            + Lolos Uji Fisik Sesuai Resep
                        </button>
                        <button type="button" class="quick-chip-btn" onclick="setConditionText('Hangat, higienis, kemasan baki tertutup rapat siap distribusi')">
                            + Hangat &amp; Siap Distribusi
                        </button>
                        <button type="button" class="quick-chip-btn" onclick="setConditionText('Lolos Uji Organoleptik & Aman Dikonsumsi')">
                            + Lolos Uji Organoleptik
                        </button>
                    </div>
                </div>

                {{-- 7. Catatan (Opsional) --}}
                <div>
                    <label for="hasil_note_input" style="display: block; font-size: 13px; font-weight: 600; color: #071E49; margin-bottom: 6px;">
                        Catatan Ahli Gizi / Pemeriksa <span style="font-size: 11.5px; font-weight: 400; color: #94A3B8;">(Opsional)</span>
                    </label>
                    <textarea name="note" id="hasil_note_input" rows="2" placeholder="Catatan tambahan mengenai komposisi, rasa, atau instruksi distribusi..." class="form-input" style="width: 100%; font-size: 13px; border-radius: 8px; border: 1px solid #CBD5E1; padding: 8px 12px; box-sizing: border-box; resize: vertical;">{{ old('note') }}</textarea>
                </div>

                {{-- 8. Bukti Foto --}}
                <div>
                    <label style="display: block; font-size: 13px; font-weight: 600; color: #071E49; margin-bottom: 6px;">
                        Foto Bukti Porsi Siap Saji <span style="color: #EF4444;">*</span>
                    </label>
                    <div id="dropzone_hasil" class="hasil-upload-dropzone">
                        <input type="file" name="evidence_file" id="evidence_file_input" required accept="image/*" onchange="previewEvidenceImage(this)" style="display: none;">
                        <label for="evidence_file_input" class="hasil-upload-label">
                            <div class="hasil-upload-icon">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"></path>
                                    <circle cx="12" cy="13" r="4"></circle>
                                </svg>
                            </div>
                            <div>
                                <span style="font-size: 13px; font-weight: 600; color: #042A70;">Unggah foto porsi siap kirim</span>
                                <div style="font-size: 11px; color: #94A3B8; margin-top: 2px;">JPG, PNG, WEBP · Maks. 5MB</div>
                            </div>
                        </label>
                        <div id="image_preview_box" style="display: none; margin-top: 10px; position: relative;">
                            <img id="preview_img" src="" alt="Preview Bukti" style="max-height: 140px; border-radius: 8px; border: 1px solid #CBD5E1; object-fit: cover;">
                            <button type="button" onclick="clearEvidencePreview()" style="position: absolute; top: -8px; right: calc(50% - 80px); background: #EF4444; color: #FFFFFF; border: none; border-radius: 50%; width: 24px; height: 24px; cursor: pointer; font-size: 13px; display: flex; align-items: center; justify-content: center;" title="Hapus foto">&times;</button>
                        </div>
                    </div>

                    {{-- Checkbox Jadikan Publik --}}
                    <div style="margin-top: 10px; display: flex; align-items: center; gap: 8px;">
                        <input type="checkbox" name="is_public" id="is_public_checkbox" value="1" {{ old('is_public', '1') ? 'checked' : '' }} style="width: 16px; height: 16px; accent-color: #042A70; cursor: pointer;">
                        <label for="is_public_checkbox" style="font-size: 12.5px; color: #334155; cursor: pointer;">
                            Tampilkan foto bukti ini di portal publik &amp; pelacakan QR siswa
                        </label>
                    </div>
                </div>
            </div>

            {{-- Modal Footer --}}
            <div style="display: flex; align-items: center; justify-content: flex-end; gap: 10px; padding: 14px 24px; border-top: 1px solid #F1F5F9; background: #F8FAFC; border-radius: 0 0 16px 16px; position: sticky; bottom: 0; z-index: 10;">
                <button type="button" onclick="closeAddHasilModal()" class="btn-secondary" style="padding: 9px 18px; font-size: 13px;">
                    Batal
                </button>
                <button type="submit" class="btn-primary" style="padding: 9px 20px; font-size: 13px;">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                        <polyline points="20 6 9 17 4 12"></polyline>
                    </svg>
                    <span>Simpan &amp; Sahkan Hasil</span>
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
                    <div class="hasil-upload-dropzone" id="dropzone_susut">
                        <input type="file" name="evidence_file" id="susut_evidence_file_input" accept="image/*" onchange="previewEvidenceImage(this)" style="display: none;">
                        <label for="susut_evidence_file_input" class="hasil-upload-label" style="cursor: pointer; width: 100%;">
                            <div class="hasil-upload-icon">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"></path>
                                    <circle cx="12" cy="13" r="4"></circle>
                                </svg>
                            </div>
                            <div>
                                <span style="font-size: 12.5px; font-weight: 600; color: #042A70;">Pilih file foto bukti</span>
                                <span style="font-size: 11.5px; color: #64748B;"> atau seret ke sini</span>
                            </div>
                            <span style="font-size: 11px; color: #94A3B8;">Format JPG, PNG, WEBP maks. 5MB</span>
                        </label>
                        <div id="susut_image_preview_box" style="display: none; margin-top: 10px; position: relative;">
                            <img id="susut_preview_img" src="" alt="Preview Bukti Susut" style="max-height: 120px; border-radius: 8px; border: 1px solid #CBD5E1; object-fit: cover;">
                            <button type="button" onclick="clearSusutEvidencePreview(); event.stopPropagation();" style="position: absolute; top: -8px; right: calc(50% - 70px); background: #EF4444; color: #FFFFFF; border: none; border-radius: 50%; width: 22px; height: 22px; cursor: pointer; font-size: 12px; display: flex; align-items: center; justify-content: center;" title="Hapus foto">&times;</button>
                        </div>
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

{{-- Modal Preview Foto Bukti & Detail Pemeriksaan QC --}}
<div id="modalPreviewFoto" class="modal-backdrop modal-backdrop-custom" style="display: none; position: fixed; inset: 0; background: rgba(15, 23, 42, 0.75); backdrop-filter: blur(4px); z-index: 99999; align-items: center; justify-content: center; padding: 16px;" onclick="closePreviewModal(event)">
    <div style="background: #FFFFFF; border-radius: 16px; max-width: 680px; width: 100%; max-height: 90vh; overflow-y: auto; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);" onclick="event.stopPropagation()">
        <div style="display: flex; align-items: center; justify-content: space-between; padding: 14px 20px; border-bottom: 1px solid #F1F5F9; position: sticky; top: 0; background: #FFFFFF; z-index: 10;">
            <div>
                <div style="font-size: 15px; font-weight: 700; color: #071E49;" id="preview_modal_title">Detail Hasil QC &amp; Uji Fisik</div>
                <div style="font-size: 11.5px; color: #64748B;" id="preview_modal_subtitle">Pemeriksaan Kelayakan Saji Makanan MBG</div>
            </div>
            <button type="button" onclick="closePreviewModal()" style="background: none; border: none; color: #94A3B8; cursor: pointer; padding: 4px;">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
            </button>
        </div>

        {{-- Foto Bukti --}}
        <div id="preview_img_wrapper" style="padding: 14px; text-align: center; background: #0F172A;">
            <img id="preview_modal_img" src="" alt="Bukti Porsi" style="max-height: 320px; max-width: 100%; border-radius: 8px; object-fit: contain;">
        </div>

        {{-- Detail Informasi Uji Fisik & Waktu Kritis --}}
        <div style="padding: 16px 20px; display: flex; flex-direction: column; gap: 12px;">
            {{-- Badge Waktu Kritis Banner --}}
            <div id="preview_critical_time_banner" style="display: flex; align-items: center; justify-content: space-between; background: #FEF2F2; border: 1.5px solid #FCA5A5; border-radius: 10px; padding: 10px 14px;">
                <div style="display: flex; align-items: center; gap: 8px;">
                    <div>
                        <div style="font-size: 11px; font-weight: 700; color: #991B1B; text-transform: uppercase; letter-spacing: 0.5px;">Waktu Kritis: Batas Maksimal Dimakan</div>
                        <div style="font-size: 14px; font-weight: 800; color: #DC2626;" id="preview_consume_by_text">-</div>
                    </div>
                </div>
                <span id="preview_modal_public_badge"></span>
            </div>

            {{-- 3 Kolom Info: Jam Uji, Jam Distribusi, Ahli Gizi --}}
            <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px; background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 10px; padding: 12px;">
                <div>
                    <span style="font-size: 11px; color: #64748B; display: block;">Jam Uji Fisik (QC):</span>
                    <strong style="font-size: 13px; color: #071E49;" id="preview_checked_at_text">-</strong>
                </div>
                <div>
                    <span style="font-size: 11px; color: #64748B; display: block;">Estimasi Distribusi:</span>
                    <strong style="font-size: 13px; color: #071E49;" id="preview_estimated_dist_text">-</strong>
                </div>
                <div>
                    <span style="font-size: 11px; color: #64748B; display: block;">Tenaga Ahli Gizi:</span>
                    <strong style="font-size: 13px; color: #166534;" id="preview_nutritionist_text">-</strong>
                </div>
            </div>

            {{-- Ringkasan Kondisi & Form Type --}}
            <div style="background: #FFFFFF; border: 1px solid #E2E8F0; border-radius: 10px; padding: 12px;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px;">
                    <span style="font-size: 11.5px; font-weight: 700; color: #475569;">KATEGORI &amp; RINGKASAN KONDISI:</span>
                    <span id="preview_form_type_badge" style="background: #EFF6FF; color: #1D4ED8; font-size: 11px; font-weight: 700; padding: 2px 8px; border-radius: 6px;">Makanan Siap Santap</span>
                </div>
                <div style="font-size: 13px; color: #1E293B; line-height: 1.4;" id="preview_modal_desc">-</div>
                <div id="preview_modal_note_box" style="margin-top: 6px; font-size: 12px; color: #64748B; display: none;">
                    <em>Catatan:</em> <span id="preview_modal_note">-</span>
                </div>
            </div>
        </div>

        <div style="padding: 12px 20px; background: #F8FAFC; border-top: 1px solid #F1F5F9; display: flex; justify-content: flex-end;">
            <button type="button" onclick="closePreviewModal()" class="btn-secondary" style="padding: 7px 16px; font-size: 12.5px;">
                Tutup
            </button>
        </div>
    </div>
</div>


