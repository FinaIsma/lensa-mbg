<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Radar MBG - Transparansi Makanan Bergizi Gratis')</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    @vite(['resources/css/app.css', 'resources/css/public/portal.css', 'resources/js/app.js', 'resources/js/public/portal.js'])
    @stack('styles')
</head>
<body class="min-h-screen flex flex-col bg-slate-50 text-slate-800 antialiased selection:bg-blue-600 selection:text-white">

    <!-- Top Utility Bar -->
    <header class="bg-white border-b border-slate-200 sticky top-0 z-50 shadow-xs">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-20">
                <!-- Brand / Logo dari folder public -->
                <div class="flex items-center gap-3">
                    <a href="{{ route('public.home') }}" class="flex items-center gap-3 group">
                        <img src="{{ asset('images/logo-trimmed.png') }}" alt="Logo Lensa MBG" class="h-9 sm:h-11 w-auto object-contain">
                        <div>
                            <div class="text-base sm:text-lg font-black text-slate-900 tracking-tight leading-none flex items-center">
                                Lensa<span class="text-[#071E49]">MBG</span>
                            </div>
                            <div class="text-[10px] sm:text-[11px] font-medium text-slate-500 leading-tight mt-1">
                                Transparansi Nutrisi & Makan Bergizi Generasi Emas
                            </div>
                        </div>
                    </a>
                </div>

                <!-- Tombol ke Halaman Login -->
                <div class="flex items-center gap-3">
                    <a href="{{ route('login') }}" class="inline-flex items-center gap-2 px-4 sm:px-5 py-2.5 rounded-xl text-xs sm:text-sm font-bold bg-[#071E49] text-white hover:bg-[#042A70] shadow-sm hover:shadow transition-all duration-150">
                        <svg class="w-4 h-4 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                        </svg>
                        <span>Masuk Petugas</span>
                    </a>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="flex-grow">
        @yield('content')
    </main>

    <!-- Clean Footer -->
    <footer class="bg-white border-t border-slate-200 mt-16 text-slate-600 text-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <div class="flex flex-col sm:flex-row items-center justify-between text-xs text-slate-400 gap-4">
                <p>&copy; {{ date('Y') }} Badan Gizi Nasional Republik Indonesia. Seluruh hak cipta dilindungi.</p>
                <div class="flex items-center gap-6">
                    <span>Sistem Terenkripsi & Audit Digital</span>
                    <span>Waktu Server: WIB (Asia/Jakarta)</span>
                </div>
            </div>
        </div>
    </footer>

    @stack('scripts')
</body>
</html>

