@php
    $currentUser = auth('staff')->user() ?: auth()->user();
    $currentName = $currentUser->nama ?? 'Roh Yu-na';
    $inisial = mb_strtoupper(mb_substr(trim($currentName), 0, 1)) ?: 'R';
@endphp

<header class="top-header">
    <div class="header-profile-dropdown" id="profileDropdownWrapper">
        <button type="button" class="header-user-btn" id="profileDropdownBtn" aria-expanded="false" aria-haspopup="true">
            <div class="avatar-circle" aria-hidden="true">{{ $inisial }}</div>
            <div class="user-info">
                <span class="user-name">{{ $currentName }}</span>
                <span class="user-role">Staff SPPG</span>
            </div>
            <svg class="dropdown-chevron" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                <polyline points="6 9 12 15 18 9"></polyline>
            </svg>
        </button>

        <div class="profile-dropdown-menu" id="profileDropdownMenu">
            <div class="profile-dropdown-header">
                <strong>{{ $currentName }}</strong>
                <small>Staff SPPG</small>
            </div>
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
