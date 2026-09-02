<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Verifikasi Nomor WhatsApp | PT META Adhya Tirta Umbulan</title>

    {{-- Favicon Aplikasi --}}
    <link rel="icon" type="image/png" href="{{ asset('images/iconfav.png') }}">
    <link rel="shortcut icon" type="image/png" href="{{ asset('images/iconfav.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/iconfav.png') }}">

    {{-- Konfigurasi Tailwind CSS --}}
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        // Kunci Halaman Auth Selalu Light Mode
        document.documentElement.classList.remove('dark');
    </script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['"Plus Jakarta Sans"', '"Instrument Sans"', 'sans-serif'],
                    },
                    colors: {
                        brand: {
                            50: '#f0f9ff',
                            100: '#e0f2fe',
                            200: '#bae6fd',
                            300: '#7dd3fc',
                            400: '#38bdf8',
                            500: '#0ea5e9',
                            600: '#0284c7',
                            700: '#0369a1',
                            800: '#075985',
                            900: '#0c4a6e',
                            950: '#082f49',
                        },
                        wa: {
                            50: '#f0fdf4',
                            100: '#dcfce7',
                            500: '#22c55e',
                            600: '#16a34a',
                            700: '#15803d',
                        }
                    },
                    animation: {
                        'pulse-slow': 'pulse 4s cubic-bezier(0.4, 0, 0.6, 1) infinite',
                        'float': 'float 5s ease-in-out infinite',
                    },
                    keyframes: {
                        float: {
                            '0%, 100%': { transform: 'translateY(0px)' },
                            '50%': { transform: 'translateY(-6px)' },
                        }
                    }
                }
            }
        };
    </script>

    {{-- FontAwesome dan Google Fonts --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Instrument+Sans:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        body {
            font-family: 'Plus Jakarta Sans', 'Instrument Sans', -apple-system, BlinkMacSystemFont, sans-serif;
        }

        /* Subtle grid background pattern */
        .bg-grid-pattern {
            background-size: 32px 32px;
            background-image: 
                linear-gradient(to right, rgba(14, 165, 233, 0.05) 1px, transparent 1px),
                linear-gradient(to bottom, rgba(14, 165, 233, 0.05) 1px, transparent 1px);
        }
    </style>

    {{-- Komponen Head PWA --}}
    @pwaHead
</head>
<body class="h-full bg-slate-50 text-slate-800 flex flex-col justify-between antialiased relative selection:bg-brand-500 selection:text-white">

    {{-- Elemen Background Gradien Dekoratif --}}
    <div class="fixed inset-0 pointer-events-none overflow-hidden bg-grid-pattern z-0">
        <div class="absolute -top-32 left-1/2 -translate-x-1/2 w-[650px] h-[360px] bg-gradient-to-tr from-emerald-400/20 to-cyan-300/20 blur-3xl rounded-full"></div>
        <div class="absolute bottom-0 right-0 w-[420px] h-[320px] bg-gradient-to-tl from-brand-400/15 to-emerald-300/15 blur-3xl rounded-full"></div>
    </div>

    {{-- Header Minimalis Logo Perusahaan --}}
    <header class="relative z-10 w-full max-w-5xl mx-auto px-4 sm:px-6 pt-6 pb-2 flex items-center justify-between">
        <div class="flex items-center gap-3">
            {{-- Logo PT Perusahaan --}}
            <div class="w-11 h-11 rounded-full bg-white border border-slate-200/90 shadow-sm flex items-center justify-center p-1.5 shrink-0 overflow-hidden">
                <img src="{{ asset('images/iconfav.png') }}" alt="Logo PT Umbulan" class="w-full h-full object-contain rounded-full">
            </div>
            <div>
                <h1 class="text-xs sm:text-sm font-black tracking-wider text-slate-900 uppercase">PT META Adhya Tirta Umbulan</h1>
                <p class="text-[10px] sm:text-[11px] font-semibold text-brand-600 tracking-wider">Enterprise Resource Portal</p>
            </div>
        </div>
    </header>

    {{-- Kontainer Konten Utama --}}
    <main class="relative z-10 flex-1 flex items-center justify-center px-4 py-6 sm:px-6 lg:px-8">
        <div class="w-full max-w-xl mx-auto">

            {{-- Kartu Verifikasi WhatsApp --}}
            <div class="bg-white/95 backdrop-blur-xl rounded-3xl border border-slate-200/90 shadow-2xl shadow-slate-200/60 p-6 sm:p-10 transition-all duration-300 relative overflow-hidden">

                {{-- Garis Aksen WhatsApp Emerald --}}
                <div class="absolute top-0 left-0 right-0 h-1.5 bg-gradient-to-r from-emerald-500 via-teal-400 to-brand-500"></div>

                {{-- Indikator Progres 2 Tahap Verifikasi --}}
                <div class="flex items-center justify-center gap-2 mb-6">
                    <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                        <i class="fa-solid fa-circle-check text-xs"></i>
                        <span>1. Email Terverifikasi</span>
                    </div>
                    <i class="fa-solid fa-chevron-right text-[10px] text-slate-300"></i>
                    <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[11px] font-bold bg-brand-50 text-brand-700 border border-brand-200">
                        <span class="w-2 h-2 rounded-full bg-brand-500 animate-ping"></span>
                        <span>2. Verifikasi WhatsApp</span>
                    </div>
                </div>

                {{-- Ikon dan Informasi Hero --}}
                <div class="flex flex-col items-center text-center">
                    
                    {{-- Badge Visual Ikon WhatsApp --}}
                    <div class="relative mb-5 mt-1 animate-float">
                        <div class="absolute -inset-2 bg-gradient-to-r from-emerald-400/25 to-teal-400/25 rounded-full blur-md"></div>
                        <div class="relative w-20 h-20 sm:w-24 sm:h-24 rounded-full bg-gradient-to-br from-emerald-500 via-teal-600 to-brand-700 text-white shadow-xl shadow-emerald-600/30 flex items-center justify-center border-2 border-white/70">
                            {{-- Ikon WhatsApp dan Perisai Proteksi --}}
                            <div class="relative flex items-center justify-center">
                                <i class="fa-brands fa-whatsapp text-4xl sm:text-5xl text-white"></i>
                                <span class="absolute -bottom-1 -right-1 w-6 h-6 sm:w-7 sm:h-7 rounded-full bg-brand-600 text-white border-2 border-white flex items-center justify-center text-[10px] sm:text-xs shadow-md">
                                    <i class="fa-solid fa-shield-halved"></i>
                                </span>
                            </div>
                        </div>
                    </div>

                    {{-- Judul dan Petunjuk Verifikasi WhatsApp --}}
                    <div class="space-y-1.5 mb-6">
                        <h2 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">
                            Verifikasi Nomor WhatsApp
                        </h2>
                        <p class="text-xs sm:text-sm text-slate-500 leading-relaxed max-w-md mx-auto">
                            Masukkan 6 digit kode OTP yang telah dikirimkan ke nomor WhatsApp Anda untuk mengaktifkan hak akses internal sistem.
                        </p>
                    </div>

                    {{-- Notifikasi Sukses Kirim OTP --}}
                    @if (session('status') == 'otp-sent' || session('message'))
                        <div class="w-full mb-5 p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs sm:text-sm flex items-start gap-3 text-left transition-all shadow-xs">
                            <div class="w-6 h-6 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center shrink-0 mt-0.5">
                                <i class="fa-solid fa-check text-xs"></i>
                            </div>
                            <div class="flex-1">
                                <p class="font-bold">Kode OTP Berhasil Dikirim!</p>
                                <p class="text-xs text-emerald-700 mt-0.5">
                                    {{ session('message') ?? 'Periksa pesan masuk WhatsApp pada nomor terdaftar Anda.' }}
                                </p>
                            </div>
                        </div>
                    @endif

                    {{-- Alert Error Validasi --}}
                    @if ($errors->any())
                        <div class="w-full mb-5 p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 text-xs sm:text-sm flex items-start gap-3 text-left transition-all shadow-xs">
                            <div class="w-6 h-6 rounded-full bg-rose-100 text-rose-600 flex items-center justify-center shrink-0 mt-0.5">
                                <i class="fa-solid fa-triangle-exclamation text-xs"></i>
                            </div>
                            <div class="flex-1">
                                <p class="font-bold">Pemeriksaan Gagal</p>
                                <ul class="text-xs text-rose-700 mt-0.5 list-disc list-inside space-y-0.5">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    @endif

                    {{-- Ringkasan Nomor WhatsApp dan Aksi Ubah --}}
                    <div class="w-full bg-slate-50 border border-slate-200/80 rounded-2xl p-4 mb-6 text-left">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                            <div class="flex items-center gap-3 min-w-0">
                                <div class="w-10 h-10 rounded-full bg-emerald-100 text-emerald-700 flex items-center justify-center shrink-0 font-bold text-sm border border-emerald-200/70">
                                    <i class="fa-brands fa-whatsapp text-lg"></i>
                                </div>
                                <div class="min-w-0 flex-1">
                                    <div class="flex items-center gap-2">
                                        <span class="text-xs sm:text-sm font-bold text-slate-800 truncate">
                                            {{ auth()->user()->name }}
                                        </span>
                                    </div>
                                    <div class="flex items-center gap-1.5 text-xs text-emerald-700 font-semibold truncate mt-0.5">
                                        <i class="fa-solid fa-phone text-[11px]"></i>
                                        <span class="font-mono">{{ auth()->user()->phone_number ?? 'Nomor belum diisi' }}</span>
                                    </div>
                                </div>
                            </div>
                            <div class="shrink-0 flex items-center gap-2">
                                <button type="button"
                                    id="btn-toggle-edit-phone"
                                    class="inline-flex items-center gap-1 px-3 py-1.5 rounded-xl text-xs font-bold text-brand-600 bg-brand-50 hover:bg-brand-100 border border-brand-200/80 transition-all cursor-pointer">
                                    <i class="fa-solid fa-pen-to-square text-[10px]"></i>
                                    <span>Ganti Nomor</span>
                                </button>
                            </div>
                        </div>

                        {{-- Formulir Ubah Nomor WhatsApp (Dapat Ditutup) --}}
                        <div id="section-edit-phone" class="hidden mt-4 pt-4 border-t border-slate-200/80">
                            <form method="POST" action="{{ route('verification.phone.update') }}" class="space-y-3">
                                @csrf
                                <div>
                                    <label class="block text-xs font-bold text-slate-700 mb-1">Nomor WhatsApp Baru (Aktif):</label>
                                    <div class="flex gap-2">
                                        <input type="text"
                                            name="phone_number"
                                            value="{{ old('phone_number', auth()->user()->phone_number) }}"
                                            placeholder="Contoh: 08123456789"
                                            class="flex-1 px-3.5 py-2 text-xs sm:text-sm border border-slate-300 rounded-xl focus:outline-none focus:border-emerald-500 font-mono font-medium">
                                        <button type="submit"
                                            class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-xl shadow-sm transition-all whitespace-nowrap cursor-pointer">
                                            Kirim OTP Baru
                                        </button>
                                    </div>
                                    <p class="text-[10px] text-slate-400 mt-1">* Pastikan nomor diawali 08xx atau 62xx dan terdaftar di WhatsApp.</p>
                                </div>
                            </form>
                        </div>
                    </div>

                    {{-- Formulir Verifikasi Kode OTP --}}
                    <form method="POST" action="{{ route('verification.phone.verify') }}" class="w-full space-y-4" id="form-verify-otp">
                        @csrf
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                                Masukkan 6 Digit Kode OTP
                            </label>
                            <div class="relative">
                                <input type="text"
                                    name="otp"
                                    id="otp-input"
                                    maxlength="6"
                                    inputmode="numeric"
                                    pattern="[0-9]*"
                                    autofocus
                                    required
                                    placeholder="••••••"
                                    class="w-full py-3.5 px-4 text-center font-mono font-extrabold text-2xl sm:text-3xl tracking-[0.4em] sm:tracking-[0.6em] border-2 border-slate-200 focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10 rounded-2xl transition-all shadow-inner outline-none text-slate-800 placeholder:text-slate-300">
                            </div>
                            <p class="text-[11px] text-slate-400 mt-1.5">
                                Kode berlaku selama 5 menit sejak dikirimkan.
                            </p>
                        </div>

                        {{-- Tombol Verifikasi OTP --}}
                        <button type="submit"
                            id="btn-submit-otp"
                            class="w-full group relative flex items-center justify-center gap-2 py-3.5 px-6 rounded-2xl bg-gradient-to-r from-emerald-600 via-teal-600 to-brand-600 hover:from-emerald-500 hover:to-brand-500 text-white font-bold text-sm shadow-lg shadow-emerald-600/25 hover:shadow-emerald-600/35 active:scale-[0.99] transition-all duration-200 disabled:opacity-60 disabled:cursor-not-allowed focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2 cursor-pointer">
                            
                            <span id="btn-otp-icon">
                                <i class="fa-solid fa-circle-check text-xs"></i>
                            </span>
                            <span id="btn-otp-spinner" class="hidden">
                                <i class="fa-solid fa-circle-notch fa-spin text-xs"></i>
                            </span>
                            <span id="btn-otp-text">Verifikasi & Masuk Dashboard</span>
                        </button>
                    </form>

                    {{-- Form Kirim Ulang OTP --}}
                    <div class="w-full mt-4 pt-3 border-t border-slate-100">
                        <div class="flex flex-col sm:flex-row items-center justify-between gap-3 text-xs">
                            <span class="text-slate-500 text-center sm:text-left">
                                Tidak menerima kode di WhatsApp?
                            </span>

                            <form method="POST" action="{{ route('verification.phone.send') }}" id="form-resend-otp">
                                @csrf
                                <button type="submit"
                                    id="btn-resend-otp"
                                    class="font-bold text-emerald-700 hover:text-emerald-800 disabled:text-slate-400 disabled:cursor-not-allowed transition-colors inline-flex items-center gap-1.5 cursor-pointer">
                                    <i class="fa-solid fa-rotate-right text-xs" id="resend-icon"></i>
                                    <span id="resend-text">Kirim Ulang OTP</span>
                                </button>
                            </form>
                        </div>
                    </div>

                    {{-- Aksi Keluar Akun --}}
                    <div class="w-full mt-3 pt-3 border-t border-slate-100 flex items-center justify-between text-xs">
                        <span class="text-slate-400">
                            Bukan akun Anda?
                        </span>
                        
                        <form method="POST" action="{{ route('logout') }}" class="inline">
                            @csrf
                            <button type="submit" class="inline-flex items-center gap-1.5 font-bold text-rose-600 hover:text-rose-700 py-1 px-2.5 rounded-lg hover:bg-rose-50 transition-colors cursor-pointer">
                                <i class="fa-solid fa-arrow-right-from-bracket text-xs"></i>
                                <span>Keluar Akun</span>
                            </button>
                        </form>
                    </div>

                </div>

            </div>

            {{-- Footer Keamanan --}}
            <div class="mt-6 text-center text-xs text-slate-400 flex items-center justify-center gap-2">
                <i class="fa-solid fa-shield-halved text-[11px] text-emerald-600"></i>
                <span>Enkripsi Aman SSL 256-bit • Hak Cipta &copy; {{ date('Y') }} PT META Adhya Tirta Umbulan</span>
            </div>

        </div>
    </main>

    {{-- Logika JavaScript Verifikasi WhatsApp --}}
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            // Toggle edit phone section
            const btnToggleEditPhone = document.getElementById("btn-toggle-edit-phone");
            const sectionEditPhone = document.getElementById("section-edit-phone");

            if (btnToggleEditPhone && sectionEditPhone) {
                btnToggleEditPhone.addEventListener("click", function () {
                    sectionEditPhone.classList.toggle("hidden");
                });
            }

            // OTP Input: numbers only & auto clean
            const otpInput = document.getElementById("otp-input");
            if (otpInput) {
                otpInput.addEventListener("input", function (e) {
                    this.value = this.value.replace(/[^0-9]/g, '');
                    if (this.value.length === 6) {
                        // Auto submit when 6 digits are typed
                        // document.getElementById("form-verify-otp").submit();
                    }
                });
            }

            // Submit OTP Loading state
            const formVerify = document.getElementById("form-verify-otp");
            const btnSubmitOtp = document.getElementById("btn-submit-otp");
            const btnOtpText = document.getElementById("btn-otp-text");
            const btnOtpIcon = document.getElementById("btn-otp-icon");
            const btnOtpSpinner = document.getElementById("btn-otp-spinner");

            if (formVerify) {
                formVerify.addEventListener("submit", function () {
                    btnSubmitOtp.disabled = true;
                    btnOtpIcon.classList.add("hidden");
                    btnOtpSpinner.classList.remove("hidden");
                    btnOtpText.innerText = "Memverifikasi...";
                });
            }

            // Resend Cooldown (60 seconds)
            const formResend = document.getElementById("form-resend-otp");
            const btnResend = document.getElementById("btn-resend-otp");
            const resendText = document.getElementById("resend-text");
            const resendIcon = document.getElementById("resend-icon");
            const COOLDOWN_SECONDS = 60;

            function startCooldown(duration) {
                btnResend.disabled = true;
                resendIcon.classList.add("fa-spin");
                let timeLeft = duration;

                const timer = setInterval(function () {
                    if (timeLeft <= 0) {
                        clearInterval(timer);
                        localStorage.removeItem("otp_resend_expiry");
                        btnResend.disabled = false;
                        resendIcon.classList.remove("fa-spin");
                        resendText.innerText = "Kirim Ulang OTP";
                    } else {
                        resendText.innerText = `Tunggu (${timeLeft}s)`;
                        timeLeft--;
                    }
                }, 1000);
            }

            // Check existing cooldown on load
            const storedExpiry = localStorage.getItem("otp_resend_expiry");
            if (storedExpiry) {
                const now = Math.floor(Date.now() / 1000);
                const remaining = parseInt(storedExpiry, 10) - now;
                if (remaining > 0) {
                    startCooldown(remaining);
                } else {
                    localStorage.removeItem("otp_resend_expiry");
                }
            }

            if (formResend) {
                formResend.addEventListener("submit", function () {
                    const expiry = Math.floor(Date.now() / 1000) + COOLDOWN_SECONDS;
                    localStorage.setItem("otp_resend_expiry", expiry);
                    btnResend.disabled = true;
                    resendText.innerText = "Mengirim...";
                });
            }
        });
    </script>
</body>
</html>
