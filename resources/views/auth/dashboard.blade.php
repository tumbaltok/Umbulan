<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Portal Cuti META</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- FontAwesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
        }
    </style>
</head>
<body class="bg-slate-50 min-h-screen text-slate-800 overflow-x-hidden">

    <!-- Wrapper Layout -->
    <div class="flex h-screen overflow-hidden">

        <!-- 1. SIDEBAR (Hidden on mobile, slide-in/out on action) -->
        <aside id="sidebar" class="fixed inset-y-0 left-0 z-50 w-64 bg-slate-900 text-white transform -translate-x-full lg:translate-x-0 lg:static lg:inset-0 transition-transform duration-300 ease-in-out flex flex-col justify-between">
            <div>
                <!-- Sidebar Header / Brand -->
                <div class="p-6 border-b border-slate-800 flex items-center justify-between">
                    <div class="flex items-center space-x-3">
                        <div class="bg-sky-500/10 p-2 rounded-xl border border-sky-500/20">
                            <svg class="w-6 h-6 text-sky-400 fill-current" viewBox="0 0 24 24">
                                <path d="M12,2.69C12,2.69 19,10 19,14C19,17.86 15.86,21 12,21C8.14,21 5,17.86 5,14C5,10 12,2.69 12,2.69M12,5.18C9.53,8.71 7,12.16 7,14A5,5 0 0,0 12,19A5,5 0 0,0 17,14C17,12.16 14.47,8.71 12,5.18Z"/>
                            </svg>
                        </div>
                        <div>
                            <h2 class="font-bold tracking-wide text-sm text-sky-400">META ADHYA TIRTA UMBULAN</h2>
                            <p class="text-[9px] text-slate-400 uppercase tracking-widest font-semibold">Portal Karyawan</p>
                        </div>
                    </div>
                    <!-- Close button for mobile sidebar -->
                    <button onclick="toggleSidebar()" class="lg:hidden text-slate-400 hover:text-white">
                        <i class="fa-solid fa-xmark text-xl"></i>
                    </button>
                </div>

                <!-- Navigation Menu -->
                <nav class="p-4 space-y-1.5">
                    <span class="px-3 text-[10px] font-bold text-slate-500 uppercase tracking-wider block mb-2">Utama</span>

                    <a href="#" class="flex items-center space-x-3 px-3 py-2.5 rounded-xl bg-sky-600 text-white font-medium transition-colors">
                        <i class="fa-solid fa-chart-line w-5 text-center"></i>
                        <span class="text-sm">Dashboard</span>
                    </a>

                    <a href="#" onclick="openLeaveModal()" class="flex items-center space-x-3 px-3 py-2.5 rounded-xl text-slate-400 hover:bg-slate-800/50 hover:text-white transition-colors">
                        <i class="fa-solid fa-paper-plane w-5 text-center"></i>
                        <span class="text-sm">Ajukan Cuti</span>
                    </a>

                    <a href="#" class="flex items-center space-x-3 px-3 py-2.5 rounded-xl text-slate-400 hover:bg-slate-800/50 hover:text-white transition-colors">
                        <i class="fa-solid fa-history w-5 text-center"></i>
                        <span class="text-sm">Riwayat Cuti</span>
                    </a>

                    <span class="px-3 pt-4 text-[10px] font-bold text-slate-500 uppercase tracking-wider block mb-2">Manajemen Atasan</span>

                    <a href="#atasan-section" class="flex items-center space-x-3 px-3 py-2.5 rounded-xl text-slate-400 hover:bg-slate-800/50 hover:text-white transition-colors">
                        <i class="fa-solid fa-user-check w-5 text-center"></i>
                        <span class="text-sm">Antrean Persetujuan</span>
                    </a>
                </nav>
            </div>

            <!-- Sidebar User Profile Footer -->
            <div class="p-4 border-t border-slate-800">
                <div class="flex items-center space-x-3 p-2 rounded-xl bg-slate-800/30">
                    <div class="w-10 h-10 rounded-full bg-linear-to-tr from-sky-400 to-blue-600 flex items-center justify-center text-white font-bold shadow-md">
                        <?= strtoupper(substr(auth()->user()->name, 0, 1)) ?>
                    </div>
                    <div class="flex-1 min-w-0">
                        <h4 class="text-xs font-semibold text-slate-200 truncate"><?= auth()->user()->name ?></h4>
                        <p class="text-[10px] text-slate-400 truncate"><?= auth()->user()->role->role_name ?> (<?= auth()->user()->tipe->name ?? 'Unknown' ?>)</p>
                    </div>
                    <form action="{{ route('logout') }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" class="text-slate-400 hover:text-rose-400 p-1.5 rounded-lg transition-colors" title="Keluar">
                            <i class="fa-solid fa-right-from-bracket"></i>
                        </button>
                    </form>
                </div>
            </div>
        </aside>

        <!-- Overlay for mobile sidebar -->
        <div id="sidebar-overlay" onclick="toggleSidebar()" class="fixed inset-0 z-40 bg-slate-900/40 backdrop-blur-sm lg:hidden" style="display: none;"></div>

        <!-- 2. MAIN CONTENT CONTAINER -->
        <div class="flex-1 flex flex-col h-screen overflow-y-auto">

            <!-- Top Header Navbar -->
            <header class="bg-white border-b border-slate-100 px-6 py-4 flex items-center justify-between sticky top-0 z-30">
                <div class="flex items-center space-x-4">
                    <!-- Hamburger button for mobile -->
                    <button onclick="toggleSidebar()" class="lg:hidden text-slate-600 hover:text-slate-900">
                        <i class="fa-solid fa-bars text-xl"></i>
                    </button>
                    <div>
                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest block">Unit Pengolahan <?= auth()->user()->station->name ?? 'Unknown' ?></span>
                        <h1 class="text-lg font-bold text-slate-800">Sistem Portal Distribusi Cuti</h1>
                    </div>
                </div>

                <!-- Live Clock and Date -->
                <div class="hidden sm:flex items-center space-x-3 text-right">
                    <div class="bg-slate-50 border border-slate-100 rounded-xl px-3 py-1.5 flex items-center space-x-2">
                        <i class="fa-solid fa-clock text-sky-500 animate-pulse text-sm"></i>
                        <span id="live-time" class="text-xs font-semibold text-slate-700"><?= date('H:i:s') ?></span>
                    </div>
                    <div class="bg-sky-50 border border-sky-100 rounded-xl px-3 py-1.5 flex items-center space-x-2">
                        <i class="fa-solid fa-calendar text-sky-600 text-sm"></i>
                        <span class="text-xs font-semibold text-sky-800"><?= date('l, j F Y') ?></span>
                    </div>
                </div>
            </header>

            <!-- Dashboard Content Wrapper -->
            <main class="p-4 sm:p-6 lg:p-8 space-y-6 max-w-7xl w-full mx-auto">

                <!-- Live Alert / Notification Message -->
                <div id="status-alert" class="p-4 rounded-2xl border flex items-center justify-between bg-sky-50/50 border-sky-100 text-sky-800" style="display: none;">
                    <div class="flex items-center space-x-3">
                        <i class="fa-solid fa-info-circle text-sky-500 text-lg"></i>
                        <span id="alert-msg" class="text-xs sm:text-sm font-medium">Aksi berhasil dilakukan.</span>
                    </div>
                    <button onclick="closeAlert()" class="text-sky-400 hover:text-sky-600">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>

                <!-- SECTION 1: QUICK ACTION HERO CARDS -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

                    <!-- ABSENSI BOX -->
                    <div class="bg-white p-6 rounded-3xl shadow-sm border border-slate-100 flex flex-col justify-between">
                        <div class="flex items-start justify-between">
                            <div>
                                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Absensi Kerja Hari Ini</span>
                                <h3 class="text-lg font-bold text-slate-800 mt-1">Presensi Distribusi</h3>
                            </div>
                            <div class="bg-emerald-50 text-emerald-600 px-2.5 py-1 rounded-lg text-[10px] font-bold uppercase tracking-wider">
                                Aktif
                            </div>
                        </div>

                        <!-- Mini Live Digital Clock inside mobile view too -->
                        <div class="my-4 text-center py-3 bg-slate-50 rounded-2xl border border-slate-100">
                            <span id="live-time-mobile" class="text-2xl font-bold tracking-tight text-slate-800"><?= date('H:i:s') ?></span>
                            <p class="text-[10px] text-slate-400 mt-0.5">Waktu Server (WIB)</p>
                        </div>

                        <!-- Dynamic Check In/Out Buttons -->
                        <div class="grid grid-cols-2 gap-3" id="absensi-actions">
                            <button onclick="processAbsensi('Masuk')" class="bg-sky-600 hover:bg-sky-700 text-white font-semibold py-2.5 rounded-xl text-xs transition-all active:scale-[0.98] flex items-center justify-center space-x-1.5">
                                <i class="fa-solid fa-fingerprint"></i>
                                <span>Absen Masuk</span>
                            </button>
                            <button onclick="processAbsensi('Pulang')" class="bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold py-2.5 rounded-xl text-xs transition-all active:scale-[0.98] flex items-center justify-center space-x-1.5">
                                <i class="fa-solid fa-house-user"></i>
                                <span>Absen Pulang</span>
                            </button>
                        </div>
                    </div>

                <!-- LEAVE BALANCES CARD -->
                <div class="bg-white p-6 rounded-3xl shadow-sm border border-slate-100 flex flex-col justify-between relative overflow-hidden">
                {{-- <div class="bg-linear-to-br from-sky-500 via-blue-600 to-indigo-800 p-6 rounded-3xl shadow-lg hover:shadow-xl text-black flex flex-col justify-between relative overflow-hidden transition-all duration-300 group hover:-translate-y-1 border border-white/5"> --}}

                    <!-- Decorative Glassmorphism Glow Orbs & SVG Drop -->
                    <!-- PERBAIKAN: Mengganti right-[-20px] bottom-[-20px] menjadi -right-5 -bottom-5 -->
                    <div class="absolute -right-5 -bottom-5 w-44 h-44 bg-sky-400/20 rounded-full blur-2xl pointer-events-none group-hover:bg-sky-400/30 transition-all duration-500"></div>

                    <div class="absolute right-4 bottom-4 opacity-15 text-white pointer-events-none transition-transform duration-500 group-hover:scale-110 group-hover:rotate-12">
                        <!-- Premium Water Drop Vector SVG -->
                        <svg class="w-32 h-32 fill-current" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path d="M12,2.69C12,2.69 19,10 19,14C19,17.86 15.86,21 12,21C8.14,21 5,17.86 5,14C5,10 12,2.69 12,2.69M12,5.18C9.53,8.71 7,12.16 7,14A5,5 0 0,0 12,19A5,5 0 0,0 17,14C17,12.16 14.47,8.71 12,5.18Z"/>
                        </svg>
                    </div>

                    <!-- Card Header & Badge -->
                    <div class="z-10">
                        <div class="flex items-center justify-between">
                            <span class="text-[10px] font-bold text-slate-800 uppercase tracking-widest block">Sisa Saldo Cuti Utama</span>
                            <span class="bg-white/10 backdrop-blur-md text-sky-600 border border-white/15 px-2 py-0.5 rounded-full text-[9px] font-semibold">Tahun {{ date('Y') }}</span>
                        </div>

                        <!-- Large counter visual -->
                        <div class="flex items-baseline space-x-1.5 mt-2">
                            <span class="text-5xl font-extrabold tracking-tight drop-shadow-sm">12</span>
                            <span class="text-xs text-sky-500 font-semibold uppercase tracking-wider">Hari Kerja</span>
                        </div>
                        <p class="text-black/70 text-[9px] mt-1.5 flex items-center">
                            <i class="fa-solid fa-calendar-day mr-1 text-[10px]"></i>
                            Valid s/d 31 Desember 2026
                        </p>
                    </div>
                </div>

                    <!-- STATISTICS CARDS -->
                    <div class="bg-white p-6 rounded-3xl shadow-sm border border-slate-100 flex flex-col justify-between">
                        <div>
                            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Status Pengajuan Anda</span>
                            <h3 class="text-lg font-bold text-slate-800 mt-1">Kalkulasi Pengajuan</h3>
                        </div>

                        <div class="grid grid-cols-3 gap-2 my-4">
                            <div class="bg-emerald-50 border border-emerald-100/50 p-2.5 rounded-2xl text-center">
                                <span class="text-lg font-bold text-emerald-600 block">3</span>
                                <span class="text-[9px] text-slate-500 font-semibold uppercase">Disetujui</span>
                            </div>
                            <div class="bg-amber-50 border border-amber-100/50 p-2.5 rounded-2xl text-center">
                                <span class="text-lg font-bold text-amber-600 block">1</span>
                                <span class="text-[9px] text-slate-500 font-semibold uppercase">Diproses</span>
                            </div>
                            <div class="bg-rose-50 border border-rose-100/50 p-2.5 rounded-2xl text-center">
                                <span class="text-lg font-bold text-rose-600 block">0</span>
                                <span class="text-[9px] text-slate-500 font-semibold uppercase">Ditolak</span>
                            </div>
                        </div>

                        <button onclick="openLeaveModal()" class="w-full bg-sky-50 hover:bg-sky-100 text-sky-700 font-bold py-2.5 rounded-xl text-xs transition-colors flex items-center justify-center space-x-1">
                            <i class="fa-solid fa-plus-circle text-sm"></i>
                            <span>Buat Pengajuan Cuti Baru</span>
                        </button>
                    </div>

                </div>

                <!-- SECTION 2: LEAVE HISTORY TABLE -->
                <div class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden">
                    <div class="p-6 border-b border-slate-100 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                        <div>
                            <h3 class="text-base font-bold text-slate-800">Daftar Pengajuan Cuti Terbaru</h3>
                            <p class="text-xs text-slate-400 mt-0.5">Daftar riwayat izin dan cuti yang diajukan dalam 1 tahun terakhir</p>
                        </div>
                        <div class="flex items-center space-x-2 w-full sm:w-auto">
                            <span class="text-xs text-slate-400 whitespace-nowrap">Filter Status:</span>
                            <select class="bg-slate-50 border border-slate-200 text-slate-700 text-xs px-3 py-1.5 rounded-xl focus:outline-none focus:ring-1 focus:ring-sky-500 w-full sm:w-auto">
                                <option>Semua Status</option>
                                <option>Menunggu Persetujuan</option>
                                <option>Disetujui</option>
                                <option>Ditolak</option>
                            </select>
                        </div>
                    </div>

                    <!-- Responsive Table Container -->
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-slate-50/50 border-b border-slate-100 text-[10px] font-bold text-slate-500 uppercase tracking-wider">
                                    <th class="p-4 pl-6">ID Pengajuan</th>
                                    <th class="p-4">Tipe Cuti</th>
                                    <th class="p-4">Tanggal Mulai</th>
                                    <th class="p-4">Durasi</th>
                                    <th class="p-4">Status Approver</th>
                                    <th class="p-4 text-right pr-6">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 text-xs sm:text-sm text-slate-700">
                                <!-- Table Row 1 -->
                                <tr class="hover:bg-slate-50/50 transition-colors">
                                    <td class="p-4 pl-6 font-semibold text-slate-900">#CT-2026002</td>
                                    <td class="p-4">
                                        <div class="flex items-center space-x-2">
                                            <span class="w-2 h-2 rounded-full bg-sky-500"></span>
                                            <span>Cuti Tahunan</span>
                                        </div>
                                    </td>
                                    <td class="p-4">18 Juni 2026</td>
                                    <td class="p-4 font-semibold">3 Hari Kerja</td>
                                    <td class="p-4">
                                        <div class="flex flex-col space-y-1">
                                            <div class="flex items-center space-x-1.5 text-[11px] text-amber-600">
                                                <i class="fa-solid fa-circle-notch animate-spin"></i>
                                                <span>SPV: Menunggu</span>
                                            </div>
                                            <div class="flex items-center space-x-1.5 text-[11px] text-slate-400">
                                                <i class="fa-solid fa-clock"></i>
                                                <span>MGR: Antre</span>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="p-4 text-right pr-6">
                                        <button class="text-sky-600 hover:text-sky-700 font-semibold hover:underline">Detail</button>
                                    </td>
                                </tr>
                                <!-- Table Row 2 -->
                                <tr class="hover:bg-slate-50/50 transition-colors">
                                    <td class="p-4 pl-6 font-semibold text-slate-900">#CT-2026001</td>
                                    <td class="p-4">
                                        <div class="flex items-center space-x-2">
                                            <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                                            <span>Cuti Sakit</span>
                                        </div>
                                    </td>
                                    <td class="p-4">02 Juni 2026</td>
                                    <td class="p-4 font-semibold">1 Hari Kerja</td>
                                    <td class="p-4">
                                        <div class="flex flex-col space-y-1">
                                            <div class="flex items-center space-x-1.5 text-[11px] text-emerald-600 font-semibold">
                                                <i class="fa-solid fa-circle-check"></i>
                                                <span>SPV: Disetujui</span>
                                            </div>
                                            <div class="flex items-center space-x-1.5 text-[11px] text-emerald-600 font-semibold">
                                                <i class="fa-solid fa-circle-check"></i>
                                                <span>MGR: Disetujui</span>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="p-4 text-right pr-6">
                                        <button class="text-sky-600 hover:text-sky-700 font-semibold hover:underline">Detail</button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- SECTION 3: ATASAN APPROVAL LIST SECTION -->
                <div id="atasan-section" class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden">
                    <div class="p-6 border-b border-slate-100">
                        <div class="flex items-center space-x-2">
                            <div class="bg-sky-100 text-sky-600 p-2 rounded-xl">
                                <i class="fa-solid fa-user-shield"></i>
                            </div>
                            <div>
                                <h3 class="text-base font-bold text-slate-800">Antrean Persetujuan Cuti Bawahan</h3>
                                <p class="text-xs text-slate-400 mt-0.5">Berisi daftar pengajuan cuti yang memerlukan persetujuan Anda sebagai Supervisor / Manager</p>
                            </div>
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-slate-50/50 border-b border-slate-100 text-[10px] font-bold text-slate-500 uppercase tracking-wider">
                                    <th class="p-4 pl-6">Karyawan Pengaju</th>
                                    <th class="p-4">Unit / Stasiun</th>
                                    <th class="p-4">Tipe & Durasi</th>
                                    <th class="p-4">Alasan Cuti</th>
                                    <th class="p-4 text-right pr-6">Keputusan</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 text-xs sm:text-sm text-slate-700">
                                <!-- Row 1 Antrean -->
                                <tr id="approval-row-1" class="hover:bg-slate-50/50 transition-colors">
                                    <td class="p-4 pl-6">
                                        <div class="flex items-center space-x-3">
                                            <div class="w-9 h-9 rounded-full bg-teal-500 text-white font-bold flex items-center justify-center text-xs">
                                                RS
                                            </div>
                                            <div>
                                                <h4 class="font-bold text-slate-900 text-xs sm:text-sm">Randi Saputra</h4>
                                                <span class="text-[10px] text-slate-400 block font-medium">NIP: TA-2026115 (Operator Pompa)</span>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="p-4">Stasiun Kedungkandang</td>
                                    <td class="p-4">
                                        <div class="flex flex-col">
                                            <span class="font-semibold text-slate-950">Cuti Tahunan</span>
                                            <span class="text-[11px] text-slate-400">22 Juni - 24 Juni 2026 (3 Hari)</span>
                                        </div>
                                    </td>
                                    <td class="p-4 italic text-slate-500">"Acara lamaran pernikahan keluarga dekat di Blitar"</td>
                                    <td class="p-4 text-right pr-6">
                                        <div class="flex items-center justify-end space-x-2">
                                            <button onclick="approveRequest('1')" class="bg-emerald-50 hover:bg-emerald-100 text-emerald-700 border border-emerald-200 font-semibold px-2.5 py-1.5 rounded-lg text-xs transition-colors flex items-center space-x-1">
                                                <i class="fa-solid fa-check"></i>
                                                <span>Setujui</span>
                                            </button>
                                            <button onclick="rejectRequest('1')" class="bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 font-semibold px-2.5 py-1.5 rounded-lg text-xs transition-colors flex items-center space-x-1">
                                                <i class="fa-solid fa-xmark"></i>
                                                <span>Tolak</span>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

            </main>

            <!-- Main Footer -->
            <footer class="bg-white border-t border-slate-100 px-6 py-4 text-center text-xs text-slate-400 mt-auto">
                &copy; 2026 PT Meta Adhya Tirta Umbulan (Penyaluran Air Bersih). Semua hak cipta dilindungi.
            </footer>
        </div>

    </div>

    <!-- 4. MODAL: FORMULIR PENGAJUAN CUTI BARU -->
    <div id="leaveModal" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
        <!-- Modal Backdrop -->
        <div class="fixed inset-0 bg-slate-950/40 backdrop-blur-sm" onclick="closeLeaveModal()"></div>

        <!-- Modal Dialog Box -->
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="relative bg-white w-full max-w-lg rounded-3xl shadow-2xl overflow-hidden transform transition-all duration-300">
                <!-- Header -->
                <div class="wave-bg text-white p-6 relative">
                    <button onclick="closeLeaveModal()" class="absolute top-4 right-4 text-white/75 hover:text-white">
                        <i class="fa-solid fa-xmark text-xl"></i>
                    </button>
                    <div class="flex items-center space-x-3">
                        <div class="bg-white/20 p-2.5 rounded-2xl">
                            <i class="fa-solid fa-paper-plane text-cyan-200"></i>
                        </div>
                        <div>
                            <h2 class="text-lg font-bold">Form Pengajuan Cuti Baru</h2>
                            <p class="text-[10px] text-white/70 tracking-wide uppercase font-semibold">Meta Adhya Tirta Umbulan</p>
                        </div>
                    </div>
                </div>

                <!-- Form Body -->
                <form id="leaveForm" class="p-6 space-y-4" onsubmit="submitLeave(event)">
                    <div class="grid grid-cols-1 gap-4">

                        <!-- Leave Type Selection -->
                        <div>
                            <label for="tipe_cuti" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">Jenis Cuti</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                                    <i class="fa-solid fa-tags text-xs"></i>
                                </div>
                                <select id="tipe_cuti" required class="block w-full pl-9 pr-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-slate-800 text-xs sm:text-sm focus:ring-2 focus:ring-sky-500 focus:border-sky-500 focus:bg-white focus:outline-none transition-all appearance-none">
                                    <option value="" disabled selected>Pilih Jenis Cuti</option>
                                    <option value="Cuti Tahunan">Cuti Tahunan</option>
                                    <option value="Cuti Sakit">Cuti Sakit</option>
                                    <option value="Cuti Bersalin">Cuti Bersalin</option>
                                    <option value="Izin Khusus">Izin Khusus / Kemandirian</option>
                                </select>
                                <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-slate-400">
                                    <i class="fa-solid fa-chevron-down text-[10px]"></i>
                                </div>
                            </div>
                        </div>

                        <!-- Date range picker -->
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label for="mulai_tanggal" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">Tanggal Mulai</label>
                                <input type="date" id="mulai_tanggal" required
                                    class="block w-full px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-slate-800 text-xs sm:text-sm focus:ring-2 focus:ring-sky-500 focus:border-sky-500 focus:bg-white focus:outline-none transition-all">
                            </div>
                            <div>
                                <label for="akhir_tanggal" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">Tanggal Berakhir</label>
                                <input type="date" id="akhir_tanggal" required
                                    class="block w-full px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-slate-800 text-xs sm:text-sm focus:ring-2 focus:ring-sky-500 focus:border-sky-500 focus:bg-white focus:outline-none transition-all">
                            </div>
                        </div>

                        <!-- Leave Reason -->
                        <div>
                            <label for="alasan" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">Alasan Pengajuan Cuti</label>
                            <textarea id="alasan" required rows="3"
                                class="block w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-slate-800 placeholder-slate-400 text-xs sm:text-sm focus:ring-2 focus:ring-sky-500 focus:border-sky-500 focus:bg-white focus:outline-none transition-all"
                                placeholder="Jelaskan alasan pengajuan cuti secara singkat dan jelas..."></textarea>
                        </div>
                    </div>

                    <!-- Alert inside modal -->
                    <div class="bg-amber-50 border border-amber-100 text-amber-800 p-3 rounded-xl flex items-start space-x-2.5 text-[11px]">
                        <i class="fa-solid fa-circle-exclamation mt-0.5 text-amber-500 text-sm"></i>
                        <span class="leading-relaxed">Pengajuan cuti minimal didaftarkan 3 hari sebelum tanggal mulai pelaksanaan cuti utama agar tidak mengganggu jalur operasional.</span>
                    </div>

                    <!-- Buttons Group -->
                    <div class="flex items-center space-x-3 pt-2">
                        <button type="button" onclick="closeLeaveModal()" class="w-1/2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold py-2.5 rounded-xl text-xs transition-colors">
                            Batal
                        </button>
                        <button type="submit" class="w-1/2 bg-sky-600 hover:bg-sky-700 text-white font-semibold py-2.5 rounded-xl text-xs transition-colors flex items-center justify-center space-x-1">
                            <i class="fa-solid fa-paper-plane"></i>
                            <span>Kirim Pengajuan</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Script Operations -->
    <script>
        // 1. Live Clock Controller
        function updateTime() {
            const timeElement = document.getElementById('live-time');
            const timeMobileElement = document.getElementById('live-time-mobile');
            const now = new Date();

            const hours = String(now.getHours()).padStart(2, '0');
            const minutes = String(now.getMinutes()).padStart(2, '0');
            const seconds = String(now.getSeconds()).padStart(2, '0');

            const timeString = `${hours}:${minutes}:${seconds}`;
            if(timeElement) timeElement.innerText = timeString;
            if(timeMobileElement) timeMobileElement.innerText = timeString;
        }
        setInterval(updateTime, 1000);
        updateTime();

        // 2. Sidebar Navigation Toggle
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('sidebar-overlay');

            if (sidebar.classList.contains('-translate-x-full')) {
                sidebar.classList.remove('-translate-x-full');
                overlay.style.display = 'block';
            } else {
                sidebar.classList.add('-translate-x-full');
                overlay.style.display = 'none';
            }
        }

        // 3. Status Alert Management
        function showAlert(message, type = 'success') {
            const alertBox = document.getElementById('status-alert');
            const alertMsg = document.getElementById('alert-msg');

            alertMsg.innerText = message;

            if (type === 'success') {
                alertBox.className = "p-4 rounded-2xl border flex items-center justify-between bg-emerald-50 border-emerald-100 text-emerald-800";
            } else if (type === 'danger') {
                alertBox.className = "p-4 rounded-2xl border flex items-center justify-between bg-rose-50 border-rose-100 text-rose-800";
            } else {
                alertBox.className = "p-4 rounded-2xl border flex items-center justify-between bg-sky-50 border-sky-100 text-sky-800";
            }

            alertBox.style.display = 'flex';

            // Auto close after 4 seconds
            setTimeout(closeAlert, 4000);
        }

        function closeAlert() {
            document.getElementById('status-alert').style.display = 'none';
        }

        // 4. Leave Modal Controller
        function openLeaveModal() {
            document.getElementById('leaveModal').style.display = 'block';
        }

        function closeLeaveModal() {
            document.getElementById('leaveModal').style.display = 'none';
            document.getElementById('leaveForm').reset();
        }

        function submitLeave(event) {
            event.preventDefault();
            const type = document.getElementById('tipe_cuti').value;
            const start = document.getElementById('mulai_tanggal').value;

            closeLeaveModal();
            showAlert(`Pengajuan ${type} Anda berhasil didaftarkan mulai tanggal ${start}. Menunggu proses persetujuan SPV.`);
        }

        // 5. Interactive Mock Absensi
        function processAbsensi(type) {
            showAlert(`Absensi ${type} berhasil dicatat pada sistem kehadiran META. Terima kasih!`, 'success');
        }

        // 6. Interactive Mock Atasan Approval Action
        function approveRequest(id) {
            const row = document.getElementById(`approval-row-${id}`);
            if (row) {
                row.style.opacity = '0.4';
                setTimeout(() => {
                    row.remove();
                    showAlert("Pengajuan cuti bawahan berhasil Anda DISETUJUI.");
                }, 400);
            }
        }

        function rejectRequest(id) {
            const row = document.getElementById(`approval-row-${id}`);
            if (row) {
                row.style.opacity = '0.4';
                setTimeout(() => {
                    row.remove();
                    showAlert("Pengajuan cuti bawahan telah Anda TOLAK.", "danger");
                }, 400);
            }
        }
    </script>
</body>
</html>
