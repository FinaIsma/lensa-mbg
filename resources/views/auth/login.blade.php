<!DOCTYPE html>
<html lang="id">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Login - Lensa MBG</title>
        @vite(['resources/css/login.css', 'resources/js/login.js'])
    </head>

    <body>
        <header class="header">
            <div class="logo-left">
                <img src="{{ asset('images/LogoLensa.png') }}" alt="Logo Lensa MBG">
            </div>
            <div class="logo-right">
                <img src="{{ asset('images/LogoBGN.png') }}" alt="Logo Badan Gizi Nasional">
            </div>
        </header>

        <main class="login-wrapper">
            <div class="login-card">
                @if (session('success'))
                    <div class="success-alert" style="background-color: #ECFDF5; border: 1px solid #10B981; color: #065F46; padding: 12px 16px; border-radius: 8px; margin-bottom: 20px; font-size: 14px; text-align: left;">
                        <strong>Berhasil!</strong> {{ session('success') }}
                    </div>
                @endif
                <h1 class="login-title">Masuk ke Lensa MBG</h1>
                <p class="login-subtitle">Pilih Role Anda Untuk Melanjutkan ke Sistem.</p>
                <form action="{{ route('login.process') }}" method="POST">
                    <div class="role-buttons">
                        <button type="button" id="adminSistemButton" class="role-button active" onclick="selectRole('admin_sistem')">
                            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path>
                            </svg>
                            <span>Admin Sistem</span>
                        </button>

                        <button type="button" id="adminSppgButton" class="role-button" onclick="selectRole('admin_sppg')">
                            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                                <circle cx="9" cy="7" r="4"></circle>
                                <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                                <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                            </svg>
                            <span>Admin SPPG</span>
                        </button>
                    </div>

                    <input type="hidden" name="role" id="role" value="admin_sistem">

                    <div class="form-group">

                        <label for="email">Email</label>

                        <div class="input-wrapper">
                            <svg class="input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <rect x="3" y="5" width="18" height="14" rx="2"></rect>
                                <polyline points="3,7 12,13 21,7"></polyline>
                            </svg>
                            <input type="email" id="email" name="email" value="{{ old('email') }}" placeholder="Masukkan Email" autocomplete="email" required>
                        </div>
                        @error('email')
                            <div class="error">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="password">Password</label>
                        <div class="input-wrapper">
                            <svg class="input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <rect x="5" y="10" width="14" height="11" rx="2"></rect>
                                <path d="M8 10V7a4 4 0 0 1 8 0v3"></path>
                            </svg>
                            <input type="password" id="password" name="password" placeholder="Masukkan Password" autocomplete="current-password" required>
                            <button type="button" class="password-toggle" onclick="togglePassword()" aria-label="Tampilkan password">
                                <svg id="eyeIcon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8S1 12 1 12z"></path>
                                    <circle cx="12" cy="12" r="3"></circle>
                                </svg>
                            </button>
                        </div>
                        @error('password')
                            <div class="error">{{ $message }}</div>
                        @enderror
                    </div>

                    <button type="submit" class="login-button">Masuk</button>
                </form>

                <div id="registerSection" class="register-section hidden">
                    <p class="register-text">Belum Punya Akun?</p>
                    <a href="{{ route('register') }}" class="register-button">
                        Register
                    </a>
                </div>
            </div>
        </main>
    </body>
</html>