<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Radar MBG - Transparansi Makanan Bergizi Gratis')</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <style>
        body {
            font-family: 'Plus Jakarta Sans', 'Poppins', sans-serif;
            background-color: #F8FAFC;
            color: #0F172A;
        }
        
        .brand-navy {
            background-color: #071E49;
        }

        .brand-navy-accent {
            background-color: #042A70;
        }

        ::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }
        ::-webkit-scrollbar-track {
            background: #F1F5F9;
        }
        ::-webkit-scrollbar-thumb {
            background: #CBD5E1;
            border-radius: 9999px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: #94A3B8;
        }
    </style>
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
                        <img src="{{ asset('images/LogoLensa.png') }}" alt="Logo Lensa MBG" class="h-10 sm:h-12 w-auto object-contain">
                    </a>
                </div>

                <!-- Tombol ke Halaman Login -->
                <div class="flex items-center gap-3">
                    <a href="{{ route('login') }}" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl text-sm font-bold bg-[#071E49] text-white hover:bg-[#042A70] shadow-sm hover:shadow transition-all duration-150">
                        <svg class="w-4 h-4 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
                        </svg>
                        <span>Masuk</span>
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
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8 mb-8">
                <div class="md:col-span-2">
                    <div class="flex items-center gap-3 mb-3">
                        <div class="w-8 h-8 rounded-lg bg-[#071E49] flex items-center justify-center text-white font-bold text-sm">
                            MBG
                        </div>
                        <span class="text-base font-bold text-[#071E49]">Radar & Lensa MBG</span>
                    </div>
                    <p class="text-slate-500 text-sm leading-relaxed max-w-md">
                        Platform transparansi dan pemantauan distribusi Makan Bergizi Gratis (MBG) dari hulu ke hilir. Menjamin pangan anak bangsa higienis, bergizi seimbang, dan terserap dari produsen pangan lokal.
                    </p>
                </div>
                <div>
                    <h4 class="font-bold text-slate-900 mb-3 text-sm uppercase tracking-wider">Akses Cepat</h4>
                    <ul class="space-y-2 text-sm text-slate-500">
                        <li><a href="{{ route('public.home') }}" class="hover:text-blue-600 transition">Cari Sekolah Penerima</a></li>
                        <li><a href="{{ route('public.trace') }}" class="hover:text-blue-600 transition">Telusur Riwayat Batch</a></li>
                        <li><a href="{{ route('login') }}" class="hover:text-blue-600 transition">Login Staf / Kepala SPPG</a></li>
                        <li><a href="{{ route('register') }}" class="hover:text-blue-600 transition">Pendaftaran Dapur SPPG Baru</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="font-bold text-slate-900 mb-3 text-sm uppercase tracking-wider">Standar Kualitas</h4>
                    <ul class="space-y-2 text-sm text-slate-500">
                        <li class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            <span>Bahan Baku Petani Lokal (FEFO)</span>
                        </li>
                        <li class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            <span>Dapur Higienis & Uji Sampel Saji</span>
                        </li>
                        <li class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            <span>Audit Trail Data Terkunci</span>
                        </li>
                    </ul>
                </div>
            </div>

            <div class="pt-8 border-t border-slate-100 flex flex-col sm:flex-row items-center justify-between text-xs text-slate-400 gap-4">
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

