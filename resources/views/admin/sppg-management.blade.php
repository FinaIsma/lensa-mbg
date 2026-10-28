<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SPPG Management - Admin Sistem Lensa MBG</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script>window.adminSppgUpdateBase = '{{ url('admin/sppg') }}';</script>
    <script src="https://cdn.jsdelivr.net/npm/pdfjs-dist@3.11.174/build/pdf.min.js"></script>
    @vite(['resources/css/admin/sppg-management.css', 'resources/js/admin/sppg-management.js'])
</head>
<body>
    <div class="admin-container">
        @include('admin.partials.sidebar')

        <div class="main-wrapper">
            @include('admin.partials.navbar')

            <main class="content-body">
                @if (session('success'))
                    <div class="alert alert-success">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                            <polyline points="22 4 12 14.01 9 11.01"></polyline>
                        </svg>
                        <span>{{ session('success') }}</span>
                    </div>
                @endif

                @if (session('info'))
                    <div class="alert alert-info">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="10"></circle>
                            <line x1="12" y1="16" x2="12" y2="12"></line>
                            <line x1="12" y1="8" x2="12.01" y2="8"></line>
                        </svg>
                        <span>{{ session('info') }}</span>
                    </div>
                @endif

                <div class="page-title-section">
                    <h1 class="page-title">SPPG Management</h1>
                    <p class="page-subtitle">Kelola akun pengguna sistem Lensa MBG, atur role dan status akses</p>
                </div>

                <div class="stat-cards-grid">

                    <div class="stat-card">
                        <div class="stat-icon-wrapper icon-blue">
                            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <rect x="4" y="2" width="16" height="20" rx="2"></rect>
                                <path d="M9 22v-4h6v4"></path>
                                <rect x="8" y="6" width="2" height="2"></rect>
                                <rect x="14" y="6" width="2" height="2"></rect>
                                <rect x="8" y="11" width="2" height="2"></rect>
                                <rect x="14" y="11" width="2" height="2"></rect>
                            </svg>
                        </div>
                        <div class="stat-content">
                            <span class="stat-label">Total SPPG</span>
                            <span class="stat-value">{{ $totalCount }}</span>
                            <span class="stat-subtext">SPPG Terdaftar</span>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-icon-wrapper icon-yellow">
                            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"></path>
                                <polyline points="3 3 3 8 8 8"></polyline>
                                <line x1="12" y1="7" x2="12" y2="12"></line>
                                <line x1="12" y1="12" x2="16" y2="14"></line>
                            </svg>
                        </div>
                        <div class="stat-content">
                            <span class="stat-label">Belum Verifikasi</span>
                            <span class="stat-value">{{ $pendingCount }}</span>
                            <span class="stat-subtext">Menunggu Verifikasi</span>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-icon-wrapper icon-green">
                            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                                <polyline points="22 4 12 14.01 9 11.01"></polyline>
                            </svg>
                        </div>
                        <div class="stat-content">
                            <span class="stat-label">Status Aktif</span>
                            <span class="stat-value">{{ $activeCount }}</span>
                            <span class="stat-subtext">SPPG Aktif</span>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-icon-wrapper icon-red">
                            <svg width="22" height="22" viewBox="0 0 24 24" fill="none">
                                <circle cx="12" cy="12" r="10" fill="#DC2626"></circle>
                                <path d="M15 9l-6 6m0-6l6 6" stroke="#FFFFFF" stroke-width="2" stroke-linecap="round"></path>
                            </svg>
                        </div>
                        <div class="stat-content">
                            <span class="stat-label">Status Nonaktif</span>
                            <span class="stat-value">{{ $inactiveCount }}</span>
                            <span class="stat-subtext">SPPG Nonaktif</span>
                        </div>
                    </div>
                </div>

                <div class="table-card">
                    <form action="{{ route('admin.sppg.index') }}" method="GET" class="filter-bar-form">
                        <div class="search-input-box">
                            <svg class="search-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="11" cy="11" r="8"></circle>
                                <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                            </svg>
                            <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama, email atau tempat SPPG" class="input-search">
                        </div>

                        <div class="filter-actions-group">
                            <div class="select-dropdown-box">
                                <select name="status_verif" class="select-status-verif" onchange="this.form.submit()">
                                    <option value="semua" {{ !request('status_verif') || request('status_verif') == 'semua' ? 'selected' : '' }}>Status Verif</option>
                                    <option value="Terverifikasi" {{ request('status_verif') == 'Terverifikasi' ? 'selected' : '' }}>Terverifikasi</option>
                                    <option value="Belum Diverifikasi" {{ request('status_verif') == 'Belum Diverifikasi' ? 'selected' : '' }}>Belum Diverifikasi</option>
                                    <option value="Perubahan" {{ request('status_verif') == 'Perubahan' ? 'selected' : '' }}>Perubahan</option>
                                    <option value="Ditolak" {{ request('status_verif') == 'Ditolak' ? 'selected' : '' }}>Ditolak</option>
                                </select>
                                <svg class="chevron-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <polyline points="6 9 12 15 18 9"></polyline>
                                </svg>
                            </div>

                            <a href="{{ route('admin.sppg.index') }}" class="btn-reset-filter">
                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"></path>
                                    <polyline points="3 3 3 8 8 8"></polyline>
                                </svg>
                                <span>Hapus Filter</span>
                            </a>
                        </div>
                    </form>

                    <div class="table-responsive">
                        <table class="sppg-table">
                            <thead>
                                <tr>
                                    <th>IDSPPG</th>
                                    <th>SPPG</th>
                                    <th>Kepala SPPG</th>
                                    <th>Staff SPPG</th>
                                    <th>Status Verif</th>
                                    <th>Status</th>
                                    <th class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($sppgs as $item)
                                    @php
                                        $idFormatted = 'SPPG' . str_pad($item->id_sppg, 3, '0', STR_PAD_LEFT);
                                        $bulanIndo = [1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
                                        $tgl = $item->created_at ? \Carbon\Carbon::parse($item->created_at) : now();
                                        $tglFormatted = $tgl->format('d') . ' ' . ($bulanIndo[(int)$tgl->format('n')] ?? $tgl->format('F')) . ' ' . $tgl->format('Y');

                                        $pendingStaffs = $item->staffs ? $item->staffs->whereIn('status', ['menunggu', 'menunggu_persetujuan'])->values() : collect();
                                        $pendingStaffCount = $pendingStaffs->count();
                                        $hasPendingStaffs = $pendingStaffCount > 0;
                                        $hasChanges = !empty($item->proposed) || ($item->status_verifikasi === 'perubahan') || $hasPendingStaffs;

                                        $activeStaff = $item->staffs ? $item->staffs->where('status', 'aktif')->sortByDesc('id')->first() : null;
                                        $staffLama = $activeStaff ?: $item->staff;

                                        $itemJson = json_encode([
                                            'id_sppg' => $idFormatted,
                                            'raw_id' => $item->id_sppg,
                                            'nama_pegawai' => $item->user->nama ?? '-',
                                            'email' => $item->user->email ?? '-',
                                            'no_telepon' => $item->no_telepon ?? '-',
                                            'staff_nama' => $staffLama?->nama ?? '-',
                                            'staff_email' => $staffLama?->email ?? '-',
                                            'staff_no_telepon' => $staffLama?->no_telepon ?? '-',
                                            'has_active_staff' => (bool) $activeStaff,
                                            'nama_sppg' => $item->nama_sppg,
                                            'status' => $item->status,
                                            'status_verifikasi' => $item->status_verifikasi ?? 'terverifikasi',
                                            'alamat_sppg' => $item->alamat_sppg ?? '-',
                                            'provinsi' => $item->provinsi ?? '-',
                                            'kabupaten_kota' => $item->kabupaten_kota ?? '-',
                                            'kecamatan' => $item->kecamatan ?? '-',
                                            'created_at' => $tglFormatted,
                                            'foto_ktp' => $item->foto_ktp ? (str_starts_with($item->foto_ktp, 'http') ? $item->foto_ktp : asset($item->foto_ktp)) : null,
                                            'foto_kantor_sppg' => $item->foto_kantor_sppg ? (str_starts_with($item->foto_kantor_sppg, 'http') ? $item->foto_kantor_sppg : asset($item->foto_kantor_sppg)) : null,
                                            'foto_surat_resmi' => $item->foto_surat_resmi ? (str_starts_with($item->foto_surat_resmi, 'http') ? $item->foto_surat_resmi : asset($item->foto_surat_resmi)) : null,
                                            'has_changes' => $hasChanges,
                                            'has_pending_staffs' => $hasPendingStaffs,
                                            'pending_staffs' => $pendingStaffs->map(fn ($st) => [
                                                'id' => $st->id,
                                                'nama' => $st->nama,
                                                'email' => $st->email,
                                                'no_telepon' => $st->no_telepon ?? '-',
                                                'created_at' => $st->created_at ? $st->created_at->locale('id')->translatedFormat('d F Y') : '-',
                                            ])->all(),
                                            'all_staffs' => $item->staffs ? $item->staffs->map(fn ($st) => [
                                                'id' => $st->id,
                                                'nama' => $st->nama,
                                                'email' => $st->email,
                                                'no_telepon' => $st->no_telepon ?? '-',
                                                'status' => $st->status,
                                                'catatan_admin' => $st->catatan_admin,
                                            ])->all() : [],
                                            'proposed' => $item->proposed ?? null,
                                            'before_values' => $item->before_values ?? null,
                                        ]);
                                    @endphp
                                    <tr>
                                        <td class="font-medium text-dark">{{ $idFormatted }}</td>
                                        <td class="font-medium text-dark">{{ $item->nama_sppg }}</td>
                                        <td class="font-medium text-dark">{{ $item->user->nama ?? '-' }}</td>
                                        <td class="font-medium text-dark">
                                            <div class="staff-cell-wrapper">
                                                <span class="staff-cell-name">{{ $staffLama?->nama ?? '-' }}</span>
                                                @if ($pendingStaffCount > 0)
                                                    <span class="badge-staff-pending-count" title="{{ $pendingStaffCount }} staf baru menunggu verifikasi">
                                                        <span class="badge-staff-dot"></span>
                                                        <span>+{{ $pendingStaffCount }} baru</span>
                                                    </span>
                                                @endif
                                            </div>
                                        </td>
                                        <td>
                                            @if ($item->status_verifikasi === 'terverifikasi' && $pendingStaffCount === 0)
                                                <span class="badge-verif badge-terverifikasi">Terverifikasi</span>
                                            @elseif ($item->status_verifikasi === 'perubahan' || $pendingStaffCount > 0)
                                                <span class="badge-verif badge-perubahan">Perubahan</span>
                                            @elseif ($item->status_verifikasi === 'menunggu_verifikasi')
                                                <span class="badge-verif badge-belum">Belum Diverifikasi</span>
                                            @else
                                                <span class="badge-verif badge-ditolak">Ditolak</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if ($item->status === 'aktif')
                                                <span class="badge-status badge-status-aktif">
                                                     <span class="status-dot"></span> Aktif
                                                </span>
                                            @elseif ($item->status_verifikasi === 'menunggu_verifikasi' || $item->status === 'menunggu_verifikasi')
                                                <span class="badge-status badge-status-belum">
                                                     <span class="status-dot"></span> Belum Aktif
                                                </span>
                                            @else
                                                <span class="badge-status badge-status-nonaktif">
                                                     <span class="status-dot"></span> Nonaktif
                                                </span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            @if ($item->status_verifikasi === 'menunggu_verifikasi' || $item->status_verifikasi === 'perubahan' || $pendingStaffCount > 0)
                                                <button type="button" class="btn-action btn-verifikasi" onclick='openVerifikasiModal({{ $itemJson }})'>
                                                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                        <path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                                                        <circle cx="9" cy="7" r="4"></circle>
                                                        <polyline points="16 11 18 13 22 9"></polyline>
                                                    </svg>
                                                    <span>Verifikasi</span>
                                                </button>
                                            @else
                                                <button type="button" class="btn-action btn-detail" onclick='openDetailModal({{ $itemJson }})'>
                                                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8S1 12 1 12z"></path>
                                                        <circle cx="12" cy="12" r="3"></circle>
                                                    </svg>
                                                    <span>Detail</span>
                                                </button>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="empty-state">
                                            Tidak ada data SPPG yang sesuai dengan kriteria filter.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="table-pagination-footer">
                        <div class="pagination-info">
                            Menampilkan {{ $sppgs->firstItem() ?? 0 }}-{{ $sppgs->lastItem() ?? 0 }} dari {{ $sppgs->total() }} data
                        </div>
                        <div class="pagination-links">
                            {{ $sppgs->onEachSide(1)->links('pagination::bootstrap-4') }}
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <div id="modalDetail" class="modal-backdrop hidden">
        <div class="modal-dialog">
            <div class="modal-header">
                <h3 class="modal-title">Detail SPPG</h3>
                <button type="button" class="btn-close" onclick="closeModal('modalDetail')" aria-label="Tutup">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4">
                        <line x1="18" y1="6" x2="6" y2="18"></line>
                        <line x1="6" y1="6" x2="18" y2="18"></line>
                    </svg>
                </button>
            </div>
            <form id="formDetail" action="" method="POST">
                @csrf
                <input type="hidden" name="status" id="inputDetailStatusVal" value="nonaktif">

                <div class="modal-body" id="detailModalContent">
                </div>

                <div class="modal-footer full-width-footer">
                    <button type="button" id="btnDetailDeactivate" class="btn-modal-action-deactivate" onclick="submitDeactivateDetail()">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                            <circle cx="8.5" cy="7" r="4"></circle>
                            <line x1="18" y1="8" x2="23" y2="13"></line>
                            <line x1="23" y1="8" x2="18" y2="13"></line>
                        </svg>
                        <span id="btnDetailText">Nonaktifkan</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div id="modalVerifikasi" class="modal-backdrop hidden">
        <div class="modal-dialog">
            <div class="modal-header">
                <h3 class="modal-title">Detail SPPG</h3>
                <button type="button" class="btn-close" onclick="closeModal('modalVerifikasi')" aria-label="Tutup">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4">
                        <line x1="18" y1="6" x2="6" y2="18"></line>
                        <line x1="6" y1="6" x2="18" y2="18"></line>
                    </svg>
                </button>
            </div>
            <form id="formVerifikasi" action="" method="POST">
                @csrf
                <input type="hidden" name="status" id="inputStatusVal" value="aktif">
                <input type="hidden" name="review_note" id="inputReviewNote" value="">

                <div class="modal-body" id="verifikasiModalContent">
                </div>

                <div class="modal-footer full-width-footer">
                    <div class="verify-action-grid">
                        <button type="button" class="btn-modal-action-approve" onclick="submitVerifikasi('aktif')">
                            <svg width="22" height="22" viewBox="0 0 24 24" fill="none">
                                <circle cx="12" cy="12" r="10" fill="#16A34A"/>
                                <path d="M8 12.5l2.5 2.5 5.5-5.5" stroke="#FFFFFF" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                            <span>Verifikasi</span>
                        </button>

                        <button type="button" class="btn-modal-action-reject" onclick="submitVerifikasi('nonaktif')">
                            <svg width="22" height="22" viewBox="0 0 24 24" fill="none">
                                <circle cx="12" cy="12" r="10" fill="#DC2626"/>
                                <path d="M15 9l-6 6m0-6l6 6" stroke="#FFFFFF" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                            <span>Tolak Verifikasi</span>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Modal Konfirmasi / Catatan Tolak Verifikasi --}}
    <div id="modalRejectSppg" class="sppg-confirm-backdrop hidden">
        <div class="sppg-confirm-card">
            <div class="sppg-confirm-header">
                <div class="sppg-confirm-icon-danger">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#DC2626" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="10"></circle>
                        <line x1="15" y1="9" x2="9" y2="15"></line>
                        <line x1="9" y1="9" x2="15" y2="15"></line>
                    </svg>
                </div>
                <div>
                    <h3 class="sppg-confirm-title">Tolak Verifikasi SPPG</h3>
                    <p class="sppg-confirm-desc">Masukkan catatan atau alasan penolakan untuk pihak Kepala SPPG (opsional):</p>
                </div>
            </div>

            <div class="sppg-confirm-body">
                <textarea id="rejectReasonTextarea" class="sppg-confirm-textarea" placeholder="Tulis catatan atau alasan penolakan di sini (contoh: Dokumen SK/KTP buram, silakan upload ulang)..." rows="3"></textarea>
            </div>

            <div class="sppg-confirm-actions">
                <button type="button" class="btn-confirm-cancel" onclick="closeRejectModal()">Batal</button>
                <button type="button" class="btn-confirm-reject" onclick="confirmRejectVerifikasi()">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                        <line x1="18" y1="6" x2="6" y2="18"></line>
                        <line x1="6" y1="6" x2="18" y2="18"></line>
                    </svg>
                    <span>Tolak Verifikasi</span>
                </button>
            </div>
        </div>
    </div>
</body>
</html>
