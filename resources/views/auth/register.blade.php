<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Pendaftaran Akun Karyawan - PT.META</title>
    <link rel="icon" type="image/png" href="{{ asset('images/iconfav.png') }}">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = { darkMode: 'class' };
        // Kunci Halaman Auth Selalu Light Mode & pastikan default adalah 'light' jika belum diset
        document.documentElement.classList.remove('dark');
        if (!localStorage.getItem('theme')) {
            localStorage.setItem('theme', 'light');
        }
    </script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght=300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
        }
        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-8px); }
        }
        .float-animation {
            animation: float 5s ease-in-out infinite;
        }
        .wave-bg {
            background: radial-gradient(circle at 15% 15%, #0369a1 0%, #075985 40%, #082f49 100%);
        }
        .water-pattern {
            background-image: radial-gradient(rgba(56, 189, 248, 0.15) 1px, transparent 1px);
            background-size: 20px 20px;
        }
    </style>
    {{-- Komponen Head PWA --}}
    @pwaHead
</head>
<body class="bg-slate-50 min-h-screen flex items-center justify-center p-3 sm:p-6 md:p-8 overflow-x-hidden">

    {{-- Kontainer Utama Registrasi --}}
    <div class="bg-white w-full max-w-6xl rounded-2xl sm:rounded-3xl shadow-2xl overflow-hidden flex flex-col lg:flex-row min-h-auto lg:min-h-[700px] border border-slate-100 transition-all duration-300">

        {{-- Sisi Kiri: Branding & Infrastruktur Mega Transmisi Air Umbulan --}}
        <div class="lg:w-5/12 wave-bg text-white p-4 sm:p-6 lg:p-10 flex flex-col justify-between relative overflow-hidden shrink-0">
            {{-- Elemen Grafis Latar Belakang (Grid & Water Wave) --}}
            <div class="absolute inset-0 water-pattern opacity-70 pointer-events-none"></div>
            <div class="absolute -top-24 -left-24 w-72 h-72 bg-cyan-400/20 rounded-full blur-3xl pointer-events-none"></div>
            <div class="absolute -bottom-24 -right-24 w-72 h-72 bg-sky-500/20 rounded-full blur-3xl pointer-events-none"></div>

            <div class="absolute bottom-0 left-0 right-0 opacity-15 pointer-events-none">
                <svg viewBox="0 0 1440 320" xmlns="http://www.w3.org/2000/svg">
                    <path fill="#ffffff" fill-opacity="1" d="M0,96L48,112C96,128,192,160,288,186.7C384,213,480,235,576,213.3C672,192,768,128,864,117.3C960,107,1056,149,1152,154.7C1248,160,1344,128,1392,112L1440,96L1440,320L1392,320C1344,320,1248,320,1152,320C1056,320,960,320,864,320C768,320,672,320,576,320C480,320,384,320,288,320C192,320,96,320,48,320L0,320Z"></path>
                </svg>
            </div>

            {{-- 1. Logo Brand Perusahaan (Tampil di Desktop & Mobile sebagai header ringkas) --}}
            <div class="z-10 flex items-center space-x-3.5">
                <div class="bg-white/15 p-1.5 rounded-2xl backdrop-blur-md border border-white/20 w-11 h-11 sm:w-12 sm:h-12 flex items-center justify-center shadow-lg shadow-slate-950/20 overflow-hidden shrink-0">
                    <img src="{{ asset('images/iconfav.png') }}" alt="Logo PT META" class="w-full h-full object-cover rounded-xl">
                </div>

                <div>
                    <h2 class="font-extrabold tracking-wide text-xs sm:text-base text-cyan-200 leading-tight">META ADHYA TIRTA UMBULAN</h2>
                    <p class="text-[9px] sm:text-[10px] text-white/80 uppercase tracking-widest font-semibold flex items-center gap-1.5">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                        Portal Layanan Karyawan
                    </p>
                </div>
            </div>

            {{-- 2. Visual Informasi Registrasi Akun & Panduan Kepegawaian (Tampil di Desktop lg:flex, Disembunyikan di Mobile) --}}
            <div class="my-auto py-6 z-10 hidden lg:flex flex-col items-start w-full">
                {{-- Kartu Status & Validasi Registrasi Akun --}}
                <div class="w-full bg-slate-950/45 backdrop-blur-md border border-white/20 rounded-2xl p-4 sm:p-5 shadow-2xl shadow-sky-950/50 mb-5 relative overflow-hidden">
                    <div class="absolute -right-8 -top-8 w-32 h-32 bg-cyan-400/10 rounded-full blur-xl pointer-events-none"></div>

                    {{-- Header Kartu Registrasi --}}
                    <div class="flex items-center justify-between pb-3 border-b border-white/10 mb-3.5">
                        <div class="flex items-center gap-2">
                            <span class="relative flex h-2.5 w-2.5">
                                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                                <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-emerald-400"></span>
                            </span>
                            <span class="text-[11px] font-bold text-cyan-200 tracking-wider uppercase font-mono">REGISTRASI AKUN • RESMI</span>
                        </div>
                        <div id="liveClock" class="text-[11px] font-mono text-cyan-300 font-semibold bg-white/10 px-2.5 py-0.5 rounded-lg border border-white/10">
                            {{ date('H:i:s') }} WIB
                        </div>
                    </div>

                    {{-- Grid 3 Pilar Validasi Pendaftaran --}}
                    <div class="grid grid-cols-3 gap-2 text-center mb-3.5">
                        <div class="p-2.5 rounded-xl bg-white/5 border border-white/10">
                            <span class="text-[9px] text-cyan-200/80 uppercase tracking-wider block font-semibold">Identitas</span>
                            <span class="text-xs sm:text-sm font-black text-cyan-200 font-mono">Tervalidasi</span>
                            <span class="text-[9px] text-white/60 block">Email & NIK</span>
                        </div>
                        <div class="p-2.5 rounded-xl bg-white/5 border border-white/10">
                            <span class="text-[9px] text-cyan-200/80 uppercase tracking-wider block font-semibold">Penugasan</span>
                            <span class="text-xs sm:text-sm font-black text-white font-mono">Resmi</span>
                            <span class="text-[9px] text-white/60 block">Role & Unit</span>
                        </div>
                        <div class="p-2.5 rounded-xl bg-white/5 border border-white/10">
                            <span class="text-[9px] text-cyan-200/80 uppercase tracking-wider block font-semibold">Keamanan</span>
                            <span class="text-xs sm:text-sm font-black text-emerald-300 font-mono">Terenkripsi</span>
                            <span class="text-[9px] text-white/60 block">Akses TLS</span>
                        </div>
                    </div>

                    {{-- Baris Status Pendaftaran --}}
                    <div class="flex items-center justify-between text-[10px] text-cyan-100/90 bg-white/5 px-3 py-1.5 rounded-lg border border-white/5 font-mono">
                        <span class="flex items-center gap-1.5">
                            <i class="fa-solid fa-user-check text-cyan-300"></i> Sistem Registrasi Kedinasan Terpadu
                        </span>
                        <span class="text-emerald-300 font-semibold flex items-center gap-1">
                            <i class="fa-solid fa-circle-check text-[9px]"></i> Portal Siap
                        </span>
                    </div>
                </div>

                {{-- Badging & Headline --}}
                <div class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-white/10 backdrop-blur-md border border-white/15 text-[10px] font-bold tracking-wider text-cyan-200 uppercase mb-2.5">
                    <i class="fa-solid fa-id-card-clip text-cyan-300 text-xs"></i>
                    <span>Aktivasi Akun Kepegawaian</span>
                </div>

                <h1 class="text-2xl sm:text-3xl font-extrabold leading-tight text-white mb-2.5 tracking-tight">
                    Pendaftaran Akun Karyawan
                </h1>
                
                <p class="text-cyan-100/85 text-xs sm:text-sm font-light leading-relaxed mb-5">
                    Lengkapi pendaftaran akun resmi kedinasan Anda untuk mengakses portal mandiri karyawan: Presensi biometrik wajah berbasis lokasi kerja, pengajuan dokumen dinas (CAR & MPR), serta administrasi kepegawaian.
                </p>

                {{-- 3 Panduan Pendaftaran Karyawan --}}
                <div class="grid grid-cols-1 gap-2 w-full text-xs text-white/90">
                    <div class="flex items-center gap-2.5 bg-white/5 backdrop-blur-sm px-3 py-2 rounded-xl border border-white/10">
                        <i class="fa-solid fa-envelope-circle-check text-cyan-300 text-xs shrink-0"></i>
                        <span class="text-[11px] font-medium text-cyan-100">Gunakan Email Perusahaan & Identitas Sesuai SK Kepegawaian</span>
                    </div>
                    <div class="flex items-center gap-2.5 bg-white/5 backdrop-blur-sm px-3 py-2 rounded-xl border border-white/10">
                        <i class="fa-solid fa-fingerprint text-cyan-300 text-xs shrink-0"></i>
                        <span class="text-[11px] font-medium text-cyan-100">Presensi Biometrik Wajah Berbasis Geofencing Lokasi Kerja</span>
                    </div>
                    <div class="flex items-center gap-2.5 bg-white/5 backdrop-blur-sm px-3 py-2 rounded-xl border border-white/10">
                        <i class="fa-solid fa-shield-halved text-cyan-300 text-xs shrink-0"></i>
                        <span class="text-[11px] font-medium text-cyan-100">Multi-Tier Authorization & Validasi Alur Dokumen Dinas</span>
                    </div>
                </div>
            </div>

            {{-- 3. Footer Hak Cipta & Keamanan (Disembunyikan di Mobile) --}}
            <div class="z-10 pt-4 border-t border-white/15 hidden lg:flex flex-col sm:flex-row items-center justify-between text-[11px] text-cyan-100/70 gap-2">
                <span>&copy; <?= date('Y') ?> PT Meta Adhya Tirta Umbulan</span>
                <span class="flex items-center gap-1.5 text-[10px] font-medium text-cyan-200/90">
                    <i class="fa-solid fa-shield-halved text-cyan-300"></i> Sistem Resmi Terenkripsi
                </span>
            </div>
        </div>

        {{-- Sisi Kanan: Formulir Registrasi Akun --}}
        <div class="w-full lg:w-7/12 p-5 sm:p-10 md:p-12 flex flex-col justify-between">

            <div>
                <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 border-b border-slate-100 pb-5">
                    <div>
                        <div class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full bg-sky-50 border border-sky-100 text-sky-700 text-[11px] font-bold tracking-wide uppercase mb-1">
                            <i class="fa-solid fa-id-card-clip text-[10px]"></i> Formulir Akun Resmi
                        </div>
                        <h2 class="text-2xl sm:text-3xl font-extrabold text-slate-800 tracking-tight">Registrasi Karyawan</h2>
                        <p class="text-slate-500 text-xs sm:text-sm mt-0.5">Lengkapi data di bawah sesuai database penugasan & SK kepegawaian Anda.</p>
                    </div>
                    <div class="hidden sm:flex items-center gap-2 px-3 py-1.5 rounded-xl bg-slate-50 border border-slate-200/80 text-[11px] text-slate-600 font-medium shrink-0">
                        <i class="fa-solid fa-circle-check text-emerald-500"></i> Verifikasi Aktif
                    </div>
                </div>

                {{-- Kotak Notifikasi Dinamis JS --}}
                <div id="notification" style="display: none;" class="mb-6 p-4 rounded-xl border flex items-center space-x-3 transition-all duration-300">
                    <div id="notif-icon"></div>
                    <div class="text-xs sm:text-sm font-medium" id="notif-message"></div>
                </div>

                {{-- Alert Pesan Validasi Server --}}
                @if ($errors->any())
                    <div class="mb-6 p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-sm">
                        <div class="font-bold mb-2 flex items-center">
                            <i class="fa-solid fa-triangle-exclamation mr-2 text-rose-500"></i>
                            Gagal Mendaftar:
                        </div>
                        <ul class="list-disc list-inside space-y-1 text-xs">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form id="registerForm" class="space-y-4" onsubmit="handleRegistration(event)" method="POST" action="{{ route('register.post') }}">
                    @csrf

                    {{-- Seksi 1: Informasi Dasar Karyawan --}}
                    <div class="border-b border-slate-100 pb-4">
                        <span class="text-xs font-bold text-sky-600 uppercase tracking-wider block mb-3">
                            <i class="fa-solid fa-user-gear mr-1"></i> Data Utama & Akun
                        </span>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            {{-- Field Input Nama Lengkap --}}
                            <div>
                                <label for="name" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">Nama Lengkap</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                                        <i class="fa-solid fa-user text-xs"></i>
                                    </div>
                                    <input type="text" id="name" name="name" value="{{ old('name') }}" required
                                        class="block w-full pl-9 pr-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-slate-800 placeholder-slate-400 text-xs sm:text-sm focus:ring-2 focus:ring-sky-500 focus:border-sky-500 focus:bg-white focus:outline-none transition-all"
                                        placeholder="Nama sesuai KTP">
                                </div>
                            </div>

                            {{-- Field Input Alamat Email --}}
                            <div>
                                <label for="email" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">Alamat Email</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                                        <i class="fa-solid fa-envelope text-xs"></i>
                                    </div>
                                    <input type="email" id="email" name="email" value="{{ old('email') }}" required
                                        class="block w-full pl-9 pr-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-slate-800 placeholder-slate-400 text-xs sm:text-sm focus:ring-2 focus:ring-sky-500 focus:border-sky-500 focus:bg-white focus:outline-none transition-all"
                                        placeholder="mail@meta.com">
                                </div>
                            </div>

                            {{-- Pilihan Jenis Kelamin --}}
                            <div class="md:col-span-2">
                                <label for="gender_id" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">Jenis Kelamin</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                                        <i class="fa-solid fa-venus-mars text-xs"></i>
                                    </div>
                                    <select id="gender_id" name="gender_id" required class="block w-full pl-9 pr-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-slate-800 text-xs sm:text-sm focus:ring-2 focus:ring-sky-500 focus:border-sky-500 focus:bg-white focus:outline-none transition-all appearance-none cursor-pointer">
                                        <option value="" disabled {{ old('gender_id') ? '' : 'selected' }}>Pilih Jenis Kelamin</option>
                                        @if(isset($daftarGender) && count($daftarGender) > 0)
                                            @foreach($daftarGender as $gender)
                                                <option value="{{ $gender->id }}" {{ old('gender_id') == $gender->id ? 'selected' : '' }}>
                                                    {{ $gender->name ?? $gender->gender_name }}
                                                </option>
                                            @endforeach
                                        @else
                                            <option value="1" {{ old('gender_id') == '1' ? 'selected' : '' }}>Laki-laki</option>
                                            <option value="2" {{ old('gender_id') == '2' ? 'selected' : '' }}>Perempuan</option>
                                        @endif
                                    </select>
                                    <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-slate-400">
                                        <i class="fa-solid fa-chevron-down text-[10px]"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Seksi 2: Penempatan & Struktur Organisasi --}}
                    <div class="border-b border-slate-100 pb-4">
                        <span class="text-xs font-bold text-sky-600 uppercase tracking-wider block mb-3">
                            <i class="fa-solid fa-network-wired mr-1"></i> Penempatan & Struktur Kerja
                        </span>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            {{-- Dropdown Stasiun Penempatan --}}
                            <div class="md:col-span-2">
                                <label for="station_id" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">
                                    Penempatan Kerja
                                </label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                                        <i class="fa-solid fa-location-dot text-xs"></i>
                                    </div>
                                    <select id="station_id" name="station_id" required class="block w-full pl-9 pr-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-slate-800 text-xs sm:text-sm focus:ring-2 focus:ring-sky-500 focus:border-sky-500 focus:bg-white focus:outline-none transition-all appearance-none cursor-pointer">
                                        <option value="" disabled {{ old('station_id') ? '' : 'selected' }}>Pilih Tempat Kerja</option>
                                        @foreach($daftarStasiun as $stasiun)
                                            <option value="{{ $stasiun->id }}" {{ old('station_id') == $stasiun->id ? 'selected' : '' }}>
                                                {{ $stasiun->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-slate-400">
                                        <i class="fa-solid fa-chevron-down text-[10px]"></i>
                                    </div>
                                </div>
                            </div>

                            {{-- Dropdown Pilihan Multi-Role/Jabatan --}}
                            <div class="md:col-span-2 relative" id="roleDropdownWrapper">
                                <div class="flex items-center justify-between mb-1.5">
                                    <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider">
                                        Peran / Jabatan
                                    </label>
                                    <span class="text-[10px] text-slate-400 font-medium">* Bisa pilih lebih dari satu peran</span>
                                </div>

                                {{-- Trigger Panel Dropdown Peran --}}
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400 z-10">
                                        <i class="fa-solid fa-briefcase text-xs"></i>
                                    </div>
                                    <div id="roleDropdownTrigger" onclick="toggleRoleDropdown()"
                                         class="min-h-[42px] w-full pl-9 pr-3 py-1.5 bg-slate-50 hover:bg-white border border-slate-200 rounded-xl text-slate-800 text-xs sm:text-sm cursor-pointer transition-all flex items-center justify-between gap-2 select-none hover:border-slate-300 focus-within:ring-2 focus-within:ring-sky-500 focus-within:border-sky-500 focus-within:bg-white shadow-2xs">
                                        <div id="selectedRolesPills" class="flex flex-wrap items-center gap-1.5 flex-1 min-w-0 py-0.5">
                                            <span id="rolePlaceholder" class="text-slate-400 text-xs sm:text-sm py-1">Pilih satu atau beberapa jabatan...</span>
                                        </div>
                                        <div class="flex items-center gap-2 text-slate-400 shrink-0 pl-1">
                                            <span id="roleCountBadge" class="hidden px-2 py-0.5 text-[10px] font-bold bg-sky-100 text-sky-700 rounded-full border border-sky-200">0</span>
                                            <i id="roleChevronIcon" class="fa-solid fa-chevron-down text-[10px] transition-transform duration-200"></i>
                                        </div>
                                    </div>
                                </div>

                                {{-- Panel Melayang Dropdown Peran --}}
                                <div id="roleDropdownPanel" class="hidden absolute left-0 right-0 top-full mt-1.5 z-50 bg-white border border-slate-200 rounded-2xl shadow-xl p-3 animate-in fade-in zoom-in-95 duration-150">
                                    {{-- Kolom Pencarian Cepat Peran --}}
                                    <div class="relative mb-2">
                                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                                            <i class="fa-solid fa-magnifying-glass text-xs"></i>
                                        </div>
                                        <input type="text" id="roleSearchInput" onkeyup="filterRoleList(this.value)" placeholder="Cari nama jabatan..."
                                               class="w-full pl-8 pr-8 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs sm:text-sm text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-sky-500 focus:bg-white transition-all">
                                        <button type="button" onclick="clearRoleSearch()" id="clearRoleSearchBtn" class="hidden absolute inset-y-0 right-0 pr-2.5 flex items-center text-slate-400 hover:text-slate-600">
                                            <i class="fa-solid fa-circle-xmark text-xs"></i>
                                        </button>
                                    </div>

                                    {{-- Status dan Tombol Reset Pilihan --}}
                                    <div class="flex items-center justify-between px-1 pb-2 border-b border-slate-100 text-[11px] text-slate-500">
                                        <span id="roleFilterSummary" class="font-medium">Daftar Jabatan:</span>
                                        <button type="button" onclick="resetSelectedRoles()" class="text-rose-500 hover:text-rose-600 font-semibold cursor-pointer text-[10px] flex items-center gap-1">
                                            <i class="fa-solid fa-rotate-left text-[9px]"></i> Reset Pilihan
                                        </button>
                                    </div>

                                    {{-- Daftar Checkbox Pilihan Peran --}}
                                    <div id="roleItemsContainer" class="max-h-52 overflow-y-auto space-y-1 pt-1.5 pr-0.5">
                                        @if(isset($daftarRole) && count($daftarRole) > 0)
                                            @foreach($daftarRole as $role)
                                                @php
                                                    $isSelected = (is_array(old('roles')) && in_array($role->id, old('roles'))) || old('role_id') == $role->id;
                                                @endphp
                                                <label class="role-item flex items-center justify-between p-2 rounded-xl hover:bg-sky-50 cursor-pointer transition-colors text-xs select-none" data-name="{{ strtolower($role->role_name) }}">
                                                    <div class="flex items-center space-x-2.5 min-w-0">
                                                        <input type="checkbox" name="roles[]" value="{{ $role->id }}" data-label="{{ $role->role_name }}"
                                                               {{ $isSelected ? 'checked' : '' }}
                                                               onchange="onRoleCheckboxChange()"
                                                               class="rounded border-slate-300 text-sky-600 focus:ring-sky-500 w-4 h-4 cursor-pointer role-checkbox">
                                                        <span class="font-medium text-slate-700 truncate">{{ $role->role_name }}</span>
                                                    </div>
                                                    <span class="text-[10px] text-slate-400 font-mono">#{{ $role->id }}</span>
                                                </label>
                                            @endforeach
                                        @else
                                            <p class="text-xs text-slate-400 p-3 text-center">Tidak ada data role tersedia</p>
                                        @endif
                                        <div id="noRoleFound" class="hidden text-center py-4 text-xs text-slate-400">
                                            <i class="fa-solid fa-magnifying-glass mb-1 block text-sm"></i>
                                            Tidak ada jabatan yang cocok
                                        </div>
                                    </div>
                                </div>
                                <span id="role-selection-error" class="text-xs text-rose-500 mt-1 hidden font-semibold">* Silakan pilih setidaknya satu peran/jabatan kerja.</span>
                            </div>

                            {{-- Penugasan Rumah Meter (Khusus Peran Area Pipeline) --}}
                            <div class="md:col-span-2 hidden transition-all duration-300" id="pipelineRumahMeterContainer">
                                <div class="p-4 bg-amber-50/80 border border-amber-200 rounded-2xl space-y-3">
                                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                                        <div>
                                            <label class="block text-xs font-bold text-amber-800 uppercase tracking-wider flex items-center gap-1.5">
                                                <i class="fa-solid fa-gauge-high text-amber-600"></i> Cakupan Rumah Meter (Role Pipeline)
                                            </label>
                                            <p class="text-[11px] text-amber-700 font-medium mt-0.5">
                                                Pilih satu atau beberapa Checkpoint Rumah Meter yang menjadi wilayah tugas Anda:
                                            </p>
                                        </div>
                                        <div class="flex items-center gap-2 self-end sm:self-auto">
                                            <button type="button" onclick="selectAllRumahMeter(true)" class="text-[10px] font-bold text-amber-700 hover:text-amber-900 underline cursor-pointer">Pilih Semua</button>
                                            <span class="text-amber-300 text-xs">|</span>
                                            <button type="button" onclick="selectAllRumahMeter(false)" class="text-[10px] font-bold text-slate-500 hover:text-slate-700 underline cursor-pointer">Reset</button>
                                        </div>
                                    </div>

                                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-2 max-h-48 overflow-y-auto pr-1">
                                        @if(isset($daftarRumahMeter) && count($daftarRumahMeter) > 0)
                                            @foreach($daftarRumahMeter as $rm)
                                                @php
                                                    $isRmChecked = is_array(old('assigned_stations')) && in_array($rm->id, old('assigned_stations'));
                                                @endphp
                                                <label class="flex items-center space-x-2 p-2 bg-white border border-amber-200/80 rounded-xl cursor-pointer hover:border-amber-400 transition-all text-xs select-none shadow-2xs">
                                                    <input type="checkbox" name="assigned_stations[]" value="{{ $rm->id }}"
                                                        {{ $isRmChecked ? 'checked' : '' }}
                                                        class="rounded border-slate-300 text-amber-600 focus:ring-amber-500 w-3.5 h-3.5 cursor-pointer rm-checkbox">
                                                    <span class="font-medium text-slate-700 truncate">
                                                        <strong class="font-mono text-amber-700">{{ $rm->kode_stasiun }}</strong> {{ $rm->name }}
                                                    </span>
                                                </label>
                                            @endforeach
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Seksi 3: Keamanan Sandi Akun --}}
                    <div>
                        <span class="text-xs font-bold text-sky-600 uppercase tracking-wider block mb-3">
                            <i class="fa-solid fa-shield-halved mr-1"></i> Keamanan Akun
                        </span>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            {{-- Field Kata Sandi Baru --}}
                            <div>
                                <label for="password" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">Kata Sandi Baru</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                                        <i class="fa-solid fa-lock text-xs"></i>
                                    </div>
                                    <input type="password" id="password" name="password" required onkeyup="checkPasswordStrength()"
                                        class="block w-full pl-9 pr-10 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-slate-800 placeholder-slate-400 text-xs sm:text-sm focus:ring-2 focus:ring-sky-500 focus:border-sky-500 focus:bg-white focus:outline-none transition-all"
                                        placeholder="Kombinasi sandi kuat">

                                    <button type="button" tabindex="-1" onclick="togglePasswordVisibility('password', 'password-icon')" class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-600">
                                        <i id="password-icon" class="fa-solid fa-eye text-xs"></i>
                                    </button>
                                </div>
                            </div>

                            {{-- Field Konfirmasi Kata Sandi --}}
                            <div>
                                <label for="confirm_password" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">Konfirmasi Kata Sandi</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                                        <i class="fa-solid fa-lock text-xs"></i>
                                    </div>
                                    <input type="password" id="confirm_password" name="password_confirmation" required
                                        class="block w-full pl-9 pr-10 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-slate-800 placeholder-slate-400 text-xs sm:text-sm focus:ring-2 focus:ring-sky-500 focus:border-sky-500 focus:bg-white focus:outline-none transition-all"
                                        placeholder="Ulangi kata sandi">

                                    <button type="button" tabindex="-1" onclick="togglePasswordVisibility('confirm_password', 'confirm-icon')" class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-600">
                                        <i id="confirm-icon" class="fa-solid fa-eye text-xs"></i>
                                    </button>
                                </div>
                            </div>
                        </div>

                        {{-- Indikator Kekuatan Kata Sandi Real-Time --}}
                        <div class="mt-3 p-3 bg-slate-50 rounded-xl border border-slate-200/80 text-[11px] space-y-1">
                            <div class="mt-2 space-y-1.5">
                                <div class="flex items-center justify-between text-[11px] font-medium text-slate-500">
                                    <span>Kekuatan Kata Sandi:</span>
                                    <span id="pwd-status-text" class="font-bold text-slate-400">Belum diisi</span>
                                </div>

                                <div class="w-full h-1.5 bg-slate-100 rounded-full overflow-hidden flex gap-1">
                                    <div id="pwd-bar-1" class="h-full w-1/4 bg-slate-200 transition-all duration-300"></div>
                                    <div id="pwd-bar-2" class="h-full w-1/4 bg-slate-200 transition-all duration-300"></div>
                                    <div id="pwd-bar-3" class="h-full w-1/4 bg-slate-200 transition-all duration-300"></div>
                                    <div id="pwd-bar-4" class="h-full w-1/4 bg-slate-200 transition-all duration-300"></div>
                                </div>

                                <p id="pwd-hint" class="text-[10px] text-slate-400 flex items-center gap-1">
                                    <i class="fa-solid fa-circle-info text-[9px]"></i>
                                    <span>Wajib min. 8 karakter, kombinasi huruf besar, kecil, angka & simbol.</span>
                                </p>
                            </div>
                        </div>
                    </div>

                    {{-- Persetujuan Syarat & Ketentuan --}}
                    <div class="flex items-start pt-2">
                        <input id="agreement" required type="checkbox"
                            class="mt-1 h-4 w-4 text-sky-600 focus:ring-sky-500 border-slate-300 rounded cursor-pointer">
                        <label for="agreement" class="ml-2 block text-xs text-slate-500 select-none leading-relaxed">
                            Saya menyatakan bahwa seluruh informasi yang diisi adalah benar, sesuai dengan Surat Keputusan (SK) dan status aktif kedinasan saya di PT Meta Adhya Tirta Umbulan.
                        </label>
                    </div>

                    {{-- Tombol Kirim Formulir Registrasi --}}
                    <button type="submit" id="submit-btn"
                        class="w-full mt-2 bg-sky-600 hover:bg-sky-700 text-white font-semibold py-3 px-4 rounded-xl shadow-lg shadow-sky-100 hover:shadow-xl hover:shadow-sky-200 transition-all active:scale-[0.99] flex items-center justify-center space-x-2">
                        <span>Daftarkan Akun Pegawai</span>
                        <i class="fa-solid fa-user-plus text-xs"></i>
                    </button>
                </form>

                <div class="mt-6 text-center text-xs sm:text-sm text-slate-500">
                    Sudah memiliki akun kepegawaian?
                    <a href="{{ route('login') }}" class="font-bold text-sky-600 hover:text-sky-700 hover:underline">Masuk ke Dashboard</a>
                </div>
            </div>

        </div>

    </div>

    {{-- Logika JavaScript Formulir Registrasi --}}
    <script>
        // Dropdown Multi-Select Peran/Jabatan
        function toggleRoleDropdown(forceState = null) {
            const panel = document.getElementById('roleDropdownPanel');
            const chevron = document.getElementById('roleChevronIcon');
            const searchInput = document.getElementById('roleSearchInput');

            if (!panel) return;

            const isCurrentlyOpen = !panel.classList.contains('hidden');
            const shouldOpen = forceState !== null ? forceState : !isCurrentlyOpen;

            if (shouldOpen) {
                panel.classList.remove('hidden');
                if (chevron) chevron.classList.add('rotate-180');
                if (searchInput) {
                    setTimeout(() => searchInput.focus(), 50);
                }
            } else {
                panel.classList.add('hidden');
                if (chevron) chevron.classList.remove('rotate-180');
            }
        }

        function filterRoleList(query) {
            const cleanQuery = (query || '').toLowerCase().trim();
            const items = document.querySelectorAll('.role-item');
            const noFound = document.getElementById('noRoleFound');
            const clearBtn = document.getElementById('clearRoleSearchBtn');
            const summary = document.getElementById('roleFilterSummary');

            if (clearBtn) {
                if (cleanQuery.length > 0) {
                    clearBtn.classList.remove('hidden');
                } else {
                    clearBtn.classList.add('hidden');
                }
            }

            let visibleCount = 0;
            items.forEach(item => {
                const name = item.getAttribute('data-name') || '';
                if (name.includes(cleanQuery)) {
                    item.classList.remove('hidden');
                    visibleCount++;
                } else {
                    item.classList.add('hidden');
                }
            });

            if (noFound) {
                if (visibleCount === 0) {
                    noFound.classList.remove('hidden');
                } else {
                    noFound.classList.add('hidden');
                }
            }

            if (summary) {
                summary.innerText = cleanQuery.length > 0
                    ? `Ditemukan: ${visibleCount} jabatan`
                    : 'Daftar Jabatan:';
            }
        }

        function clearRoleSearch() {
            const input = document.getElementById('roleSearchInput');
            if (input) {
                input.value = '';
                filterRoleList('');
                input.focus();
            }
        }

        function onRoleCheckboxChange() {
            const checkedBoxes = Array.from(document.querySelectorAll('.role-checkbox:checked'));
            const pillsContainer = document.getElementById('selectedRolesPills');
            const countBadge = document.getElementById('roleCountBadge');
            const errorMsg = document.getElementById('role-selection-error');

            if (checkedBoxes.length > 0) {
                if (errorMsg) errorMsg.classList.add('hidden');
                if (countBadge) {
                    countBadge.innerText = checkedBoxes.length;
                    countBadge.classList.remove('hidden');
                }

                let pillsHtml = '';
                checkedBoxes.forEach(cb => {
                    const label = cb.getAttribute('data-label') || 'Role';
                    const val = cb.value;
                    pillsHtml += `
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-semibold bg-sky-50 text-sky-800 border border-sky-200 shadow-2xs transition-all">
                            <span class="truncate max-w-[120px] sm:max-w-[160px]">${escapeHtml(label)}</span>
                            <button type="button" onclick="uncheckRole('${val}', event)" class="text-sky-500 hover:text-rose-600 p-0.5 rounded transition-colors cursor-pointer" title="Hapus ${escapeHtml(label)}">
                                <i class="fa-solid fa-xmark text-[10px]"></i>
                            </button>
                        </span>
                    `;
                });
                pillsContainer.innerHTML = pillsHtml;
            } else {
                pillsContainer.innerHTML = `<span id="rolePlaceholder" class="text-slate-400 text-xs sm:text-sm py-1">Pilih satu atau beberapa jabatan...</span>`;
                if (countBadge) countBadge.classList.add('hidden');
            }

            // Cek apakah role AREA (PIPELINE) dipilih
            const isPipelineSelected = checkedBoxes.some(cb => {
                const label = (cb.getAttribute('data-label') || '').toLowerCase();
                return label.includes('pipeline') || cb.value === '14';
            });

            const rmContainer = document.getElementById('pipelineRumahMeterContainer');
            if (rmContainer) {
                if (isPipelineSelected) {
                    rmContainer.classList.remove('hidden');
                } else {
                    rmContainer.classList.add('hidden');
                    // Reset centang jika uncheck pipeline
                    document.querySelectorAll('.rm-checkbox').forEach(c => c.checked = false);
                }
            }
        }

        function selectAllRumahMeter(selectAll = true) {
            document.querySelectorAll('.rm-checkbox').forEach(cb => {
                cb.checked = selectAll;
            });
        }

        function uncheckRole(roleId, event) {
            if (event) {
                event.stopPropagation();
            }
            const cb = document.querySelector(`.role-checkbox[value="${roleId}"]`);
            if (cb) {
                cb.checked = false;
                onRoleCheckboxChange();
            }
        }

        function resetSelectedRoles() {
            const checkedBoxes = document.querySelectorAll('.role-checkbox:checked');
            checkedBoxes.forEach(cb => cb.checked = false);
            onRoleCheckboxChange();
        }

        function escapeHtml(str) {
            return String(str)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
        }

        document.addEventListener('click', function(e) {
            const wrapper = document.getElementById('roleDropdownWrapper');
            if (wrapper && !wrapper.contains(e.target)) {
                toggleRoleDropdown(false);
            }
        });

        document.addEventListener('DOMContentLoaded', function() {
            onRoleCheckboxChange();
        });

        function togglePasswordVisibility(inputId, iconId) {
            const passwordInput = document.getElementById(inputId);
            const toggleIcon = document.getElementById(iconId);

            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleIcon.classList.remove('fa-eye');
                toggleIcon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                toggleIcon.classList.remove('fa-eye-slash');
                toggleIcon.classList.add('fa-eye');
            }
        }

        // Pengecekan Kriteria Password Real-Time
        function checkPasswordStrength() {
            const password = document.getElementById('password').value;

            const hasMinLen = password.length >= 8;
            const hasUpper  = /[A-Z]/.test(password);
            const hasLower  = /[a-z]/.test(password);
            const hasNumber = /[0-9]/.test(password);
            const hasSymbol = /[@$!%*?&]/.test(password);

            let score = 0;
            if (hasMinLen) score++;
            if (hasUpper && hasLower) score++;
            if (hasNumber) score++;
            if (hasSymbol) score++;

            const statusText = document.getElementById('pwd-status-text');
            const hintText   = document.getElementById('pwd-hint');
            const bars       = [
                document.getElementById('pwd-bar-1'),
                document.getElementById('pwd-bar-2'),
                document.getElementById('pwd-bar-3'),
                document.getElementById('pwd-bar-4')
            ];

            bars.forEach(bar => bar.className = "h-full w-1/4 bg-slate-200 transition-all duration-300");

            if (password.length === 0) {
                statusText.innerText = "Belum diisi";
                statusText.className = "font-bold text-slate-400";
                hintText.className = "text-[10px] text-slate-400 flex items-center gap-1";
                return false;
            }

            if (score <= 1) {
                statusText.innerText = "Sangat Lemah";
                statusText.className = "font-bold text-rose-500";
                bars[0].className = "h-full w-1/4 bg-rose-500 transition-all duration-300";
            } else if (score === 2) {
                statusText.innerText = "Sedang";
                statusText.className = "font-bold text-amber-500";
                bars[0].className = bars[1].className = "h-full w-1/4 bg-amber-500 transition-all duration-300";
            } else if (score === 3) {
                statusText.innerText = "Bagus";
                statusText.className = "font-bold text-sky-500";
                bars[0].className = bars[1].className = bars[2].className = "h-full w-1/4 bg-sky-500 transition-all duration-300";
            } else if (score === 4 && hasMinLen && hasUpper && hasLower && hasNumber && hasSymbol) {
                statusText.innerText = "Sangat Kuat ✓";
                statusText.className = "font-bold text-emerald-500";
                bars.forEach(bar => bar.className = "h-full w-1/4 bg-emerald-500 transition-all duration-300");
            }

            const isAllValid = hasMinLen && hasUpper && hasLower && hasNumber && hasSymbol;

            if (isAllValid) {
                hintText.className = "text-[10px] text-emerald-600 font-semibold flex items-center gap-1";
                hintText.innerHTML = `<i class="fa-solid fa-circle-check text-emerald-500"></i> Kata sandi memenuhi standar keamanan.`;
            } else {
                hintText.className = "text-[10px] text-slate-400 flex items-center gap-1";
                hintText.innerHTML = `<i class="fa-solid fa-circle-info text-[9px]"></i> Wajib min. 8 karakter, kombinasi huruf besar, kecil, angka & simbol.`;
            }

            return isAllValid;
        }

        function handleRegistration(event) {
            event.preventDefault();

            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            const submitBtn = document.getElementById('submit-btn');
            const notification = document.getElementById('notification');
            const notifIcon = document.getElementById('notif-icon');
            const notifMessage = document.getElementById('notif-message');

            const isPasswordValid = checkPasswordStrength();

            const selectedRoles = document.querySelectorAll('input[name="roles[]"]:checked');
            const roleError = document.getElementById('role-selection-error');
            if (selectedRoles.length === 0) {
                if (roleError) roleError.classList.remove('hidden');
                notification.style.display = 'flex';
                notification.className = "mb-6 p-4 rounded-xl border flex items-center space-x-3 bg-rose-50 border-rose-200 text-rose-800";
                notifIcon.innerHTML = `<i class="fa-solid fa-circle-exclamation text-rose-500 text-lg"></i>`;
                notifMessage.innerText = "Error: Silakan pilih minimal satu peran/jabatan yang diemban.";
                return;
            }
            if (roleError) roleError.classList.add('hidden');

            if (!isPasswordValid) {
                notification.style.display = 'flex';
                notification.className = "mb-6 p-4 rounded-xl border flex items-center space-x-3 bg-rose-50 border-rose-200 text-rose-800";
                notifIcon.innerHTML = `<i class="fa-solid fa-shield-halved text-rose-500 text-lg"></i>`;
                notifMessage.innerText = "Error: Kata Sandi Baru belum memenuhi semua standar keamanan (Huruf Besar, Huruf Kecil, Angka, & Simbol).";
                return;
            }

            if (password !== confirmPassword) {
                notification.style.display = 'flex';
                notification.className = "mb-6 p-4 rounded-xl border flex items-center space-x-3 bg-rose-50 border-rose-200 text-rose-800";
                notifIcon.innerHTML = `<i class="fa-solid fa-circle-exclamation text-rose-500 text-lg"></i>`;
                notifMessage.innerText = "Error: Konfirmasi Kata Sandi tidak cocok dengan Kata Sandi Baru.";
                return;
            }

            if (submitBtn && !submitBtn.dataset.submitted) {
                submitBtn.dataset.submitted = "true";
                setTimeout(() => {
                    submitBtn.disabled = true;
                    submitBtn.innerHTML = `
                        <svg class="animate-spin -ml-1 mr-3 h-4 w-4 text-white inline" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span>Mendaftarkan data Anda...</span>
                    `;
                }, 10);
            }

            event.target.submit();
        }

        // BFCache Buster: Cegah form register memakai CSRF token kadaluwarsa saat navigasi Back browser
        window.addEventListener('pageshow', function(event) {
            if (event.persisted || (window.performance && window.performance.getEntriesByType("navigation")[0]?.type === "back_forward")) {
                window.location.reload();
            }
        });

        // Jam Real-Time Portal Presensi (WIB)
        function updateLiveClock() {
            const clockEl = document.getElementById('liveClock');
            if (!clockEl) return;
            const now = new Date();
            const pad = (n) => String(n).padStart(2, '0');
            clockEl.textContent = `${pad(now.getHours())}:${pad(now.getMinutes())}:${pad(now.getSeconds())} WIB`;
        }
        setInterval(updateLiveClock, 1000);
    </script>

    @laravelPwa
    @pwaInstallButton
</body>
</html>
