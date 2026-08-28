<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Verifikasi Alamat Email | PT META Adhya Tirta Umbulan</title>

    <!-- Favicon -->
    <link rel="icon" type="image/png" href="{{ asset('images/iconfav.png') }}">
    <link rel="shortcut icon" type="image/png" href="{{ asset('images/iconfav.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/iconfav.png') }}">

    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
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

    <!-- FontAwesome & Google Fonts -->
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

    <!-- PWA Head -->
    @pwaHead
</head>
<body class="h-full bg-slate-50 text-slate-800 flex flex-col justify-between antialiased relative selection:bg-brand-500 selection:text-white">

    <!-- Ambient Glowing Background Orbs -->
    <div class="fixed inset-0 pointer-events-none overflow-hidden bg-grid-pattern z-0">
        <div class="absolute -top-32 left-1/2 -translate-x-1/2 w-[650px] h-[360px] bg-gradient-to-tr from-brand-400/25 to-cyan-300/20 blur-3xl rounded-full"></div>
        <div class="absolute bottom-0 right-0 w-[420px] h-[320px] bg-gradient-to-tl from-sky-400/15 to-emerald-300/10 blur-3xl rounded-full"></div>
    </div>

    <!-- Minimal Header: Branding Bulat Sempurna -->
    <header class="relative z-10 w-full max-w-5xl mx-auto px-4 sm:px-6 pt-6 pb-2 flex items-center justify-between">
        <div class="flex items-center gap-3">
            <!-- Logo PT Bulat Sempurna -->
            <div class="w-11 h-11 rounded-full bg-white border border-slate-200/90 shadow-sm flex items-center justify-center p-1.5 shrink-0 overflow-hidden">
                <img src="{{ asset('images/iconfav.png') }}" alt="Logo PT Umbulan" class="w-full h-full object-contain rounded-full">
            </div>
            <div>
                <h1 class="text-xs sm:text-sm font-black tracking-wider text-slate-900 uppercase">PT META Adhya Tirta Umbulan</h1>
                <p class="text-[10px] sm:text-[11px] font-semibold text-brand-600 tracking-wider">Enterprise Resource Portal</p>
            </div>
        </div>
    </header>

    <!-- Main Content Container -->
    <main class="relative z-10 flex-1 flex items-center justify-center px-4 py-8 sm:px-6 lg:px-8">
        <div class="w-full max-w-xl mx-auto">

            <!-- Card Utama Glassmorphism Light Clean -->
            <div class="bg-white/95 backdrop-blur-xl rounded-3xl border border-slate-200/90 shadow-2xl shadow-slate-200/60 p-6 sm:p-10 transition-all duration-300 relative overflow-hidden">

                <!-- Accent Water Line -->
                <div class="absolute top-0 left-0 right-0 h-1.5 bg-gradient-to-r from-brand-600 via-cyan-400 to-brand-500"></div>

                <!-- Hero Section: Visual Icon Badge -->
                <div class="flex flex-col items-center text-center">
                    
                    <!-- Layered Glowing Mail Shield Badge Bulat & Elegan -->
                    <div class="relative mb-5 mt-2 animate-float">
                        <div class="absolute -inset-2 bg-gradient-to-r from-brand-500/20 to-cyan-400/20 rounded-full blur-md"></div>
                        <div class="relative w-20 h-20 sm:w-24 sm:h-24 rounded-full bg-gradient-to-br from-brand-500 via-brand-600 to-brand-700 text-white shadow-xl shadow-brand-600/30 flex items-center justify-center border-2 border-white/60">
                            <!-- Dual Icon: Mail + Verified Shield Badge -->
                            <div class="relative flex items-center justify-center">
                                <i class="fa-solid fa-envelope-open-text text-3xl sm:text-4xl text-cyan-100"></i>
                                <span class="absolute -bottom-1 -right-1 w-6 h-6 sm:w-7 sm:h-7 rounded-full bg-emerald-500 text-white border-2 border-white flex items-center justify-center text-[10px] sm:text-xs shadow-md">
                                    <i class="fa-solid fa-check"></i>
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Title & Subtitle -->
                    <div class="space-y-2 mb-6">
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[11px] font-bold uppercase tracking-wider bg-brand-50 text-brand-700 border border-brand-200/80">
                            <span class="w-1.5 h-1.5 rounded-full bg-brand-500 animate-ping"></span>
                            Proteksi Keamanan Akun
                        </span>
                        <h2 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">
                            Verifikasi Alamat Email Anda
                        </h2>
                        <p class="text-xs sm:text-sm text-slate-500 leading-relaxed max-w-md mx-auto">
                            Untuk menjaga integritas dan keamanan data operasional perusahaan, akun Anda wajib diverifikasi sebelum dapat mengakses Dashboard dan fasilitas internal lainnya.
                        </p>
                    </div>

                    <!-- Flash Alert Status Saat Berhasil Resend Link -->
                    @if (session('status') == 'verification-link-sent' || session('message') == 'verification-link-sent' || session('status'))
                        <div class="w-full mb-6 p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs sm:text-sm flex items-start gap-3 text-left transition-all shadow-xs">
                            <div class="w-6 h-6 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center shrink-0 mt-0.5">
                                <i class="fa-solid fa-check text-xs"></i>
                            </div>
                            <div class="flex-1">
                                <p class="font-bold">Tautan Verifikasi Baru Terkirim!</p>
                                <p class="text-xs text-emerald-700 mt-0.5">
                                    Silakan periksa kotak masuk atau folder spam pada email Anda dalam beberapa saat ke depan.
                                </p>
                            </div>
                        </div>
                    @endif

                    <!-- User Information Chip (Target Email) -->
                    <div class="w-full bg-slate-50 border border-slate-200/80 rounded-2xl p-4 mb-6 text-left">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                            <div class="flex items-center gap-3 min-w-0">
                                <div class="w-10 h-10 rounded-full bg-brand-100 text-brand-700 flex items-center justify-center shrink-0 font-bold text-sm border border-brand-200/60">
                                    {{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 2)) }}
                                </div>
                                <div class="min-w-0 flex-1">
                                    <div class="flex items-center gap-2">
                                        <span class="text-xs sm:text-sm font-bold text-slate-800 truncate">
                                            {{ auth()->user()->name }}
                                        </span>
                                        @if(auth()->user()->nip)
                                            <span class="px-1.5 py-0.5 rounded text-[10px] font-mono bg-slate-200 text-slate-700">
                                                {{ auth()->user()->nip }}
                                            </span>
                                        @endif
                                    </div>
                                    <div class="flex items-center gap-1.5 text-xs text-brand-700 font-semibold truncate mt-0.5">
                                        <i class="fa-solid fa-envelope text-[11px]"></i>
                                        <span class="truncate">{{ auth()->user()->email }}</span>
                                    </div>
                                </div>
                            </div>
                            <div class="shrink-0 self-start sm:self-center">
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold bg-amber-50 text-amber-700 border border-amber-200/80">
                                    <i class="fa-solid fa-clock text-[10px]"></i>
                                    Menunggu Verifikasi
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- 3 Step Quick Guidance -->
                    <div class="w-full grid grid-cols-1 sm:grid-cols-3 gap-2.5 mb-7 text-left">
                        <div class="p-3 rounded-xl bg-white border border-slate-200/80 flex items-start gap-2.5 shadow-2xs">
                            <span class="w-5 h-5 rounded-full bg-slate-100 text-slate-700 font-bold text-[10px] flex items-center justify-center shrink-0 mt-0.5">1</span>
                            <p class="text-[11px] text-slate-600 leading-tight">Buka inbox email atau folder spam/promosi Anda.</p>
                        </div>
                        <div class="p-3 rounded-xl bg-white border border-slate-200/80 flex items-start gap-2.5 shadow-2xs">
                            <span class="w-5 h-5 rounded-full bg-slate-100 text-slate-700 font-bold text-[10px] flex items-center justify-center shrink-0 mt-0.5">2</span>
                            <p class="text-[11px] text-slate-600 leading-tight">Klik tombol <strong>"Verify Email Address"</strong> pada email resmi.</p>
                        </div>
                        <div class="p-3 rounded-xl bg-white border border-slate-200/80 flex items-start gap-2.5 shadow-2xs">
                            <span class="w-5 h-5 rounded-full bg-slate-100 text-slate-700 font-bold text-[10px] flex items-center justify-center shrink-0 mt-0.5">3</span>
                            <p class="text-[11px] text-slate-600 leading-tight">Sistem otomatis mengarahkan Anda ke Dashboard ERP.</p>
                        </div>
                    </div>

                    <!-- Call To Action Actions -->
                    <div class="w-full space-y-3">

                        <!-- Primary CTA: Resend Email -->
                        <form method="POST" action="{{ route('verification.send') }}" id="resend-email-form" class="w-full">
                            @csrf
                            <button type="submit"
                                id="btn-resend-email"
                                class="w-full group relative flex items-center justify-center gap-2 py-3.5 px-6 rounded-2xl bg-gradient-to-r from-brand-600 to-brand-700 hover:from-brand-500 hover:to-brand-600 text-white font-bold text-sm shadow-lg shadow-brand-600/25 hover:shadow-brand-600/35 active:scale-[0.99] transition-all duration-200 disabled:opacity-60 disabled:cursor-not-allowed disabled:shadow-none focus:outline-hidden focus:ring-2 focus:ring-brand-500 focus:ring-offset-2">
                                
                                <span id="btn-icon" class="transition-transform group-hover:rotate-45">
                                    <i class="fa-solid fa-paper-plane text-xs"></i>
                                </span>
                                <span id="btn-spinner" class="hidden">
                                    <i class="fa-solid fa-circle-notch fa-spin text-xs"></i>
                                </span>
                                <span id="btn-text">Kirim Ulang Email Verifikasi</span>
                            </button>
                        </form>

                        <!-- Secondary Actions: Logout -->
                        <div class="pt-3 border-t border-slate-100 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs">
                            <span class="text-slate-500 text-center sm:text-left">
                                Salah memasukkan alamat email?
                            </span>
                            
                            <!-- Logout Button -->
                            <form method="POST" action="{{ route('logout') }}" class="inline">
                                @csrf
                                <button type="submit" class="inline-flex items-center gap-1.5 font-bold text-rose-600 hover:text-rose-700 py-1.5 px-3 rounded-xl hover:bg-rose-50 transition-colors cursor-pointer">
                                    <i class="fa-solid fa-arrow-right-from-bracket text-xs"></i>
                                    <span>Keluar / Ganti Akun</span>
                                </button>
                            </form>
                        </div>

                    </div>

                </div>

            </div>

            <!-- Security Footer Note -->
            <div class="mt-6 text-center text-xs text-slate-400 flex items-center justify-center gap-2">
                <i class="fa-solid fa-shield-halved text-[11px] text-brand-500"></i>
                <span>Enkripsi Aman SSL 256-bit • Hak Cipta &copy; {{ date('Y') }} PT META Adhya Tirta Umbulan</span>
            </div>

        </div>
    </main>

    <!-- Page Scripts -->
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            // Resend Form Cooldown & Loading State
            const form = document.getElementById("resend-email-form");
            const btnResend = document.getElementById("btn-resend-email");
            const btnText = document.getElementById("btn-text");
            const btnIcon = document.getElementById("btn-icon");
            const btnSpinner = document.getElementById("btn-spinner");
            const COOLDOWN_SECONDS = 60;

            function startCooldown(duration) {
                btnResend.disabled = true;
                btnSpinner.classList.add("hidden");
                btnIcon.classList.remove("hidden");
                let timeLeft = duration;

                const timer = setInterval(function () {
                    if (timeLeft <= 0) {
                        clearInterval(timer);
                        localStorage.removeItem("email_resend_expiry");
                        btnResend.disabled = false;
                        btnText.innerText = "Kirim Ulang Email Verifikasi";
                        btnIcon.innerHTML = '<i class="fa-solid fa-paper-plane text-xs"></i>';
                    } else {
                        btnText.innerText = `Tunggu (${timeLeft}s) untuk Kirim Ulang`;
                        btnIcon.innerHTML = '<i class="fa-solid fa-clock text-xs"></i>';
                        timeLeft--;
                    }
                }, 1000);
            }

            // Check existing cooldown on load
            const storedExpiry = localStorage.getItem("email_resend_expiry");
            if (storedExpiry) {
                const now = Math.floor(Date.now() / 1000);
                const remaining = parseInt(storedExpiry, 10) - now;
                if (remaining > 0) {
                    startCooldown(remaining);
                } else {
                    localStorage.removeItem("email_resend_expiry");
                }
            }

            // Handle submit
            if (form) {
                form.addEventListener("submit", function () {
                    const expiry = Math.floor(Date.now() / 1000) + COOLDOWN_SECONDS;
                    localStorage.setItem("email_resend_expiry", expiry);

                    btnResend.disabled = true;
                    btnIcon.classList.add("hidden");
                    btnSpinner.classList.remove("hidden");
                    btnText.innerText = "Mengirim Tautan...";
                });
            }
        });
    </script>
</body>
</html>
