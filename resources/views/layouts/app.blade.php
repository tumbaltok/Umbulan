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
        .dropdown-content {
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.3s ease-out;
        }
        .dropdown-open .dropdown-content {
            max-height: 500px;
            transition: max-height 0.5s ease-in;
        }
        .dropdown-open .chevron-icon {
            transform: rotate(180deg);
        }
    </style>
    <!-- PWA Head -->
    @pwaHead
</head>
<body class="bg-slate-50 min-h-screen text-slate-800 flex overflow-hidden">

    @php
        // Ambil nama role user dalam format lowercase
        $userRole = strtolower(Auth::user()->role->role_name ?? '');

        // MODIFIKASI: Karyawan DAN Staff dilarang melihat menu manajemen/administrasi
        $hasAccess = ($userRole !== 'karyawan' && $userRole !== 'staff');
    @endphp

    <aside id="sidebarApp" class="w-64 bg-slate-900 text-slate-300 flex flex-col h-screen justify-between border-r border-slate-800 shrink-0 transition-all duration-300 z-30 fixed md:relative -translate-x-full md:translate-x-0">
        <div>
            <div class="p-5 border-b border-slate-800 flex items-center justify-between bg-slate-950/40">
                <div class="z-10 flex items-center space-x-3">
                    <div class="bg-white/20 p-1 rounded-full backdrop-blur-md border border-white/20 w-12 h-12 flex items-center justify-center overflow-hidden shrink-0">
                        <img src="{{ asset('images/iconfav.png') }}" alt="Logo" class="w-full h-full object-cover rounded-full">
                    </div>
                    <div>
                        <h2 class="font-bold tracking-wide text-sm text-cyan-100">META ADHYA TIRTA UMBULAN</h2>
                    </div>
                </div>
                <button id="closeSidebarBtn" class="md:hidden text-slate-400 hover:text-white p-1.5 rounded-lg hover:bg-slate-800">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>

            <nav class="p-4 space-y-1.5 overflow-y-auto max-h-[calc(100vh-160px)]">
                <span class="text-[10px] font-bold text-slate-500 uppercase tracking-widest block px-3 mb-2">Menu Utama</span>

                <a href="/dashboard" class="flex items-center space-x-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all {{ request()->is('dashboard') ? 'bg-sky-600 text-white shadow-lg shadow-sky-900/20' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}">
                    <i class="fa-solid fa-chart-pie text-base w-5"></i>
                    <span>Dashboard</span>
                </a>

                @php $isCutiActive = request()->is('cuti/*') || request()->is('admin/persetujuan/cuti*'); @endphp
                <div class="dropdown-container {{ $isCutiActive ? 'dropdown-open' : '' }}">
                    <button class="dropdown-btn w-full flex items-center justify-between px-3 py-2.5 rounded-xl text-sm font-medium transition-all text-slate-400 hover:bg-slate-800 hover:text-white relative">
                        <div class="flex items-center space-x-3">
                            <i class="fa-solid fa-calendar-check text-base w-5"></i>
                            <span>Fasilitas Cuti</span>
                        </div>

                        <div class="flex items-center space-x-2">
                            @if(isset($jumlahSaranCuti) && $jumlahSaranCuti > 0)
                                <span class="h-2 w-2 rounded-full bg-rose-500 ring-2 ring-slate-900 block shrink-0"></span>
                            @endif
                            <i class="fa-solid fa-chevron-down text-xs transition-transform duration-200 chevron-icon"></i>
                        </div>
                    </button>

                    <div class="dropdown-content space-y-1 pl-4 pr-1 mt-1">
                        <a href="/cuti/ajukan" class="block px-3 py-2.5 rounded-xl text-sm transition-all {{ request()->is('cuti/ajukan') ? 'bg-sky-600 text-white shadow-lg shadow-sky-900/20' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}">Ajukan Cuti</a>
                        <a href="/cuti/riwayat" class="block px-3 py-2.5 rounded-xl text-sm transition-all {{ request()->is('cuti/riwayat*') ? 'bg-sky-600 text-white shadow-lg shadow-sky-900/20' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}">Riwayat Cuti</a>

                        {{-- Menggunakan variabel akses baru --}}
                        @if($hasAccess)
                            <a href="{{ route('admin.persetujuan.cuti') }}" class="flex items-center justify-between px-3 py-2.5 rounded-xl text-sm transition-all {{ request()->is('admin/persetujuan/cuti*') ? 'bg-sky-600 text-white shadow-lg shadow-sky-900/20' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}">
                                <span>Persetujuan Cuti</span>
                                @if(isset($jumlahSaranCuti) && $jumlahSaranCuti > 0)
                                    <span class="flex h-5 min-w-5 items-center justify-center rounded-full bg-rose-500 px-1 text-[10px] font-bold text-white animate-pulse shadow-sm">
                                        {{ $jumlahSaranCuti }}
                                    </span>
                                @endif
                            </a>
                        @endif
                    </div>
                </div>

                @php $isCarActive = request()->is('car/*') || request()->is('admin/persetujuan/car*'); @endphp
                <div class="dropdown-container {{ $isCarActive ? 'dropdown-open' : '' }}">
                    <button class="dropdown-btn w-full flex items-center justify-between px-3 py-2.5 rounded-xl text-sm font-medium transition-all text-slate-400 hover:bg-slate-800 hover:text-white relative">
                        <div class="flex items-center space-x-3">
                            <i class="fa-solid fa-file-invoice-dollar text-base w-5"></i>
                            <span>Fasilitas CAR</span>
                        </div>

                        <div class="flex items-center space-x-2">
                            @if(isset($jumlahSaranCar) && $jumlahSaranCar > 0)
                                <span class="h-2 w-2 rounded-full bg-rose-500 ring-2 ring-slate-900 block shrink-0"></span>
                            @endif
                            <i class="fa-solid fa-chevron-down text-xs transition-transform duration-200 chevron-icon"></i>
                        </div>
                    </button>

                    <div class="dropdown-content space-y-1 pl-4 pr-1 mt-1">
                        <a href="/car/create" class="block px-3 py-2.5 rounded-xl text-sm transition-all {{ request()->is('car/create') ? 'bg-sky-600 text-white shadow-lg shadow-sky-900/20' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}">Ajukan CAR</a>
                        <a href="/car/riwayat" class="block px-3 py-2.5 rounded-xl text-sm transition-all {{ request()->is('car/riwayat*') ? 'bg-sky-600 text-white shadow-lg shadow-sky-900/20' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}">Riwayat CAR</a>

                        {{-- Menggunakan variabel akses baru --}}
                        @if($hasAccess)
                            <a href="{{ route('admin.persetujuan.car') }}" class="flex items-center justify-between px-3 py-2.5 rounded-xl text-sm transition-all {{ request()->is('admin/persetujuan/car*') ? 'bg-sky-600 text-white shadow-lg shadow-sky-900/20' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}">
                                <span>Persetujuan CAR</span>
                                @if(isset($jumlahSaranCar) && $jumlahSaranCar > 0)
                                    <span class="flex h-5 min-w-5 items-center justify-center rounded-full bg-rose-500 px-1 text-[10px] font-bold text-white animate-pulse shadow-sm">
                                        {{ $jumlahSaranCar }}
                                    </span>
                                @endif
                            </a>
                        @endif
                    </div>
                </div>

                {{-- Menggunakan variabel akses baru --}}
                @if($hasAccess)
                    @php
                        $isAdminActive = request()->is('admin/*') || request()->routeIs('admin.*');
                    @endphp
                    <div class="dropdown-container {{ $isAdminActive ? 'dropdown-open' : '' }}">
                        <button class="dropdown-btn w-full flex items-center justify-between px-3 py-2.5 rounded-xl text-sm font-medium transition-all text-slate-400 hover:bg-slate-800 hover:text-white">
                            <div class="flex items-center space-x-3">
                                <i class="fa-solid fa-folder-open text-base w-5"></i>
                                <span>Administrasi</span>
                            </div>
                            <i class="fa-solid fa-chevron-down text-xs transition-transform duration-200 chevron-icon"></i>
                        </button>
                        <div class="dropdown-content space-y-1 pl-4 pr-1 mt-1">
                            <a href="{{ route('admin.karyawan.index') }}" class="block px-3 py-2.5 rounded-xl text-sm transition-all {{ request()->routeIs('admin.karyawan.*') ? 'bg-sky-600 text-white shadow-lg shadow-sky-900/20' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}">Daftar Karyawan</a>
                            <a href="{{ route('admin.stations.index') }}" class="block px-3 py-2.5 rounded-xl text-sm transition-all {{ request()->routeIs('admin.stations.*') ? 'bg-sky-600 text-white shadow-lg shadow-sky-900/20' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}">Daftar Stasiun Kerja</a>
                            <a href="{{ route('admin.record.cuti') }}" class="block px-3 py-2.5 rounded-xl text-sm transition-all {{ request()->is('admin/record/cuti*') ? 'bg-sky-600 text-white shadow-lg shadow-sky-900/20' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}">Record Cuti Karyawan</a>
                            <a href="{{ route('admin.record.car') }}" class="block px-3 py-2.5 rounded-xl text-sm transition-all {{ request()->is('admin/record/car*') ? 'bg-sky-600 text-white shadow-lg shadow-sky-900/20' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}">Record Car Karyawan</a>
                        </div>
                    </div>
                @endif

                <span class="text-[10px] font-bold text-slate-500 uppercase tracking-widest block px-3 pt-4 mb-2">Pengaturan</span>
                <a href="{{ route('account.index') }}" class="flex items-center space-x-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all {{ request()->routeIs('account.*') ? 'bg-sky-600 text-white shadow-lg shadow-sky-900/20' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}">
                    <i class="fa fa-cog text-base w-5"></i>
                    <span>Pengaturan Akun</span>
                </a>
            </nav>
        </div>

        <div class="p-4 border-t border-slate-800/60 bg-slate-950/20">
            <form action="/logout" method="POST">
                @csrf
                <button type="submit" class="w-full flex items-center space-x-3 hover:bg-rose-500/10 text-rose-400 px-3 py-2.5 rounded-xl text-sm font-medium transition-colors group">
                    <i class="fa-solid fa-arrow-right-from-bracket text-base w-5 transition-transform group-hover:translate-x-0.5"></i>
                    <span>Keluar Aplikasi</span>
                </button>
            </form>
        </div>
    </aside>

    <div id="sidebarBackdrop" class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm z-20 hidden md:hidden"></div>

    <div class="flex-1 flex flex-col h-screen overflow-y-auto">
        <header class="bg-white border-b border-slate-100 px-6 py-4 flex justify-between items-center sticky top-0 z-20">
            <div class="flex items-center space-x-3">
                <button id="toggleSidebarBtn" class="md:hidden text-slate-600 hover:text-slate-900 p-2 rounded-xl bg-slate-50 border border-slate-100">
                    <i class="fa-solid fa-bars-staggered text-lg"></i>
                </button>
                <div>
                    <p class="text-sm font-semibold text-slate-400 tracking-wider">Sektor Kerja,</p>
                    <h1 class="text-lg font-bold text-slate-800">{{ Auth::user()->station->name }}</h1>
                </div>
            </div>

            <div class="flex items-center space-x-4">
                <div class="text-right hidden sm:block">
                    <p class="text-lg font-bold text-slate-700">{{ Auth::user()->name}}</p>
                    <p class="text-sm font-bold text-slate-700">{{ strtoupper(auth()->user()->role->role_name) }} {{Auth::user()->job_title}}</p>
                </div>
                <div class="w-10 h-10 rounded-xl bg-sky-600 text-white flex items-center justify-center font-bold shadow-md shadow-sky-100 overflow-hidden">
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

    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const sidebar = document.getElementById("sidebarApp");
            const backdrop = document.getElementById("sidebarBackdrop");
            const toggleBtn = document.getElementById("toggleSidebarBtn");
            const closeBtn = document.getElementById("closeSidebarBtn");

            const dropdownContainers = document.querySelectorAll('.dropdown-container');

            dropdownContainers.forEach(container => {
                const btn = container.querySelector('.dropdown-btn');

                if (container.classList.contains('dropdown-open')) {
                    container.querySelector('.dropdown-content').style.maxHeight = '500px';
                }

                btn.addEventListener('click', function(e) {
                    e.preventDefault();

                    dropdownContainers.forEach(otherContainer => {
                        if (otherContainer !== container && otherContainer.classList.contains('dropdown-open')) {
                            otherContainer.classList.remove('dropdown-open');
                            otherContainer.querySelector('.dropdown-content').style.maxHeight = '0';
                        }
                    });

                    const isOpen = container.classList.toggle('dropdown-open');
                    const content = container.querySelector('.dropdown-content');
                    content.style.maxHeight = isOpen ? '500px' : '0';
                });
            });

            function openSidebar() {
                if(sidebar && backdrop) {
                    sidebar.classList.remove("-translate-x-full");
                    sidebar.classList.add("translate-x-0");
                    backdrop.classList.remove("hidden");
                }
            }

            function closeSidebar() {
                if(sidebar && backdrop) {
                    sidebar.classList.remove("translate-x-0");
                    sidebar.classList.add("-translate-x-full");
                    backdrop.classList.add("hidden");
                }
            }

            if (toggleBtn) toggleBtn.addEventListener("click", openSidebar);
            if (closeBtn) closeBtn.addEventListener("click", closeSidebar);
            if (backdrop) backdrop.addEventListener("click", closeSidebar);

            window.addEventListener("resize", function () {
                if (sidebar && backdrop) {
                    if (window.innerWidth >= 768) {
                        sidebar.classList.remove("-translate-x-full");
                        backdrop.classList.add("hidden");
                    } else if (!sidebar.classList.contains("translate-x-0")) {
                        sidebar.classList.add("-translate-x-full");
                    }
                }
            });
        });
    </script>

    <!-- PWA Script Registrations & Tools -->
    @laravelPwa
    @pwaInstallButton

    <!-- Custom PWA Update Notifier -->
    {{-- <script>
        if ('serviceWorker' in navigator) {
            navigator.serviceWorker.ready.then(registration => {
                registration.addEventListener('updatefound', () => {
                    const newWorker = registration.installing;
                    if (newWorker) {
                        newWorker.addEventListener('statechange', () => {
                            if (newWorker.state === 'installed' && navigator.serviceWorker.controller) {
                                if (confirm("[META System] Versi baru telah tersedia. Perbarui halaman sekarang?")) {
                                    window.location.reload();
                                }
                            }
                        });
                    }
                });
            });
        }
    </script> --}}
</body>
</html>
