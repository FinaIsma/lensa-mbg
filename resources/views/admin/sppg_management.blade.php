<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SPPG Management - Admin Sistem Lensa MBG</title>
    <script>window.adminSppgUpdateBase = '{{ url('admin/sppg') }}';</script>
    @vite(['resources/css/admin_sppg.css', 'resources/js/admin_sppg.js'])
</head>
<body>
    <div class="admin-container">
        <!-- Sidebar Left -->
        <aside class="sidebar">
            <div class="sidebar-brand">
                <a href="{{ route('admin.dashboard') }}" class="brand-link">
                    <img src="{{ asset('images/LogoLensa.png') }}" alt="Logo Lensa MBG" class="brand-logo-img">
                </a>
            </div>

            <nav class="sidebar-nav">
                <a href="{{ route('admin.dashboard') }}" class="nav-item">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="3" y="3" width="7" height="7" rx="1"></rect>
                        <rect x="14" y="3" width="7" height="7" rx="1"></rect>
                        <rect x="14" y="14" width="7" height="7" rx="1"></rect>
                        <rect x="3" y="14" width="7" height="7" rx="1"></rect>
                    </svg>
                    <span>Dashboard</span>
                </a>

                <a href="{{ route('admin.sppg.index') }}" class="nav-item active">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                        <circle cx="9" cy="7" r="4"></circle>
                        <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                        <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                    </svg>
                    <span>SPPG Management</span>
                </a>

                <a href="{{ route('admin.monitoring') }}" class="nav-item">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8S1 12 1 12z"></path>
                        <circle cx="12" cy="12" r="3"></circle>
                    </svg>
                    <span>Monitoring</span>
                </a>

                <a href="{{ route('admin.correction') }}" class="nav-item">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                        <polyline points="14 2 14 8 20 8"></polyline>
                        <line x1="16" y1="13" x2="8" y2="13"></line>
                        <line x1="16" y1="17" x2="8" y2="17"></line>
                    </svg>
                    <span>Correction Request</span>
                </a>

                <a href="{{ route('admin.audit') }}" class="nav-item">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="10"></circle>
                        <polyline points="12 6 12 12 16 14"></polyline>
                    </svg>
                    <span>Audit Trail</span>
                </a>
            </nav>
        </aside>

        <!-- Main Workspace -->
        <div class="main-wrapper">
            <!-- Top Header -->
            <header class="top-header">
                <div class="header-user-section">
                    <div class="avatar-circle">
                        <span>{{ strtoupper(substr(Auth::user()->nama ?? 'Nanda', 0, 1)) }}</span>
                    </div>
                    <div class="user-info">
                        <span class="user-name">{{ Auth::user()->nama ?? 'Nanda' }}</span>
                        <span class="user-role">Admin Sistem</span>
                    </div>
                    <form action="{{ route('logout') }}" method="POST" class="logout-form">
                        @csrf
                        <button type="submit" class="btn-logout" title="Keluar dari sistem">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                                <polyline points="16 17 21 12 16 7"></polyline>
                                <line x1="21" y1="12" x2="9" y2="12"></line>
                            </svg>
                        </button>
                    </form>
                </div>
            </header>

            <!-- Main Content Container -->
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

                <!-- Page Header Title -->
                <div class="page-title-section">
                    <h1 class="page-title">SPPG Management</h1>
                    <p class="page-subtitle">Kelola akun pengguna sistem Lensa MBG, atur role dan status akses</p>
                </div>

                <!-- 4 Stat Summary Cards -->
                <div class="stat-cards-grid">
                    <!-- Card 1: Total SPPG -->
                    <a href="{{ route('admin.sppg.index') }}" class="stat-card {{ !request('status_verif') && !request('card_filter') ? 'active-card' : '' }}">
                        <div class="stat-icon-wrapper icon-blue">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <rect x="4" y="2" width="16" height="20" rx="2"></rect>
                                <path d="M9 22v-4h6v4"></path>
                                <path d="M8 6h.01M16 6h.01M8 10h.01M16 10h.01M8 14h.01M16 14h.01"></path>
                            </svg>
                        </div>
                        <div class="stat-content">
                            <span class="stat-label">Total SPPG</span>
                            <span class="stat-value">{{ $totalCount }}</span>
                            <span class="stat-subtext">SPPG Terdaftar</span>
                        </div>
                    </a>

                    <!-- Card 2: Belum Verifikasi -->
                    <a href="{{ route('admin.sppg.index', ['status_verif' => 'Belum Diverifikasi']) }}" class="stat-card {{ request('status_verif') === 'Belum Diverifikasi' || request('card_filter') === 'pending' ? 'active-card' : '' }}">
                        <div class="stat-icon-wrapper icon-yellow">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M21.5 2v6h-6"></path>
                                <path d="M21.34 15.57a10 10 0 1 1-.57-8.38l5.67-5.67"></path>
                            </svg>
                        </div>
                        <div class="stat-content">
                            <span class="stat-label">Belum Verifikasi</span>
                            <span class="stat-value">{{ $pendingCount }}</span>
                            <span class="stat-subtext">Menunggu Verifikasi</span>
                        </div>
                    </a>

                    <!-- Card 3: Status Aktif -->
                    <a href="{{ route('admin.sppg.index', ['card_filter' => 'aktif']) }}" class="stat-card {{ request('card_filter') === 'aktif' || request('status_verif') === 'Terverifikasi' ? 'active-card' : '' }}">
                        <div class="stat-icon-wrapper icon-green">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                                <polyline points="22 4 12 14.01 9 11.01"></polyline>
                            </svg>
                        </div>
                        <div class="stat-content">
                            <span class="stat-label">Status Aktif</span>
                            <span class="stat-value">{{ $activeCount }}</span>
                            <span class="stat-subtext">SPPG Aktif</span>
                        </div>
                    </a>

                    <!-- Card 4: Status Nonaktif -->
                    <a href="{{ route('admin.sppg.index', ['card_filter' => 'nonaktif']) }}" class="stat-card {{ request('card_filter') === 'nonaktif' || request('status_verif') === 'Ditolak' ? 'active-card' : '' }}">
                        <div class="stat-icon-wrapper icon-red">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="12" cy="12" r="10"></circle>
                                <line x1="15" y1="9" x2="9" y2="15"></line>
                                <line x1="9" y1="9" x2="15" y2="15"></line>
                            </svg>
                        </div>
                        <div class="stat-content">
                            <span class="stat-label">Status Nonaktif</span>
                            <span class="stat-value">{{ $inactiveCount }}</span>
                            <span class="stat-subtext">SPPG Nonaktif</span>
                        </div>
                    </a>
                </div>

                <!-- Filter & Search Control Panel -->
                <div class="table-card">
                    <form action="{{ route('admin.sppg.index') }}" method="GET" class="filter-bar-form">
                        <div class="search-input-box">
                            <svg class="search-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
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
                                    <option value="Ditolak" {{ request('status_verif') == 'Ditolak' ? 'selected' : '' }}>Ditolak</option>
                                </select>
                                <svg class="chevron-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <polyline points="6 9 12 15 18 9"></polyline>
                                </svg>
                            </div>

                            <a href="{{ route('admin.sppg.index') }}" class="btn-reset-filter">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M21.5 2v6h-6"></path>
                                    <path d="M21.34 15.57a10 10 0 1 1-.57-8.38l5.67-5.67"></path>
                                </svg>
                                <span>Hapus Filter</span>
                            </a>
                        </div>
                    </form>

                    <!-- SPPG Table -->
                    <div class="table-responsive">
                        <table class="sppg-table">
                            <thead>
                                <tr>
                                    <th>IDSPPG</th>
                                    <th>Nama Pegawai</th>
                                    <th>Email</th>
                                    <th>SPPG</th>
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

                                        $itemJson = json_encode([
                                            'id_sppg' => $idFormatted,
                                            'raw_id' => $item->id_sppg,
                                            'nama_pegawai' => $item->user->nama ?? '-',
                                            'email' => $item->user->email ?? '-',
                                            'nama_sppg' => $item->nama_sppg,
                                            'status' => $item->status,
                                            'no_telepon' => $item->no_telepon ?? '-',
                                            'alamat_sppg' => $item->alamat_sppg ?? '-',
                                            'provinsi' => $item->provinsi ?? '-',
                                            'kabupaten_kota' => $item->kabupaten_kota ?? '-',
                                            'kecamatan' => $item->kecamatan ?? '-',
                                            'created_at' => $tglFormatted,
                                            'foto_ktp' => $item->foto_ktp ? (str_starts_with($item->foto_ktp, 'http') ? $item->foto_ktp : asset($item->foto_ktp)) : null,
                                            'foto_kantor_sppg' => $item->foto_kantor_sppg ? (str_starts_with($item->foto_kantor_sppg, 'http') ? $item->foto_kantor_sppg : asset($item->foto_kantor_sppg)) : null,
                                            'foto_surat_resmi' => $item->foto_surat_resmi ? (str_starts_with($item->foto_surat_resmi, 'http') ? $item->foto_surat_resmi : asset($item->foto_surat_resmi)) : null,
                                        ]);
                                    @endphp
                                    <tr>
                                        <td class="font-bold text-navy">{{ $idFormatted }}</td>
                                        <td class="font-medium">{{ $item->user->nama ?? '-' }}</td>
                                        <td class="text-muted">{{ $item->user->email ?? '-' }}</td>
                                        <td class="font-medium">{{ $item->nama_sppg }}</td>
                                        <td>
                                            @if ($item->status === 'aktif')
                                                <span class="badge-verif badge-terverifikasi">Terverifikasi</span>
                                            @elseif ($item->status === 'menunggu_verifikasi')
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
                                            @elseif ($item->status === 'menunggu_verifikasi')
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
                                            @if ($item->status === 'menunggu_verifikasi')
                                                <button type="button" class="btn-action btn-verifikasi" onclick='openVerifikasiModal({{ $itemJson }})'>
                                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                        <path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                                                        <circle cx="9" cy="7" r="4"></circle>
                                                        <polyline points="16 11 18 13 22 9"></polyline>
                                                    </svg>
                                                    <span>Verifikasi</span>
                                                </button>
                                            @else
                                                <button type="button" class="btn-action btn-detail" onclick='openDetailModal({{ $itemJson }})'>
                                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
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

                    <!-- Pagination Footer -->
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

    <!-- Modal Detail SPPG -->
    <div id="modalDetail" class="modal-backdrop hidden">
        <div class="modal-dialog">
            <div class="modal-header">
                <h3 class="modal-title">Detail SPPG</h3>
                <button type="button" class="btn-close" onclick="closeModal('modalDetail')">&times;</button>
            </div>
            <div class="modal-body" id="detailModalContent">
            </div>
            <div class="modal-footer full-width-footer">
                <button type="button" class="btn-modal-close" onclick="closeModal('modalDetail')">Tutup</button>
            </div>
        </div>
    </div>

    <!-- Modal Verifikasi SPPG -->
    <div id="modalVerifikasi" class="modal-backdrop hidden">
        <div class="modal-dialog">
            <div class="modal-header">
                <h3 class="modal-title">Detail SPPG</h3>
                <button type="button" class="btn-close" onclick="closeModal('modalVerifikasi')">&times;</button>
            </div>
            <form id="formVerifikasi" action="" method="POST">
                @csrf
                <input type="hidden" name="status" id="inputStatusVal" value="aktif">

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
</body>
</html>
