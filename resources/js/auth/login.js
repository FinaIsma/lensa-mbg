function selectRole(role, resetFields = true) {
    const roleInput = document.getElementById('role');
    const adminButton = document.getElementById('adminButton');
    const sppgButton = document.getElementById('sppgButton');
    const registerSection = document.getElementById('registerSection');

    const isSppg = role === 'sppg' || role === 'kepala_sppg' || role === 'staff';
    const activeRole = isSppg ? 'sppg' : 'admin';

    if (roleInput) roleInput.value = activeRole;

    if (activeRole === 'admin') {
        if (adminButton) adminButton.classList.add('active');
        if (sppgButton) sppgButton.classList.remove('active');
        if (registerSection) registerSection.classList.add('hidden');
    } else {
        if (sppgButton) sppgButton.classList.add('active');
        if (adminButton) adminButton.classList.remove('active');
        if (registerSection) registerSection.classList.remove('hidden');
    }

    if (resetFields) {
        const emailInput = document.getElementById('email');
        const passwordInput = document.getElementById('password');

        if (emailInput) emailInput.value = '';
        if (passwordInput) passwordInput.value = '';

        const eyeIcon = document.getElementById('eyeIcon');
        if (eyeIcon && passwordInput) {
            passwordInput.type = 'password';
            eyeIcon.innerHTML = `
                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                <circle cx="12" cy="12" r="3"></circle>
            `;
        }

        document.querySelectorAll('.login-card > div[style]').forEach(el => {
            if (el.querySelector('strong')) {
                el.style.display = 'none';
            }
        });

        document.querySelectorAll('.error').forEach(el => {
            el.style.display = 'none';
        });
    }
}

function togglePassword() {
    const password = document.getElementById('password');
    const eyeIcon = document.getElementById('eyeIcon');

    if (password.type === 'password') {
        password.type = 'text';

        eyeIcon.innerHTML = `
            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8S1 12 1 12z"></path>
            <line x1="4" y1="4" x2="20" y2="20"></line>
        `;
    } else {
        password.type = 'password';

        eyeIcon.innerHTML = `
            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
            <circle cx="12" cy="12" r="3"></circle>
        `;
    }
}

document.addEventListener('DOMContentLoaded', function () {
    const roleInput = document.getElementById('role');
    const urlParams = new URLSearchParams(window.location.search);
    const roleFromUrl = urlParams.get('role') || urlParams.get('tab');
    const initRole = roleFromUrl || document.body.dataset.initRole || (roleInput ? roleInput.value : 'admin') || 'admin';

    selectRole(initRole, false);
});

window.selectRole = selectRole;
window.togglePassword = togglePassword;