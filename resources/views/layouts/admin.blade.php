<!DOCTYPE html>
<html lang="id" class="bg-slate-100 min-h-screen">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Admin Dashboard - Portal Bimbingan')</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Tailwind CSS -->
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
    <!-- Alpine JS -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body class="font-sans antialiased text-slate-800 min-h-screen bg-slate-100 flex flex-col md:flex-row relative" x-data="{ sidebarOpen: false }">

    <!-- Sidebar Desktop & Mobile -->
    <aside class="fixed inset-y-0 left-0 z-40 w-64 bg-slate-900 text-slate-300 flex flex-col transition-transform duration-300 transform md:translate-x-0"
           :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full md:translate-x-0'">
        
        <!-- Sidebar Brand -->
        <div class="h-16 px-6 bg-slate-950 flex items-center justify-between border-b border-slate-800">
            <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl bg-gradient-to-tr from-brand-600 to-indigo-500 flex items-center justify-center text-white font-bold shadow-md shadow-brand-500/20">
                    <i data-lucide="shield-check" class="w-5 h-5"></i>
                </div>
                <div>
                    <span class="font-bold text-slate-100 text-sm tracking-tight block">Portal Bimbingan</span>
                    <span class="text-[10px] uppercase font-bold tracking-widest text-brand-400">Admin Panel</span>
                </div>
            </a>
            <button @click="sidebarOpen = false" class="md:hidden text-slate-400 hover:text-white">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>

        <!-- Navigation Menu -->
        <nav class="flex-grow p-4 space-y-1 overflow-y-auto">
            <div class="px-3 py-2 text-[11px] font-bold uppercase tracking-wider text-slate-500">Menu Utama</div>
            
            <a href="{{ route('admin.dashboard') }}" 
               class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl font-semibold text-sm transition-all {{ request()->routeIs('admin.dashboard') ? 'bg-brand-600 text-white shadow-lg shadow-brand-600/30' : 'text-slate-400 hover:bg-slate-800 hover:text-slate-100' }}">
                <i data-lucide="calendar" class="w-4 h-4"></i> Jadwal Tamu
            </a>

            <a href="{{ route('admin.schedules.index') }}" 
               class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl font-semibold text-sm transition-all {{ request()->routeIs('admin.schedules.*') ? 'bg-brand-600 text-white shadow-lg shadow-brand-600/30' : 'text-slate-400 hover:bg-slate-800 hover:text-slate-100' }}">
                <i data-lucide="clock" class="w-4 h-4"></i> Jam & Kuota Bimbingan
            </a>

            <div class="pt-4 px-3 py-2 text-[11px] font-bold uppercase tracking-wider text-slate-500">Pengaturan Sistem</div>

            <a href="{{ route('admin.smtp.index') }}" 
               class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl font-semibold text-sm transition-all {{ request()->routeIs('admin.smtp.*') ? 'bg-brand-600 text-white shadow-lg shadow-brand-600/30' : 'text-slate-400 hover:bg-slate-800 hover:text-slate-100' }}">
                <i data-lucide="mail" class="w-4 h-4"></i> Konfigurasi SMTP Email
            </a>
        </nav>

        <!-- User Profile & Logout -->
        <div class="p-4 border-t border-slate-800 bg-slate-950/50">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3 overflow-hidden group">
                    <div class="w-9 h-9 rounded-full bg-brand-700 text-white flex items-center justify-center font-bold text-sm shadow-sm">
                        {{ strtoupper(substr(Auth::user()->name ?? 'A', 0, 1)) }}
                    </div>
                    <div class="truncate">
                        <p class="text-sm font-semibold text-slate-100 truncate">{{ Auth::user()->name ?? 'Admin Dosen' }}</p>
                        <p class="text-xs text-slate-500 truncate">{{ Auth::user()->email ?? 'admin' }}</p>
                    </div>
                </div>
                <form action="{{ route('admin.logout') }}" method="POST">
                    @csrf
                    <button type="submit" title="Logout" class="p-2 rounded-lg text-slate-400 hover:text-red-400 hover:bg-slate-800 transition-colors">
                        <i data-lucide="log-out" class="w-4 h-4"></i>
                    </button>
                </form>
            </div>
        </div>
    </aside>

    <!-- Overlay Mobile -->
    <div x-show="sidebarOpen" @click="sidebarOpen = false" x-transition.opacity class="fixed inset-0 bg-slate-950/60 z-40 md:hidden"></div>

    <!-- Main Content Wrapper -->
    <div class="flex-grow flex flex-col min-w-0 md:pl-64">
        <!-- Top Navbar -->
        <header class="h-16 bg-white/90 backdrop-blur-md border-b border-slate-200/80 px-6 sm:px-10 flex items-center justify-between sticky top-0 z-20 shadow-xs">
            <!-- Left: Sidebar Toggle & Page Title Breadcrumb -->
            <div class="flex items-center gap-4 min-w-0">
                <button @click="sidebarOpen = true" class="md:hidden p-2 rounded-xl text-slate-600 hover:bg-slate-100 transition-colors">
                    <i data-lucide="menu" class="w-5 h-5"></i>
                </button>

                <div class="flex items-center gap-2.5 truncate">
                    <span class="hidden sm:inline-flex items-center gap-1.5 px-3 py-1 rounded-lg bg-slate-100 text-slate-600 text-xs font-semibold">
                        <i data-lucide="shield" class="w-3.5 h-3.5 text-brand-600"></i> Admin Panel
                    </span>
                    <span class="hidden sm:inline text-slate-300">/</span>
                    <h1 class="text-base sm:text-lg font-extrabold text-slate-900 tracking-tight truncate">
                        @yield('header_title', 'Dashboard Bimbingan')
                    </h1>
                </div>
            </div>

            <!-- Right: Public Portal Link & Profile Dropdown -->
            <div class="flex items-center gap-4">
                <a href="{{ route('student.index') }}" target="_blank" 
                   class="hidden sm:inline-flex items-center gap-2 px-4 py-2 rounded-xl border border-slate-200 bg-slate-50/50 hover:bg-brand-50 hover:border-brand-300 text-slate-700 hover:text-brand-700 text-xs font-bold transition-all shadow-2xs">
                    <i data-lucide="external-link" class="w-3.5 h-3.5 text-brand-600"></i> Lihat Portal Publik
                </a>

                <!-- Profile Dropdown -->
                <div class="relative" x-data="{ userMenuOpen: false }">
                    <button @click="userMenuOpen = !userMenuOpen" @click.outside="userMenuOpen = false" 
                            class="flex items-center gap-2.5 p-1.5 pr-3 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 transition-all shadow-2xs focus:outline-none">
                        <div class="w-7 h-7 rounded-lg bg-gradient-to-tr from-brand-600 to-indigo-600 text-white font-bold text-xs flex items-center justify-center shadow-xs">
                            {{ strtoupper(substr(Auth::user()->name ?? 'A', 0, 1)) }}
                        </div>
                        <span class="text-xs font-bold text-slate-800 hidden md:inline truncate max-w-[120px]">{{ Auth::user()->name ?? 'Admin' }}</span>
                        <i data-lucide="chevron-down" class="w-3.5 h-3.5 text-slate-400 transition-transform" :class="userMenuOpen ? 'rotate-180' : ''"></i>
                    </button>

                    <!-- Dropdown Menu -->
                    <div x-show="userMenuOpen" x-cloak 
                         x-transition:enter="transition ease-out duration-100"
                         x-transition:enter-start="transform opacity-0 scale-95"
                         x-transition:enter-end="transform opacity-100 scale-100"
                         x-transition:leave="transition ease-in duration-75"
                         x-transition:leave-start="transform opacity-100 scale-100"
                         x-transition:leave-end="transform opacity-0 scale-95"
                         class="absolute right-0 mt-2 w-56 bg-white rounded-2xl shadow-xl border border-slate-200/80 py-2 z-50 divide-y divide-slate-100">
                        
                        <div class="px-4 py-2.5">
                            <p class="text-xs font-bold text-slate-900 truncate">{{ Auth::user()->name ?? 'Admin Dosen' }}</p>
                            <p class="text-[11px] text-slate-500 truncate font-mono">@ {{ Auth::user()->email ?? 'admin' }}</p>
                        </div>

                        <div class="py-1">
                            <a href="{{ route('student.index') }}" target="_blank" class="flex items-center gap-2.5 px-4 py-2 text-xs font-semibold text-slate-700 hover:bg-brand-50 hover:text-brand-700 transition-colors">
                                <i data-lucide="external-link" class="w-4 h-4 text-brand-600"></i> Portal Publik
                            </a>
                        </div>

                        <div class="py-1">
                            <form action="{{ route('admin.logout') }}" method="POST">
                                @csrf
                                <button type="submit" class="w-full flex items-center gap-2.5 px-4 py-2 text-xs font-semibold text-rose-600 hover:bg-rose-50 transition-colors">
                                    <i data-lucide="log-out" class="w-4 h-4"></i> Keluar (Logout)
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <!-- Main Body -->
        <main class="p-6 flex-grow">
            @yield('content')
        </main>
    </div>

    <script>
        lucide.createIcons();

        const Toast = Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 4000,
            timerProgressBar: true,
            didOpen: (toast) => {
                toast.addEventListener('mouseenter', Swal.stopTimer)
                toast.addEventListener('mouseleave', Swal.resumeTimer)
            }
        });

        @if(session('success'))
            Toast.fire({
                icon: 'success',
                title: "{{ session('success') }}"
            });
        @endif

        @if(session('warning'))
            Toast.fire({
                icon: 'warning',
                title: "{{ session('warning') }}"
            });
        @endif

        @if(session('error'))
            Toast.fire({
                icon: 'error',
                title: "{{ session('error') }}"
            });
        @endif
    </script>
</body>
</html>
