<!DOCTYPE html>
<html lang="id" class="h-full bg-slate-50">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Portal Bimbingan Akademik')</title>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Tailwind CSS CDN + AlpineJS for fast UI reactivity -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['"Plus Jakarta Sans"', 'sans-serif'],
                    },
                    colors: {
                        brand: {
                            50: '#eff6ff',
                            100: '#dbeafe',
                            500: '#3b82f6',
                            600: '#2563eb',
                            700: '#1d4ed8',
                            800: '#1e40af',
                            900: '#1e3a8a',
                        }
                    }
                }
            }
        }
    </script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body class="font-sans antialiased text-slate-800 flex flex-col min-h-full">

    <!-- Header Navigation -->
    <header class="bg-white/95 backdrop-blur-md border-b border-slate-200 sticky top-0 z-40" x-data="{ mobileMenuOpen: false }">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <!-- Logo -->
                <a href="{{ route('student.index') }}" class="flex items-center gap-2.5 group min-w-0">
                    <div class="w-9 h-9 sm:w-10 sm:h-10 rounded-xl bg-gradient-to-tr from-brand-700 to-indigo-600 flex items-center justify-center text-white shadow-md shadow-brand-500/25 group-hover:scale-105 transition-transform duration-200 flex-shrink-0">
                        <i data-lucide="calendar-check-2" class="w-5 h-5"></i>
                    </div>
                    <div class="truncate">
                        <span class="font-extrabold text-base sm:text-lg text-slate-900 tracking-tight block leading-tight truncate">Portal Bimbingan</span>
                        <span class="text-[10px] sm:text-xs font-bold text-brand-600 tracking-wide uppercase block">Layanan Akademik</span>
                    </div>
                </a>

                <!-- Desktop Nav Links -->
                <nav class="hidden md:flex items-center gap-2 lg:gap-4">
                    <a href="{{ route('student.index') }}" class="px-3.5 py-2 text-xs font-bold text-slate-700 hover:text-brand-600 rounded-xl hover:bg-slate-100 transition-all">
                        Buat Janji
                    </a>
                    <a href="{{ route('student.tracker') }}" class="px-3.5 py-2 text-xs font-bold text-slate-700 hover:text-brand-600 rounded-xl hover:bg-slate-100 transition-all flex items-center gap-1.5">
                        <i data-lucide="search" class="w-3.5 h-3.5"></i> Cek Status Booking
                    </a>
                    <a href="{{ route('admin.login') }}" class="ml-2 px-4 py-2 text-xs font-bold text-white bg-slate-900 hover:bg-brand-600 rounded-xl shadow-xs transition-all flex items-center gap-1.5">
                        <i data-lucide="lock" class="w-3.5 h-3.5"></i> Login Dosen / Admin
                    </a>
                </nav>

                <!-- Mobile Hamburger Button -->
                <div class="flex md:hidden">
                    <button @click="mobileMenuOpen = !mobileMenuOpen" 
                            type="button"
                            class="p-2 rounded-xl text-slate-700 hover:bg-slate-100 focus:outline-none transition-colors"
                            aria-label="Toggle Menu">
                        <i data-lucide="menu" class="w-6 h-6" x-show="!mobileMenuOpen"></i>
                        <i data-lucide="x" class="w-6 h-6" x-show="mobileMenuOpen" x-cloak></i>
                    </button>
                </div>
            </div>
        </div>

        <!-- Mobile Navigation Menu Dropdown -->
        <div x-show="mobileMenuOpen" x-cloak
             x-transition:enter="transition ease-out duration-150"
             x-transition:enter-start="opacity-0 -translate-y-2"
             x-transition:enter-end="opacity-100 translate-y-0"
             x-transition:leave="transition ease-in duration-100"
             x-transition:leave-start="opacity-100 translate-y-0"
             x-transition:leave-end="opacity-0 -translate-y-2"
             class="md:hidden bg-white border-b border-slate-200 px-4 pt-2 pb-4 space-y-2 shadow-lg">
            
            <a href="{{ route('student.index') }}" 
               class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs font-bold text-slate-700 hover:bg-brand-50 hover:text-brand-700 transition-all">
                <i data-lucide="calendar-plus" class="w-4 h-4 text-brand-600"></i> Buat Janji Bimbingan
            </a>
            
            <a href="{{ route('student.tracker') }}" 
               class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs font-bold text-slate-700 hover:bg-brand-50 hover:text-brand-700 transition-all">
                <i data-lucide="search" class="w-4 h-4 text-brand-600"></i> Cek Status Booking
            </a>

            <div class="pt-2 border-t border-slate-100">
                <a href="{{ route('admin.login') }}" 
                   class="flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl text-xs font-bold text-white bg-slate-900 hover:bg-brand-600 shadow-sm transition-all w-full">
                    <i data-lucide="lock" class="w-4 h-4"></i> Login Dosen / Admin
                </a>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="flex-grow">
        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="bg-slate-900 text-slate-400 border-t border-slate-800 mt-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
            <div class="flex flex-col md:flex-row justify-between items-center gap-4">
                <div class="flex items-center gap-2">
                    <div class="w-7 h-7 rounded-lg bg-brand-600 flex items-center justify-center text-white font-bold text-xs">PB</div>
                    <span class="text-slate-200 font-semibold text-sm">Portal Bimbingan Akademik</span>
                </div>
                <p class="text-xs text-slate-500 text-center">
                    &copy; {{ date('Y') }} Sistem Informasi Manajemen Bimbingan & Janji Temu Akademik. All rights reserved.
                </p>
            </div>
        </div>
    </footer>

    <script>
        lucide.createIcons();
    </script>
</body>
</html>
