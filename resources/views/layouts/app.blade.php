<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title') | PT META Adhya Tirta Umbulan</title>
    <link rel="icon" type="image/png" href="{{ asset('images/iconfav.png') }}?v=2">
    <script src="https://cdn.tailwindcss.com"></script>
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
            transition: max-height 0.45s cubic-bezier(0.16, 1, 0.3, 1);
        }

        .chevron-icon, .sub-chevron-icon {
            transition: transform 0.3s ease;
        }

        .dropdown-open > .dropdown-btn .chevron-icon,
        .sub-dropdown-open > .sub-dropdown-btn .sub-chevron-icon {
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
<body class="bg-slate-50 min-h-screen text-slate-800 flex overflow-hidden">

    @php
        $authUser    = Auth::user();
        $userRole    = $authUser->role;
        $isRootAdmin = $userRole?->id === 1;
        $isLevel1Or2 = (int)($userRole?->level ?? 99) <= 2;
        $hasAccess   = $isRootAdmin || $isLevel1Or2;
        $isAdminRole = $isRootAdmin || $isLevel1Or2;
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
                        <a href="/mpr/create" class="block px-3 py-2 rounded-xl text-sm transition-all {{ request()->is('mpr/create') ? 'bg-sky-500/20 text-sky-300 font-semibold' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}">
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
                        <a href="/car/create" class="block px-3 py-2 rounded-xl text-sm transition-all {{ request()->is('car/create') ? 'bg-sky-500/20 text-sky-300 font-semibold' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}">
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
                                        <span class="hide-on-collapse">CUTI</span>
                                    </a>
                                    <a href="{{ route('admin.record.car') }}" class="block px-3 py-1.5 rounded-lg text-xs transition-all {{ request()->is('admin/record/car*') ? 'text-sky-300 font-semibold bg-sky-500/10' : 'text-slate-400 hover:text-white' }}">
                                        <span class="hide-on-collapse">CAR</span>
                                    </a>
                                    <a href="{{ route('admin.record.mpr') }}" class="block px-3 py-1.5 rounded-lg text-xs transition-all {{ request()->is('admin/record/mpr*') ? 'text-sky-300 font-semibold bg-sky-500/10' : 'text-slate-400 hover:text-white' }}">
                                        <span class="hide-on-collapse">MPR</span>
                                    </a>
                                </div>
                            </div>
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

        <!-- Logout Button -->
        <div class="p-3 border-t border-slate-800/60 bg-slate-950/20 shrink-0">
            <form action="/logout" method="POST">
                @csrf
                <button type="submit" title="Keluar Aplikasi" class="w-full flex items-center space-x-3 hover:bg-rose-500/10 text-rose-400 px-2.5 py-2 rounded-xl text-sm font-medium transition-colors group">
                    <div class="w-9 h-9 flex items-center justify-center shrink-0">
                        <i class="fa-solid fa-arrow-right-from-bracket text-base text-center transition-transform group-hover:translate-x-0.5"></i>
                    </div>
                    <span class="hide-on-collapse">Keluar Aplikasi</span>
                </button>
            </form>
        </div>
    </aside>

    <!-- BACKDROP MOBILE -->
    <div id="sidebarBackdrop" class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm z-30 opacity-0 pointer-events-none md:hidden"></div>

    <!-- MAIN CONTENT AREA -->
    <div class="flex-1 flex flex-col h-screen overflow-y-auto">
        <!-- HEADER UTAMA DENGAN JAM DIGITAL -->
        <header class="bg-white border-b border-slate-100 px-6 py-3 flex justify-between items-center sticky top-0 z-20 shadow-sm">
            <div class="flex items-center space-x-3">
                <button id="toggleSidebarBtn" class="md:hidden text-slate-600 hover:text-slate-900 p-2 rounded-xl bg-slate-50 border border-slate-100 active:scale-95 transition-transform">
                    <i class="fa-solid fa-bars-staggered text-lg"></i>
                </button>
                <div>
                    <p class="text-[11px] font-semibold text-slate-400 tracking-wider uppercase">Penempatan Kerja,</p>
                    <h1 class="text-base font-bold text-slate-800 leading-tight">{{ Auth::user()->station->name ?? 'Stasiun Umbulan' }}</h1>
                </div>
            </div>

            <div class="fixed bottom-4 right-4 z-20 sm:static sm:right-auto flex flex-col items-center justify-center text-center px-4 py-1.5 sm:px-5 bg-slate-900/90 sm:bg-slate-50 text-white sm:text-slate-800 backdrop-blur-md sm:backdrop-blur-none border border-slate-700/50 sm:border-slate-200/60 rounded-2xl shadow-xl sm:shadow-inner transition-all duration-300">
                <div class="flex items-center space-x-2 text-sky-400 sm:text-sky-600 font-mono font-black text-xs sm:text-base md:text-lg tracking-wider">
                    <i class="fa-solid fa-clock text-[10px] sm:text-xs text-sky-400 sm:text-sky-500"></i>
                    <span id="headerDigitalClock">00:00:00 WIB</span>
                </div>
                <span id="headerDateDisplay" class="text-[9px] sm:text-[10px] text-slate-300 sm:text-slate-500 font-medium tracking-tight">--</span>
            </div>

            <div onclick="openProfileDetailModal()" class="flex items-center space-x-3 cursor-pointer group p-1.5 rounded-2xl hover:bg-slate-100/80 transition-all" title="Klik untuk lihat detail akun">
                <div class="text-right hidden sm:block">
                    <p class="text-sm font-bold text-slate-800 leading-tight group-hover:text-sky-600 transition-colors">{{ Auth::user()->name }}</p>
                    <p class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider">
                        {{ Auth::user()->role->role_name ?? 'USER' }}
                    </p>
                </div>
                <div class="w-10 h-10 rounded-xl bg-sky-600 text-white flex items-center justify-center font-bold shadow-md shadow-sky-100 overflow-hidden border border-slate-100 shrink-0 group-hover:ring-2 group-hover:ring-sky-500 transition-all">
                    @if(Auth::user()->profile_photo)
                        <img src="{{ asset('storage/' . Auth::user()->profile_photo) }}" alt="User" class="w-full h-full object-cover">
                    @else
                        {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                    @endif
                </div>
            </div>
        </header>

        <main class="p-6 max-w-7xl w-full mx-auto pb-20 sm:pb-6">
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

        setInterval(updateHeaderClock, 1000);

        document.addEventListener("DOMContentLoaded", function () {
            updateHeaderClock();

            const sidebar = document.getElementById("sidebarApp");
            const backdrop = document.getElementById("sidebarBackdrop");
            const toggleBtn = document.getElementById("toggleSidebarBtn");
            const closeBtn = document.getElementById("closeSidebarBtn");

            // HANDLER DROPDOWN LEVEL 1
            const dropdownContainers = document.querySelectorAll('.dropdown-container');

            function openDropdown(container) {
                const content = container.querySelector('.dropdown-content');
                container.classList.add('dropdown-open');

                content.style.maxHeight = content.scrollHeight + "px";
            }

            function closeDropdown(container) {
                const content = container.querySelector('.dropdown-content');
                container.classList.remove('dropdown-open');
                content.style.maxHeight = "0px";
            }

            dropdownContainers.forEach(container => {
                const btn = container.querySelector('.dropdown-btn');
                btn.addEventListener('click', function(e) {
                    e.preventDefault();
                    if (window.innerWidth >= 768 && sidebar.matches(':not(:hover)')) return;
                    const isOpen = container.classList.contains('dropdown-open');
                    if (isOpen) {
                        closeDropdown(container);
                    } else {
                        openDropdown(container);
                    }
                });
            });

            const subDropdownContainers = document.querySelectorAll('.sub-dropdown-container');

            function openSubDropdown(subContainer) {
                const subContent = subContainer.querySelector('.sub-dropdown-content');
                subContainer.classList.add('sub-dropdown-open');

                const subHeight = subContent.scrollHeight;
                subContent.style.maxHeight = subHeight + "px";

                const parentDropdown = subContainer.closest('.dropdown-container');
                if (parentDropdown) {
                    const parentContent = parentDropdown.querySelector('.dropdown-content');
                    requestAnimationFrame(() => {
                        parentContent.style.maxHeight = (parentContent.scrollHeight + subHeight) + "px";
                    });
                }
            }

            function closeSubDropdown(subContainer) {
                const subContent = subContainer.querySelector('.sub-dropdown-content');
                const subHeight = subContent.scrollHeight;

                subContainer.classList.remove('sub-dropdown-open');
                subContent.style.maxHeight = "0px";

                const parentDropdown = subContainer.closest('.dropdown-container');
                if (parentDropdown) {
                    const parentContent = parentDropdown.querySelector('.dropdown-content');
                    const currentParentHeight = parentContent.scrollHeight;
                    parentContent.style.maxHeight = Math.max(0, currentParentHeight - subHeight) + "px";
                }
            }

            subDropdownContainers.forEach(subContainer => {
                const subBtn = subContainer.querySelector('.sub-dropdown-btn');
                subBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();

                    const isSubOpen = subContainer.classList.contains('sub-dropdown-open');
                    if (isSubOpen) closeSubDropdown(subContainer); else openSubDropdown(subContainer);
                });
            });

            // HOVER MOUSE SIDEBAR DESKTOP
            let sidebarHoverTimeout = null;
            if (sidebar) {
                sidebar.addEventListener('mouseenter', function() {
                    if (window.innerWidth >= 768) {
                        if (sidebarHoverTimeout) clearTimeout(sidebarHoverTimeout);
                        setTimeout(() => {
                            dropdownContainers.forEach(container => {
                                if (container.getAttribute('data-active') === 'true') {
                                    openDropdown(container);

                                    // Auto-open sub-dropdown if active
                                    const activeSub = container.querySelectorAll('.sub-dropdown-container[data-active="true"]');
                                    activeSub.forEach(sub => openSubDropdown(sub));
                                }
                            });
                        }, 200);
                    }
                });

                sidebar.addEventListener('mouseleave', function() {
                    if (window.innerWidth >= 768) {
                        if (sidebarHoverTimeout) clearTimeout(sidebarHoverTimeout);
                    }
                });
            }

            // AUTO OPEN JIKA DIPANGGIL LANGSUNG
            dropdownContainers.forEach(container => {
                if (container.getAttribute('data-active') === 'true') {
                    openDropdown(container);
                    const activeSubs = container.querySelectorAll('.sub-dropdown-container[data-active="true"]');
                    activeSubs.forEach(sub => openSubDropdown(sub));
                }
            });

            // MOBILE SIDEBAR HANDLER
            function openSidebarMobile() {
                if (sidebar && backdrop) {
                    sidebar.classList.remove("-translate-x-full");
                    sidebar.classList.add("translate-x-0");
                    backdrop.classList.remove("pointer-events-none", "opacity-0");
                    backdrop.classList.add("opacity-100");
                }
            }

            function closeSidebarMobile() {
                if (sidebar && backdrop) {
                    sidebar.classList.remove("translate-x-0");
                    sidebar.classList.add("-translate-x-full");
                    backdrop.classList.remove("opacity-100");
                    backdrop.classList.add("opacity-0", "pointer-events-none");
                }
            }

            if (toggleBtn) toggleBtn.addEventListener("click", openSidebarMobile);
            if (closeBtn) closeBtn.addEventListener("click", closeSidebarMobile);
            if (backdrop) backdrop.addEventListener("click", closeSidebarMobile);
        });
    </script>

    <!-- MODAL POPUP DETAIL AKUN USER -->
    <div id="profileDetailModal" class="fixed inset-0 z-50 hidden flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm transition-opacity duration-300">
        <div id="profileDetailModalCard" class="relative bg-white rounded-3xl shadow-2xl max-w-lg w-full overflow-hidden border border-slate-100 transform transition-all duration-300 scale-95 opacity-0">

            <!-- HEADER MODAL -->
            <div class="p-5 border-b border-slate-100 flex items-center justify-between bg-gradient-to-r from-sky-600 to-indigo-700 text-white">
                <div class="flex items-center space-x-2">
                    <i class="fa-solid fa-id-card text-lg"></i>
                    <h3 class="text-xs font-bold tracking-wide uppercase">Detail Informasi Akun Karyawan</h3>
                </div>
                <button onclick="closeProfileDetailModal()" class="w-8 h-8 rounded-full bg-white/20 hover:bg-white/30 text-white flex items-center justify-center transition-colors">
                    <i class="fa-solid fa-xmark text-sm"></i>
                </button>
            </div>

            <!-- BODY MODAL -->
            <div class="p-6 space-y-5 max-h-[80vh] overflow-y-auto">
                <!-- RINGKASAN AVATAR & NAMA -->
                <div class="flex flex-col sm:flex-row items-center gap-4 bg-slate-50 p-4 rounded-2xl border border-slate-100">
                    <div class="w-20 h-20 rounded-2xl bg-sky-600 text-white flex items-center justify-center font-extrabold text-2xl shadow-md overflow-hidden shrink-0 border-2 border-white">
                        @if(Auth::user()->profile_photo)
                            <img src="{{ asset('storage/' . Auth::user()->profile_photo) }}" alt="User Photo" class="w-full h-full object-cover">
                        @else
                            {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                        @endif
                    </div>
                    <div class="text-center sm:text-left space-y-1">
                        <h4 class="font-extrabold text-slate-800 text-base sm:text-lg leading-tight">{{ Auth::user()->name }}</h4>
                        <p class="text-xs text-slate-500 font-semibold">{{ Auth::user()->email }}</p>
                        <div class="flex flex-wrap gap-1.5 justify-center sm:justify-start mt-1">
                            <span class="px-2.5 py-0.5 bg-sky-100 text-sky-800 rounded-md text-[10px] font-bold uppercase tracking-wider">
                                {{ Auth::user()->role->role_name ?? 'STAFF' }}
                            </span>
                            @if(Auth::user()->nip)
                                <span class="px-2.5 py-0.5 bg-slate-200 text-slate-700 rounded-md text-[10px] font-mono font-bold">
                                    NIP: {{ Auth::user()->nip }}
                                </span>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- GRID DETAIL INFORMASI LENGKAP -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs">
                    <div class="p-3 bg-white border border-slate-100 rounded-xl space-y-0.5 shadow-sm">
                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">NIP</span>
                        <p class="font-bold text-slate-800 font-mono text-xs sm:text-sm">{{ Auth::user()->nip ?? '-' }}</p>
                    </div>

                    <div class="p-3 bg-white border border-slate-100 rounded-xl space-y-0.5 shadow-sm">
                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Nomor Telepon</span>
                        <p class="font-bold text-slate-800">{{ Auth::user()->phone_number ?? '-' }}</p>
                    </div>

                    <div class="p-3 bg-white border border-slate-100 rounded-xl space-y-0.5 shadow-sm">
                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Jenis Kelamin</span>
                        <p class="font-bold text-slate-800">
                            {{ Auth::user()->gender->name_gender ?? (Auth::user()->gender_id == 2 ? 'Wanita' : 'Laki-Laki') }}
                        </p>
                    </div>

                    <div class="p-3 bg-white border border-slate-100 rounded-xl space-y-0.5 shadow-sm">
                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Divisi</span>
                        <p class="font-bold text-slate-800">{{ Auth::user()->role->divisi ?? 'Operasional' }}</p>
                    </div>

                    <div class="p-3 bg-white border border-slate-100 rounded-xl space-y-0.5 shadow-sm">
                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Stasiun Penempatan</span>
                        <p class="font-bold text-sky-700">{{ Auth::user()->station->name ?? 'Stasiun Umbulan' }}</p>
                    </div>

                    <div class="p-3 bg-white border border-slate-100 rounded-xl space-y-0.5 shadow-sm">
                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Sektor / Area</span>
                        <p class="font-bold text-slate-800">{{ Auth::user()->sektor ?? '-' }}</p>
                    </div>

                    <div class="p-3 bg-white border border-slate-100 rounded-xl space-y-0.5 shadow-sm">
                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Jobdesk / Title</span>
                        <p class="font-bold text-slate-800">
                            @php
                                $userAuth = Auth::user();
                                $jobText = $userAuth->jobdesk
                                    ?? $userAuth->job_title
                                    ?? optional($userAuth->jobTitle)->job_title;
                            @endphp
                            {{ !empty($jobText) ? $jobText : '-' }}
                        </p>
                    </div>

                    <div class="p-3 bg-white border border-slate-100 rounded-xl space-y-0.5 shadow-sm">
                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Tipe Jadwal Kerja</span>
                        <p class="font-bold text-indigo-700">
                            @if(Auth::user()->schedule_type === 'roster')
                                Sistem Roster (12 Jam)
                            @elseif(Auth::user()->schedule_type === 'normal')
                                Jam Kerja Normal
                            @else
                                Belum Diatur
                            @endif
                        </p>
                    </div>
                </div>
            </div>

            <!-- FOOTER MODAL -->
            <div class="p-4 border-t border-slate-100 bg-slate-50 flex items-center justify-between">
                <a href="{{ route('account.index') }}" class="px-4 py-2 bg-sky-600 hover:bg-sky-700 text-white text-xs font-bold rounded-xl transition-all flex items-center space-x-1.5 shadow-sm">
                    <i class="fa-solid fa-gear"></i>
                    <span>Pengaturan Akun</span>
                </a>
                <button onclick="closeProfileDetailModal()" class="px-4 py-2 bg-slate-200 hover:bg-slate-300 text-slate-700 text-xs font-bold rounded-xl transition-all cursor-pointer">
                    Tutup
                </button>
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

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeProfileDetailModal();
            }
        });
    </script>

    @stack('scripts')
</body>
</html>
