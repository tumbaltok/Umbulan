<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Sistem Informasi Cuti Karyawan - PT.META</title>
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
<body class="bg-slate-50 dark:bg-slate-900 min-h-screen flex items-center justify-center p-3 sm:p-6 md:p-8 overflow-x-hidden transition-colors">

    <div class="bg-white dark:bg-slate-800 w-full max-w-5xl rounded-3xl shadow-2xl overflow-hidden flex flex-col md:flex-row h-auto md:min-h-[600px] border border-slate-100 dark:border-slate-700 transition-all duration-300 my-4 md:my-0">

        {{-- Sisi Kiri: Branding & Infrastruktur Mega Transmisi Air Umbulan --}}
        <div class="w-full md:w-1/2 wave-bg text-white p-6 sm:p-8 md:p-10 flex flex-col justify-between relative overflow-hidden shrink-0">
            {{-- Elemen Grafis Latar Belakang (Grid & Water Wave) --}}
            <div class="absolute inset-0 water-pattern opacity-70 pointer-events-none"></div>
            <div class="absolute -top-24 -left-24 w-72 h-72 bg-cyan-400/20 rounded-full blur-3xl pointer-events-none"></div>
            <div class="absolute -bottom-24 -right-24 w-72 h-72 bg-sky-500/20 rounded-full blur-3xl pointer-events-none"></div>

            <div class="absolute bottom-0 left-0 right-0 opacity-15 pointer-events-none">
                <svg viewBox="0 0 1440 320" xmlns="http://www.w3.org/2000/svg">
                    <path fill="#ffffff" fill-opacity="1" d="M0,96L48,112C96,128,192,160,288,186.7C384,213,480,235,576,213.3C672,192,768,128,864,117.3C960,107,1056,149,1152,154.7C1248,160,1344,128,1392,112L1440,96L1440,320L1392,320C1344,320,1248,320,1152,320C1056,320,960,320,864,320C768,320,672,320,576,320C480,320,384,320,288,320C192,320,96,320,48,320L0,320Z"></path>
                </svg>
            </div>

            {{-- 1. Logo Brand Perusahaan --}}
            <div class="z-10 flex items-center space-x-3.5">
                <div class="bg-white/15 p-1.5 rounded-2xl backdrop-blur-md border border-white/20 w-11 h-11 sm:w-12 sm:h-12 flex items-center justify-center shadow-lg shadow-slate-950/20 overflow-hidden shrink-0">
                    <img src="{{ asset('images/iconfav.png') }}" alt="Logo PT META" class="w-full h-full object-cover rounded-xl">
                </div>

                <div>
                    <h2 class="font-extrabold tracking-wide text-xs sm:text-sm text-cyan-200 leading-tight">META ADHYA TIRTA UMBULAN</h2>
                    <p class="text-[9px] sm:text-[10px] text-white/80 uppercase tracking-widest font-semibold flex items-center gap-1.5">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                        Transmisi Air Baku Terpadu
                    </p>
                </div>
            </div>

            {{-- 2. Visual Showcase Fasilitas Transmisi Skala Mega & Sambutan --}}
            <div class="my-auto py-6 z-10 hidden md:flex flex-col items-start w-full">
                {{-- Foto Nyata Fasilitas Transmisi Air Umbulan --}}
                <div class="relative w-full rounded-2xl overflow-hidden border border-white/20 shadow-2xl shadow-sky-950/60 mb-4 group">
                    <img src="{{ asset('images/water_transmission_hero.jpg') }}" alt="Fasilitas Transmisi Air Umbulan" class="w-full h-40 object-cover group-hover:scale-105 transition-transform duration-700">
                    <div class="absolute inset-0 bg-gradient-to-t from-slate-950/90 via-slate-950/30 to-transparent"></div>

                    {{-- Badge Status Telemetri Live --}}
                    <div class="absolute top-2.5 left-2.5 flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-slate-950/70 backdrop-blur-md border border-white/15 text-[10px] text-cyan-200 font-medium shadow-md">
                        <span class="relative flex h-2 w-2">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-400"></span>
                        </span>
                        <span>Transmisi Aktif • 4.000 L/s</span>
                    </div>

                    {{-- Label Kapasitas & Jaringan Wilayah --}}
                    <div class="absolute bottom-2.5 left-2.5 right-2.5 text-white">
                        <div class="flex items-center justify-between text-[10.5px] font-bold text-white mb-0.5">
                            <span>SPAM Regional Umbulan</span>
                            <span class="text-cyan-300 font-mono text-[9.5px]">Ø 1500mm</span>
                        </div>
                        <p class="text-[9px] text-cyan-100/80 font-light truncate">
                            Jaringan Transmisi Pasuruan &rarr; Sidoarjo &rarr; Surabaya &rarr; Gresik
                        </p>
                    </div>
                </div>

                {{-- Badging & Headline --}}
                <div class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-lg bg-white/10 backdrop-blur-md border border-white/15 text-[9.5px] font-bold tracking-wider text-cyan-200 uppercase mb-2">
                    <i class="fa-solid fa-water text-cyan-300 text-[10px]"></i>
                    <span>Infrastruktur Strategis Air Baku</span>
                </div>

                <h1 class="text-xl sm:text-2xl font-extrabold leading-tight text-white mb-2 tracking-tight">
                    Portal Operasional & Kepegawaian
                </h1>
                
                <p class="text-cyan-100/85 text-xs font-light leading-relaxed mb-4">
                    Akses terpadu seluruh personel untuk Presensi Biometrik Wajah, Monitoring Stasiun Offtake, Pengajuan CAR & MPR, serta Administrasi Kepegawaian.
                </p>

                {{-- 3 Metrik Utama Skala Industri --}}
                <div class="grid grid-cols-3 gap-2 w-full">
                    <div class="bg-white/10 backdrop-blur-md border border-white/15 rounded-xl p-2 text-center transition hover:bg-white/15">
                        <div class="text-base font-black text-cyan-200 leading-none">4.000 <span class="text-[9px] font-semibold text-cyan-300">L/s</span></div>
                        <div class="text-[8.5px] text-white/70 uppercase tracking-wider font-semibold mt-1">Debit Air</div>
                    </div>
                    <div class="bg-white/10 backdrop-blur-md border border-white/15 rounded-xl p-2 text-center transition hover:bg-white/15">
                        <div class="text-base font-black text-white leading-none">93.7 <span class="text-[9px] font-semibold text-slate-300">Km</span></div>
                        <div class="text-[8.5px] text-white/70 uppercase tracking-wider font-semibold mt-1">Transmisi</div>
                    </div>
                    <div class="bg-white/10 backdrop-blur-md border border-white/15 rounded-xl p-2 text-center transition hover:bg-white/15">
                        <div class="text-base font-black text-cyan-200 leading-none">22 <span class="text-[9px] font-semibold text-cyan-300">Titik</span></div>
                        <div class="text-[8.5px] text-white/70 uppercase tracking-wider font-semibold mt-1">Stasiun</div>
                    </div>
                </div>
            </div>

            {{-- 3. Footer Hak Cipta & Keamanan --}}
            <div class="z-10 text-[10px] sm:text-xs text-white/60 flex items-center justify-between border-t border-white/15 pt-3">
                <span>&copy; <?= date('Y') ?> PT Meta Adhya Tirta Umbulan</span>
                <span class="flex items-center gap-1 text-[9px] text-cyan-200/90 font-medium">
                    <i class="fa-solid fa-shield-halved text-cyan-300"></i> Sistem Resmi
                </span>
            </div>
        </div>

        <div class="w-full md:w-1/2 p-5 sm:p-10 md:p-12 flex flex-col justify-between bg-white dark:bg-slate-800 transition-colors">

            {{-- NOTIFIKASI SUKSES SETELAH REGISTRASI --}}
            @if (session('success'))
                <div class="mb-6 p-4 rounded-2xl bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800 text-emerald-800 dark:text-emerald-300 text-xs sm:text-sm flex items-start space-x-3 shadow-sm transition-all">
                    <div class="text-emerald-500 mt-0.5 shrink-0">
                        <i class="fa-solid fa-circle-check text-base sm:text-lg"></i>
                    </div>
                    <div>
                        <h4 class="font-bold mb-0.5 text-emerald-900 dark:text-emerald-200">Pendaftaran Berhasil!</h4>
                        <p class="text-emerald-700 dark:text-emerald-300 leading-relaxed">{{ session('success') }}</p>
                    </div>
                </div>
            @endif

            {{-- NOTIFIKASI PERINGATAN / SESI KEDALUWARSA --}}
            @if (session('warning'))
                <div class="mb-6 p-4 rounded-2xl bg-amber-50 dark:bg-amber-950/40 border border-amber-200 dark:border-amber-800 text-amber-800 dark:text-amber-300 text-xs sm:text-sm flex items-start space-x-3 shadow-sm transition-all">
                    <div class="text-amber-500 mt-0.5 shrink-0">
                        <i class="fa-solid fa-triangle-exclamation text-base sm:text-lg"></i>
                    </div>
                    <div>
                        <h4 class="font-bold mb-0.5 text-amber-900 dark:text-amber-200">Perhatian</h4>
                        <p class="text-amber-700 dark:text-amber-300 leading-relaxed">{{ session('warning') }}</p>
                    </div>
                </div>
            @endif

            @if (session('error'))
                <div class="mb-6 p-4 rounded-2xl bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-800 text-rose-800 dark:text-rose-300 text-xs sm:text-sm flex items-start space-x-3 shadow-sm transition-all">
                    <div class="text-rose-500 mt-0.5 shrink-0">
                        <i class="fa-solid fa-circle-exclamation text-base sm:text-lg"></i>
                    </div>
                    <div>
                        <h4 class="font-bold mb-0.5 text-rose-900 dark:text-rose-200">Terjadi Kesalahan</h4>
                        <p class="text-rose-700 dark:text-rose-300 leading-relaxed">{{ session('error') }}</p>
                    </div>
                </div>
            @endif

            <div class="w-full my-auto">
                <div class="mb-6">
                    <h2 class="text-2xl sm:text-3xl font-bold text-slate-800 dark:text-slate-100 tracking-tight">Selamat Datang</h2>
                    <p class="text-slate-500 dark:text-slate-400 text-xs sm:text-sm mt-1">Silakan masuk menggunakan akun kepegawaian Anda.</p>
                </div>

                @if ($errors->any())
                    <div class="mb-6 p-4 rounded-2xl border flex items-center space-x-3 bg-rose-50 dark:bg-rose-950/40 border-rose-200 dark:border-rose-800 text-rose-800 dark:text-rose-300 animate-fade-in">
                        <div>
                            <i class="fa-solid fa-circle-exclamation text-rose-500 text-lg"></i>
                        </div>
                        <div class="text-sm font-medium">
                            {{ $errors->first() }}
                        </div>
                    </div>
                @endif

                <div id="notification" style="display: none;" class="mb-6 p-4 rounded-xl border flex items-center space-x-3 transition-all duration-300">
                    <div id="notif-icon"></div>
                    <div class="text-sm font-medium" id="notif-message"></div>
                </div>

                <form id="loginForm" class="space-y-5" onsubmit="handleLogin(event)" method="POST" action="{{ route('login.post') }}">
                    @csrf

                    <div>
                        <label for="employee-id" class="block text-xs font-semibold text-slate-700 dark:text-slate-200 uppercase tracking-wider mb-2">EMAIL</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                <i class="fa-solid fa-id-card text-base"></i>
                            </div>
                            <input type="email" id="employee-id" name="email" required
                                value="{{ old('email') }}"
                                class="block w-full pl-11 pr-4 py-3 sm:py-3.5 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-2xl text-slate-800 dark:text-slate-100 placeholder-slate-400 text-sm transition-all focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-sky-500 focus:bg-white dark:focus:bg-slate-900"
                                placeholder="Contoh: nama@meta.com">
                        </div>
                    </div>

                    <div>
                        <div class="flex justify-between items-center mb-2">
                            <label for="password" class="block text-xs font-semibold text-slate-700 dark:text-slate-200 uppercase tracking-wider">Kata Sandi</label>
                            <a href="{{ route('forgot') }}" tabindex="-1" class="text-xs font-medium text-sky-600 dark:text-sky-400 hover:text-sky-700 dark:hover:text-sky-300 transition-colors">
                                Lupa kata sandi?
                            </a>
                        </div>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                <i class="fa-solid fa-lock text-base"></i>
                            </div>
                            <input type="password" id="password" name="password" required
                                class="block w-full pl-11 pr-11 py-3 sm:py-3.5 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-2xl text-slate-800 dark:text-slate-100 placeholder-slate-400 text-sm transition-all focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-sky-500 focus:bg-white dark:focus:bg-slate-900"
                                placeholder="Masukkan kata sandi Anda">
                            <button type="button" onclick="togglePasswordVisibility()" class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 cursor-pointer">
                                <i id="password-toggle-icon" class="fa-solid fa-eye text-sm"></i>
                            </button>
                        </div>
                    </div>

                    <div class="flex items-center">
                        <input id="remember-me" name="remember" type="checkbox" checked
                            class="h-4.5 w-4.5 text-sky-600 focus:ring-sky-500 border-slate-300 dark:border-slate-600 rounded-lg cursor-pointer">
                        <label for="remember-me" class="ml-2 block text-sm text-slate-600 dark:text-slate-300 select-none cursor-pointer">
                            Ingat saya di perangkat ini
                        </label>
                    </div>

                    <button type="submit" id="submit-btn"
                        class="w-full bg-sky-600 hover:bg-sky-700 text-white font-semibold py-3.5 px-4 rounded-2xl shadow-lg shadow-sky-100 dark:shadow-none hover:shadow-xl transition-all active:scale-[0.98] flex items-center justify-center space-x-2 cursor-pointer">
                        <span>Masuk ke Portal Cuti</span>
                        <i class="fa-solid fa-arrow-right text-xs"></i>
                    </button>
                </form>

                <div class="mt-8 pt-6 border-t border-slate-100 dark:border-slate-700 flex flex-col sm:flex-row items-center justify-between gap-4">
                    <div class="flex items-center space-x-2 text-slate-500 dark:text-slate-400 text-xs">
                        <i class="fa-solid fa-circle-question text-sky-500 text-sm"></i>
                        <span>Butuh bantuan akses login?</span>
                    </div>
                    <a href="http://wa.me/+6281131132067" target="blank" class="text-xs font-semibold text-sky-600 dark:text-sky-400 hover:text-sky-700 dark:hover:text-sky-300 border border-sky-100 dark:border-sky-800 hover:border-sky-200 dark:hover:border-sky-700 bg-sky-50/50 dark:bg-sky-950/40 hover:bg-sky-50 dark:hover:bg-sky-900/50 px-3 py-1.5 rounded-xl transition-colors">
                        <i class="fa-solid fa-headset mr-1"></i> Kontak HR / IT Helpdesk
                    </a>
                </div>
            </div>

            <div class="text-center text-[10px] text-slate-400 mt-8 md:hidden">
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
