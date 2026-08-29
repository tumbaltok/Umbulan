<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title') | PT META Adhya Tirta Umbulan</title>

    <!-- Favicon -->
    <link rel="icon" type="image/png" href="{{ asset('images/logo-circle.png') }}">
    <link rel="shortcut icon" type="image/png" href="{{ asset('images/logo-circle.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/logo-circle.png') }}">
    <script>
        @auth
            if (localStorage.getItem('theme') === 'dark') {
                document.documentElement.classList.add('dark');
            } else {
                document.documentElement.classList.remove('dark');
            }
        @else
            document.documentElement.classList.remove('dark');
        @endauth
    </script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class'
        };
    </script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght=300;400;500;600;700&display=swap" rel="stylesheet">

    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }

        /* SEMBUNYIKAN SCROLLBAR */
        html, body, div, nav, aside, main, section {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }

        html::-webkit-scrollbar, body::-webkit-scrollbar, div::-webkit-scrollbar,
        nav::-webkit-scrollbar, aside::-webkit-scrollbar, main::-webkit-scrollbar,
        section::-webkit-scrollbar {
            display: none;
        }

        /* Dropdown Animation Base */
        .dropdown-content, .sub-dropdown-content {
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.4s cubic-bezier(0.16, 1, 0.3, 1);
        }

        .dropdown-container.dropdown-open > .dropdown-content {
            max-height: 1000px;
        }

        .sub-dropdown-container.sub-dropdown-open > .sub-dropdown-content {
            max-height: 500px;
        }

        .chevron-icon, .sub-chevron-icon, .navbar-profile-chevron {
            transition: transform 0.3s ease;
        }

        .dropdown-open > .dropdown-btn .chevron-icon,
        .sub-dropdown-open > .sub-dropdown-btn .sub-chevron-icon,
        .navbar-profile-open .navbar-profile-chevron {
            transform: rotate(180deg);
        }

        /* TRANSISI MOBILE */
        #sidebarApp {
            will-change: transform, width;
            transition: transform 0.45s cubic-bezier(0.16, 1, 0.3, 1);
        }

        #sidebarBackdrop {
            transition: opacity 0.45s ease, visibility 0.45s ease;
        }

        @media (max-width: 767px) {
            #sidebarApp {
                width: 17.5rem !important; /* 280px */
            }
            #sidebarApp .hide-on-collapse {
                opacity: 1 !important;
                transform: none !important;
                pointer-events: auto !important;
                white-space: normal !important;
            }
        }

        /* EFEK HOVER SIDEBAR DESKTOP */
        @media (min-width: 768px) {
            .sidebar-hover-mode {
                width: 5rem; /* ~80px saat kuncup */
                transition: width 0.55s cubic-bezier(0.25, 1, 0.3, 1), box-shadow 0.55s ease;
                overflow: hidden !important;
                will-change: width;
            }

            .sidebar-hover-mode:hover {
                width: 17.5rem;
                box-shadow: 12px 0 35px -5px rgba(0, 0, 0, 0.35);
                transition: width 0.5s cubic-bezier(0.16, 1, 0.3, 1), box-shadow 0.5s ease;
            }

            .sidebar-hover-mode .sidebar-nav-container {
                overflow-y: hidden;
            }
            .sidebar-hover-mode:hover .sidebar-nav-container {
                overflow-y: auto;
            }

            .hide-on-collapse {
                opacity: 0;
                transform: translateX(-10px);
                white-space: nowrap;
                pointer-events: none;
                transition: opacity 0.2s ease-in, transform 0.2s ease-in;
            }

            .sidebar-hover-mode:hover .hide-on-collapse {
                opacity: 1;
                transform: translateX(0);
                pointer-events: auto;
                transition: opacity 0.3s ease-out, transform 0.3s ease-out;
                transition-delay: 0.165s;
            }

            .sidebar-hover-mode:not(:hover) .dropdown-content,
            .sidebar-hover-mode:not(:hover) .sub-dropdown-content {
                max-height: 0 !important;
                transition: max-height 0.35s ease-in-out !important;
            }
        }
    </style>

    @stack('styles')
