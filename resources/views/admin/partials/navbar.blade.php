@php
    $currentUser = \Illuminate\Support\Facades\Auth::user();
    $currentName = $currentUser->nama ?? 'Nanda';
    $inisial = mb_strtoupper(mb_substr(trim($currentName), 0, 1)) ?: 'A';
    $currentRole = $currentUser->role ?? 'admin_sistem';
    $roleLabel = match ($currentRole) {
        'admin_sistem' => 'Admin Sistem',
        'admin_sppg'   => 'Admin SPPG',
        default        => 'Administrator',
    };
@endphp
<!-- Top Navbar / Header -->
<header class="top-header">
    <div class="header-user-section">
        <div class="avatar-circle" aria-hidden="true">{{ $inisial }}</div>
        <div class="user-info">
            <span class="user-name">{{ $currentName }}</span>
            <span class="user-role">{{ $roleLabel }}</span>
        </div>
    </div>
</header>
