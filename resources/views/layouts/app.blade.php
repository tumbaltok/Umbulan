<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title') | PT META Adhya Tirta Umbulan</title>
    <link rel="icon" type="image/png" href="{{ asset('images/iconfav.png') }}">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }

        /* ========================================================
           SEMBUNYIKAN SCROLLBAR DI SELURUH ELEMENT & ELEMEN SPESIFIK
           ======================================================== */
        html, body, div, nav, aside, main, section {
            -ms-overflow-style: none;  /* IE and Edge */
            scrollbar-width: none;  /* Firefox */
        }

        html::-webkit-scrollbar,
        body::-webkit-scrollbar,
        div::-webkit-scrollbar,
        nav::-webkit-scrollbar,
        aside::-webkit-scrollbar,
        main::-webkit-scrollbar,
        section::-webkit-scrollbar {
            display: none; /* Chrome, Safari, Opera */
        }

        /* Dropdown Animation Base */
        .dropdown-content {
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.45s cubic-bezier(0.16, 1, 0.3, 1);
        }
        
        .chevron-icon {
            transition: transform 0.3s ease;
        }

        .dropdown-open .chevron-icon {
            transform: rotate(180deg);
        }

        /* ========================================================
           EFEK HOVER SIDEBAR SMOOTH (KHUSUS DESKTOP >= 768px)
           ======================================================== */
        @media (min-width: 768px) {
            .sidebar-hover-mode {
                width: 5rem; /* ~80px saat kuncup */
                transition: width 0.35s cubic-bezier(0.4, 0, 0.2, 1);
                overflow: hidden !important;
                will-change: width;
            }
            
            /* Sidebar Mekar saat Hover */
            .sidebar-hover-mode:hover {
                width: 16rem; /* 256px saat mekar */
                box-shadow: 10px 0 30px -5px rgba(0, 0, 0, 0.3);
            }

            /* Hilangkan Scrollbar saat proses transisi mekar/kuncup */
            .sidebar-hover-mode .sidebar-nav-container {
                overflow-y: hidden;
            }
            .sidebar-hover-mode:hover .sidebar-nav-container {
                overflow-y: auto;
            }

            /* Smooth Fade-In Teks Menu Utama & Sub-menu */
            .hide-on-collapse {
                opacity: 0;
                transform: translateX(-10px);
                white-space: nowrap;
                pointer-events: none;
                transition: opacity 0.25s ease-out, transform 0.25s ease-out;
                transition-delay: 0s;
            }

            /* Teks Muncul Perlahan Hanya Saat Sidebar Sudah Melebar */
            .sidebar-hover-mode:hover .hide-on-collapse {
                opacity: 1;
                transform: translateX(0);
                pointer-events: auto;
                transition-delay: 0.15s;
            }

            /* Paksa Tutup Dropdown saat Mouse Keluar dari Sidebar */
            .sidebar-hover-mode:not(:hover) .dropdown-content {
                max-height: 0 !important;
            }
        }
    </style>

    <!-- PWA Head -->
    @pwaHead
