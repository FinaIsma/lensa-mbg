<aside class="sidebar">
    <div class="sidebar-brand">
        <a href="{{ route('sppg.dashboard') }}" class="brand-link">
            <img src="{{ asset('images/LogoMBG1.png') }}" alt="Logo Lensa MBG" class="brand-logo-img">
        </a>
    </div>

    <nav class="sidebar-nav">
        <a href="{{ route('sppg.dashboard') }}" class="nav-item {{ request()->routeIs('sppg.dashboard') ? 'active' : '' }}">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <rect x="3" y="3" width="7" height="7" rx="1"></rect>
                <rect x="14" y="3" width="7" height="7" rx="1"></rect>
                <rect x="14" y="14" width="7" height="7" rx="1"></rect>
                <rect x="3" y="14" width="7" height="7" rx="1"></rect>
            </svg>
            <span>Dashboard</span>
        </a>

        <a href="{{ route('sppg.correction') }}" class="nav-item {{ request()->routeIs('sppg.correction*') ? 'active' : '' }}">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                <polyline points="14 2 14 8 20 8"></polyline>
                <path d="M9 15l2 2 4-4"></path>
            </svg>
            <span>Correct Request</span>
        </a>

        <a href="{{ route('sppg.audit') }}" class="nav-item {{ request()->routeIs('sppg.audit*') ? 'active' : '' }}">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"></path>
                <polyline points="3 3 3 8 8 8"></polyline>
                <line x1="12" y1="7" x2="12" y2="12"></line>
                <line x1="12" y1="12" x2="16" y2="14"></line>
            </svg>
            <span>Audit Trail</span>
        </a>

        <a href="{{ route('sppg.staff.index') }}" class="nav-item {{ request()->routeIs('sppg.staff*') ? 'active' : '' }}">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                <circle cx="9" cy="7" r="4"></circle>
                <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
            </svg>
            <span>Staff Manajemen</span>
        </a>
    </nav>
</aside>
