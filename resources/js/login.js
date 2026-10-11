function selectRole(role, resetFields = true) {
    const roleInput = document.getElementById('role');
    const adminSistemButton = document.getElementById('adminSistemButton');
    const adminSppgButton = document.getElementById('adminSppgButton');
    const registerSection = document.getElementById('registerSection');

    roleInput.value = role;

    if (role === 'admin_sistem') {
        adminSistemButton.classList.add('active');
        adminSppgButton.classList.remove('active');
        registerSection.classList.add('hidden');
    } else {
        adminSppgButton.classList.add('active');
        adminSistemButton.classList.remove('active');
        registerSection.classList.remove('hidden');
    }

    // Reset form fields when manually switching role
    if (resetFields) {
        const emailInput = document.getElementById('email');
        const passwordInput = document.getElementById('password');

        if (emailInput) emailInput.value = '';
        if (passwordInput) passwordInput.value = '';

        // Reset eye icon back to visible
        const eyeIcon = document.getElementById('eyeIcon');
        if (eyeIcon && passwordInput) {
            passwordInput.type = 'password';
            eyeIcon.innerHTML = `
                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                <circle cx="12" cy="12" r="3"></circle>
            `;
        }

        // Sembunyikan semua alert banner (success & sppg_status)
        document.querySelectorAll('.login-card > div[style]').forEach(el => {
            if (el.querySelector('strong')) {
                el.style.display = 'none';
            }
        });

        // Sembunyikan error validasi inline (email/password tidak sesuai, dll)
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
    const initRole = roleFromUrl || document.body.dataset.initRole || (roleInput ? roleInput.value : 'admin_sistem') || 'admin_sistem';

    // Pass resetFields=false so we don't wipe old() email value or success alerts on load
    selectRole(initRole, false);
});

window.selectRole = selectRole;
window.togglePassword = togglePassword;