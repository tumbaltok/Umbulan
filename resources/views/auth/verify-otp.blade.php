<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Verifikasi Kode OTP - PT META Adhya Tirta Umbulan</title>

    <!-- Favicon -->
    <link rel="icon" type="image/png" href="{{ asset('images/iconfav.png') }}">
    <link rel="shortcut icon" type="image/png" href="{{ asset('images/iconfav.png') }}">

    <!-- Kunci Halaman Selalu Light Mode -->
    <script>
        document.documentElement.classList.remove('dark');
        localStorage.theme = 'light';
    </script>

    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['"Plus Jakarta Sans"', 'ui-sans-serif', 'system-ui', 'sans-serif'],
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
                        },
                        wa: {
                            50: '#f0fdf4',
                            100: '#dcfce7',
                            500: '#22c55e',
                            600: '#16a34a',
                            700: '#15803d',
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
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
        }
        .bg-grid-subtle {
            background-size: 32px 32px;
            background-image: 
                linear-gradient(to right, rgba(14, 165, 233, 0.04) 1px, transparent 1px),
                linear-gradient(to bottom, rgba(14, 165, 233, 0.04) 1px, transparent 1px);
        }
        /* Hide arrows on number inputs */
        input::-webkit-outer-spin-button,
        input::-webkit-inner-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }
        input[type=number] {
            -moz-appearance: textfield;
        }
    </style>

    <!-- PWA Head -->
    @pwaHead
