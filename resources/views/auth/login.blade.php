<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Portal Masuk Karyawan - PT Meta Adhya Tirta Umbulan</title>
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

    <div class="bg-white w-full max-w-5xl rounded-2xl sm:rounded-3xl shadow-2xl overflow-hidden flex flex-col md:flex-row h-auto md:min-h-[620px] border border-slate-100 transition-all duration-300 my-4 md:my-0">

        {{-- Sisi Kiri: Branding & Cockpit Operasional Digital Mega Industri --}}
        <div class="w-full md:w-1/2 wave-bg text-white p-4 sm:p-6 md:p-10 flex flex-col justify-between relative overflow-hidden shrink-0">
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
                    <h2 class="font-extrabold tracking-wide text-xs sm:text-sm text-cyan-200 leading-tight">META ADHYA TIRTA UMBULAN</h2>
                    <p class="text-[9px] sm:text-[10px] text-white/80 uppercase tracking-widest font-semibold flex items-center gap-1.5">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                        Portal Layanan Karyawan
                    </p>
                </div>
            </div>

            {{-- 2. Visual Informasi Portal Kepegawaian & Presensi Digital (Disembunyikan di Mobile, Tampil di md:flex) --}}
            <div class="my-auto py-6 z-10 hidden md:flex flex-col items-start w-full">
                {{-- Kartu Status Informasi & Waktu Presensi Portal --}}
                <div class="w-full bg-slate-950/45 backdrop-blur-md border border-white/20 rounded-2xl p-4 sm:p-5 shadow-2xl shadow-sky-950/50 mb-5 relative overflow-hidden">
                    <div class="absolute -right-8 -top-8 w-32 h-32 bg-cyan-400/10 rounded-full blur-xl pointer-events-none"></div>

                    {{-- Header Kartu Status --}}
                    <div class="flex items-center justify-between pb-3 border-b border-white/10 mb-3.5">
                        <div class="flex items-center gap-2">
                            <span class="relative flex h-2.5 w-2.5">
                                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                                <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-emerald-400"></span>
                            </span>
                            <span class="text-[11px] font-bold text-cyan-200 tracking-wider uppercase font-mono">PORTAL KEPEGAWAIAN • AKTIF</span>
                        </div>
                        <div id="liveClock" class="text-[11px] font-mono text-cyan-300 font-semibold bg-white/10 px-2.5 py-0.5 rounded-lg border border-white/10">
                            {{ date('H:i:s') }} WIB
                        </div>
                    </div>

                    {{-- Grid 3 Pilar Layanan Mandiri --}}
                    <div class="grid grid-cols-3 gap-2 text-center mb-3.5">
                        <div class="p-2.5 rounded-xl bg-white/5 border border-white/10">
                            <span class="text-[9px] text-cyan-200/80 uppercase tracking-wider block font-semibold">Presensi</span>
                            <span class="text-xs sm:text-sm font-black text-cyan-200 font-mono">Biometrik</span>
                            <span class="text-[9px] text-white/60 block">Geofencing</span>
                        </div>
                        <div class="p-2.5 rounded-xl bg-white/5 border border-white/10">
                            <span class="text-[9px] text-cyan-200/80 uppercase tracking-wider block font-semibold">Layanan</span>
                            <span class="text-xs sm:text-sm font-black text-white font-mono">Mandiri</span>
                            <span class="text-[9px] text-white/60 block">Cuti & Tugas</span>
                        </div>
                        <div class="p-2.5 rounded-xl bg-white/5 border border-white/10">
                            <span class="text-[9px] text-cyan-200/80 uppercase tracking-wider block font-semibold">Keamanan</span>
                            <span class="text-xs sm:text-sm font-black text-emerald-300 font-mono">Terenkripsi</span>
                            <span class="text-[9px] text-white/60 block">Akses Aman</span>
                        </div>
                    </div>

                    {{-- Baris Status Autentikasi & Layanan --}}
                    <div class="flex items-center justify-between text-[10px] text-cyan-100/90 bg-white/5 px-3 py-1.5 rounded-lg border border-white/5 font-mono">
                        <span class="flex items-center gap-1.5">
                            <i class="fa-solid fa-shield-halved text-cyan-300"></i> Autentikasi Pengguna Terverifikasi
                        </span>
                        <span class="text-emerald-300 font-semibold flex items-center gap-1">
                            <i class="fa-solid fa-circle-check text-[9px]"></i> Sistem Online
                        </span>
                    </div>
                </div>

                {{-- Badging & Headline --}}
                <div class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-white/10 backdrop-blur-md border border-white/15 text-[10px] font-bold tracking-wider text-cyan-200 uppercase mb-2.5">
                    <i class="fa-solid fa-id-badge text-cyan-300 text-xs"></i>
                    <span>Sistem Informasi & Layanan Karyawan</span>
                </div>

                <h1 class="text-2xl font-extrabold leading-tight text-white mb-2 tracking-tight">
                    Pusat Layanan Terpadu Karyawan
                </h1>
                
                <p class="text-cyan-100/85 text-xs sm:text-sm font-light leading-relaxed mb-5">
                    Sistem pintu masuk terpadu untuk pencatatan presensi biometrik wajah berbasis lokasi kerja, administrasi cuti, pengajuan dokumen dinas, serta tata kelola kepegawaian.
                </p>

                {{-- 2 Pilar Layanan Karyawan --}}
                <div class="grid grid-cols-1 gap-2 w-full text-xs">
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
            <div class="z-10 text-[10px] sm:text-xs text-white/60 hidden md:flex items-center justify-between border-t border-white/15 pt-3">
                <span>&copy; <?= date('Y') ?> PT Meta Adhya Tirta Umbulan</span>
                <span class="flex items-center gap-1 text-[9px] text-cyan-200/90 font-medium">
                    <i class="fa-solid fa-shield-halved text-cyan-300"></i> Sistem Resmi Terenkripsi
                </span>
            </div>
        </div>

        {{-- Sisi Kanan: Formulir Masuk Akun --}}
        <div class="w-full md:w-1/2 p-5 sm:p-8 md:p-12 flex flex-col justify-between bg-white transition-colors">

            {{-- NOTIFIKASI SUKSES SETELAH REGISTRASI --}}
            @if (session('success'))
                <div class="mb-5 p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs sm:text-sm flex items-start space-x-3 shadow-sm transition-all">
                    <div class="text-emerald-500 mt-0.5 shrink-0">
                        <i class="fa-solid fa-circle-check text-base sm:text-lg"></i>
                    </div>
                    <div>
                        <h4 class="font-bold mb-0.5 text-emerald-900">Pendaftaran Berhasil!</h4>
                        <p class="text-emerald-700 leading-relaxed">{{ session('success') }}</p>
                    </div>
                </div>
            @endif

            {{-- NOTIFIKASI PERINGATAN / SESI KEDALUWARSA --}}
            @if (session('warning'))
                <div class="mb-5 p-4 rounded-xl bg-amber-50 border border-amber-200 text-amber-800 text-xs sm:text-sm flex items-start space-x-3 shadow-sm transition-all">
                    <div class="text-amber-500 mt-0.5 shrink-0">
                        <i class="fa-solid fa-triangle-exclamation text-base sm:text-lg"></i>
                    </div>
                    <div>
                        <h4 class="font-bold mb-0.5 text-amber-900">Perhatian</h4>
                        <p class="text-amber-700 leading-relaxed">{{ session('warning') }}</p>
                    </div>
                </div>
            @endif

            @if (session('error'))
                <div class="mb-5 p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-xs sm:text-sm flex items-start space-x-3 shadow-sm transition-all">
                    <div class="text-rose-500 mt-0.5 shrink-0">
                        <i class="fa-solid fa-circle-exclamation text-base sm:text-lg"></i>
                    </div>
                    <div>
                        <h4 class="font-bold mb-0.5 text-rose-900">Terjadi Kesalahan</h4>
                        <p class="text-rose-700 leading-relaxed">{{ session('error') }}</p>
                    </div>
                </div>
            @endif

            <div class="w-full my-auto">
                <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 border-b border-slate-100 pb-5">
                    <div>
                        <div class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full bg-sky-50 border border-sky-100 text-sky-700 text-[11px] font-bold tracking-wide uppercase mb-1">
                            <i class="fa-solid fa-arrow-right-to-bracket text-[10px]"></i> Portal Otentikasi
                        </div>
                        <h2 class="text-2xl sm:text-3xl font-extrabold text-slate-800 tracking-tight">Selamat Datang</h2>
                        <p class="text-slate-500 text-xs sm:text-sm mt-0.5">Silakan masuk menggunakan akun kedinasan Anda.</p>
                    </div>
                    <div class="hidden sm:flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-slate-50 border border-slate-200/80 text-[11px] text-slate-600 font-medium shrink-0">
                        <i class="fa-solid fa-shield-halved text-emerald-500"></i> SSO Terintegrasi
                    </div>
                </div>

                @if ($errors->any())
                    <div class="mb-5 p-4 rounded-xl border flex items-center space-x-3 bg-rose-50 border-rose-200 text-rose-800 animate-fade-in">
                        <div>
                            <i class="fa-solid fa-circle-exclamation text-rose-500 text-lg"></i>
                        </div>
                        <div class="text-xs sm:text-sm font-medium">
                            {{ $errors->first() }}
                        </div>
                    </div>
                @endif

                <div id="notification" style="display: none;" class="mb-5 p-4 rounded-xl border flex items-center space-x-3 transition-all duration-300">
                    <div id="notif-icon"></div>
                    <div class="text-sm font-medium" id="notif-message"></div>
                </div>

                <form id="loginForm" class="space-y-4" onsubmit="handleLogin(event)" method="POST" action="{{ route('login.post') }}">
                    @csrf

                    <div>
                        <label for="employee-id" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">Alamat Email</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                <i class="fa-solid fa-envelope text-xs"></i>
                            </div>
                            <input type="email" id="employee-id" name="email" required
                                value="{{ old('email') }}"
                                class="block w-full pl-10 pr-4 py-2.5 sm:py-3 bg-slate-50 border border-slate-200 rounded-xl text-slate-800 placeholder-slate-400 text-xs sm:text-sm transition-all focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-sky-500 focus:bg-white"
                                placeholder="Contoh: nama@meta.com">
                        </div>
                    </div>

                    <div>
                        <div class="flex justify-between items-center mb-1.5">
                            <label for="password" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider">Kata Sandi</label>
                            <a href="{{ route('forgot') }}" tabindex="-1" class="text-xs font-medium text-sky-600 hover:text-sky-700 transition-colors">
                                Lupa kata sandi?
                            </a>
                        </div>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                <i class="fa-solid fa-lock text-xs"></i>
                            </div>
                            <input type="password" id="password" name="password" required
                                class="block w-full pl-10 pr-10 py-2.5 sm:py-3 bg-slate-50 border border-slate-200 rounded-xl text-slate-800 placeholder-slate-400 text-xs sm:text-sm transition-all focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-sky-500 focus:bg-white"
                                placeholder="Masukkan kata sandi Anda">
                            <button type="button" onclick="togglePasswordVisibility()" class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-600 cursor-pointer">
                                <i id="password-toggle-icon" class="fa-solid fa-eye text-xs"></i>
                            </button>
                        </div>
                    </div>

                    <div class="flex items-center pt-1">
                        <input id="remember-me" name="remember" type="checkbox" checked
                            class="h-4 w-4 text-sky-600 focus:ring-sky-500 border-slate-300 rounded cursor-pointer">
                        <label for="remember-me" class="ml-2 block text-xs sm:text-sm text-slate-600 select-none cursor-pointer">
                            Ingat saya di perangkat ini
                        </label>
                    </div>

                    <button type="submit" id="submit-btn"
                        class="w-full mt-2 bg-gradient-to-r from-sky-600 to-sky-700 hover:from-sky-700 hover:to-sky-800 text-white font-bold py-3.5 px-6 rounded-xl shadow-lg shadow-sky-600/25 hover:shadow-xl hover:shadow-sky-600/35 transition-all active:scale-[0.99] flex items-center justify-center space-x-2 cursor-pointer">
                        <span>Masuk ke Dashboard</span>
                        <i class="fa-solid fa-arrow-right text-xs"></i>
                    </button>
                </form>

                <div class="mt-4 text-center text-xs sm:text-sm text-slate-500">
                    Belum memiliki akun kepegawaian?
                    <a href="{{ route('register') }}" class="font-bold text-sky-600 hover:text-sky-700 hover:underline">Daftar Akun Baru</a>
                </div>

                <div class="mt-6 pt-5 border-t border-slate-100 flex flex-col sm:flex-row items-center justify-between gap-3">
                    <div class="flex items-center space-x-2 text-slate-500 text-xs">
                        <i class="fa-solid fa-circle-question text-sky-500 text-sm"></i>
                        <span>Butuh bantuan akses login?</span>
                    </div>
                    <a href="https://wa.me/6281131132067" target="_blank" class="text-xs font-semibold text-sky-700 hover:text-sky-800 border border-sky-200 bg-sky-50 hover:bg-sky-100 px-3 py-1.5 rounded-xl transition-colors flex items-center gap-1.5">
                        <i class="fa-brands fa-whatsapp text-emerald-600 text-sm"></i> Kontak IT Helpdesk
                    </a>
                </div>
            </div>

            <div class="text-center text-[10px] text-slate-400 mt-6 md:hidden">
                &copy; <?= date('Y') ?> PT Meta Adhya Tirta Umbulan. All rights reserved.
            </div>

        </div>
    </div>

    <script>
        function togglePasswordVisibility() {
            const passwordInput = document.getElementById('password');
            const toggleIcon = document.getElementById('password-toggle-icon');

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

        function handleLogin(event) {
            const employeeId = document.getElementById('employee-id').value;
            const password = document.getElementById('password').value;
            const submitBtn = document.getElementById('submit-btn');
            const notification = document.getElementById('notification');
            const notifIcon = document.getElementById('notif-icon');
            const notifMessage = document.getElementById('notif-message');

            notification.style.display = 'none';

            if (employeeId.trim().length < 4 || password.length < 4) {
                event.preventDefault();
                notification.style.display = 'flex';
                notification.className = "mb-6 p-4 rounded-2xl border flex items-center space-x-3 bg-rose-50 border-rose-200 text-rose-800";
                notifIcon.innerHTML = `<i class="fa-solid fa-circle-exclamation text-rose-500 text-lg"></i>`;
                notifMessage.innerText = "Format email atau password terlalu pendek.";
                return;
            }

            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.innerHTML = `
                    <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white inline" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span>Memproses Autentikasi...</span>
                `;
            }

            event.preventDefault();
            document.getElementById('loginForm').submit();
        }

        // BFCache Buster: Cegah form login memakai CSRF token kadaluwarsa saat navigasi Back browser
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

    {{-- Pendaftaran Skrip PWA --}}
    @laravelPwa
    @pwaInstallButton
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
