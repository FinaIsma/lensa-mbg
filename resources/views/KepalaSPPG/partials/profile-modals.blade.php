@php
    $currentUser = auth('staff')->user() ?: auth()->user();
    $currentSppg = $currentUser?->sppg ?? \App\Models\Sppg::where('id_user', $currentUser?->id_user)->first();

    $sppgIdRaw = $currentSppg?->id_sppg ?? 1;
    $sppgIdFormatted = 'SPPG' . str_pad((string) $sppgIdRaw, 3, '0', STR_PAD_LEFT);
    $sppgNama = $currentSppg->nama_sppg ?? 'SPPG Malang 1';
    $inisialSppg = mb_strtoupper(mb_substr(trim($sppgNama), 0, 1)) ?: 'S';

    $kepalaNama = $currentUser->nama ?? 'Yu Ji-Min';
    $kepalaEmail = $currentUser->email ?? 'Katarinaa@gmail.com';
    $kepalaTelepon = $currentSppg->no_telepon ?? '081726386138';

    $sppgAlamat = $currentSppg->alamat_sppg ?? 'Jl Bedali Lawang';
    $sppgProvinsi = $currentSppg->provinsi ?? 'Jawa Timur';
    $sppgKabupaten = $currentSppg->kabupaten_kota ?? 'Kabupaten Malang';
    $sppgKecamatan = $currentSppg->kecamatan ?? 'Lawang';

    $bulanIndo = [1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
    $tglCreated = $currentSppg?->created_at ? \Illuminate\Support\Carbon::parse($currentSppg->created_at) : now();
    $tglFormatted = $tglCreated->format('d') . ' ' . ($bulanIndo[(int) $tglCreated->format('n')] ?? $tglCreated->format('F')) . ' ' . $tglCreated->format('Y');

    $resolveDocUrl = function (?string $path): ?string {
        if (! $path) {
            return null;
        }

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        return asset($path);
    };

    $fotoKtp = $resolveDocUrl($currentSppg?->foto_ktp);
    $fotoKantor = $resolveDocUrl($currentSppg?->foto_kantor_sppg);
    $fotoSurat = $resolveDocUrl($currentSppg?->foto_surat_resmi);

    $lastChangeRequest = \Illuminate\Support\Facades\Schema::hasTable('kepala_change_requests') && $currentSppg
        ? \Illuminate\Support\Facades\DB::table('kepala_change_requests')
            ->where('id_sppg', $currentSppg->id_sppg)
            ->latest('id')
            ->first()
        : null;
@endphp

{{-- ========================================================= --}}
{{-- 1. MODAL PROFIL SPPG (MODE TAMPILAN & MODE EDIT)          --}}
{{-- ========================================================= --}}
<div class="pm-backdrop hidden" id="profilModal" style="display: none;" role="dialog" aria-modal="true" aria-labelledby="modalProfilTitle">
    <div class="pm-dialog">
        {{-- Header Top --}}
        <div class="pm-header">
            <div class="pm-header-left">
                <button type="button" class="pm-btn-back" id="btnBackToView" onclick="setProfilMode('view')" title="Kembali ke Mode Tampilan" style="display: none;" aria-label="Kembali ke Mode Tampilan">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="19" y1="12" x2="5" y2="12"></line>
                        <polyline points="12 19 5 12 12 5"></polyline>
                    </svg>
                </button>
                <h2 class="pm-title" id="modalProfilTitle">Profil & Informasi SPPG</h2>
            </div>
            <button type="button" class="pm-btn-close" onclick="closeProfilModal()" aria-label="Tutup Modal Profil">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
            </button>
        </div>

        {{-- Entity Summary --}}
        <div class="pm-entity-header">
            <div class="pm-entity-left">
                <div class="pm-entity-avatar">{{ $inisialSppg }}</div>
                <div class="pm-entity-info">
                    <span class="pm-entity-name">{{ $sppgNama }}</span>
                    <span class="pm-entity-id">{{ $sppgIdFormatted }}</span>
                </div>
            </div>
            <div class="pm-badge-aktif">
                <span class="pm-badge-dot"></span>
                <span>Aktif</span>
            </div>
        </div>

        {{-- VIEW MODE CONTAINER --}}
        <div id="profilViewContainer">
            @if ($currentSppg?->status_verifikasi === 'perubahan')
                {{-- Banner Callout (Dalam Proses Verifikasi / Terkunci) --}}
                <div class="pm-banner-callout" style="background-color: #FEF3C7; border: 1.5px solid #F59E0B;">
                    <div class="pm-banner-left">
                        <svg class="pm-banner-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#D97706" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="12" r="10"></circle>
                            <line x1="12" y1="8" x2="12" y2="12"></line>
                            <line x1="12" y1="16" x2="12.01" y2="16"></line>
                        </svg>
                        <span style="color: #92400E; font-weight: 500; font-size: 13px;">
                            Pengajuan perubahan data Anda sedang dalam proses verifikasi oleh Admin Sistem.
                        </span>
                    </div>
                    <button type="button" class="pm-btn-edit-mode" disabled style="opacity: 0.6; cursor: not-allowed; background-color: #9CA3AF;" title="Fitur edit dikunci sementara selama verifikasi">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                            <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                        </svg>
                        <span>Terkunci</span>
                    </button>
                </div>
            @else
                @if ($lastChangeRequest && $lastChangeRequest->status === 'rejected' && !empty($lastChangeRequest->review_note))
                    <div class="pm-banner-callout" style="background-color: #FEE2E2; border: 1.5px solid #EF4444; margin-bottom: 14px;">
                        <div class="pm-banner-left">
                            <svg class="pm-banner-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#DC2626" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="12" cy="12" r="10"></circle>
                                <line x1="15" y1="9" x2="9" y2="15"></line>
                                <line x1="9" y1="9" x2="15" y2="15"></line>
                            </svg>
                            <span style="color: #991B1B; font-weight: 500; font-size: 13px;">
                                Pengajuan perubahan sebelumnya ditolak: {{ $lastChangeRequest->review_note }}
                            </span>
                        </div>
                    </div>
                @endif

                {{-- Banner Callout (Mode Tampilan Normal) --}}
                <div class="pm-banner-callout">
                    <div class="pm-banner-left">
                        <svg class="pm-banner-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="12" r="10"></circle>
                            <polyline points="12 6 12 12 16 14"></polyline>
                        </svg>
                        <span class="pm-banner-text-view">Mode tampilan. Klik tombol untuk edit nama & data.</span>
                    </div>
                    <button type="button" class="pm-btn-edit-mode" onclick="setProfilMode('edit')">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M12 20h9"></path>
                            <path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"></path>
                        </svg>
                        <span>Edit Data</span>
                    </button>
                </div>
            @endif

            {{-- Card 1: Informasi Kepala SPPG --}}
            <div class="pm-card">
                <h3 class="pm-card-title">Informasi Kepala SPPG</h3>
                <div class="pm-view-row">
                    <div class="pm-view-label">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                            <circle cx="12" cy="7" r="4"></circle>
                        </svg>
                        <span>Nama Lengkap</span>
                    </div>
                    <div class="pm-view-val" id="viewNamaKepala">{{ $kepalaNama }}</div>
                </div>

                <div class="pm-view-row">
                    <div class="pm-view-label">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="2" y="4" width="20" height="16" rx="2"></rect>
                            <path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"></path>
                        </svg>
                        <span>Email</span>
                    </div>
                    <div class="pm-view-val" id="viewEmailKepala">{{ $kepalaEmail }}</div>
                </div>

                <div class="pm-view-row">
                    <div class="pm-view-label">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path>
                        </svg>
                        <span>No Telepon</span>
                    </div>
                    <div class="pm-view-val" id="viewTeleponKepala">{{ $kepalaTelepon }}</div>
                </div>
            </div>

            {{-- Card 2: Informasi SPPG --}}
            <div class="pm-card">
                <h3 class="pm-card-title">Informasi SPPG</h3>
                <div class="pm-view-row">
                    <div class="pm-view-label">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
                            <polyline points="9 22 9 12 15 12 15 22"></polyline>
                        </svg>
                        <span>Nama SPPG</span>
                    </div>
                    <div class="pm-view-val" id="viewNamaSppg">{{ $sppgNama }}</div>
                </div>

                <div class="pm-view-row">
                    <div class="pm-view-label">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="3" y="4" width="18" height="16" rx="2"></rect>
                            <circle cx="9" cy="10" r="2"></circle>
                            <line x1="15" y1="8" x2="17" y2="8"></line>
                            <line x1="15" y1="12" x2="17" y2="12"></line>
                            <line x1="7" y1="16" x2="17" y2="16"></line>
                        </svg>
                        <span>ID SPPG</span>
                    </div>
                    <div class="pm-view-val" id="viewIdSppg">{{ $sppgIdFormatted }}</div>
                </div>

                <div class="pm-view-row">
                    <div class="pm-view-label">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M12 21s-7-4.5-7-11a7 7 0 0 1 14 0c0 6.5-7 11-7 11z"></path>
                            <circle cx="12" cy="10" r="2.5"></circle>
                        </svg>
                        <span>Alamat Lengkap</span>
                    </div>
                    <div class="pm-view-val" id="viewAlamatSppg">{{ $sppgAlamat }}</div>
                </div>

                <div class="pm-view-row">
                    <div class="pm-view-label">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M12 21s-7-4.5-7-11a7 7 0 0 1 14 0c0 6.5-7 11-7 11z"></path>
                            <circle cx="12" cy="10" r="2.5"></circle>
                        </svg>
                        <span>Provinsi</span>
                    </div>
                    <div class="pm-view-val" id="viewProvinsiSppg">{{ $sppgProvinsi }}</div>
                </div>

                <div class="pm-view-row">
                    <div class="pm-view-label">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M12 21s-7-4.5-7-11a7 7 0 0 1 14 0c0 6.5-7 11-7 11z"></path>
                            <circle cx="12" cy="10" r="2.5"></circle>
                        </svg>
                        <span>Kota/Kabupaten</span>
                    </div>
                    <div class="pm-view-val" id="viewKabupatenSppg">{{ $sppgKabupaten }}</div>
                </div>

                <div class="pm-view-row">
                    <div class="pm-view-label">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <polygon points="12 2 19 21 12 17 5 21 12 2"></polygon>
                        </svg>
                        <span>Kecamatan</span>
                    </div>
                    <div class="pm-view-val" id="viewKecamatanSppg">{{ $sppgKecamatan }}</div>
                </div>

                <div class="pm-view-row">
                    <div class="pm-view-label">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="3" y="4" width="18" height="18" rx="2"></rect>
                            <line x1="16" y1="2" x2="16" y2="6"></line>
                            <line x1="8" y1="2" x2="8" y2="6"></line>
                            <line x1="3" y1="10" x2="21" y2="10"></line>
                        </svg>
                        <span>Tanggal dibuat</span>
                    </div>
                    <div class="pm-view-val" id="viewTglSppg">{{ $tglFormatted }}</div>
                </div>
            </div>

            {{-- Card 3: Dokumen --}}
            <div class="cr-info-card pm-card">
                <h3 class="cr-card-title pm-card-title">Dokumen</h3>
                <div class="mv2-doc-grid">
                    {{-- Foto KTP --}}
                    <div class="mv2-doc-item">
                        <div class="mv2-doc-img-box">
                            @if ($fotoKtp)
                                <img src="{{ $fotoKtp }}" alt="Foto KTP" onerror="this.onerror=null; this.style.display='none'; if(this.nextElementSibling) this.nextElementSibling.style.display='flex';">
                                <div class="mv2-doc-empty" style="display: none;">
                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#94A3B8" stroke-width="1.8"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
                                </div>
                            @else
                                <div class="mv2-doc-empty">
                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#94A3B8" stroke-width="1.8"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
                                </div>
                            @endif
                        </div>
                        <span class="mv2-doc-label">Foto KTP</span>
                        <a href="{{ $fotoKtp ?: '#' }}" {{ $fotoKtp ? 'target="_blank"' : '' }} class="btn-file-view {{ !$fotoKtp ? 'mv2-btn-disabled' : '' }}">Lihat</a>
                    </div>

                    {{-- Foto Kantor SPPG --}}
                    <div class="mv2-doc-item">
                        <div class="mv2-doc-img-box">
                            @if ($fotoKantor)
                                <img src="{{ $fotoKantor }}" alt="Foto Kantor SPPG" onerror="this.onerror=null; this.style.display='none'; if(this.nextElementSibling) this.nextElementSibling.style.display='flex';">
                                <div class="mv2-doc-empty" style="display: none;">
                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#94A3B8" stroke-width="1.8"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
                                </div>
                            @else
                                <div class="mv2-doc-empty">
                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#94A3B8" stroke-width="1.8"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
                                </div>
                            @endif
                        </div>
                        <span class="mv2-doc-label">Foto Kantor SPPG</span>
                        <a href="{{ $fotoKantor ?: '#' }}" {{ $fotoKantor ? 'target="_blank"' : '' }} class="btn-file-view {{ !$fotoKantor ? 'mv2-btn-disabled' : '' }}">Lihat</a>
                    </div>

                    {{-- Foto Surat Resmi --}}
                    <div class="mv2-doc-item">
                        <div class="mv2-doc-img-box">
                            @if ($fotoSurat)
                                @if (preg_match('/\.pdf($|\?)/i', $fotoSurat))
                                    <canvas class="mv2-pdf-canvas" data-pdf="{{ $fotoSurat }}"></canvas>
                                @else
                                    <img src="{{ $fotoSurat }}" alt="Foto Surat Resmi" onerror="this.onerror=null; this.style.display='none'; if(this.nextElementSibling) this.nextElementSibling.style.display='flex';">
                                    <div class="mv2-doc-empty" style="display: none;">
                                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#94A3B8" stroke-width="1.8"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>
                                    </div>
                                @endif
                            @else
                                <div class="mv2-doc-empty">
                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#94A3B8" stroke-width="1.8"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>
                                </div>
                            @endif
                        </div>
                        <span class="mv2-doc-label">Foto Surat Resmi</span>
                        <a href="{{ $fotoSurat ?: '#' }}" {{ $fotoSurat ? 'target="_blank"' : '' }} class="btn-file-view {{ !$fotoSurat ? 'mv2-btn-disabled' : '' }}">Lihat</a>
                    </div>
                </div>
            </div>
        </div>

        {{-- EDIT MODE CONTAINER --}}
        <div id="profilEditContainer" style="display: none;">
            <form id="formEditProfil" action="{{ route('sppg.profile.update') }}" method="POST" enctype="multipart/form-data">
                @csrf

                {{-- Banner Callout (Mode Edit) --}}
                <div class="pm-banner-callout">
                    <div class="pm-banner-left">
                        <svg class="pm-banner-icon" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M12 20h9"></path>
                            <path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"></path>
                        </svg>
                        <div class="pm-banner-text-edit">
                            <span class="pm-banner-edit-title">Mode edit data</span>
                            <span class="pm-banner-edit-sub">Perbarui informasi dan dokumen SPPG, lalu klik Simpan Perubahan.</span>
                        </div>
                    </div>
                </div>

                {{-- Card 1: Informasi Kepala SPPG --}}
                <div class="pm-card">
                    <h3 class="pm-card-title">Informasi Kepala SPPG</h3>
                    <div class="pm-edit-row">
                        <div class="pm-edit-label">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                                <circle cx="12" cy="7" r="4"></circle>
                            </svg>
                            <span>Nama Lengkap</span>
                        </div>
                        <div class="pm-edit-input-wrapper">
                            <input type="text" name="nama_lengkap" class="pm-input" value="{{ $kepalaNama }}" required>
                        </div>
                    </div>

                    <div class="pm-edit-row">
                        <div class="pm-edit-label">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <rect x="2" y="4" width="20" height="16" rx="2"></rect>
                                <path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"></path>
                            </svg>
                            <span>Email</span>
                        </div>
                        <div class="pm-edit-input-wrapper">
                            <input type="email" name="email" class="pm-input" value="{{ $kepalaEmail }}" required>
                        </div>
                    </div>

                    <div class="pm-edit-row">
                        <div class="pm-edit-label">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path>
                            </svg>
                            <span>No Telepon</span>
                        </div>
                        <div class="pm-edit-input-wrapper">
                            <input type="tel" name="no_telepon" class="pm-input" value="{{ $kepalaTelepon }}" required>
                        </div>
                    </div>
                </div>

                {{-- Card 2: Informasi SPPG --}}
                <div class="pm-card">
                    <h3 class="pm-card-title">Informasi SPPG</h3>
                    <div class="pm-edit-row">
                        <div class="pm-edit-label">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
                                <polyline points="9 22 9 12 15 12 15 22"></polyline>
                            </svg>
                            <span>Nama SPPG</span>
                        </div>
                        <div class="pm-edit-input-wrapper">
                            <input type="text" name="nama_sppg" class="pm-input" value="{{ $sppgNama }}" required>
                        </div>
                    </div>

                    <div class="pm-edit-row">
                        <div class="pm-edit-label">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <rect x="3" y="4" width="18" height="16" rx="2"></rect>
                                <circle cx="9" cy="10" r="2"></circle>
                                <line x1="15" y1="8" x2="17" y2="8"></line>
                                <line x1="15" y1="12" x2="17" y2="12"></line>
                                <line x1="7" y1="16" x2="17" y2="16"></line>
                            </svg>
                            <span>ID SPPG</span>
                        </div>
                        <div class="pm-edit-input-wrapper">
                            <input type="text" class="pm-input pm-input-disabled" value="{{ $sppgIdFormatted }}" disabled>
                            <svg class="pm-input-lock" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                                <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                            </svg>
                        </div>
                    </div>

                    <div class="pm-edit-row">
                        <div class="pm-edit-label">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M12 21s-7-4.5-7-11a7 7 0 0 1 14 0c0 6.5-7 11-7 11z"></path>
                                <circle cx="12" cy="10" r="2.5"></circle>
                            </svg>
                            <span>Alamat Lengkap</span>
                        </div>
                        <div class="pm-edit-input-wrapper">
                            <input type="text" name="alamat_sppg" class="pm-input" value="{{ $sppgAlamat }}" required>
                        </div>
                    </div>

                    <div class="pm-edit-row">
                        <div class="pm-edit-label">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M12 21s-7-4.5-7-11a7 7 0 0 1 14 0c0 6.5-7 11-7 11z"></path>
                                <circle cx="12" cy="10" r="2.5"></circle>
                            </svg>
                            <span>Provinsi</span>
                        </div>
                        <div class="pm-edit-input-wrapper">
                            <select name="provinsi" id="editProvinsi" class="pm-select" data-current="{{ $sppgProvinsi }}" required>
                                <option value="{{ $sppgProvinsi }}" selected>{{ $sppgProvinsi }}</option>
                            </select>
                            <svg class="pm-select-chevron" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                <polyline points="6 9 12 15 18 9"></polyline>
                            </svg>
                        </div>
                    </div>

                    <div class="pm-edit-row">
                        <div class="pm-edit-label">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M12 21s-7-4.5-7-11a7 7 0 0 1 14 0c0 6.5-7 11-7 11z"></path>
                                <circle cx="12" cy="10" r="2.5"></circle>
                            </svg>
                            <span>Kota/Kabupaten</span>
                        </div>
                        <div class="pm-edit-input-wrapper">
                            <select name="kabupaten_kota" id="editKabupaten" class="pm-select" data-current="{{ $sppgKabupaten }}" required>
                                <option value="{{ $sppgKabupaten }}" selected>{{ $sppgKabupaten }}</option>
                            </select>
                            <svg class="pm-select-chevron" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                <polyline points="6 9 12 15 18 9"></polyline>
                            </svg>
                        </div>
                    </div>

                    <div class="pm-edit-row">
                        <div class="pm-edit-label">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <polygon points="12 2 19 21 12 17 5 21 12 2"></polygon>
                            </svg>
                            <span>Kecamatan</span>
                        </div>
                        <div class="pm-edit-input-wrapper">
                            <select name="kecamatan" id="editKecamatan" class="pm-select" data-current="{{ $sppgKecamatan }}" required>
                                <option value="{{ $sppgKecamatan }}" selected>{{ $sppgKecamatan }}</option>
                            </select>
                            <svg class="pm-select-chevron" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                <polyline points="6 9 12 15 18 9"></polyline>
                            </svg>
                        </div>
                    </div>

                    <div class="pm-edit-row">
                        <div class="pm-edit-label">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <rect x="3" y="4" width="18" height="18" rx="2"></rect>
                                <line x1="16" y1="2" x2="16" y2="6"></line>
                                <line x1="8" y1="2" x2="8" y2="6"></line>
                                <line x1="3" y1="10" x2="21" y2="10"></line>
                            </svg>
                            <span>Tanggal dibuat</span>
                        </div>
                        <div class="pm-edit-input-wrapper">
                            <input type="text" class="pm-input pm-input-disabled" value="{{ $tglFormatted }}" disabled>
                            <svg class="pm-input-lock" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                                <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                            </svg>
                        </div>
                    </div>
                </div>

                {{-- Card 3: Dokumen --}}
                <div class="cr-info-card pm-card">
                    <h3 class="cr-card-title pm-card-title">Dokumen</h3>
                    <p class="pm-docs-subtitle">Ganti dokumen jika diperlukan. Format JPG, PNG, atau PDF, maksimal 5 MB per dokumen.</p>
                    <div class="mv2-doc-grid">
                        {{-- Foto KTP --}}
                        <div class="mv2-doc-item">
                            <div class="mv2-doc-img-box" id="editKtpPreview">
                                @if ($fotoKtp)
                                    <img src="{{ $fotoKtp }}" alt="Foto KTP" onerror="this.onerror=null; this.style.display='none'; if(this.nextElementSibling) this.nextElementSibling.style.display='flex';">
                                    <div class="mv2-doc-empty" style="display: none;">
                                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#94A3B8" stroke-width="1.8"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
                                    </div>
                                @else
                                    <div class="mv2-doc-empty">
                                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#94A3B8" stroke-width="1.8"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
                                    </div>
                                @endif
                            </div>
                            <span class="mv2-doc-label">Foto KTP</span>
                            <input type="file" name="foto_ktp" id="editFileKtp" accept="image/jpeg,image/png,application/pdf" style="display: none;" onchange="handleFileSelect(this, 'editKtpPreview')">
                            <button type="button" class="btn-file-view" onclick="document.getElementById('editFileKtp').click()">Ganti Dokumen</button>
                        </div>

                        {{-- Foto Kantor SPPG --}}
                        <div class="mv2-doc-item">
                            <div class="mv2-doc-img-box" id="editKantorPreview">
                                @if ($fotoKantor)
                                    <img src="{{ $fotoKantor }}" alt="Foto Kantor SPPG" onerror="this.onerror=null; this.style.display='none'; if(this.nextElementSibling) this.nextElementSibling.style.display='flex';">
                                    <div class="mv2-doc-empty" style="display: none;">
                                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#94A3B8" stroke-width="1.8"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
                                    </div>
                                @else
                                    <div class="mv2-doc-empty">
                                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#94A3B8" stroke-width="1.8"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
                                    </div>
                                @endif
                            </div>
                            <span class="mv2-doc-label">Foto Kantor SPPG</span>
                            <input type="file" name="foto_kantor_sppg" id="editFileKantor" accept="image/jpeg,image/png,application/pdf" style="display: none;" onchange="handleFileSelect(this, 'editKantorPreview')">
                            <button type="button" class="btn-file-view" onclick="document.getElementById('editFileKantor').click()">Ganti Dokumen</button>
                        </div>

                        {{-- Foto Surat Resmi --}}
                        <div class="mv2-doc-item">
                            <div class="mv2-doc-img-box" id="editSuratPreview">
                                @if ($fotoSurat)
                                    @if (preg_match('/\.pdf($|\?)/i', $fotoSurat))
                                        <canvas class="mv2-pdf-canvas" data-pdf="{{ $fotoSurat }}"></canvas>
                                    @else
                                        <img src="{{ $fotoSurat }}" alt="Foto Surat Resmi" onerror="this.onerror=null; this.style.display='none'; if(this.nextElementSibling) this.nextElementSibling.style.display='flex';">
                                        <div class="mv2-doc-empty" style="display: none;">
                                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#94A3B8" stroke-width="1.8"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>
                                        </div>
                                    @endif
                                @else
                                    <div class="mv2-doc-empty">
                                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#94A3B8" stroke-width="1.8"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>
                                    </div>
                                @endif
                            </div>
                            <span class="mv2-doc-label">Foto Surat Resmi</span>
                            <input type="file" name="foto_surat_resmi" id="editFileSurat" accept="image/jpeg,image/png,application/pdf" style="display: none;" onchange="handleFileSelect(this, 'editSuratPreview')">
                            <button type="button" class="btn-file-view" onclick="document.getElementById('editFileSurat').click()">Ganti Dokumen</button>
                        </div>
                    </div>
                </div>

                {{-- Footer Bar (Mode Edit) --}}
                <div class="pm-footer">
                    <div class="pm-footer-left">
                        <span class="pm-footer-title">Pastikan data sudah sesuai.</span>
                        <span class="pm-footer-sub">Perubahan data akan diverifikasi oleh Admin Sistem sebelum diterapkan.</span>
                    </div>
                    <div class="pm-footer-actions">
                        <button type="button" class="pm-btn-cancel" onclick="setProfilMode('view')">Batal</button>
                        <button type="submit" class="pm-btn-submit-green">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path>
                                <polyline points="17 21 17 13 7 13 7 21"></polyline>
                                <polyline points="7 3 7 8 15 8"></polyline>
                            </svg>
                            <span>Ajukan Perubahan</span>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ========================================================= --}}
{{-- 2. MODAL UBAH KATA SANDI                                  --}}
{{-- ========================================================= --}}
<div class="pm-backdrop hidden" id="passwordModal" style="display: none;" role="dialog" aria-modal="true" aria-labelledby="modalPasswordTitle">
    <div class="pm-dialog pm-dialog-password">
        {{-- Header Top --}}
        <div class="pm-header">
            <h2 class="pm-title" id="modalPasswordTitle">Ubah Kata Sandi</h2>
            <button type="button" class="pm-btn-close" onclick="closePasswordModal()" aria-label="Tutup Modal Ubah Kata Sandi">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
            </button>
        </div>

        {{-- Entity Summary --}}
        <div class="pm-entity-header">
            <div class="pm-entity-left">
                <div class="pm-entity-avatar">{{ $inisialSppg }}</div>
                <div class="pm-entity-info">
                    <span class="pm-entity-name">{{ $sppgNama }}</span>
                    <span class="pm-entity-id">{{ $sppgIdFormatted }}</span>
                </div>
            </div>
            <div class="pm-badge-aktif">
                <span class="pm-badge-dot"></span>
                <span>Aktif</span>
            </div>
        </div>

        <form id="formUbahPassword" action="{{ route('sppg.password.update') }}" method="POST">
            @csrf

            {{-- Card: Keamanan Akun --}}
            <div class="pm-card">
                <h3 class="pm-card-title">Keamanan Akun</h3>

                <div class="pm-edit-row">
                    <div class="pm-edit-label">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                            <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                        </svg>
                        <span>Password Lama</span>
                    </div>
                    <div class="pm-edit-input-wrapper">
                        <input type="password" id="modal_current_password" name="current_password" class="pm-input pm-input-password" placeholder="Masukkan password lama" autocomplete="current-password" required>
                        <button type="button" class="pm-password-toggle" onclick="toggleModalPassword('modal_current_password', 'eyeCurrentPassword')" aria-label="Tampilkan password lama" title="Tampilkan password">
                            <svg id="eyeCurrentPassword" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path>
                                <line x1="1" y1="1" x2="23" y2="23"></line>
                            </svg>
                        </button>
                    </div>
                </div>

                <div class="pm-edit-row">
                    <div class="pm-edit-label">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="7.5" cy="15.5" r="5.5"></circle>
                            <path d="m21 2-9.6 9.6"></path>
                            <path d="m15.5 7.5 3 3"></path>
                        </svg>
                        <span>Password Baru</span>
                    </div>
                    <div class="pm-edit-input-wrapper">
                        <input type="password" id="modal_new_password" name="new_password" class="pm-input pm-input-password" placeholder="Masukkan password baru" autocomplete="new-password" required>
                        <button type="button" class="pm-password-toggle" onclick="toggleModalPassword('modal_new_password', 'eyeNewPassword')" aria-label="Tampilkan password baru" title="Tampilkan password">
                            <svg id="eyeNewPassword" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path>
                                <line x1="1" y1="1" x2="23" y2="23"></line>
                            </svg>
                        </button>
                    </div>
                </div>

                <div class="pm-edit-row">
                    <div class="pm-edit-label">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path>
                            <polyline points="9 12 11 14 15 10"></polyline>
                        </svg>
                        <span>Konfirmasi Password</span>
                    </div>
                    <div class="pm-edit-input-wrapper">
                        <input type="password" id="modal_new_password_confirmation" name="new_password_confirmation" class="pm-input pm-input-password" placeholder="Masukkan ulang password baru" autocomplete="new-password" required>
                        <button type="button" class="pm-password-toggle" onclick="toggleModalPassword('modal_new_password_confirmation', 'eyeConfirmPassword')" aria-label="Tampilkan konfirmasi password baru" title="Tampilkan password">
                            <svg id="eyeConfirmPassword" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path>
                                <line x1="1" y1="1" x2="23" y2="23"></line>
                            </svg>
                        </button>
                    </div>
                </div>
            </div>

            {{-- Footer Bar --}}
            <div class="pm-footer">
                <div class="pm-footer-left">
                    <span class="pm-footer-title">Pastikan kata sandi baru sudah sesuai.</span>
                    <span class="pm-footer-sub">Kata sandi akan diperbarui setelah disimpan.</span>
                </div>
                <div class="pm-footer-actions">
                    <button type="button" class="pm-btn-cancel" onclick="closePasswordModal()">Batal</button>
                    <button type="submit" class="pm-btn-submit-green">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path>
                            <polyline points="17 21 17 13 7 13 7 21"></polyline>
                            <polyline points="7 3 7 8 15 8"></polyline>
                        </svg>
                        <span>Simpan Perubahan</span>
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- ========================================================= --}}
{{-- 3. LIGHTBOX PREVIEW DOKUMEN                               --}}
{{-- ========================================================= --}}
<div class="pm-lightbox-backdrop hidden" id="docLightboxModal" style="display: none;">
    <div class="pm-lightbox-dialog">
        <div class="pm-lightbox-header">
            <h4 style="font-size: 15px; font-weight: 700; color: #071E49;" id="lightboxTitle">Preview Dokumen</h4>
            <button type="button" class="pm-btn-close" onclick="closeLightbox()" aria-label="Tutup Preview">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4">
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
            </button>
        </div>
        <div class="pm-lightbox-body" id="lightboxContent"></div>
    </div>