</head>
<body class="min-h-screen bg-slate-50 text-slate-800 flex flex-col justify-between antialiased selection:bg-brand-500 selection:text-white relative overflow-x-hidden">

    <!-- Ambient Gradient Background Elements -->
    <div class="fixed inset-0 pointer-events-none overflow-hidden bg-grid-subtle z-0">
        <div class="absolute -top-32 left-1/2 -translate-x-1/2 w-[600px] h-[320px] bg-gradient-to-tr from-sky-400/15 via-teal-300/15 to-emerald-300/10 blur-3xl rounded-full"></div>
        <div class="absolute bottom-0 right-0 w-[450px] h-[300px] bg-gradient-to-tl from-brand-300/15 to-sky-200/15 blur-3xl rounded-full"></div>
    </div>

    <!-- Header / Branding -->
    <header class="relative z-10 w-full max-w-4xl mx-auto px-4 sm:px-6 pt-6 pb-2 flex items-center justify-between">
        <div class="flex items-center gap-3">
            <div class="w-11 h-11 rounded-full bg-white border border-slate-200 shadow-sm flex items-center justify-center p-1.5 shrink-0">
                <img src="{{ asset('images/iconfav.png') }}" alt="Logo PT Umbulan" class="w-full h-full object-contain rounded-full">
            </div>
            <div>
                <h1 class="text-sm sm:text-base font-bold text-slate-800 leading-tight">PT META Adhya Tirta Umbulan</h1>
                <p class="text-[11px] text-slate-500">Sistem ERP & Verifikasi Keamanan OTP</p>
            </div>
        </div>
        <a href="{{ route('forgot') }}" class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-600 hover:text-brand-600 px-3 py-1.5 rounded-xl border border-slate-200 hover:border-brand-200 bg-white shadow-xs transition-colors">
            <i class="fa-solid fa-arrow-left text-[11px]"></i>
            <span>Ubah Saluran</span>
        </a>
    </header>

    <!-- Main Content Container -->
    <main class="relative z-10 flex-1 flex items-center justify-center p-4 sm:p-6 my-auto">
        <div class="w-full max-w-md">

            <!-- Card Utama -->
            <div class="bg-white rounded-3xl border border-slate-200/90 shadow-xl shadow-slate-200/60 overflow-hidden p-6 sm:p-8 transition-all">

                <!-- Header Icon & Title -->
                <div class="text-center mb-6">
                    <div class="mx-auto w-14 h-14 bg-gradient-to-br from-emerald-500 to-teal-700 rounded-2xl flex items-center justify-center text-white text-2xl shadow-lg shadow-emerald-500/25 mb-4">
                        <i class="fa-solid fa-shield-halved"></i>
                    </div>
                    <h2 class="text-2xl font-extrabold text-slate-800 tracking-tight">Verifikasi Kode OTP</h2>
                    <p class="text-xs sm:text-sm text-slate-500 mt-1 leading-relaxed">
                        Masukkan 6 digit kode verifikasi yang telah dikirimkan ke akun Anda.
                    </p>
                </div>

                <!-- Info Saluran Pengiriman Terpilih -->
                <div class="w-full bg-slate-50 border border-slate-200/90 rounded-2xl p-4 mb-6 text-left">
                    <div class="flex items-center justify-between gap-3">
                        <div class="flex items-center gap-3 min-w-0">
                            @if($channel === 'whatsapp')
                                <div class="w-10 h-10 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center shrink-0 text-lg border border-emerald-200">
                                    <i class="fa-brands fa-whatsapp"></i>
                                </div>
                                <div class="min-w-0 flex-1">
                                    <div class="text-[11px] font-bold text-emerald-700 uppercase tracking-wider">WhatsApp Gateway</div>
                                    <div class="text-xs sm:text-sm font-bold text-slate-800 font-mono truncate mt-0.5">{{ $targetMasked }}</div>
                                </div>
                            @else
                                <div class="w-10 h-10 rounded-full bg-sky-100 text-brand-600 flex items-center justify-center shrink-0 text-base border border-brand-200">
                                    <i class="fa-solid fa-envelope"></i>
                                </div>
                                <div class="min-w-0 flex-1">
                                    <div class="text-[11px] font-bold text-brand-700 uppercase tracking-wider">Email Kepegawaian</div>
                                    <div class="text-xs sm:text-sm font-bold text-slate-800 font-mono truncate mt-0.5">{{ $targetMasked }}</div>
                                </div>
                            @endif
                        </div>
                        <a href="{{ route('forgot') }}" class="text-xs font-semibold text-brand-600 hover:text-brand-700 whitespace-nowrap">
                            Ganti
                        </a>
                    </div>
                </div>

                <!-- Flash Alert Messages -->
                <div id="alert-box" style="display: none;" class="mb-5 p-4 rounded-2xl border text-xs sm:text-sm flex items-start gap-3 transition-all">
                    <div id="alert-icon" class="shrink-0 mt-0.5 text-base"></div>
                    <div id="alert-message" class="font-medium"></div>
                </div>

                @if($errors->any())
                    <div class="mb-5 p-4 rounded-2xl border flex items-start gap-3 bg-rose-50 border-rose-200 text-rose-800 text-xs sm:text-sm shadow-xs">
                        <i class="fa-solid fa-circle-exclamation text-rose-500 text-base shrink-0 mt-0.5"></i>
                        <div class="font-medium">{{ $errors->first() }}</div>
                    </div>
                @endif

                @if(session('success'))
                    <div class="mb-5 p-4 rounded-2xl border flex items-start gap-3 bg-emerald-50 border-emerald-200 text-emerald-800 text-xs sm:text-sm shadow-xs">
                        <i class="fa-solid fa-circle-check text-emerald-500 text-base shrink-0 mt-0.5"></i>
                        <div class="font-medium">{{ session('success') }}</div>
                    </div>
                @endif

                <!-- Form Verifikasi OTP Utama -->
                <form id="verifyOtpForm" onsubmit="handleVerifyOtp(event)" method="POST" action="{{ route('forgot.verify_otp') }}" class="space-y-6">
                    @csrf
                    <input type="hidden" name="otp" id="otp-complete-value" required>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider text-center mb-3">
                            6 Digit Kode OTP
                        </label>

                        <!-- 6 Kotak Input OTP Interaktif -->
                        <div class="flex justify-center items-center gap-2 sm:gap-2.5" id="otp-container">
                            @for($i = 0; $i < 6; $i++)
                                <input type="text"
                                    inputmode="numeric"
                                    pattern="[0-9]*"
                                    maxlength="1"
                                    data-index="{{ $i }}"
                                    id="otp-box-{{ $i }}"
                                    class="otp-box w-11 h-13 sm:w-12 sm:h-14 text-center font-mono font-extrabold text-2xl text-slate-800 bg-slate-50 border-2 border-slate-200 rounded-2xl focus:border-brand-500 focus:bg-white focus:ring-4 focus:ring-brand-500/10 outline-none transition-all shadow-xs"
                                    autocomplete="off"
                                    {{ $i === 0 ? 'autofocus' : '' }}>
                            @endfor
                        </div>

                        <p class="text-[11px] text-slate-400 text-center mt-2.5">
                            ⏳ Kode berlaku selama <strong>5 menit</strong> sejak dikirimkan.
                        </p>
                    </div>

                    <!-- Tombol Verifikasi -->
                    <button type="submit" id="btn-verify"
                        class="w-full bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-3.5 px-4 rounded-2xl shadow-lg shadow-emerald-600/25 hover:shadow-emerald-600/35 transition-all active:scale-[0.98] flex items-center justify-center gap-2 cursor-pointer">
                        <span id="btn-verify-text">Verifikasi Kode OTP</span>
                        <i class="fa-solid fa-circle-check text-xs" id="btn-verify-icon"></i>
                        <i class="fa-solid fa-circle-notch fa-spin text-xs hidden" id="btn-verify-spinner"></i>
                    </button>
                </form>

                <!-- Kirim Ulang OTP & Countdown Timer -->
                <div class="mt-6 pt-4 border-t border-slate-100 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs">
                    <span class="text-slate-500 text-center sm:text-left">
                        Tidak menerima kode?
                    </span>

                    <button type="button"
                        id="btn-resend"
                        onclick="handleResendOtp()"
                        class="font-bold text-brand-600 hover:text-brand-700 disabled:text-slate-400 disabled:cursor-not-allowed transition-colors inline-flex items-center gap-1.5 cursor-pointer">
                        <i class="fa-solid fa-rotate-right text-xs" id="resend-icon"></i>
                        <span id="resend-text">Kirim Ulang OTP</span>
                    </button>
                </div>

                <!-- Footer Link Back to Login -->
                <div class="mt-6 pt-4 border-t border-slate-100 text-center">
                    <a href="{{ route('login') }}" class="text-xs font-semibold text-slate-500 hover:text-brand-600 transition-colors inline-flex items-center gap-1.5">
                        <i class="fa-solid fa-arrow-left text-[10px]"></i>
                        <span>Batalkan & Kembali ke Login</span>
                    </a>
                </div>

            </div>

            <!-- Security Footer Note -->
            <div class="mt-6 text-center text-xs text-slate-400 flex items-center justify-center gap-2">
                <i class="fa-solid fa-shield-halved text-[11px] text-emerald-600"></i>
                <span>Enkripsi Aman SSL 256-bit &bull; Hak Cipta &copy; {{ date('Y') }} PT META Adhya Tirta Umbulan</span>
            </div>

        </div>
    </main>

    <!-- Page JavaScript Logic -->
    <script>
        const alertBox = document.getElementById('alert-box');
        const alertIcon = document.getElementById('alert-icon');
        const alertMessage = document.getElementById('alert-message');

        function showAlert(type, message) {
            alertBox.style.display = 'flex';
            if (type === 'success') {
                alertBox.className = "mb-5 p-4 rounded-2xl border flex items-start gap-3 bg-emerald-50 border-emerald-200 text-emerald-800 text-xs sm:text-sm shadow-xs";
                alertIcon.innerHTML = `<i class="fa-solid fa-circle-check text-emerald-500 text-base"></i>`;
            } else {
                alertBox.className = "mb-5 p-4 rounded-2xl border flex items-start gap-3 bg-rose-50 border-rose-200 text-rose-800 text-xs sm:text-sm shadow-xs";
                alertIcon.innerHTML = `<i class="fa-solid fa-circle-exclamation text-rose-500 text-base"></i>`;
            }
            alertMessage.innerText = message;
        }

        function hideAlert() {
            alertBox.style.display = 'none';
        }

        // ==========================================================
        // 6-BOX OTP LOGIC (Auto-Focus, Backspace, Paste Handling)
        // ==========================================================
        const otpBoxes = Array.from(document.querySelectorAll('.otp-box'));
        const otpCompleteInput = document.getElementById('otp-complete-value');

        function updateCompleteOtp() {
            const val = otpBoxes.map(b => b.value).join('');
            otpCompleteInput.value = val;
            return val;
        }

        otpBoxes.forEach((box, index) => {
            // Typing input
            box.addEventListener('input', (e) => {
                const val = e.target.value.replace(/[^0-9]/g, '');
                e.target.value = val ? val.slice(-1) : '';

                const completeVal = updateCompleteOtp();

                if (e.target.value && index < otpBoxes.length - 1) {
                    otpBoxes[index + 1].focus();
                }

                // Auto submit jika sudah 6 digit terisi
                if (completeVal.length === 6) {
                    // Optional: trigger verify
                }
            });

            // Keydown (Backspace & Arrow Navigation)
            box.addEventListener('keydown', (e) => {
                if (e.key === 'Backspace') {
                    if (!box.value && index > 0) {
                        otpBoxes[index - 1].focus();
                        otpBoxes[index - 1].value = '';
                        updateCompleteOtp();
                    } else {
                        box.value = '';
                        updateCompleteOtp();
                    }
                } else if (e.key === 'ArrowLeft' && index > 0) {
                    otpBoxes[index - 1].focus();
                } else if (e.key === 'ArrowRight' && index < otpBoxes.length - 1) {
                    otpBoxes[index + 1].focus();
                }
            });

            // Focus styling
            box.addEventListener('focus', () => {
                box.select();
            });

            // Paste Handling
            box.addEventListener('paste', (e) => {
                e.preventDefault();
                const pastedData = (e.clipboardData || window.clipboardData).getData('text');
                const cleanDigits = pastedData.replace(/[^0-9]/g, '').slice(0, 6);

                if (cleanDigits) {
                    cleanDigits.split('').forEach((char, i) => {
                        if (otpBoxes[i]) {
                            otpBoxes[i].value = char;
                        }
                    });

                    updateCompleteOtp();

                    const nextIndex = Math.min(cleanDigits.length, otpBoxes.length - 1);
                    otpBoxes[nextIndex].focus();
                }
            });
        });

        // Verifikasi Form Submit (AJAX)
        function handleVerifyOtp(e) {
            e.preventDefault();
            hideAlert();

            const otpVal = updateCompleteOtp();
            if (otpVal.length !== 6) {
                showAlert('error', 'Silakan masukkan 6 digit angka kode OTP secara lengkap.');
                const firstEmpty = otpBoxes.find(b => !b.value);
                if (firstEmpty) firstEmpty.focus();
                return;
            }

            const btn = document.getElementById('btn-verify');
            const btnText = document.getElementById('btn-verify-text');
            const btnIcon = document.getElementById('btn-verify-icon');
            const btnSpinner = document.getElementById('btn-verify-spinner');

            btn.disabled = true;
            btnText.innerText = 'Memverifikasi...';
            btnIcon.classList.add('hidden');
            btnSpinner.classList.remove('hidden');

            fetch("{{ route('forgot.verify_otp') }}", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "Accept": "application/json",
                    "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ otp: otpVal })
            })
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success') {
                    window.location.href = data.redirect_url;
                } else {
                    showAlert('error', data.message || 'Kode OTP salah.');
                    btn.disabled = false;
                    btnText.innerText = 'Verifikasi Kode OTP';
                    btnIcon.classList.remove('hidden');
                    btnSpinner.classList.add('hidden');
                    otpBoxes.forEach(b => b.value = '');
                    updateCompleteOtp();
                    otpBoxes[0].focus();
                }
            })
            .catch(() => {
                showAlert('error', 'Terjadi kesalahan sistem saat memvalidasi kode OTP.');
                btn.disabled = false;
                btnText.innerText = 'Verifikasi Kode OTP';
                btnIcon.classList.remove('hidden');
                btnSpinner.classList.add('hidden');
            });
        }

        // ==========================================================
        // COUNTDOWN TIMER & RESEND OTP LOGIC (PERSISTENT VIA LOCALSTORAGE)
        // ==========================================================
        const btnResend = document.getElementById('btn-resend');
        const resendText = document.getElementById('resend-text');
        const resendIcon = document.getElementById('resend-icon');
        const serverCooldown = {{ (int) $cooldownSeconds }};

        let timerInterval = null;

        function startCooldown(duration) {
            if (timerInterval) clearInterval(timerInterval);

            btnResend.disabled = true;
            resendIcon.classList.add('fa-spin');
            let timeLeft = duration;

            timerInterval = setInterval(() => {
                if (timeLeft <= 0) {
                    clearInterval(timerInterval);
                    localStorage.removeItem('otp_resend_expiry');
                    btnResend.disabled = false;
                    resendIcon.classList.remove('fa-spin');
                    resendText.innerText = 'Kirim Ulang OTP';
                } else {
                    resendText.innerText = `Tunggu (${timeLeft}s)`;
                    timeLeft--;
                }
            }, 1000);
        }

        // Check cooldown on page load
        const storedExpiry = localStorage.getItem('otp_resend_expiry');
        const nowSec = Math.floor(Date.now() / 1000);

        if (storedExpiry && parseInt(storedExpiry, 10) > nowSec) {
            startCooldown(parseInt(storedExpiry, 10) - nowSec);
        } else if (serverCooldown > 0) {
            localStorage.setItem('otp_resend_expiry', nowSec + serverCooldown);
            startCooldown(serverCooldown);
        }

        function handleResendOtp() {
            hideAlert();

            btnResend.disabled = true;
            resendText.innerText = 'Mengirimkan...';
            resendIcon.classList.add('fa-spin');

            fetch("{{ route('forgot.resend_otp') }}", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "Accept": "application/json",
                    "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({})
            })
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success') {
                    showAlert('success', data.message || 'Kode OTP baru berhasil dikirimkan.');
                    const expiry = Math.floor(Date.now() / 1000) + 60;
                    localStorage.setItem('otp_resend_expiry', expiry);
                    startCooldown(60);

                    // Bersihkan input
                    otpBoxes.forEach(b => b.value = '');
                    updateCompleteOtp();
                    otpBoxes[0].focus();
                } else {
                    showAlert('error', data.message || 'Gagal mengirim ulang kode OTP.');
                    btnResend.disabled = false;
                    resendIcon.classList.remove('fa-spin');
                    resendText.innerText = 'Kirim Ulang OTP';
                }
            })
            .catch(() => {
                showAlert('error', 'Terjadi kesalahan sistem saat menghubungi server.');
                btnResend.disabled = false;
                resendIcon.classList.remove('fa-spin');
                resendText.innerText = 'Kirim Ulang OTP';
            });
        }

        // BFCache Buster
        window.addEventListener('pageshow', function(event) {
            if (event.persisted || (window.performance && window.performance.getEntriesByType("navigation")[0]?.type === "back_forward")) {
                window.location.reload();
            }
        });
    </script>

    <!-- PWA Scripts -->
    @laravelPwa
    @pwaInstallButton
</body>
</html>
