@php
    $currentUser = \Illuminate\Support\Facades\Auth::user();
    $currentName = $currentUser->nama ?? 'Nanda Ricco';
    $firstName = explode(' ', trim($currentName))[0] ?: 'Nanda';
    $inisial = mb_strtoupper(mb_substr(trim($firstName), 0, 1)) ?: 'N';
    $currentRole = $currentUser->role ?? 'admin';
    $roleLabel = match ($currentRole) {
        'admin' => 'Admin Sistem',
        'kepala_sppg' => 'Kepala SPPG',
        'staff' => 'Staff',
        default => 'Administrator',
    };
@endphp

<header class="top-header">
    <div class="header-profile-dropdown" id="profileDropdownWrapper">
        <button type="button" class="header-user-btn" id="profileDropdownBtn" aria-expanded="false" aria-haspopup="true">
            <div class="avatar-circle" aria-hidden="true">{{ $inisial }}</div>
            <div class="user-info">
                <span class="user-name">{{ $firstName }}</span>
                <span class="user-role">{{ $roleLabel }}</span>
            </div>
            <svg class="dropdown-chevron" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                <polyline points="6 9 12 15 18 9"></polyline>
            </svg>
        </button>

        <div class="profile-dropdown-menu" id="profileDropdownMenu">
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
