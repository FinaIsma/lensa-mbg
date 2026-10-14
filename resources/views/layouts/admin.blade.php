<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Admin Sistem') - Lensa MBG</title>
    
    <!-- Google Fonts: Plus Jakarta Sans & Poppins -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Chart.js CDN -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <style>
        body {
            font-family: 'Plus Jakarta Sans', 'Poppins', sans-serif;
            background-color: #F8FAFC;
            color: #0F172A;
        }

        /* Custom scrollbar for clean UI */
        ::-webkit-scrollbar {
            width: 6px;
            height: 6px;
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
<body class="min-h-screen bg-slate-50 antialiased flex flex-col lg:flex-row">

    <!-- Mobile Top Navigation Bar -->
    <div class="lg:hidden bg-white border-b border-slate-200 px-4 py-3 flex items-center justify-between sticky top-0 z-40">
        <div class="flex items-center gap-3">
            <button id="mobileMenuToggle" type="button" class="p-2 rounded-lg text-slate-600 hover:bg-slate-100 focus:outline-none">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
            </button>
            <div class="flex items-center gap-2">
                <img src="{{ asset('images/LogoLensa.png') }}" alt="Lensa MBG" class="h-8 w-auto object-contain">
            </div>
        </div>
        <div class="flex items-center gap-2">
            <div class="w-8 h-8 rounded-full bg-slate-300 flex items-center justify-center font-bold text-xs text-slate-700">
                {{ strtoupper(substr(Auth::user()->nama ?? 'Nanda', 0, 1)) }}
            </div>
            <span class="text-xs font-semibold text-slate-800">{{ Auth::user()->nama ?? 'Nanda' }}</span>
        </div>
    </div>

    <!-- Sidebar Overlay for Mobile -->
    <div id="sidebarBackdrop" class="fixed inset-0 bg-slate-900/40 z-40 hidden lg:hidden"></div>

    <!-- Sidebar Navigation -->
    <aside id="adminSidebar" class="fixed inset-y-0 left-0 z-50 w-64 bg-white border-r border-slate-200/80 flex flex-col transition-transform duration-200 ease-in-out -translate-x-full lg:translate-x-0 lg:static lg:h-screen lg:shrink-0">
        
        <!-- Logo Section -->
        <div class="pt-8 pb-6 px-6 flex items-center justify-center">
            <a href="{{ route('admin.dashboard') }}" class="group flex flex-col items-center">
                <div class="relative w-20 h-20 flex items-center justify-center transition-transform duration-200 group-hover:scale-105">
                    @if(file_exists(public_path('images/LogoLensa.png')))
                        <img src="{{ asset('images/LogoLensa.png') }}" alt="Logo Lensa MBG" class="max-h-20 w-auto object-contain">
                    @else
                        <!-- Stylized Iris/Lens fallback if image not loaded -->
                        <div class="w-16 h-16 rounded-full bg-gradient-to-tr from-blue-600 to-cyan-400 p-1 flex items-center justify-center shadow-md">
                            <div class="w-10 h-10 rounded-full bg-white flex items-center justify-center">
                                <div class="w-6 h-6 rounded-full bg-blue-600"></div>
                            </div>
                        </div>
                    @endif
                </div>
            </a>
        </div>

        <!-- Navigation Menu -->
        <nav class="flex-1 px-4 space-y-1.5 overflow-y-auto">
            
            <!-- Dashboard -->
            <a href="{{ route('admin.dashboard') }}"
               class="group flex items-center gap-3.5 px-4 py-3 text-sm font-medium rounded-xl transition-colors duration-150 {{ request()->routeIs('admin.dashboard') ? 'bg-[#EEF2F6] text-[#2563EB] font-semibold' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100/70' }}">
                <svg class="w-5 h-5 shrink-0 {{ request()->routeIs('admin.dashboard') ? 'text-[#2563EB]' : 'text-slate-500 group-hover:text-slate-700' }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="3" y="3" width="7" height="7" rx="1.5"></rect>
                    <rect x="14" y="3" width="7" height="7" rx="1.5"></rect>
                    <rect x="14" y="14" width="7" height="7" rx="1.5"></rect>
                    <rect x="3" y="14" width="7" height="7" rx="1.5"></rect>
                </svg>
                <span>Dashboard</span>
            </a>

            <!-- User Management -->
            <a href="{{ route('admin.users.index') }}"
               class="group flex items-center gap-3.5 px-4 py-3 text-sm font-medium rounded-xl transition-colors duration-150 {{ request()->routeIs('admin.users.*') ? 'bg-[#EEF2F6] text-[#2563EB] font-semibold' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100/70' }}">
                <svg class="w-5 h-5 shrink-0 {{ request()->routeIs('admin.users.*') ? 'text-[#2563EB]' : 'text-slate-500 group-hover:text-slate-700' }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"></path>
                    <circle cx="9" cy="7" r="4"></circle>
                    <path d="M22 21v-2a4 4 0 0 0-3-3.87"></path>
                    <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                </svg>
                <span>User Management</span>
            </a>

            <!-- Correction Request -->
            <a href="{{ route('admin.correction-requests.index') }}"
               class="group flex items-center gap-3.5 px-4 py-3 text-sm font-medium rounded-xl transition-colors duration-150 {{ request()->routeIs('admin.correction-requests.*') ? 'bg-[#EEF2F6] text-[#2563EB] font-semibold' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100/70' }}">
                <svg class="w-5 h-5 shrink-0 {{ request()->routeIs('admin.correction-requests.*') ? 'text-[#2563EB]' : 'text-slate-500 group-hover:text-slate-700' }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                    <polyline points="14 2 14 8 20 8"></polyline>
                    <line x1="16" y1="13" x2="8" y2="13"></line>
                    <line x1="16" y1="17" x2="8" y2="17"></line>
                    <polyline points="10 9 9 9 8 9"></polyline>
                </svg>
                <span>Correction Request</span>
            </a>

            <!-- Audit Trail -->
            <a href="{{ route('admin.audit-trail.index') }}"
               class="group flex items-center gap-3.5 px-4 py-3 text-sm font-medium rounded-xl transition-colors duration-150 {{ request()->routeIs('admin.audit-trail.*') ? 'bg-[#EEF2F6] text-[#2563EB] font-semibold' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100/70' }}">
                <svg class="w-5 h-5 shrink-0 {{ request()->routeIs('admin.audit-trail.*') ? 'text-[#2563EB]' : 'text-slate-500 group-hover:text-slate-700' }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"></path>
                    <path d="M3 3v5h5"></path>
                    <polyline points="12 7 12 12 15 15"></polyline>
                </svg>
                <span>Audit Trail</span>
            </a>

        </nav>

        <!-- Sidebar Footer / Logout -->
        <div class="p-4 border-t border-slate-100">
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit" class="w-full flex items-center gap-3 px-4 py-2.5 text-xs font-semibold text-rose-600 hover:bg-rose-50 rounded-xl transition-colors duration-150">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                    </svg>
                    <span>Keluar Sistem</span>
                </button>
            </form>
        </div>
    </aside>

    <!-- Main Content Area -->
    <div class="flex-1 flex flex-col min-w-0 h-screen overflow-y-auto">
        
        <!-- Top Header Bar -->
        <header class="bg-white border-b border-slate-200/80 sticky top-0 z-30 px-6 sm:px-10 py-4 flex items-center justify-between">
            <div>
                <h2 class="text-base sm:text-lg font-bold text-slate-900 tracking-tight">
                    Selamat datang, {{ Auth::user()->nama ?? 'Nanda Ricco' }}
                </h2>
            </div>
            
            <!-- User Profile Badge -->
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-full bg-slate-300 flex items-center justify-center text-slate-600 font-bold text-sm shadow-inner overflow-hidden">
                    {{ strtoupper(substr(Auth::user()->nama ?? 'Nanda', 0, 1)) }}
                </div>
                <div class="hidden sm:flex flex-col text-left">
                    <span class="text-sm font-bold text-slate-900 leading-tight">
                        {{ explode(' ', Auth::user()->nama ?? 'Nanda')[0] }}
                    </span>
                    <span class="text-xs text-slate-500 font-medium">
                        Admin Sistem
                    </span>
                </div>
            </div>
        </header>

        <!-- Dynamic Content Slot -->
        <main class="flex-1 p-6 sm:p-10 max-w-[1400px] w-full mx-auto">
            @yield('content')
        </main>
    </div>

    <!-- Mobile Drawer Script -->
    <script>
        const menuToggle = document.getElementById('mobileMenuToggle');
        const sidebar = document.getElementById('adminSidebar');
        const backdrop = document.getElementById('sidebarBackdrop');

        if (menuToggle && sidebar && backdrop) {
            menuToggle.addEventListener('click', () => {
                sidebar.classList.toggle('-translate-x-full');
                backdrop.classList.toggle('hidden');
            });

            backdrop.addEventListener('click', () => {
                sidebar.classList.add('-translate-x-full');
                backdrop.classList.add('hidden');
            });
        }
    </script>

    @stack('scripts')
</body>
</html>