</div>

{{-- ========================================================= --}}
{{-- 4. CUSTOM NOTIFICATION POPUP MODAL                        --}}
{{-- ========================================================= --}}
<div class="pm-alert-backdrop hidden" id="pmAlertModal" style="display: none;">
    <div class="pm-alert-card">
        <div class="pm-alert-icon-container" id="pmAlertIconContainer">
            <svg id="pmAlertIconSuccess" class="pm-alert-icon" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#16A34A" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                <polyline points="22 4 12 14.01 9 11.01"></polyline>
            </svg>
            <svg id="pmAlertIconError" class="pm-alert-icon" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#DC2626" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="display: none;">
                <circle cx="12" cy="12" r="10"></circle>
                <line x1="15" y1="9" x2="9" y2="15"></line>
                <line x1="9" y1="9" x2="15" y2="15"></line>
            </svg>
            <svg id="pmAlertIconWarning" class="pm-alert-icon" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#D97706" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="display: none;">
                <circle cx="12" cy="12" r="10"></circle>
                <line x1="12" y1="8" x2="12" y2="12"></line>
                <line x1="12" y1="16" x2="12.01" y2="16"></line>
            </svg>
        </div>
        <div class="pm-alert-content">
            <h3 class="pm-alert-title" id="pmAlertTitle">Berhasil</h3>
            <p class="pm-alert-message" id="pmAlertMessage">Pesan notifikasi di sini.</p>
        </div>
        <div class="pm-alert-actions">
            <button type="button" class="pm-alert-btn success" id="pmAlertBtn">Mengerti</button>
        </div>
    </div>
</div>

