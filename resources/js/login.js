function selectRole(role) {
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
    const roleFromUrl = urlParams.get('role');
    const initialRole = roleFromUrl || (roleInput ? roleInput.value : 'admin_sistem') || 'admin_sistem';
    selectRole(initialRole);
});

window.selectRole = selectRole;
window.togglePassword = togglePassword;