</head>
<body class="bg-slate-50 min-h-screen text-slate-800 flex overflow-hidden">

    @php
        $userRole = strtolower(Auth::user()->role->role_name ?? '');
        $hasAccess = ($userRole !== 'karyawan' && $userRole !== 'staff');
    @endphp

    <!-- SIDEBAR -->
    <aside id="sidebarApp" class="sidebar-hover-mode bg-slate-900 text-slate-300 flex flex-col h-screen justify-between border-r border-slate-800 shrink-0 z-30 fixed md:relative -translate-x-full md:translate-x-0">
        <div class="flex flex-col h-full overflow-hidden">
            <!-- Header Sidebar -->
            <div class="p-4 border-b border-slate-800 flex items-center justify-between bg-slate-950/40 h-20 shrink-0">
                <div class="z-10 flex items-center space-x-3 overflow-hidden">
                    <div class="bg-white/20 p-1 rounded-full backdrop-blur-md border border-white/20 w-10 h-10 flex items-center justify-center overflow-hidden shrink-0">
                        <img src="{{ asset('images/iconfav.png') }}" alt="Logo" class="w-full h-full object-cover rounded-full">
                    </div>
                    <div class="hide-on-collapse">
                        <h2 class="font-bold tracking-wide text-xs text-cyan-100 leading-tight">META ADHYA TIRTA UMBULAN</h2>
                    </div>
                </div>
                <button id="closeSidebarBtn" class="md:hidden text-slate-400 hover:text-white p-1.5 rounded-lg hover:bg-slate-800">
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
                            <span class="hide-on-collapse">Fasilitas Cuti</span>
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
                            <span class="hide-on-collapse">Ajukan Cuti</span>
                        </a>
                        <a href="/cuti/riwayat" class="block px-3 py-2 rounded-xl text-sm transition-all {{ request()->is('cuti/riwayat*') ? 'bg-sky-500/20 text-sky-300 font-semibold' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}">
                            <span class="hide-on-collapse">Riwayat Cuti</span>
                        </a>

                        @if($hasAccess)
                            <a href="{{ route('admin.persetujuan.cuti') }}" class="flex items-center justify-between px-3 py-2 rounded-xl text-sm transition-all {{ request()->is('admin/persetujuan/cuti*') ? 'bg-sky-500/20 text-sky-300 font-semibold' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}">
                                <span class="hide-on-collapse">Persetujuan Cuti</span>
                                @if(isset($jumlahSaranCuti) && $jumlahSaranCuti > 0)
                                    <span class="hide-on-collapse flex h-5 min-w-5 items-center justify-center rounded-full bg-rose-500 px-1 text-[10px] font-bold text-white animate-pulse">
                                        {{ $jumlahSaranCuti }}
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

                <!-- Administrasi -->
                @if($hasAccess)
                @php 
                    $isAdminActive = (request()->is('admin/*') || request()->routeIs('admin.*')) 
                                    && !request()->is('admin/persetujuan/*'); 
                @endphp
                    <div class="dropdown-container" data-active="{{ $isAdminActive ? 'true' : 'false' }}">
                        <button class="dropdown-btn w-full flex items-center justify-between px-2.5 py-2 rounded-xl text-sm font-medium transition-all relative {{ $isAdminActive ? 'bg-sky-600 text-white shadow-lg shadow-sky-900/20' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}" title="Administrasi">
                            <div class="flex items-center space-x-3">
                                <div class="w-9 h-9 flex items-center justify-center shrink-0">
                                    <i class="fa-solid fa-folder-open text-base text-center"></i>
                                </div>
                                <span class="hide-on-collapse">Administrasi</span>
                            </div>
                            <i class="fa-solid fa-chevron-down text-xs chevron-icon hide-on-collapse"></i>
                        </button>
                        <div class="dropdown-content space-y-1 pl-4 pr-1 mt-1">
                            <a href="{{ route('admin.karyawan.index') }}" class="block px-3 py-2 rounded-xl text-sm transition-all {{ request()->routeIs('admin.karyawan.*') ? 'bg-sky-500/20 text-sky-300 font-semibold' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}">
                                <span class="hide-on-collapse">Daftar Karyawan</span>
                            </a>
                            <a href="{{ route('admin.stations.index') }}" class="block px-3 py-2 rounded-xl text-sm transition-all {{ request()->routeIs('admin.stations.*') ? 'bg-sky-500/20 text-sky-300 font-semibold' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}">
                                <span class="hide-on-collapse">Daftar Stasiun Kerja</span>
                            </a>
                            <a href="{{ route('admin.record.cuti') }}" class="block px-3 py-2 rounded-xl text-sm transition-all {{ request()->is('admin/record/cuti*') ? 'bg-sky-500/20 text-sky-300 font-semibold' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}">
                                <span class="hide-on-collapse">Record Cuti Karyawan</span>
                            </a>
                            <a href="{{ route('admin.record.car') }}" class="block px-3 py-2 rounded-xl text-sm transition-all {{ request()->is('admin/record/car*') ? 'bg-sky-500/20 text-sky-300 font-semibold' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}">
                                <span class="hide-on-collapse">Record CAR Karyawan</span>
                            </a>
                            <a href="{{ route('admin.record.mpr') }}" class="block px-3 py-2 rounded-xl text-sm transition-all {{ request()->is('admin/record/mpr*') ? 'bg-sky-500/20 text-sky-300 font-semibold' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}">
                                <span class="hide-on-collapse">Record MPR Karyawan</span>
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
    <div id="sidebarBackdrop" class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm z-20 hidden md:hidden"></div>

    <!-- MAIN CONTENT AREA -->
    <div class="flex-1 flex flex-col h-screen overflow-y-auto">
        <!-- HEADER UTAMA DENGAN JAM DIGITAL DI TENGAH -->
        <header class="bg-white border-b border-slate-100 px-6 py-3 flex justify-between items-center sticky top-0 z-20 shadow-sm">
            {{-- Sisi Kiri: Tombol Drawer Mobile & Nama Stasiun --}}
            <div class="flex items-center space-x-3">
                <button id="toggleSidebarBtn" class="md:hidden text-slate-600 hover:text-slate-900 p-2 rounded-xl bg-slate-50 border border-slate-100">
                    <i class="fa-solid fa-bars-staggered text-lg"></i>
                </button>
                <div>
                    <p class="text-[11px] font-semibold text-slate-400 tracking-wider uppercase">Sektor Kerja,</p>
                    <h1 class="text-base font-bold text-slate-800 leading-tight">{{ Auth::user()->station->name ?? 'Stasiun Umbulan' }}</h1>
                </div>
            </div>

            {{-- Sisi Tengah: Jam Digital & Tanggal Real-Time --}}
            <div class="hidden sm:flex flex-col items-center justify-center text-center px-5 py-1.5 bg-slate-50 border border-slate-200/60 rounded-2xl shadow-inner">
                <div class="flex items-center space-x-2 text-sky-600 font-mono font-black text-base md:text-lg tracking-wider">
                    <i class="fa-solid fa-clock text-xs text-sky-500"></i>
                    <span id="headerDigitalClock">00:00:00 WIB</span>
                </div>
                <span id="headerDateDisplay" class="text-[10px] text-slate-500 font-medium tracking-tight">--</span>
            </div>

            {{-- Sisi Kanan: Profil User --}}
            <div class="flex items-center space-x-3">
                <div class="text-right hidden sm:block">
                    <p class="text-sm font-bold text-slate-800 leading-tight">{{ Auth::user()->name }}</p>
                    <p class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider">{{ auth()->user()->role->role_name ?? 'USER' }} {{ Auth::user()->job_title }}</p>
                </div>
                <div class="w-10 h-10 rounded-xl bg-sky-600 text-white flex items-center justify-center font-bold shadow-md shadow-sky-100 overflow-hidden border border-slate-100 shrink-0">
                    @if(Auth::user()->profile_photo)
                        <img src="{{ asset('storage/' . Auth::user()->profile_photo) }}" alt="User" class="w-full h-full object-cover">
                    @else
                        {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                    @endif
                </div>
            </div>
        </header>

        <main class="p-6 max-w-7xl w-full mx-auto">
            @yield('content')
        </main>
    </div>

    @stack('scripts')

    <!-- JAVASCRIPT HANDLER -->
    <script>
        // --- 1. SCRIPT REAL-TIME JAM DIGITAL HEADER ---
        function updateHeaderClock() {
            const now = new Date();
            const optionsDate = { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' };
            
            const dateString = now.toLocaleDateString('id-ID', optionsDate);
            const hours = String(now.getHours()).padStart(2, '0');
            const minutes = String(now.getMinutes()).padStart(2, '0');
            const seconds = String(now.getSeconds()).padStart(2, '0');

            const clockElement = document.getElementById('headerDigitalClock');
            const dateElement = document.getElementById('headerDateDisplay');

            if (clockElement) {
                clockElement.innerText = `${hours}:${minutes}:${seconds} WIB`;
            }
            if (dateElement) {
                dateElement.innerText = dateString;
            }
        }

        setInterval(updateHeaderClock, 1000);

        document.addEventListener("DOMContentLoaded", function () {
            updateHeaderClock();

            // --- 2. SIDEBAR & DROPDOWN HANDLER ---
            const sidebar = document.getElementById("sidebarApp");
            const backdrop = document.getElementById("sidebarBackdrop");
            const toggleBtn = document.getElementById("toggleSidebarBtn");
            const closeBtn = document.getElementById("closeSidebarBtn");
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

                    if (window.innerWidth >= 768 && sidebar.matches(':not(:hover)')) {
                        return; 
                    }

                    const isOpen = container.classList.contains('dropdown-open');

                    dropdownContainers.forEach(otherContainer => {
                        if (otherContainer !== container) {
                            closeDropdown(otherContainer);
                        }
                    });

                    if (isOpen) {
                        closeDropdown(container);
                    } else {
                        openDropdown(container);
                    }
                });
            });

            if (sidebar) {
                sidebar.addEventListener('mouseenter', function() {
                    if (window.innerWidth >= 768) {
                        setTimeout(() => {
                            dropdownContainers.forEach(container => {
                                if (container.getAttribute('data-active') === 'true') {
                                    openDropdown(container);
                                }
                            });
                        }, 180);
                    }
                });

                sidebar.addEventListener('mouseleave', function() {
                    if (window.innerWidth >= 768) {
                        dropdownContainers.forEach(container => {
                            closeDropdown(container);
                        });
                    }
                });
            }

            if (window.innerWidth < 768) {
                dropdownContainers.forEach(container => {
                    if (container.getAttribute('data-active') === 'true') {
                        openDropdown(container);
                    }
                });
            }

            function openSidebarMobile() {
                if (sidebar && backdrop) {
                    sidebar.classList.remove("-translate-x-full");
                    sidebar.classList.add("translate-x-0");
                    backdrop.classList.remove("hidden");
                }
            }

            function closeSidebarMobile() {
                if (sidebar && backdrop) {
                    sidebar.classList.remove("translate-x-0");
                    sidebar.classList.add("-translate-x-full");
                    backdrop.classList.add("hidden");
                }
            }

            if (toggleBtn) toggleBtn.addEventListener("click", openSidebarMobile);
            if (closeBtn) closeBtn.addEventListener("click", closeSidebarMobile);
            if (backdrop) backdrop.addEventListener("click", closeSidebarMobile);

            window.addEventListener("resize", function () {
                if (window.innerWidth >= 768) {
                    sidebar.classList.remove("-translate-x-full", "translate-x-0");
                    backdrop.classList.add("hidden");
                } else {
                    sidebar.classList.add("-translate-x-full");
                }
            });
        });
    </script>

    @laravelPwa
    @pwaInstallButton
</body>
</html>