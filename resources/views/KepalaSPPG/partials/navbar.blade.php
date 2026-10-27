@php
    $currentUser = auth('staff')->user() ?: auth()->user();
    $currentName = $currentUser->nama ?? 'Nanda';
    $inisial = mb_strtoupper(mb_substr(trim($currentName), 0, 1)) ?: 'N';
    $currentRole = $currentUser->role ?? 'kepala_sppg';
    $roleLabel = match ($currentRole) {
        'admin' => 'Admin',
        'kepala_sppg' => 'Kepala SPPG',
        'staff' => 'Staff',
        default => 'Kepala SPPG',
    };
@endphp

<header class="top-header">
    <div class="header-profile-dropdown" id="profileDropdownWrapper">
        <button type="button" class="header-user-btn" id="profileDropdownBtn" aria-expanded="false" aria-haspopup="true">
            <div class="avatar-circle" aria-hidden="true">{{ $inisial }}</div>
            <div class="user-info">
                <span class="user-name">{{ $currentName }}</span>
                <span class="user-role">{{ $roleLabel }}</span>
            </div>
            <svg class="dropdown-chevron" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                <polyline points="6 9 12 15 18 9"></polyline>
            </svg>
        </button>

        <div class="profile-dropdown-menu" id="profileDropdownMenu">
            <button type="button" class="profile-dropdown-item" onclick="openProfilModal()">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                    <circle cx="12" cy="7" r="4"></circle>
                </svg>
                <span>Profil & Informasi SPPG</span>
            </button>

            <button type="button" class="profile-dropdown-item" onclick="openPasswordModal()">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="7.5" cy="15.5" r="5.5"></circle>
                    <path d="m21 2-9.6 9.6"></path>
                    <path d="m15.5 7.5 3 3"></path>
                </svg>
                <span>Ubah Kata Sandi</span>
            </button>

            <div class="dropdown-divider"></div>

            <form action="{{ route('logout') }}" method="POST" class="dropdown-logout-form">
                @csrf
                <button type="submit" class="dropdown-logout-btn">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                        <polyline points="16 17 21 12 16 7"></polyline>
                        <line x1="21" y1="12" x2="9" y2="12"></line>
                    </svg>
                    <span>Logout</span>
                </button>
            </form>
        </div>
    </div>
</header>

<script>
    (function () {
        const btn = document.getElementById('profileDropdownBtn');
        const menu = document.getElementById('profileDropdownMenu');
        const wrapper = document.getElementById('profileDropdownWrapper');

        if (!btn || !wrapper) return;

        btn.addEventListener('click', function (e) {
            e.stopPropagation();
            const isOpen = wrapper.classList.toggle('open');
            btn.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
        });

        document.addEventListener('click', function (e) {
            if (!wrapper.contains(e.target)) {
                wrapper.classList.remove('open');
                btn.setAttribute('aria-expanded', 'false');
            }
        });

        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') {
                wrapper.classList.remove('open');
                btn.setAttribute('aria-expanded', 'false');
            }
        });
    })();
</script>
