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
            <form action="{{ route('logout') }}" method="POST" class="dropdown-logout-form" id="staffLogoutForm">
                @csrf
                <button type="button" class="dropdown-logout-btn" onclick="openLogoutModal()">
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

{{-- Modal Konfirmasi Logout --}}
<div class="logout-modal-backdrop" id="logoutConfirmModal" style="display: none;" role="dialog" aria-modal="true" aria-labelledby="logoutModalTitle" aria-describedby="logoutModalDesc">
    <div class="logout-modal-card">
        <button type="button" class="logout-modal-close" onclick="closeLogoutModal()" aria-label="Tutup modal">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                <line x1="18" y1="6" x2="6" y2="18"></line>
                <line x1="6" y1="6" x2="18" y2="18"></line>
            </svg>
        </button>

        <div class="logout-modal-icon-wrap">
            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#DC2626" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                <polyline points="16 17 21 12 16 7"></polyline>
                <line x1="21" y1="12" x2="9" y2="12"></line>
            </svg>
        </div>

        <div class="logout-modal-content">
            <h3 class="logout-modal-title" id="logoutModalTitle">Konfirmasi Logout</h3>
            <p class="logout-modal-desc" id="logoutModalDesc">Apakah Anda yakin ingin keluar dari sistem? Anda harus login kembali untuk mengakses akun ini.</p>
        </div>

        <div class="logout-modal-actions">
            <button type="button" class="logout-btn-cancel" onclick="closeLogoutModal()">Batal</button>
            <button type="button" class="logout-btn-confirm" onclick="submitLogoutForm()">
                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                    <polyline points="16 17 21 12 16 7"></polyline>
                    <line x1="21" y1="12" x2="9" y2="12"></line>
                </svg>
                <span>Ya, Logout</span>
            </button>
        </div>
    </div>
</div>

<script>
    (function () {
        const btn = document.getElementById('profileDropdownBtn');
        const wrapper = document.getElementById('profileDropdownWrapper');
        const modal = document.getElementById('logoutConfirmModal');

        if (btn && wrapper) {
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
        }

        window.openLogoutModal = function () {
            if (wrapper) {
                wrapper.classList.remove('open');
            }
            if (btn) {
                btn.setAttribute('aria-expanded', 'false');
            }

            if (modal) {
                modal.style.display = 'flex';
                void modal.offsetWidth;
                modal.classList.add('show');
                document.body.style.overflow = 'hidden';
            }
        };

        window.closeLogoutModal = function () {
            if (modal) {
                modal.classList.remove('show');
                setTimeout(function () {
                    modal.style.display = 'none';
                    document.body.style.overflow = '';
                }, 180);
            }
        };

        window.submitLogoutForm = function () {
            const form = document.getElementById('staffLogoutForm') || document.getElementById('logoutForm');
            if (form) {
                form.submit();
            }
        };

        if (modal) {
            modal.addEventListener('click', function (e) {
                if (e.target === modal) {
                    closeLogoutModal();
                }
            });
        }

        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') {
                if (wrapper) {
                    wrapper.classList.remove('open');
                    if (btn) btn.setAttribute('aria-expanded', 'false');
                }
                if (modal && modal.classList.contains('show')) {
                    closeLogoutModal();
                }
            }
        });
    })();
</script>
