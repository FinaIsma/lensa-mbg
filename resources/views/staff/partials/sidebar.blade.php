<aside class="sidebar">
    <div class="sidebar-brand">
        <a href="{{ route('staff.dashboard') }}" class="brand-link">
            <img src="{{ asset('images/LogoMBG1.png') }}" alt="Logo Lensa MBG" class="brand-logo-img">
        </a>
    </div>

    <nav class="sidebar-nav">
        {{-- 1. Dashboard --}}
        <a href="{{ route('staff.dashboard') }}" class="nav-item {{ request()->routeIs('staff.dashboard') ? 'active' : '' }}">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <rect x="3" y="3" width="7" height="7" rx="1"></rect>
                <rect x="14" y="3" width="7" height="7" rx="1"></rect>
                <rect x="14" y="14" width="7" height="7" rx="1"></rect>
                <rect x="3" y="14" width="7" height="7" rx="1"></rect>
            </svg>
            <span>Dashboard</span>
        </a>

        {{-- 2. Master (Dropdown) --}}
        @php
            $isMasterActive = request()->routeIs('staff.menu-resep.*') || request()->routeIs('staff.menus.*') || request()->routeIs('staff.recipes.*');
        @endphp
        <div class="nav-group {{ $isMasterActive ? 'open' : '' }}">
            <button type="button" class="nav-group-header {{ $isMasterActive ? 'parent-active' : '' }}" onclick="toggleSidebarNavGroup(this)">
                <div class="nav-group-title">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <ellipse cx="12" cy="5" rx="9" ry="3"></ellipse>
                        <path d="M21 12c0 1.66-4 3-9 3s-9-1.34-9-3"></path>
                        <path d="M3 5v14c0 1.66 4 3 9 3s9-1.34 9-3V5"></path>
                    </svg>
                    <span>Master</span>
                </div>
                <svg class="chevron-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="6 9 12 15 18 9"></polyline>
                </svg>
            </button>

            <div class="nav-subitems">
                <a href="#" class="nav-subitem" onclick="return false;">
                    <span>Pemasok</span>
                </a>
                <a href="#" class="nav-subitem" onclick="return false;">
                    <span>Bahan</span>
                </a>
                <a href="{{ route('staff.menu-resep.index') }}" class="nav-subitem {{ $isMasterActive ? 'active' : '' }}">
                    <span>Menu dan Resep</span>
                </a>
                <a href="#" class="nav-subitem" onclick="return false;">
                    <span>Tujuan</span>
                </a>
            </div>
        </div>

        {{-- 3. Pengadaan (Dropdown) --}}
        @php
            $isPengadaanActive = request()->routeIs('staff.purchases.*') || request()->routeIs('staff.receivings.*');
        @endphp
        <div class="nav-group {{ $isPengadaanActive ? 'open' : '' }}">
            <button type="button" class="nav-group-header {{ $isPengadaanActive ? 'parent-active' : '' }}" onclick="toggleSidebarNavGroup(this)">
                <div class="nav-group-title">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="9" cy="21" r="1"></circle>
                        <circle cx="20" cy="21" r="1"></circle>
                        <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>
                    </svg>
                    <span>Pengadaan</span>
                </div>
                <svg class="chevron-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="6 9 12 15 18 9"></polyline>
                </svg>
            </button>
            <div class="nav-subitems">
                <a href="{{ route('staff.purchases.index') }}" class="nav-subitem {{ request()->routeIs('staff.purchases.*') ? 'active' : '' }}"><span>Pembelian</span></a>
                <a href="{{ route('staff.receivings.index') }}" class="nav-subitem {{ request()->routeIs('staff.receivings.*') ? 'active' : '' }}"><span>Penerimaan</span></a>
            </div>
        </div>

        @php
            $isStokActive = request()->routeIs('staff.lots.*') || request()->routeIs('staff.riwayat.*');
        @endphp
        <div class="nav-group {{ $isStokActive ? 'open' : '' }}">
            <button type="button" class="nav-group-header {{ $isStokActive ? 'parent-active' : '' }}" onclick="toggleSidebarNavGroup(this)">
                <div class="nav-group-title">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path>
                        <polyline points="3.27 6.96 12 12.01 20.73 6.96"></polyline>
                        <line x1="12" y1="22.08" x2="12" y2="12"></line>
                    </svg>
                    <span>Stok</span>
                </div>
                <svg class="chevron-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="6 9 12 15 18 9"></polyline>
                </svg>
            </button>
            <div class="nav-subitems">
                <a href="{{ route('staff.lots.index') }}" class="nav-subitem {{ request()->routeIs('staff.lots.*') ? 'active' : '' }}"><span>Lot Bahan</span></a>
                <a href="{{ route('staff.riwayat.index') }}" class="nav-subitem {{ request()->routeIs('staff.riwayat.*') ? 'active' : '' }}"><span>Riwayat Stok</span></a>
            </div>
        </div>

        {{-- 5. Produksi (Dropdown) --}}
        <div class="nav-group">
            <button type="button" class="nav-group-header" onclick="toggleSidebarNavGroup(this)">
                <div class="nav-group-title">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M18 2l4 4-10 10-4-4L18 2z"></path>
                        <path d="M14.5 5.5l4 4"></path>
                        <path d="M3 21l3-3"></path>
                        <path d="M6 18l-3 3"></path>
                        <line x1="8" y1="16" x2="4" y2="20"></line>
                    </svg>
                    <span>Produksi</span>
                </div>
                <svg class="chevron-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="6 9 12 15 18 9"></polyline>
                </svg>
            </button>
            <div class="nav-subitems">
                <a href="#" class="nav-subitem" onclick="return false;"><span>Batch</span></a>
                <a href="#" class="nav-subitem" onclick="return false;"><span>Pengolahan</span></a>
                <a href="#" class="nav-subitem" onclick="return false;"><span>Hasil</span></a>
            </div>
        </div>

        {{-- 6. Distribusi (Dropdown) --}}
        <div class="nav-group">
            <button type="button" class="nav-group-header" onclick="toggleSidebarNavGroup(this)">
                <div class="nav-group-title">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="1" y="3" width="15" height="13"></rect>
                        <polygon points="16 8 20 8 23 11 23 16 16 16 16 8"></polygon>
                        <circle cx="5.5" cy="18.5" r="2.5"></circle>
                        <circle cx="18.5" cy="18.5" r="2.5"></circle>
                    </svg>
                    <span>Distribusi</span>
                </div>
                <svg class="chevron-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="6 9 12 15 18 9"></polyline>
                </svg>
            </button>
            <div class="nav-subitems">
                <a href="#" class="nav-subitem" onclick="return false;"><span>Pengiriman</span></a>
                <a href="#" class="nav-subitem" onclick="return false;"><span>Limbah</span></a>
            </div>
        </div>
    </nav>
</aside>

<script>
    function toggleSidebarNavGroup(button) {
        const currentGroup = button.closest('.nav-group');
        if (!currentGroup) return;

        const isCurrentlyOpen = currentGroup.classList.contains('open');

        // Tutup semua dropdown menu lainnya (accordion mode: hanya 1 yang bisa terbuka)
        document.querySelectorAll('.sidebar-nav .nav-group').forEach(group => {
            if (group !== currentGroup) {
                group.classList.remove('open');
            }
        });

        // Buka jika sebelumnya tertutup, atau tutup jika diklik kembali (tutup manual)
        if (isCurrentlyOpen) {
            currentGroup.classList.remove('open');
        } else {
            currentGroup.classList.add('open');
        }
    }
</script>
