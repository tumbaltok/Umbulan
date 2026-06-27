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
    </style>
</head>
<body class="bg-slate-50 min-h-screen text-slate-800 flex overflow-hidden">

    <aside id="sidebarApp" class="w-64 bg-slate-900 text-slate-300 flex flex-col h-screen justify-between border-r border-slate-800 shrink-0 transition-all duration-300 z-30 fixed md:relative -translate-x-full md:translate-x-0">

        <div>
            <div class="p-5 border-b border-slate-800 flex items-center justify-between bg-slate-950/40">

                <div class="z-10 flex items-center space-x-3">
                    <div class="bg-white/20 p-1 rounded-full backdrop-blur-md border border-white/20 w-12 h-12 flex items-center justify-center overflow-hidden shrink-0">
                        <img src="{{ asset('images/iconfav.png') }}" alt="Logo" class="w-full h-full object-cover rounded-full">
                    </div>

                    <div>
                        <h2 class="font-bold tracking-wide text-sm text-cyan-100">META ADHYA TIRTA UMBULAN</h2>
                        {{-- <p class="text-[10px] text-white/70 uppercase tracking-widest font-semibold">Penyaluran Air Bersih</p> --}}
                    </div>
                </div>

                {{-- <div class="flex items-center space-x-3">
                    <div class="bg-sky-500/20 p-2 rounded-xl border border-sky-500/10">
                        <svg class="w-5 h-5 text-cyan-400 fill-current" viewBox="0 0 24 24">
                            <path d="M12,2.69C12,2.69 19,10 19,14C19,17.86 15.86,21 12,21C8.14,21 5,17.86 5,14C5,10 12,2.69 12,2.69M12,5.18C9.53,8.71 7,12.16 7,14A5,5 0 0,0 12,19A5,5 0 0,0 17,14C17,12.16 14.47,8.71 12,5.18Z"/>
                        </svg>
                    </div>
                    <div>
                        <h2 class="font-bold tracking-wide text-xs text-white">META AMTA UMBULAN</h2>
                        <p class="text-[9px] text-cyan-400/70 uppercase tracking-widest font-bold">Portal Kepegawaian</p>
                    </div>
                </div> --}}

                <button id="closeSidebarBtn" class="md:hidden text-slate-400 hover:text-white p-1.5 rounded-lg hover:bg-slate-800">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>

            <nav class="p-4 space-y-1.5">
                <span class="text-[10px] font-bold text-slate-500 uppercase tracking-widest block px-3 mb-2">Utama</span>

                <a href="/dashboard" class="flex items-center space-x-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all {{ request()->is('dashboard') ? 'bg-sky-600 text-white shadow-lg shadow-sky-900/20' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}">
                    <i class="fa-solid fa-chart-pie text-base w-5"></i>
                    <span>Dashboard</span>
                </a>

                <span class="text-[10px] font-bold text-slate-500 uppercase tracking-widest block px-3 pt-4 mb-2">Fasilitas Cuti</span>

                <a href="/cuti/ajukan" class="flex items-center space-x-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all {{ request()->is('cuti/ajukan') ? 'bg-sky-600 text-white shadow-lg shadow-sky-900/20' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}">
                    <i class="fa-solid fa-paper-plane text-base w-5"></i>
                    <span>Ajukan Cuti Baru</span>
                </a>

                <a href="/cuti/riwayat" class="flex items-center space-x-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all {{ request()->is('cuti/riwayat*') ? 'bg-sky-600 text-white shadow-lg shadow-sky-900/20' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}">
                    <i class="fa-solid fa-clock-rotate-left text-base w-5"></i>
                    <span>Riwayat Pengajuan</span>
                </a>

                @if(Auth::user()->role_id != 4)
                    <a href="/admin/persetujuan" class="flex items-center space-x-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all {{ request()->is('admin/persetujuan*') ? 'bg-sky-600 text-white shadow-lg shadow-sky-900/20' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}">
                        <i class="fa-solid fa-clipboard-check text-base w-5"></i>
                        <span>Persetujuan Cuti</span>
                    </a>
                @endif

                @if(Auth::user()->role_id != 4)
                <span class="text-[10px] font-bold text-slate-500 uppercase tracking-widest block px-3 pt-4 mb-2">Administrasi</span>

                <a href="{{ route('karyawan.index') }}" class="flex items-center space-x-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all {{ request()->routeIs('karyawan.*') ? 'bg-sky-600 text-white shadow-lg shadow-sky-900/20' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}">
                    <i class="fa-solid fa-users text-base w-5"></i>
                    <span>Daftar Karyawan</span>
                </a>

                <a href="{{ route('stations.index') }}" class="flex items-center space-x-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all {{ request()->routeIs('stations.*') ? 'bg-sky-600 text-white shadow-lg shadow-sky-900/20' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}">
                    <i class="fa fa-university text-base w-5"></i>
                    <span>Stasiun Kerja</span>
                </a>

                <a href="{{ route('record.index') }}" class="flex items-center space-x-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all {{ request()->routeIs('record.*') ? 'bg-sky-600 text-white shadow-lg shadow-sky-900/20' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}">
                    <i class="fa fa-table text-base w-5"></i>
                    <span>Record</span>
                </a>
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
                    {{-- <p class="text-sm font-semibold text-slate-400 tracking-wider">Station :</p> --}}
                    <h1 class="text-lg font-bold text-slate-800">{{ Auth::user()->station->name }}</h1>
                </div>
            </div>

            <div class="flex items-center space-x-4">
                <div class="text-right hidden sm:block">
                    {{-- Perbaikan: Menggunakan tanda ?-> untuk mengamankan data kosong --}}
                    <p class="text-lg font-bold text-slate-700">{{ Auth::user()->name}}</p>
                    <p class="text-sm font-bold text-slate-700">{{Auth::user()->job_title}}</p>
                    {{-- <p class="text-[11px] text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded-md font-semibold inline-block mt-0.5">Aktif</p> --}}
                </div>
                <div class="w-10 h-10 rounded-xl bg-sky-600 text-white flex items-center justify-center font-bold shadow-md shadow-sky-100 overflow-hidden">
                    @if(Auth::user()->profile_photo)
                        <img src="{{ asset('storage/' . Auth::user()->profile_photo) }}" alt="User" class="w-full h-full object-cover">
                    @else
                        {{-- Perbaikan: Inisial dinamis mengikuti nama user --}}
                        {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                    @endif
                </div>
            </div>
        </header>

        <main class="p-6 max-w-7xl w-full mx-auto">
            @yield('content')
        </main>

    </div>

    {{-- Penempatan Stack Scripts Agar Halaman Child (profile) Bisa Mendorong JS-nya Kesini --}}
    @stack('scripts')

    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const sidebar = document.getElementById("sidebarApp");
            const backdrop = document.getElementById("sidebarBackdrop");
            const toggleBtn = document.getElementById("toggleSidebarBtn");
            const closeBtn = document.getElementById("closeSidebarBtn");

            // Fungsi Membuka Sidebar (Mobile)
            function openSidebar() {
                if(sidebar && backdrop) {
                    sidebar.classList.remove("-translate-x-full");
                    sidebar.classList.add("translate-x-0");
                    backdrop.classList.remove("hidden");
                }
            }

            // Fungsi Menutup Sidebar (Mobile)
            function closeSidebar() {
                if(sidebar && backdrop) {
                    sidebar.classList.remove("translate-x-0");
                    sidebar.classList.add("-translate-x-full");
                    backdrop.classList.add("hidden");
                }
            }

            // Event Listeners dengan pengecekan ketersediaan element
            if (toggleBtn) toggleBtn.addEventListener("click", openSidebar);
            if (closeBtn) closeBtn.addEventListener("click", closeSidebar);
            if (backdrop) backdrop.addEventListener("click", closeSidebar);

            // Optimasi Reset State saat Layar Diperbesar ke Desktop
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
</body>
</html>
