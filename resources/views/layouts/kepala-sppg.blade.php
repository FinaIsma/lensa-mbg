<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Kepala SPPG') - Lensa MBG</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">

    <script src="https://cdn.jsdelivr.net/npm/pdfjs-dist@3.11.174/build/pdf.min.js"></script>
    @vite(['resources/css/app.css', 'resources/css/KepalaSPPG/sidebar-navbar.css', 'resources/css/KepalaSPPG/profile-modals.css', 'resources/js/app.js', 'resources/js/KepalaSPPG/profile-modals.js'])

    <style>
        body {
            font-family: 'Poppins', 'Plus Jakarta Sans', sans-serif;
            background-color: #F8FAFC;
            color: #0F172A;
        }

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
<body class="min-h-screen bg-slate-50 antialiased">

    <div class="admin-container">
        @include('KepalaSPPG.partials.sidebar')

        <div class="main-wrapper">
            @include('KepalaSPPG.partials.navbar')

            <main class="@yield('main-class', 'flex-1 p-6 sm:p-10 max-w-[1400px] w-full mx-auto')">
                @yield('content')
            </main>
        </div>
    </div>

    @include('KepalaSPPG.partials.profile-modals')

    @yield('modals')

    @stack('scripts')
</body>
</html>
