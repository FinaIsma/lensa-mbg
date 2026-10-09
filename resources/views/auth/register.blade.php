<!DOCTYPE html>
<html lang="id">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Daftar Akun SPPG - Lensa MBG</title>
        @vite(['resources/css/register.css', 'resources/js/register.js'])
    </head>

    <body>
        <header class="header">
            <div class="logo-left">
                <a href="{{ route('login') }}">
                    <img src="{{ asset('images/LogoLensa.png') }}" alt="Logo Lensa MBG">
                </a>
            </div>
            <div class="logo-right">
                <img src="{{ asset('images/LogoBGN.png') }}" alt="Logo Badan Gizi Nasional">
            </div>
        </header>

        <main class="register-wrapper">
            <div class="register-card">
                <h1 class="register-title">Daftar Akun</h1>
                <p class="register-subtitle">Lengkapi data untuk membuat akun SPPG.</p>

                @if ($errors->has('general'))
                    <div class="alert-error">
                        {{ $errors->first('general') }}
                    </div>
                @endif

                <form action="{{ route('register.process') }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    <!-- 1. DATA AKUN -->
                    <h2 class="form-section-title">Data Akun</h2>

                    <div class="grid-2">
                        <div class="form-group">
                            <label for="nama_lengkap">Nama Lengkap <span class="required">*</span></label>
                            <div class="input-wrapper">
                                <svg class="input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                                    <circle cx="12" cy="7" r="4"></circle>
                                </svg>
                                <input type="text" id="nama_lengkap" name="nama_lengkap" value="{{ old('nama_lengkap') }}" placeholder="Masukkan Nama Lengkap" required>
                            </div>
                            @error('nama_lengkap')
                                <div class="error">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="email">Email <span class="required">*</span></label>
                            <div class="input-wrapper">
                                <svg class="input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <rect x="3" y="5" width="18" height="14" rx="2"></rect>
                                    <polyline points="3,7 12,13 21,7"></polyline>
                                </svg>
                                <input type="email" id="email" name="email" value="{{ old('email') }}" placeholder="Masukkan Email" autocomplete="email" required>
                            </div>
                            @error('email')
                                <div class="error">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="grid-2">
                        <div class="form-group">
                            <label for="no_telepon">No Telepon <span class="required">*</span></label>
                            <div class="input-wrapper">
                                <svg class="input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path>
                                </svg>
                                <input type="tel" id="no_telepon" name="no_telepon" value="{{ old('no_telepon') }}" placeholder="08xxxxxxxxx" required>
                            </div>
                            @error('no_telepon')
                                <div class="error">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="password">Password <span class="required">*</span></label>
                            <div class="input-wrapper">
                                <svg class="input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <rect x="5" y="11" width="14" height="10" rx="2"></rect>
                                    <path d="M8 11V7a4 4 0 0 1 8 0v4"></path>
                                </svg>
                                <input type="password" id="password" name="password" placeholder="Masukkan Password" required>
                                <button type="button" class="password-toggle" onclick="toggleRegisterPassword('password', 'eyeIconPassword')" aria-label="Tampilkan password">
                                    <svg id="eyeIconPassword" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                        <circle cx="12" cy="12" r="3"></circle>
                                    </svg>
                                </button>
                            </div>
                            @error('password')
                                <div class="error">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="password_confirmation">Konfirmasi Password <span class="required">*</span></label>
                        <div class="input-wrapper">
                            <svg class="input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <rect x="5" y="11" width="14" height="10" rx="2"></rect>
                                <path d="M8 11V7a4 4 0 0 1 8 0v4"></path>
                            </svg>
                            <input type="password" id="password_confirmation" name="password_confirmation" placeholder="Masukkan Konfirmasi Password" required>
                            <button type="button" class="password-toggle" onclick="toggleRegisterPassword('password_confirmation', 'eyeIconConfirm')" aria-label="Tampilkan password">
                                <svg id="eyeIconConfirm" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                    <circle cx="12" cy="12" r="3"></circle>
                                </svg>
                            </button>
                        </div>
                    </div>

                    <!-- 2. DATA SPPG -->
                    <h2 class="form-section-title">Data SPPG</h2>

                    <div class="grid-2">
                        <div class="form-group">
                            <label for="nama_sppg">Nama SPPG <span class="required">*</span></label>
                            <div class="input-wrapper">
                                <svg class="input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <rect x="4" y="2" width="16" height="20" rx="2"></rect>
                                    <line x1="9" y1="22" x2="9" y2="2"></line>
                                    <line x1="15" y1="22" x2="15" y2="2"></line>
                                </svg>
                                <input type="text" id="nama_sppg" name="nama_sppg" value="{{ old('nama_sppg') }}" placeholder="Masukkan Nama SPPG" required>
                            </div>
                            @error('nama_sppg')
                                <div class="error">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="alamat_sppg">Alamat SPPG <span class="required">*</span></label>
                            <div class="input-wrapper">
                                <svg class="input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
                                    <circle cx="12" cy="10" r="3"></circle>
                                </svg>
                                <input type="text" id="alamat_sppg" name="alamat_sppg" value="{{ old('alamat_sppg') }}" placeholder="Masukkan Alamat SPPG" required>
                            </div>
                            @error('alamat_sppg')
                                <div class="error">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="grid-3">
                        <div class="form-group">
                            <label for="provinsi">Provinsi <span class="required">*</span></label>
                            <div class="input-wrapper">
                                <select id="provinsi" name="provinsi" data-old="{{ old('provinsi') }}" required>
                                    <option value="">Pilih Provinsi</option>
                                </select>
                                <svg class="select-arrow" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <polyline points="6 9 12 15 18 9"></polyline>
                                </svg>
                            </div>
                            @error('provinsi')
                                <div class="error">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="kabupaten_kota">Kabupaten / Kota <span class="required">*</span></label>
                            <div class="input-wrapper">
                                <select id="kabupaten_kota" name="kabupaten_kota" data-old="{{ old('kabupaten_kota') }}" disabled required>
                                    <option value="">Pilih Kab/Kota</option>
                                </select>
                                <svg class="select-arrow" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <polyline points="6 9 12 15 18 9"></polyline>
                                </svg>
                            </div>
                            @error('kabupaten_kota')
                                <div class="error">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="kecamatan">Kecamatan <span class="required">*</span></label>
                            <div class="input-wrapper">
                                <select id="kecamatan" name="kecamatan" data-old="{{ old('kecamatan') }}" disabled required>
                                    <option value="">Pilih Kecamatan</option>
                                </select>
                                <svg class="select-arrow" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <polyline points="6 9 12 15 18 9"></polyline>
                                </svg>
                            </div>
                            @error('kecamatan')
                                <div class="error">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- 3. UPLOAD DOKUMEN -->
                    <h2 class="form-section-title">Upload Dokumen</h2>

                    <div class="upload-grid">
                        <!-- Card 1: KTP -->
                        <div class="upload-card">
                            <div class="upload-card-header">
                                <div class="upload-title">KTP Penanggung Jawab <span class="required">*</span></div>
                                <div class="upload-desc">e-KTP asli penanggung jawab</div>
                            </div>
                            <input type="file" id="foto_ktp" name="foto_ktp" accept="image/jpeg,image/png" style="display: none;" required>
                            <div class="upload-dropzone" id="dropzone_ktp">
                                <div class="dropzone-default">
                                    <svg class="upload-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                                        <polyline points="14 2 14 8 20 8"></polyline>
                                        <line x1="12" y1="18" x2="12" y2="12"></line>
                                        <line x1="9" y1="15" x2="12" y2="12"></line>
                                        <line x1="15" y1="15" x2="12" y2="12"></line>
                                    </svg>
                                    <div class="upload-action-text">Klik untuk unggah</div>
                                    <div class="upload-format-text">Format: JPG, PNG • Maks. 5 MB</div>
                                </div>
                                <div class="file-preview-area" id="preview_ktp"></div>
                            </div>
                            @error('foto_ktp')
                                <div class="error">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Card 2: Foto Kantor -->
                        <div class="upload-card">
                            <div class="upload-card-header">
                                <div class="upload-title">Foto Kantor SPPG <span class="required">*</span></div>
                                <div class="upload-desc">Foto tampak depan kantor SPPG</div>
                            </div>
                            <input type="file" id="foto_kantor_sppg" name="foto_kantor_sppg" accept="image/jpeg,image/png" style="display: none;" required>
                            <div class="upload-dropzone" id="dropzone_kantor">
                                <div class="dropzone-default">
                                    <svg class="upload-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                        <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
                                        <circle cx="8.5" cy="8.5" r="1.5"></circle>
                                        <polyline points="21 15 16 10 5 21"></polyline>
                                    </svg>
                                    <div class="upload-action-text">Klik untuk unggah</div>
                                    <div class="upload-format-text">Format: JPG, PNG • Maks. 5 MB</div>
                                </div>
                                <div class="file-preview-area" id="preview_kantor"></div>
                            </div>
                            @error('foto_kantor_sppg')
                                <div class="error">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Card 3: Surat Resmi / SK -->
                        <div class="upload-card">
                            <div class="upload-card-header">
                                <div class="upload-title">Surat Resmi / SK <span class="required">*</span></div>
                                <div class="upload-desc">Surat resmi instansi terkait</div>
                            </div>
                            <input type="file" id="foto_surat_resmi" name="foto_surat_resmi" accept=".pdf,image/jpeg,image/png" style="display: none;" required>
                            <div class="upload-dropzone" id="dropzone_surat">
                                <div class="dropzone-default">
                                    <svg class="upload-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                                        <polyline points="14 2 14 8 20 8"></polyline>
                                        <line x1="12" y1="18" x2="12" y2="12"></line>
                                        <line x1="9" y1="15" x2="12" y2="12"></line>
                                        <line x1="15" y1="15" x2="12" y2="12"></line>
                                    </svg>
                                    <div class="upload-action-text">Klik untuk unggah</div>
                                    <div class="upload-format-text">Format: PDF, JPG • Maks. 5 MB</div>
                                </div>
                                <div class="file-preview-area" id="preview_surat"></div>
                            </div>
                            @error('foto_surat_resmi')
                                <div class="error">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Terms & Conditions -->
                    <div class="terms-wrapper">
                        <input type="checkbox" id="syarat_ketentuan" name="syarat_ketentuan" class="terms-checkbox" value="1" {{ old('syarat_ketentuan') ? 'checked' : '' }}>
                        <label for="syarat_ketentuan" class="terms-label">
                            Saya menyetujui <strong>syarat dan ketentuan</strong> yang berlaku
                        </label>
                    </div>
                    @error('syarat_ketentuan')
                        <div class="error" style="margin-top: -16px; margin-bottom: 16px;">{{ $message }}</div>
                    @enderror

                    <!-- Submit Button -->
                    <button type="submit" class="register-button">Daftar</button>

                    <!-- Bottom Link to Login -->
                    <div class="bottom-link">
                        Sudah punya akun? <a href="{{ route('login') }}">Masuk</a>
                    </div>
                </form>
            </div>
        </main>
    </body>
</html>