</head>
<body class="bg-slate-50 dark:bg-slate-950 min-h-screen text-slate-800 dark:text-slate-100 flex overflow-hidden transition-colors duration-200">

    @php
        $authUser     = Auth::user();
        $userLevel    = (int)($authUser->level ?? 3);
        $isLevel1     = $userLevel === 1;
        $isLevel1Or2  = $userLevel <= 2;
        $hasAccess    = $isLevel1Or2;
        $isAdminRole  = $isLevel1Or2;
    @endphp

    <!-- SIDEBAR -->
    <aside id="sidebarApp" class="sidebar-hover-mode bg-slate-900 text-slate-300 flex flex-col h-screen justify-between border-r border-slate-800 shrink-0 z-40 fixed md:relative -translate-x-full md:translate-x-0 shadow-2xl md:shadow-none">
        <div class="flex flex-col h-full overflow-hidden">
            <!-- Header Sidebar -->
            <div class="p-4 border-b border-slate-800 flex items-center justify-between bg-slate-950/40 h-20 shrink-0">
                <div class="z-10 flex items-center space-x-3 overflow-hidden min-w-0">
                    <div class="bg-white p-0.5 rounded-full shadow-md border border-white/20 w-10 h-10 flex items-center justify-center overflow-hidden shrink-0">
                        <img src="{{ asset('images/iconfav.png') }}"
                            alt="Logo META"
                            class="w-full h-full object-contain rounded-full">
                    </div>

                    <div class="hide-on-collapse min-w-0 w-44">
                        <h2 class="font-bold tracking-wide text-[11px] text-cyan-100 leading-snug whitespace-normal break-words">
                            META ADHYA TIRTA UMBULAN
                        </h2>
                    </div>
                </div>
                <button id="closeSidebarBtn" class="md:hidden text-slate-400 hover:text-white p-1.5 rounded-lg hover:bg-slate-800 transition-colors shrink-0">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>

            <!-- Menu Navigasi -->
            <nav class="sidebar-nav-container p-3 space-y-2 flex-1 overflow-y-auto">
                <span class="text-[10px] font-bold text-slate-500 uppercase tracking-widest block px-3 mb-2 hide-on-collapse">Menu Utama</span>

                <!-- Dashboard -->
                <a href="/dashboard" title="Dashboard" class="flex items-center space-x-3 px-2.5 py-2 rounded-xl text-sm font-medium transition-all {{ request()->is('dashboard') ? 'bg-sky-600 text-white shadow-lg shadow-sky-900/20' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}">
                    <div class="w-9 h-9 flex items-center justify-center shrink-0">
                        <i class="fa-solid fa-chart-pie text-base text-center"></i>
                    </div>
                    <span class="hide-on-collapse">Dashboard</span>
                </a>

                <!-- Fasilitas Cuti -->
                @php $isCutiActive = request()->is('cuti/*') || request()->is('admin/persetujuan/cuti*'); @endphp
                <div class="dropdown-container" data-active="{{ $isCutiActive ? 'true' : 'false' }}">
                    <button class="dropdown-btn w-full flex items-center justify-between px-2.5 py-2 rounded-xl text-sm font-medium transition-all relative {{ $isCutiActive ? 'bg-sky-600 text-white shadow-lg shadow-sky-900/20' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}" title="Fasilitas Cuti">
                        <div class="flex items-center space-x-3">
                            <div class="w-9 h-9 flex items-center justify-center shrink-0">
                                <i class="fa-solid fa-calendar-check text-base text-center"></i>
                            </div>
                            <span class="hide-on-collapse">Fasilitas CUTI</span>
                        </div>
                        <div class="flex items-center space-x-2 hide-on-collapse">
                            @if(isset($jumlahSaranCuti) && $jumlahSaranCuti > 0)
                                <span class="h-2 w-2 rounded-full bg-rose-500 ring-2 ring-slate-900 block shrink-0"></span>
                            @endif
                            <i class="fa-solid fa-chevron-down text-xs chevron-icon"></i>
                        </div>
                    </button>

                    <div class="dropdown-content space-y-1 pl-4 pr-1 mt-1">
                        <a href="/cuti/ajukan" class="block px-3 py-2 rounded-xl text-sm transition-all {{ request()->is('cuti/ajukan') ? 'bg-sky-500/20 text-sky-300 font-semibold' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}">
                            <span class="hide-on-collapse">Ajukan CUTI</span>
                        </a>
                        <a href="/cuti/riwayat" class="block px-3 py-2 rounded-xl text-sm transition-all {{ request()->is('cuti/riwayat*') ? 'bg-sky-500/20 text-sky-300 font-semibold' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}">
                            <span class="hide-on-collapse">Riwayat CUTI</span>
                        </a>

                        @if($hasAccess)
                            <a href="{{ route('admin.persetujuan.cuti') }}" class="flex items-center justify-between px-3 py-2 rounded-xl text-sm transition-all {{ request()->is('admin/persetujuan/cuti*') ? 'bg-sky-500/20 text-sky-300 font-semibold' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}">
                                <span class="hide-on-collapse">Persetujuan CUTI</span>
                                @if(isset($jumlahSaranCuti) && $jumlahSaranCuti > 0)
                                    <span class="hide-on-collapse flex h-5 min-w-5 items-center justify-center rounded-full bg-rose-500 px-1 text-[10px] font-bold text-white animate-pulse">
                                        {{ $jumlahSaranCuti }}
                                    </span>
                                @endif
                            </a>
                        @endif
                    </div>
                </div>

                <!-- Fasilitas MPR -->
                @php $isMprActive = request()->is('mpr/*') || request()->is('admin/persetujuan/mpr*'); @endphp
                <div class="dropdown-container" data-active="{{ $isMprActive ? 'true' : 'false' }}">
                    <button class="dropdown-btn w-full flex items-center justify-between px-2.5 py-2 rounded-xl text-sm font-medium transition-all relative {{ $isMprActive ? 'bg-sky-600 text-white shadow-lg shadow-sky-900/20' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}" title="Fasilitas MPR">
                        <div class="flex items-center space-x-3">
                            <div class="w-9 h-9 flex items-center justify-center shrink-0">
                                <i class="fa-solid fa-boxes-packing text-base text-center"></i>
                            </div>
                            <span class="hide-on-collapse">Fasilitas MPR</span>
                        </div>
                        <div class="flex items-center space-x-2 hide-on-collapse">
                            @if(isset($jumlahSaranMpr) && $jumlahSaranMpr > 0)
                                <span class="h-2 w-2 rounded-full bg-rose-500 ring-2 ring-slate-900 block shrink-0"></span>
                            @endif
                            <i class="fa-solid fa-chevron-down text-xs chevron-icon"></i>
                        </div>
                    </button>

                    <div class="dropdown-content space-y-1 pl-4 pr-1 mt-1">
                        <a href="/mpr/ajukan" class="block px-3 py-2 rounded-xl text-sm transition-all {{ request()->is('mpr/create') ? 'bg-sky-500/20 text-sky-300 font-semibold' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}">
                            <span class="hide-on-collapse">Ajukan MPR</span>
                        </a>
                        <a href="/mpr/riwayat" class="block px-3 py-2 rounded-xl text-sm transition-all {{ request()->is('mpr/riwayat*') ? 'bg-sky-500/20 text-sky-300 font-semibold' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}">
                            <span class="hide-on-collapse">Riwayat MPR</span>
                        </a>

                        @if($hasAccess)
                            <a href="{{ route('admin.persetujuan.mpr') }}" class="flex items-center justify-between px-3 py-2 rounded-xl text-sm transition-all {{ request()->is('admin/persetujuan/mpr*') ? 'bg-sky-500/20 text-sky-300 font-semibold' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}">
                                <span class="hide-on-collapse">Persetujuan MPR</span>
                                @if(isset($jumlahSaranMpr) && $jumlahSaranMpr > 0)
                                    <span class="hide-on-collapse flex h-5 min-w-5 items-center justify-center rounded-full bg-rose-500 px-1 text-[10px] font-bold text-white animate-pulse">
                                        {{ $jumlahSaranMpr }}
                                    </span>
                                @endif
                            </a>
                        @endif
                    </div>
                </div>

                <!-- Fasilitas CAR -->
                @php $isCarActive = request()->is('car/*') || request()->is('admin/persetujuan/car*'); @endphp
                <div class="dropdown-container" data-active="{{ $isCarActive ? 'true' : 'false' }}">
                    <button class="dropdown-btn w-full flex items-center justify-between px-2.5 py-2 rounded-xl text-sm font-medium transition-all relative {{ $isCarActive ? 'bg-sky-600 text-white shadow-lg shadow-sky-900/20' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}" title="Fasilitas CAR">
                        <div class="flex items-center space-x-3">
                            <div class="w-9 h-9 flex items-center justify-center shrink-0">
                                <i class="fa-solid fa-file-invoice-dollar text-base text-center"></i>
                            </div>
                            <span class="hide-on-collapse">Fasilitas CAR</span>
                        </div>
                        <div class="flex items-center space-x-2 hide-on-collapse">
                            @if(isset($jumlahSaranCar) && $jumlahSaranCar > 0)
                                <span class="h-2 w-2 rounded-full bg-rose-500 ring-2 ring-slate-900 block shrink-0"></span>
                            @endif
                            <i class="fa-solid fa-chevron-down text-xs chevron-icon"></i>
                        </div>
                    </button>

                    <div class="dropdown-content space-y-1 pl-4 pr-1 mt-1">
                        <a href="/car/ajukan" class="block px-3 py-2 rounded-xl text-sm transition-all {{ request()->is('car/create') ? 'bg-sky-500/20 text-sky-300 font-semibold' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}">
                            <span class="hide-on-collapse">Ajukan CAR</span>
                        </a>
                        <a href="/car/riwayat" class="block px-3 py-2 rounded-xl text-sm transition-all {{ request()->is('car/riwayat*') ? 'bg-sky-500/20 text-sky-300 font-semibold' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}">
                            <span class="hide-on-collapse">Riwayat CAR</span>
                        </a>

                        @if($hasAccess)
                            <a href="{{ route('admin.persetujuan.car') }}" class="flex items-center justify-between px-3 py-2 rounded-xl text-sm transition-all {{ request()->is('admin/persetujuan/car*') ? 'bg-sky-500/20 text-sky-300 font-semibold' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}">
                                <span class="hide-on-collapse">Persetujuan CAR</span>
                                @if(isset($jumlahSaranCar) && $jumlahSaranCar > 0)
                                    <span class="hide-on-collapse flex h-5 min-w-5 items-center justify-center rounded-full bg-rose-500 px-1 text-[10px] font-bold text-white animate-pulse">
                                        {{ $jumlahSaranCar }}
                                    </span>
                                @endif
                            </a>
                        @endif
                    </div>
                </div>

                <!-- MENU ADMINISTRATOR -->
                @if($isAdminRole)
                @php
                    $isAdminActive = (request()->is('admin/*') || request()->routeIs('admin.*'))
                                    && !request()->is('admin/persetujuan/*');
                    $isDaftarActive = request()->routeIs('admin.karyawan.*') || request()->routeIs('admin.stations.*') || request()->routeIs('admin.role.*');
                    $isRecordActive = request()->is('admin/record/*');
                @endphp
                    <div class="dropdown-container" data-active="{{ $isAdminActive ? 'true' : 'false' }}">
                        <button class="dropdown-btn w-full flex items-center justify-between px-2.5 py-2 rounded-xl text-sm font-medium transition-all relative {{ $isAdminActive ? 'bg-sky-600 text-white shadow-lg shadow-sky-900/20' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}" title="Administrator">
                            <div class="flex items-center space-x-3">
                                <div class="w-9 h-9 flex items-center justify-center shrink-0">
                                    <i class="fa-solid fa-folder-open text-base text-center"></i>
                                </div>
                                <span class="hide-on-collapse">Administrator</span>
                            </div>
                            <i class="fa-solid fa-chevron-down text-xs chevron-icon hide-on-collapse"></i>
                        </button>

                        <div class="dropdown-content space-y-1 pl-4 pr-1 mt-1">

                            <!-- REKAP ABSENSI HARIAN -->
                            <a href="{{ Route::has('admin.absensi.index') ? route('admin.absensi.index') : '/admin/absensi' }}" class="flex items-center space-x-2 px-3 py-2 rounded-xl text-xs font-semibold transition-all {{ request()->is('admin/absensi*') ? 'bg-sky-500/20 text-sky-300' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}">
                                <i class="fa-solid fa-user-check text-xs"></i>
                                <span class="hide-on-collapse">Rekap Absensi Harian</span>
                            </a>

                            <!-- SUB-MENU 1: DAFTAR -->
                            <div class="sub-dropdown-container" data-active="{{ $isDaftarActive ? 'true' : 'false' }}">
                                <button class="sub-dropdown-btn w-full flex items-center justify-between px-3 py-2 rounded-xl text-xs font-semibold text-slate-400 hover:bg-slate-800 hover:text-white transition-all">
                                    <div class="flex items-center space-x-2">
                                        <i class="fa-solid fa-list-check text-xs"></i>
                                        <span class="hide-on-collapse">Daftar</span>
                                    </div>
                                    <i class="fa-solid fa-chevron-down text-[10px] sub-chevron-icon hide-on-collapse"></i>
                                </button>
                                <div class="sub-dropdown-content space-y-1 pl-4 mt-1 border-l border-slate-800 ml-2">
                                    <a href="{{ route('admin.role.index') }}" class="block px-3 py-1.5 rounded-lg text-xs transition-all {{ request()->routeIs('admin.role.*') ? 'text-sky-300 font-semibold bg-sky-500/10' : 'text-slate-400 hover:text-white' }}">
                                        <span class="hide-on-collapse">Role</span>
                                    </a>
                                    <a href="{{ route('admin.karyawan.index') }}" class="block px-3 py-1.5 rounded-lg text-xs transition-all {{ request()->routeIs('admin.karyawan.*') ? 'text-sky-300 font-semibold bg-sky-500/10' : 'text-slate-400 hover:text-white' }}">
                                        <span class="hide-on-collapse">Karyawan</span>
                                    </a>
                                    <a href="{{ route('admin.stations.index') }}" class="block px-3 py-1.5 rounded-lg text-xs transition-all {{ request()->routeIs('admin.stations.*') ? 'text-sky-300 font-semibold bg-sky-500/10' : 'text-slate-400 hover:text-white' }}">
                                        <span class="hide-on-collapse">Stations</span>
                                    </a>
                                </div>
                            </div>

                            <!-- SUB-MENU 2: RECORD -->
                            <div class="sub-dropdown-container" data-active="{{ $isRecordActive ? 'true' : 'false' }}">
                                <button class="sub-dropdown-btn w-full flex items-center justify-between px-3 py-2 rounded-xl text-xs font-semibold text-slate-400 hover:bg-slate-800 hover:text-white transition-all">
                                    <div class="flex items-center space-x-2">
                                        <i class="fa-solid fa-clock-rotate-left text-xs"></i>
                                        <span class="hide-on-collapse">Record</span>
                                    </div>
                                    <i class="fa-solid fa-chevron-down text-[10px] sub-chevron-icon hide-on-collapse"></i>
                                </button>
                                <div class="sub-dropdown-content space-y-1 pl-4 mt-1 border-l border-slate-800 ml-2">
                                    <a href="{{ route('admin.record.cuti') }}" class="block px-3 py-1.5 rounded-lg text-xs transition-all {{ request()->is('admin/record/cuti*') ? 'text-sky-300 font-semibold bg-sky-500/10' : 'text-slate-400 hover:text-white' }}">
                                        <span class="hide-on-collapse">Cuti</span>
                                    </a>
                                    <a href="{{ route('admin.record.mpr') }}" class="block px-3 py-1.5 rounded-lg text-xs transition-all {{ request()->is('admin/record/mpr*') ? 'text-sky-300 font-semibold bg-sky-500/10' : 'text-slate-400 hover:text-white' }}">
                                        <span class="hide-on-collapse">Mpr</span>
                                    </a>
                                    <a href="{{ route('admin.record.car') }}" class="block px-3 py-1.5 rounded-lg text-xs transition-all {{ request()->is('admin/record/car*') ? 'text-sky-300 font-semibold bg-sky-500/10' : 'text-slate-400 hover:text-white' }}">
                                        <span class="hide-on-collapse">Car</span>
                                    </a>
                                </div>
                            </div>

                            <!-- WHATSAPP GATEWAY -->
                            <a href="{{ route('admin.whatsapp.index') }}" class="flex items-center space-x-2 px-3 py-2 rounded-xl text-xs font-semibold transition-all {{ request()->is('admin/whatsapp*') ? 'bg-emerald-500/20 text-emerald-300' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}">
                                <i class="fa-brands fa-whatsapp text-xs text-emerald-400"></i>
                                <span class="hide-on-collapse">WhatsApp Gateway</span>
                            </a>
                        </div>
                    </div>
                @endif

                <span class="text-[10px] font-bold text-slate-500 uppercase tracking-widest block px-3 pt-4 mb-2 hide-on-collapse">Pengaturan</span>
                <a href="{{ route('account.index') }}" title="Pengaturan Akun" class="flex items-center space-x-3 px-2.5 py-2 rounded-xl text-sm font-medium transition-all {{ request()->routeIs('account.*') ? 'bg-sky-600 text-white shadow-lg shadow-sky-900/20' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}">
                    <div class="w-9 h-9 flex items-center justify-center shrink-0">
                        <i class="fa fa-cog text-base text-center"></i>
                    </div>
                    <span class="hide-on-collapse">Pengaturan Akun</span>
                </a>
            </nav>
        </div>

        <!-- Logout Trigger Button -->
        <div class="p-3 border-t border-slate-800/60 bg-slate-950/20 shrink-0">
            <button type="button" onclick="openLogoutModal()" title="Keluar Aplikasi" class="w-full flex items-center space-x-3 hover:bg-rose-500/10 text-rose-400 px-2.5 py-2 rounded-xl text-sm font-medium transition-colors group cursor-pointer">
                <div class="w-9 h-9 flex items-center justify-center shrink-0">
                    <i class="fa-solid fa-arrow-right-from-bracket text-base text-center transition-transform group-hover:translate-x-0.5"></i>
                </div>
                <span class="hide-on-collapse">Keluar Aplikasi</span>
            </button>
        </div>
    </aside>

    <!-- BACKDROP MOBILE -->
    <div id="sidebarBackdrop" class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm z-30 opacity-0 pointer-events-none md:hidden"></div>

    <!-- MAIN CONTENT AREA -->
    <div class="flex-1 flex flex-col h-screen overflow-y-auto bg-slate-50 dark:bg-slate-950 transition-colors duration-200">
        <!-- HEADER UTAMA DENGAN JAM DIGITAL & THEME SWITCHER -->
        <header class="bg-white dark:bg-slate-900 border-b border-slate-100 dark:border-slate-800 px-6 py-3 flex justify-between items-center sticky top-0 z-20 shadow-xs transition-colors">
            <div class="flex items-center space-x-3">
                <button id="toggleSidebarBtn" class="md:hidden text-slate-600 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white p-2 rounded-xl bg-slate-50 dark:bg-slate-800 border border-slate-100 dark:border-slate-700 active:scale-95 transition-transform">
                    <i class="fa-solid fa-bars-staggered text-lg"></i>
                </button>
                <div>
                    <p class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Tempat Kerja,</p>
                    <h1 class="text-base font-bold text-slate-800 dark:text-slate-100 leading-tight">{{ Auth::user()->station->name ?? 'Stasiun Umbulan' }}</h1>
                </div>
            </div>

            <div class="fixed bottom-4 right-4 z-20 sm:static sm:right-auto flex flex-col items-center justify-center text-center px-4 py-1.5 sm:px-5 bg-slate-900/90 sm:bg-slate-50 dark:sm:bg-slate-800 text-white sm:text-slate-800 dark:sm:text-slate-100 backdrop-blur-md sm:backdrop-blur-none border border-slate-700/50 sm:border-slate-200/60 dark:sm:border-slate-700 rounded-2xl shadow-xl sm:shadow-inner transition-all duration-300">
                <div class="flex items-center space-x-2 text-sky-400 sm:text-sky-600 dark:sm:text-sky-400 font-mono font-black text-xs sm:text-base md:text-lg tracking-wider">
                    <i class="fa-solid fa-clock text-[10px] sm:text-xs text-sky-400 sm:text-sky-500"></i>
                    <span id="headerDigitalClock">00:00:00 WIB</span>
                </div>
                <span id="headerDateDisplay" class="text-[9px] sm:text-[10px] text-slate-300 sm:text-slate-500 dark:sm:text-slate-400 font-medium tracking-tight">--</span>
            </div>

            <div class="flex items-center space-x-3">
                @auth
                {{-- TOMBOL THEME SWITCHER DARK / LIGHT MODE ELEGAN --}}
                <button type="button"
                    id="themeToggleBtn"
                    onclick="toggleThemeMode()"
                    class="w-10 h-10 rounded-2xl bg-slate-100 dark:bg-slate-800 text-amber-400 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-700 border border-slate-200 dark:border-slate-700 flex items-center justify-center transition-all cursor-pointer shadow-xs active:scale-95"
                    title="Ganti Mode Tema (Dark / Light)">
                    <i id="themeToggleIcon" class="fa-solid fa-sun text-amber-400 text-sm"></i>
                </button>
                @endauth

                {{-- PROFILE DROPDOWN NAVBAR --}}
                <div class="relative" id="navbarProfileDropdownContainer">
                    <button type="button"
                        id="navbarProfileDropdownBtn"
                        class="flex items-center space-x-2 sm:space-x-3 cursor-pointer group p-1.5 rounded-2xl hover:bg-slate-100/80 dark:hover:bg-slate-800/80 transition-all border border-transparent hover:border-slate-200 dark:hover:border-slate-700"
                        title="Menu Profil Pengguna"
                        aria-expanded="false"
                        aria-haspopup="true">
                        <div class="text-right hidden sm:block">
                            <p class="text-sm font-bold text-slate-800 dark:text-slate-100 leading-tight group-hover:text-sky-600 dark:group-hover:text-sky-400 transition-colors">{{ Auth::user()->name }}</p>
                            <p class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider">
                                {{ Auth::user()->role->role_name ?? 'USER' }}
                            </p>
                        </div>
                        <div class="w-10 h-10 rounded-xl bg-sky-600 text-white flex items-center justify-center font-bold shadow-md shadow-sky-100 overflow-hidden border border-slate-100 dark:border-slate-700 shrink-0 group-hover:ring-2 group-hover:ring-sky-500 transition-all">
                            @if(Auth::user()->profile_photo)
                                <img src="{{ asset('storage/' . Auth::user()->profile_photo) }}" alt="User" class="w-full h-full object-cover">
                            @else
                                {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                            @endif
                        </div>
                        <i class="fa-solid fa-chevron-down text-[11px] text-slate-400 group-hover:text-slate-600 dark:group-hover:text-slate-300 transition-transform duration-200 navbar-profile-chevron"></i>
                    </button>

                    {{-- MENU DROPDOWN MELAYANG (FLOATING PANEL) --}}
                    <div id="navbarProfileDropdownMenu"
                        class="hidden absolute right-0 mt-2 w-72 bg-white dark:bg-slate-800 rounded-2xl shadow-xl border border-slate-100 dark:border-slate-700/80 py-2 z-50 transition-all duration-200">
                        {{-- User Header Info --}}
                        <div class="px-4 py-3 border-b border-slate-100 dark:border-slate-700/60 flex items-center space-x-3">
                            <div class="w-10 h-10 rounded-xl bg-sky-600 text-white flex items-center justify-center font-bold shadow-sm overflow-hidden shrink-0">
                                @if(Auth::user()->profile_photo)
                                    <img src="{{ asset('storage/' . Auth::user()->profile_photo) }}" alt="User" class="w-full h-full object-cover">
                                @else
                                    {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                                @endif
                            </div>
                            <div class="min-w-0 flex-1">
                                <p class="text-sm font-bold text-slate-800 dark:text-slate-100 truncate">{{ Auth::user()->name }}</p>
                                <p class="text-xs text-slate-400 truncate">{{ Auth::user()->email }}</p>
                                <span class="inline-block mt-1 px-2 py-0.5 rounded-md text-[10px] font-bold bg-sky-50 text-sky-700 dark:bg-sky-950/60 dark:text-sky-300 border border-sky-200 dark:border-sky-800">
                                    {{ Auth::user()->role->role_name ?? 'USER' }}
                                </span>
                            </div>
                        </div>

                        {{-- Stasiun Info --}}
                        <div class="px-4 py-2 bg-slate-50 dark:bg-slate-900/50 border-b border-slate-100 dark:border-slate-700/60 text-xs">
                            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Penempatan Stasiun</span>
                            <span class="font-semibold text-sky-600 dark:text-sky-400">{{ Auth::user()->station->name ?? 'Stasiun Umbulan' }}</span>
                        </div>

                        {{-- Dropdown Action Links --}}
                        <div class="py-1.5 px-1 space-y-0.5">
                            <button type="button"
                                onclick="closeNavbarProfileDropdown(); openProfileDetailModal();"
                                class="w-full flex items-center space-x-3 px-3 py-2 text-xs font-semibold text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-700/50 hover:text-sky-600 dark:hover:text-sky-400 rounded-xl transition-colors cursor-pointer text-left">
                                <div class="w-7 h-7 rounded-lg bg-sky-50 dark:bg-sky-950/40 text-sky-600 dark:text-sky-400 flex items-center justify-center shrink-0">
                                    <i class="fa-solid fa-id-card text-xs"></i>
                                </div>
                                <span>Detail Profil Lengkap</span>
                            </button>

                            <a href="{{ route('account.index') }}"
                                onclick="closeNavbarProfileDropdown()"
                                class="flex items-center space-x-3 px-3 py-2 text-xs font-semibold text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-700/50 hover:text-sky-600 dark:hover:text-sky-400 rounded-xl transition-colors">
                                <div class="w-7 h-7 rounded-lg bg-indigo-50 dark:bg-indigo-950/40 text-indigo-600 dark:text-indigo-400 flex items-center justify-center shrink-0">
                                    <i class="fa-solid fa-gear text-xs"></i>
                                </div>
                                <span>Pengaturan Akun</span>
                            </a>
                        </div>

                        {{-- Logout Trigger Button --}}
                        <div class="pt-1.5 mt-1 border-t border-slate-100 dark:border-slate-700/60 px-1">
                            <button type="button"
                                onclick="closeNavbarProfileDropdown(); openLogoutModal();"
                                class="w-full flex items-center space-x-3 px-3 py-2 text-xs font-semibold text-rose-600 dark:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-950/30 rounded-xl transition-colors cursor-pointer">
                                <div class="w-7 h-7 rounded-lg bg-rose-50 dark:bg-rose-950/40 text-rose-600 dark:text-rose-400 flex items-center justify-center shrink-0">
                                    <i class="fa-solid fa-arrow-right-from-bracket text-xs"></i>
                                </div>
                                <span>Keluar Aplikasi</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <main class="p-6 max-w-7xl w-full mx-auto pb-20 sm:pb-6">
            {{-- ALERT BANNER: WHATSAPP GATEWAY BELUM TERHUBUNG (KHUSUS LEVEL 1 / ADMIN) --}}
            @if($isAdminRole && !request()->routeIs('admin.whatsapp.*'))
                @php
                    $waStatusData = \App\Services\WhatsAppService::getStatusCached();
                @endphp
                @if(($waStatusData['status'] ?? 'disconnected') !== 'connected')
                    <div class="mb-6 p-4.5 bg-gradient-to-r from-amber-500/15 via-rose-500/10 to-amber-500/15 border border-amber-300/80 dark:border-amber-700/60 rounded-2xl shadow-sm backdrop-blur-md flex flex-col sm:flex-row sm:items-center justify-between gap-4 transition-all">
                        <div class="flex items-start sm:items-center space-x-3.5">
                            <div class="w-10 h-10 rounded-xl bg-emerald-500 text-white flex items-center justify-center text-xl shrink-0 shadow-md shadow-amber-500/20 mt-0.5 sm:mt-0">
                                <i class="fa-brands fa-whatsapp"></i>
                            </div>
                            <div>
                                <h4 class="font-extrabold text-sm text-slate-800 dark:text-slate-100 flex items-center gap-2">
                                    <span>⚠️ WhatsApp Gateway Belum Terhubung</span>
                                    <span class="text-[10px] uppercase font-black px-2 py-0.5 rounded-full bg-rose-100 text-rose-700 dark:bg-rose-950/60 dark:text-rose-300 border border-rose-200 dark:border-rose-800">
                                        {{ strtoupper($waStatusData['status'] ?? 'TERPUTUS') }}
                                    </span>
                                </h4>
                                <p class="text-xs text-slate-600 dark:text-slate-400 mt-0.5 leading-relaxed">
                                    Sistem tidak dapat mengirim OTP & notifikasi persetujuan. Sambungkan perangkat sekarang.
                                </p>
                            </div>
                        </div>
                        <a href="{{ route('admin.whatsapp.index') }}" class="inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl shadow-md shadow-emerald-600/20 transition-all shrink-0 whitespace-nowrap active:scale-95">                            <i class="fa-solid fa-qrcode"></i>
                            <span>Sambungkan Perangkat</span>
                        </a>
                    </div>
                @endif
            @endif

            @yield('content')
        </main>
    </div>

    <!-- JAVASCRIPT HANDLER -->
    <script>
        function updateHeaderClock() {
            const now = new Date();
            const optionsDate = { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' };

            const dateString = now.toLocaleDateString('id-ID', optionsDate);
            const hours = String(now.getHours()).padStart(2, '0');
            const minutes = String(now.getMinutes()).padStart(2, '0');
            const seconds = String(now.getSeconds()).padStart(2, '0');

            const clockElement = document.getElementById('headerDigitalClock');
            const dateElement = document.getElementById('headerDateDisplay');

            if (clockElement) clockElement.innerText = `${hours}:${minutes}:${seconds} WIB`;
            if (dateElement) dateElement.innerText = dateString;
        }

        function toggleThemeMode() {
            @auth
            const html = document.documentElement;
            const isDark = html.classList.toggle('dark');
            localStorage.setItem('theme', isDark ? 'dark' : 'light');
            syncThemeIcon();
            @endauth
        }

        function syncThemeIcon() {
            const icon = document.getElementById('themeToggleIcon');
            if (!icon) return;
            const isDark = document.documentElement.classList.contains('dark');
            if (isDark) {
                icon.className = 'fa-solid fa-moon text-slate-300 text-sm';
            } else {
                icon.className = 'fa-solid fa-sun text-amber-400 text-sm';
            }
        }

        // --- NAVBAR PROFILE DROPDOWN ---
        function toggleNavbarProfileDropdown(e) {
            if (e) {
                e.preventDefault();
                e.stopPropagation();
            }
            const menu = document.getElementById('navbarProfileDropdownMenu');
            const btn = document.getElementById('navbarProfileDropdownBtn');
            if (!menu || !btn) return;

            const isHidden = menu.classList.contains('hidden');
            if (isHidden) {
                openNavbarProfileDropdown();
            } else {
                closeNavbarProfileDropdown();
            }
        }

        function openNavbarProfileDropdown() {
            const menu = document.getElementById('navbarProfileDropdownMenu');
            const btn = document.getElementById('navbarProfileDropdownBtn');
            if (!menu || !btn) return;

            menu.classList.remove('hidden');
            btn.classList.add('navbar-profile-open');
            btn.setAttribute('aria-expanded', 'true');
        }

        function closeNavbarProfileDropdown() {
            const menu = document.getElementById('navbarProfileDropdownMenu');
            const btn = document.getElementById('navbarProfileDropdownBtn');
            if (!menu || !btn) return;

            menu.classList.add('hidden');
            btn.classList.remove('navbar-profile-open');
            btn.setAttribute('aria-expanded', 'false');
        }

        // --- SIDEBAR DROPDOWN (ACCORDION) ---
        function toggleDropdown(container) {
            if (!container) return;
            const isOpen = container.classList.contains('dropdown-open');
            if (isOpen) {
                container.classList.remove('dropdown-open');
            } else {
                container.classList.add('dropdown-open');
            }
        }

        function toggleSubDropdown(subContainer) {
            if (!subContainer) return;
            const isOpen = subContainer.classList.contains('sub-dropdown-open');
            if (isOpen) {
                subContainer.classList.remove('sub-dropdown-open');
            } else {
                subContainer.classList.add('sub-dropdown-open');
            }
        }

        // --- MOBILE SIDEBAR HANDLER ---
        function openSidebarMobile() {
            const sidebar = document.getElementById("sidebarApp");
            const backdrop = document.getElementById("sidebarBackdrop");
            if (sidebar && backdrop) {
                sidebar.classList.remove("-translate-x-full");
                sidebar.classList.add("translate-x-0");
                backdrop.classList.remove("pointer-events-none", "opacity-0");
                backdrop.classList.add("opacity-100");
            }
        }

        function closeSidebarMobile() {
            const sidebar = document.getElementById("sidebarApp");
            const backdrop = document.getElementById("sidebarBackdrop");
            if (sidebar && backdrop) {
                sidebar.classList.remove("translate-x-0");
                sidebar.classList.add("-translate-x-full");
                backdrop.classList.remove("opacity-100");
                backdrop.classList.add("opacity-0", "pointer-events-none");
            }
        }

        // --- SINKRONISASI TAMPILAN SAAT NAVIGASI (TURBO / DOM LOAD) ---
        function initLayoutHandlers() {
            updateHeaderClock();
            syncThemeIcon();
            closeNavbarProfileDropdown();
            closeSidebarMobile();
            closeLogoutModal();

            // Auto-open active dropdowns based on data-active="true"
            document.querySelectorAll('.dropdown-container[data-active="true"]').forEach(container => {
                container.classList.add('dropdown-open');
            });
            document.querySelectorAll('.sub-dropdown-container[data-active="true"]').forEach(sub => {
                sub.classList.add('sub-dropdown-open');
            });
        }

        // --- SINGLE GLOBAL EVENT DELEGATION (SAFE FROM MULTI-BINDING BUG) ---
        if (!window.__umbulanLayoutEventsBound) {
            window.__umbulanLayoutEventsBound = true;

            document.addEventListener('click', function(e) {
                // 1. Navbar Profile Dropdown Button
                const navProfileBtn = e.target.closest('#navbarProfileDropdownBtn');
                if (navProfileBtn) {
                    toggleNavbarProfileDropdown(e);
                    return;
                }

                // 2. Click outside Navbar Profile Dropdown -> Dismiss
                const profileDropdownContainer = document.getElementById('navbarProfileDropdownContainer');
                if (profileDropdownContainer && !profileDropdownContainer.contains(e.target)) {
                    closeNavbarProfileDropdown();
                }

                // 3. Sidebar Level 1 Dropdown Buttons (Fasilitas Cuti, MPR, CAR, Administrator)
                const dropdownBtn = e.target.closest('.dropdown-btn');
                if (dropdownBtn) {
                    e.preventDefault();
                    const container = dropdownBtn.closest('.dropdown-container');
                    if (container) {
                        toggleDropdown(container);
                    }
                    return;
                }

                // 4. Sidebar Level 2 Sub-Dropdown Buttons (Daftar, Record)
                const subBtn = e.target.closest('.sub-dropdown-btn');
                if (subBtn) {
                    e.preventDefault();
                    e.stopPropagation();
                    const subContainer = subBtn.closest('.sub-dropdown-container');
                    if (subContainer) {
                        toggleSubDropdown(subContainer);
                    }
                    return;
                }

                // 5. Mobile Sidebar Triggers
                if (e.target.closest('#toggleSidebarBtn')) {
                    e.preventDefault();
                    openSidebarMobile();
                    return;
                }
                if (e.target.closest('#closeSidebarBtn') || (e.target.id === 'sidebarBackdrop')) {
                    e.preventDefault();
                    closeSidebarMobile();
                    return;
                }
            });

            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') {
                    closeNavbarProfileDropdown();
                    closeProfileDetailModal();
                    closeSidebarMobile();
                    closeLogoutModal();
                }
            });

            setInterval(updateHeaderClock, 1000);
        }

        document.addEventListener("DOMContentLoaded", initLayoutHandlers);
        document.addEventListener("turbo:load", initLayoutHandlers);
    </script>

    <!-- MODAL POPUP DETAIL AKUN USER -->
    <div id="profileDetailModal" class="fixed inset-0 z-50 hidden flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm transition-opacity duration-300">
        <div id="profileDetailModalCard" class="relative bg-white dark:bg-slate-900 rounded-3xl shadow-2xl max-w-lg w-full overflow-hidden border border-slate-100 dark:border-slate-800 transform transition-all duration-300 scale-95 opacity-0">

            <!-- HEADER MODAL -->
            <div class="p-5 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between bg-gradient-to-r from-sky-600 to-indigo-700 text-white">
                <div class="flex items-center space-x-2">
                    <i class="fa-solid fa-id-card text-lg"></i>
                    <h3 class="text-xs font-bold tracking-wide uppercase">Detail Informasi Akun Karyawan</h3>
                </div>
                <button onclick="closeProfileDetailModal()" class="w-8 h-8 rounded-full bg-white/20 hover:bg-white/30 text-white flex items-center justify-center transition-colors cursor-pointer">
                    <i class="fa-solid fa-xmark text-sm"></i>
                </button>
            </div>

            <!-- BODY MODAL -->
            <div class="p-6 space-y-5 max-h-[80vh] overflow-y-auto">
                <!-- RINGKASAN AVATAR & NAMA -->
                <div class="flex flex-col sm:flex-row items-center gap-4 bg-slate-50 dark:bg-slate-800/80 p-4 rounded-2xl border border-slate-100 dark:border-slate-700">
                    <div class="w-20 h-20 rounded-2xl bg-sky-600 text-white flex items-center justify-center font-extrabold text-2xl shadow-md overflow-hidden shrink-0 border-2 border-white dark:border-slate-700">
                        @if(Auth::user()->profile_photo)
                            <img src="{{ asset('storage/' . Auth::user()->profile_photo) }}" alt="User Photo" class="w-full h-full object-cover">
                        @else
                            {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                        @endif
                    </div>
                    <div class="text-center sm:text-left space-y-1">
                        <h4 class="font-extrabold text-slate-800 dark:text-slate-100 text-base sm:text-lg leading-tight">{{ Auth::user()->name }}</h4>
                        <p class="text-xs text-slate-500 dark:text-slate-400 font-semibold">{{ Auth::user()->email }}</p>
                        <div class="flex flex-wrap gap-1.5 justify-center sm:justify-start mt-1.5">
                            @if(Auth::user()->roles && Auth::user()->roles->count() > 0)
                                @foreach(Auth::user()->roles as $r)
                                    <span class="px-2.5 py-0.5 bg-sky-100 text-sky-800 dark:bg-sky-900/50 dark:text-sky-300 rounded-md text-[10px] font-bold uppercase tracking-wider border border-sky-200 dark:border-sky-800">
                                        {{ $r->role_name }}
                                    </span>
                                @endforeach
                            @else
                                <span class="px-2.5 py-0.5 bg-slate-200 dark:bg-slate-700 text-slate-700 dark:text-slate-300 rounded-md text-[10px] font-bold uppercase tracking-wider">
                                    {{ Auth::user()->role->role_name ?? 'Karyawan' }}
                                </span>
                            @endif

                            @if(Auth::user()->nip)
                                <span class="px-2.5 py-0.5 bg-slate-200 dark:bg-slate-700 text-slate-700 dark:text-slate-300 rounded-md text-[10px] font-mono font-bold">
                                    NIP: {{ Auth::user()->nip }}
                                </span>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- GRID DETAIL INFORMASI LENGKAP -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs">
                    <div class="p-3 bg-white dark:bg-slate-800 border border-slate-100 dark:border-slate-700 rounded-xl space-y-0.5 shadow-xs">
                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">NIP</span>
                        <p class="font-bold text-slate-800 dark:text-slate-100 font-mono text-xs sm:text-sm">{{ Auth::user()->nip ?? '-' }}</p>
                    </div>

                    <div class="p-3 bg-white dark:bg-slate-800 border border-slate-100 dark:border-slate-700 rounded-xl space-y-0.5 shadow-xs">
                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Nomor WhatsApp</span>
                        <p class="font-bold text-slate-800 dark:text-slate-100 flex items-center justify-between">
                            <span>{{ Auth::user()->phone_number ?? '-' }}</span>
                            @if(Auth::user()->phone_verified_at)
                                <span class="text-[10px] font-bold text-emerald-600 bg-emerald-50 dark:bg-emerald-950/40 dark:text-emerald-400 px-1.5 py-0.5 rounded border border-emerald-200 dark:border-emerald-800">
                                    <i class="fa-solid fa-circle-check mr-0.5"></i> Terverifikasi
                                </span>
                            @else
                                <span class="text-[10px] font-bold text-amber-600 bg-amber-50 dark:bg-amber-950/40 dark:text-amber-400 px-1.5 py-0.5 rounded border border-amber-200 dark:border-amber-800">Belum Verifikasi</span>
                            @endif
                        </p>
                    </div>

                    <div class="p-3 bg-white dark:bg-slate-800 border border-slate-100 dark:border-slate-700 rounded-xl space-y-0.5 shadow-xs">
                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Alamat Email</span>
                        <p class="font-bold text-slate-800 dark:text-slate-100 truncate flex items-center justify-between">
                            <span class="truncate">{{ Auth::user()->email }}</span>
                            @if(Auth::user()->email_verified_at)
                                <span class="text-[10px] font-bold text-emerald-600 bg-emerald-50 dark:bg-emerald-950/40 dark:text-emerald-400 px-1.5 py-0.5 rounded ml-1 shrink-0 border border-emerald-200 dark:border-emerald-800">
                                    <i class="fa-solid fa-circle-check mr-0.5"></i> Terverifikasi
                                </span>
                            @else
                                <span class="text-[10px] font-bold text-amber-600 bg-amber-50 dark:bg-amber-950/40 dark:text-amber-400 px-1.5 py-0.5 rounded ml-1 shrink-0 border border-amber-200 dark:border-amber-800">Belum Verifikasi</span>
                            @endif
                        </p>
                    </div>

                    <div class="p-3 bg-white dark:bg-slate-800 border border-slate-100 dark:border-slate-700 rounded-xl space-y-0.5 shadow-xs">
                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Jenis Kelamin</span>
                        <p class="font-bold text-slate-800 dark:text-slate-100">
                            {{ Auth::user()->gender->name_gender ?? (Auth::user()->gender_id == 2 ? 'Perempuan' : 'Laki-Laki') }}
                        </p>
                    </div>

                    <div class="p-3 bg-white dark:bg-slate-800 border border-slate-100 dark:border-slate-700 rounded-xl space-y-0.5 shadow-xs">
                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Stasiun Penempatan</span>
                        <p class="font-bold text-sky-600 dark:text-sky-400">{{ Auth::user()->station->name ?? 'Stasiun Umbulan' }}</p>
                    </div>

                    @if(Auth::user()->hasRole('AREA (PIPELINE)') || Auth::user()->hasRole(14))
                        <div class="p-3 bg-white dark:bg-slate-800 border border-slate-100 dark:border-slate-700 rounded-xl space-y-1.5 shadow-xs col-span-1 sm:col-span-2">
                            <div class="flex items-center justify-between">
                                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Area Cakupan (Rumah Meter)</span>
                                <span class="text-[10px] font-bold text-amber-600 dark:text-amber-400 bg-amber-50 dark:bg-amber-950/40 px-2 py-0.5 rounded-full border border-amber-200 dark:border-amber-800">
                                    {{ Auth::user()->assignedStations->count() }} Checkpoint
                                </span>
                            </div>
                            <div class="flex flex-wrap gap-1.5 pt-0.5">
                                @forelse(Auth::user()->assignedStations as $rm)
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-amber-50 dark:bg-amber-950/40 text-amber-800 dark:text-amber-300 border border-amber-200/80 dark:border-amber-800/60 rounded-lg text-xs font-semibold shadow-2xs">
                                        <i class="fa-solid fa-gauge-high text-amber-500 text-[10px]"></i>
                                        <span><strong class="font-bold font-mono">{{ $rm->kode_stasiun }}</strong> - {{ $rm->name }}</span>
                                    </span>
                                @empty
                                    <span class="text-slate-400 text-xs italic">Belum ada penugasan Rumah Meter khusus.</span>
                                @endforelse
                            </div>
                        </div>
                    @endif

                    <div class="p-3 bg-white dark:bg-slate-800 border border-slate-100 dark:border-slate-700 rounded-xl space-y-0.5 shadow-xs">
                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Tipe Jadwal Kerja</span>
                        <p class="font-bold text-indigo-600 dark:text-indigo-400">
                            @if(Auth::user()->schedule_type === 'roster')
                                Sistem Roster / Shift 12 Jam
                            @elseif(Auth::user()->schedule_type === 'normal')
                                Normal ({{ Auth::user()->normal_check_in ?? '08:00' }} - {{ Auth::user()->normal_check_out ?? '17:00' }})
                            @else
                                Belum Diatur
                            @endif
                        </p>
                    </div>
                </div>
            </div>

            <!-- FOOTER MODAL -->
            <div class="p-4 border-t border-slate-100 dark:border-slate-800 bg-slate-50 dark:bg-slate-900/90 flex items-center justify-between">
                <a href="{{ route('account.index') }}" class="px-4 py-2 bg-sky-600 hover:bg-sky-700 text-white text-xs font-bold rounded-xl transition-all flex items-center space-x-1.5 shadow-xs">
                    <i class="fa-solid fa-gear"></i>
                    <span>Pengaturan Akun</span>
                </a>
                <button onclick="closeProfileDetailModal()" class="px-4 py-2 bg-slate-200 dark:bg-slate-800 hover:bg-slate-300 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 text-xs font-bold rounded-xl transition-all cursor-pointer">
                    Tutup
                </button>
            </div>
        </div>
    </div>

    <!-- MODAL KONFIRMASI LOGOUT -->
    <div id="logoutConfirmModal" class="fixed inset-0 z-50 hidden flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm transition-opacity duration-300">
        <div id="logoutConfirmModalCard" class="relative bg-white dark:bg-slate-900 rounded-3xl shadow-2xl max-w-md w-full overflow-hidden border border-slate-100 dark:border-slate-800 transform transition-all duration-300 scale-95 opacity-0">
            <!-- HEADER MODAL -->
            <div class="p-5 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between bg-gradient-to-r from-rose-600 to-rose-700 text-white">
                <div class="flex items-center space-x-2.5">
                    <div class="w-8 h-8 rounded-lg bg-white/20 flex items-center justify-center shrink-0">
                        <i class="fa-solid fa-arrow-right-from-bracket text-sm"></i>
                    </div>
                    <h3 class="text-xs font-bold tracking-wide uppercase">Konfirmasi Keluar Aplikasi</h3>
                </div>
                <button type="button" onclick="closeLogoutModal()" class="w-8 h-8 rounded-full bg-white/20 hover:bg-white/30 text-white flex items-center justify-center transition-colors cursor-pointer">
                    <i class="fa-solid fa-xmark text-sm"></i>
                </button>
            </div>

            <!-- BODY MODAL -->
            <div class="p-6 text-center space-y-4">
                <div class="w-16 h-16 rounded-2xl bg-rose-50 dark:bg-rose-950/50 text-rose-500 flex items-center justify-center mx-auto text-2xl shadow-inner border border-rose-100 dark:border-rose-900/40">
                    <i class="fa-solid fa-door-open"></i>
                </div>
                <div class="space-y-1.5">
                    <h4 class="text-base font-bold text-slate-800 dark:text-slate-100">Apakah Anda yakin ingin keluar?</h4>
                    <p class="text-xs text-slate-500 dark:text-slate-400 leading-relaxed max-w-xs mx-auto">
                        Sesi aktif Anda akan diakhiri demi keamanan akun. Anda perlu login kembali untuk mengakses sistem absensi dan dashboard.
                    </p>
                </div>
            </div>

            <!-- FOOTER MODAL ACTIONS -->
            <div class="p-4 border-t border-slate-100 dark:border-slate-800 bg-slate-50 dark:bg-slate-900/90 flex items-center justify-end gap-3">
                <button type="button" onclick="closeLogoutModal()" class="px-4 py-2.5 bg-slate-200 dark:bg-slate-800 hover:bg-slate-300 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 text-xs font-bold rounded-xl transition-all cursor-pointer">
                    Batal
                </button>
                <form id="logoutModalForm" action="{{ route('logout') }}" method="POST" data-turbo="false" class="inline m-0">
                    @csrf
                    <button type="submit" id="btnConfirmLogout" class="inline-flex items-center gap-2 px-5 py-2.5 bg-rose-600 hover:bg-rose-700 active:scale-95 text-white text-xs font-bold rounded-xl shadow-md shadow-rose-600/20 transition-all cursor-pointer">
                        <i class="fa-solid fa-arrow-right-from-bracket text-xs"></i>
                        <span>Ya, Keluar Aplikasi</span>
                    </button>
                </form>
            </div>
        </div>
    </div>

    <script>
        function openProfileDetailModal() {
            const modal = document.getElementById('profileDetailModal');
            const modalCard = document.getElementById('profileDetailModalCard');
            if (!modal || !modalCard) return;

            modal.classList.remove('hidden');
            setTimeout(() => {
                modalCard.classList.remove('scale-95', 'opacity-0');
                modalCard.classList.add('scale-100', 'opacity-100');
            }, 10);
        }

        function closeProfileDetailModal() {
            const modal = document.getElementById('profileDetailModal');
            const modalCard = document.getElementById('profileDetailModalCard');
            if (!modal || !modalCard) return;

            modalCard.classList.remove('scale-100', 'opacity-100');
            modalCard.classList.add('scale-95', 'opacity-0');

            setTimeout(() => {
                modal.classList.add('hidden');
            }, 200);
        }

        document.getElementById('profileDetailModal')?.addEventListener('click', function(e) {
            if (e.target === this) {
                closeProfileDetailModal();
            }
        });

        // --- MODAL KONFIRMASI LOGOUT HANDLERS ---
        function openLogoutModal() {
            const modal = document.getElementById('logoutConfirmModal');
            const modalCard = document.getElementById('logoutConfirmModalCard');
            if (!modal || !modalCard) return;

            // Sinkronisasi token CSRF form dengan meta tag terbaru
            const metaToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            const tokenInput = document.querySelector('#logoutModalForm input[name="_token"]');
            if (metaToken && tokenInput) {
                tokenInput.value = metaToken;
            }

            modal.classList.remove('hidden');
            setTimeout(() => {
                modalCard.classList.remove('scale-95', 'opacity-0');
                modalCard.classList.add('scale-100', 'opacity-100');
            }, 10);
        }

        function closeLogoutModal() {
            const modal = document.getElementById('logoutConfirmModal');
            const modalCard = document.getElementById('logoutConfirmModalCard');
            if (!modal || !modalCard) return;

            modalCard.classList.remove('scale-100', 'opacity-100');
            modalCard.classList.add('scale-95', 'opacity-0');

            setTimeout(() => {
                modal.classList.add('hidden');
            }, 200);
        }

        document.getElementById('logoutConfirmModal')?.addEventListener('click', function(e) {
            if (e.target === this) {
                closeLogoutModal();
            }
        });

        document.getElementById('logoutModalForm')?.addEventListener('submit', function() {
            const btn = document.getElementById('btnConfirmLogout');
            if (btn && !btn.dataset.submitted) {
                btn.dataset.submitted = "true";
                setTimeout(() => {
                    btn.disabled = true;
                    btn.innerHTML = '<i class="fa-solid fa-circle-notch fa-spin text-xs mr-1.5"></i><span>Memproses...</span>';
                }, 10);
            }
        });

        // --- BFCACHE BUSTER (Anti-Back-Button Setelah Logout) ---
        window.addEventListener('pageshow', function(event) {
            if (event.persisted || (window.performance && window.performance.getEntriesByType("navigation")[0]?.type === "back_forward")) {
                window.location.reload();
            }
        });
    </script>

    @stack('scripts')
</body>
</html>
