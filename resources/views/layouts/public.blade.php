<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Lensa MBG - Transparansi Makanan Bergizi Gratis')</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,400;0,500;0,600;0,700;0,800;1,400;1,600&family=Poppins:ital,wght@0,400;0,500;0,600;0,700;1,400&display=swap" rel="stylesheet">
    
    @vite(['resources/css/app.css', 'resources/css/public/portal.css', 'resources/js/app.js', 'resources/js/public/portal.js'])
    @stack('styles')
</head>
<body class="min-h-screen flex flex-col bg-slate-50 text-slate-800 antialiased selection:bg-[#071E49] selection:text-white">

    <!-- Clean Navigation Bar (Menyatu Bersih dengan Latar Belakang) -->
    <header class="bg-white/90 backdrop-blur-md border-b border-slate-200/80 sticky top-0 z-50">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-20">
                <!-- Brand / Logo -->
                <div class="flex items-center gap-3">
                    <a href="{{ route('public.home') }}" class="flex items-center gap-3.5 group">
                        <img src="{{ asset('images/logo-trimmed.png') }}" alt="Logo Lensa MBG" class="h-10 sm:h-11 w-auto object-contain transition-transform group-hover:scale-102">
                        <div>
                            <div class="text-lg sm:text-xl font-black text-slate-900 tracking-tight leading-none flex items-center">
                                Lensa<span class="text-[#071E49]">MBG</span>
                            </div>
                            <div class="text-[11px] font-medium text-slate-500 leading-tight mt-1">
                                Transparansi Nutrisi & Porsi Makan Bergizi Siswa
                            </div>
                        </div>
                    </a>
                </div>

                <!-- Tombol ke Halaman Login -->
                <div class="flex items-center gap-3">
                    <a href="{{ route('login') }}" class="inline-flex items-center gap-2 px-4 sm:px-5 py-2.5 rounded-xl text-xs sm:text-sm font-bold bg-[#071E49] text-white hover:bg-[#042A70] shadow-xs hover:shadow transition-all duration-150">
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
    <footer class="bg-white border-t border-slate-200/80 mt-16 text-slate-600 text-xs">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div class="flex flex-col sm:flex-row items-center justify-between text-xs text-slate-400 gap-4">
                <div class="flex items-center gap-3">
                    <img src="{{ asset('images/logo-trimmed.png') }}" alt="Logo Lensa MBG" class="h-6 w-auto object-contain">
                    <p>&copy; {{ date('Y') }} Badan Gizi Nasional Republik Indonesia. Seluruh hak cipta dilindungi.</p>
                </div>
                <div class="flex items-center gap-6 text-slate-500">
                    <span>Sistem Terenkripsi & Audit Digital</span>
                    <span>Waktu Server: WIB (Asia/Jakarta)</span>
                </div>
            </div>
        </div>
    </footer>

    <!-- Global Lightbox Modal untuk Preview Foto dengan Tombol Geser (Slider Navigasi) -->
    <div id="photoModal" class="fixed inset-0 z-50 bg-slate-900/85 backdrop-blur-sm flex items-center justify-center p-3 sm:p-4 hidden opacity-0 transition-opacity duration-200 select-none" onclick="closePhotoModal()">
        <div class="relative max-w-4xl w-full bg-white rounded-2xl overflow-hidden shadow-2xl border border-slate-200 flex flex-col max-h-[92vh]" onclick="event.stopPropagation()">
            <!-- Header Modal -->
            <div class="px-4 py-3 border-b border-slate-100 flex items-center justify-between bg-slate-50 shrink-0">
                <div class="flex items-center gap-2 min-w-0 pr-2">
                    <span id="modalCaption" class="text-xs sm:text-sm font-bold text-slate-900 truncate">Foto Verifikasi Porsi</span>
                    <span id="modalCounter" class="hidden text-[10px] sm:text-[11px] font-semibold text-slate-500 bg-slate-200/70 px-2 py-0.5 rounded-full shrink-0">1 / 1</span>
                </div>
                <button type="button" onclick="closePhotoModal()" title="Tutup (Esc)" class="w-8 h-8 rounded-lg bg-slate-200 hover:bg-slate-300 text-slate-700 flex items-center justify-center transition cursor-pointer shrink-0">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <!-- Area Foto & Tombol Geser -->
            <div class="relative bg-slate-950 flex items-center justify-center min-h-[280px] max-h-[75vh] sm:max-h-[78vh] overflow-hidden group">
                <!-- Tombol Geser Kiri (Prev) -->
                <button id="modalPrevBtn" type="button" onclick="prevPhotoModal(event)" title="Foto Sebelumnya (Panah Kiri)" class="hidden absolute left-3 top-1/2 -translate-y-1/2 z-10 w-10 h-10 rounded-full bg-slate-900/70 hover:bg-slate-900 text-white flex items-center justify-center transition backdrop-blur-xs cursor-pointer border border-white/20 shadow-lg active:scale-95">
                    <svg class="w-5 h-5 -translate-x-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/></svg>
                </button>

                <!-- Foto Utama -->
                <img id="modalImg" src="" alt="Foto Bukti" class="max-h-[75vh] sm:max-h-[78vh] w-auto max-w-full object-contain transition-transform duration-200">

                <!-- Tombol Geser Kanan (Next) -->
                <button id="modalNextBtn" type="button" onclick="nextPhotoModal(event)" title="Foto Selanjutnya (Panah Kanan)" class="hidden absolute right-3 top-1/2 -translate-y-1/2 z-10 w-10 h-10 rounded-full bg-slate-900/70 hover:bg-slate-900 text-white flex items-center justify-center transition backdrop-blur-xs cursor-pointer border border-white/20 shadow-lg active:scale-95">
                    <svg class="w-5 h-5 translate-x-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
                </button>
            </div>

            <!-- Footer / Petunjuk Navigasi Ringkas -->
            <div id="modalFooterNav" class="px-4 py-2 border-t border-slate-100 bg-slate-50 flex items-center justify-between text-[11px] text-slate-500 shrink-0">
                <span class="truncate font-medium text-slate-600">Klik tombol panah atau geser layar untuk beralih foto</span>
                <div class="hidden sm:flex items-center gap-1.5 text-[10px] text-slate-400">
                    <span class="px-1.5 py-0.5 rounded bg-slate-200/70 font-mono text-slate-600">&larr;</span>
                    <span class="px-1.5 py-0.5 rounded bg-slate-200/70 font-mono text-slate-600">&rarr;</span>
                    <span>Keyboard</span>
                </div>
            </div>
        </div>
    </div>

    @stack('scripts')
</body>
</html>
