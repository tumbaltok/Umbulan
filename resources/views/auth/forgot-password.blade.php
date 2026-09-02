<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Lupa Kata Sandi - PT META Adhya Tirta Umbulan</title>

    {{-- Favicon Aplikasi --}}
    <link rel="icon" type="image/png" href="{{ asset('images/iconfav.png') }}">
    <link rel="shortcut icon" type="image/png" href="{{ asset('images/iconfav.png') }}">

    <script>
        // Kunci Halaman Selalu Light Mode
        document.documentElement.classList.remove('dark');
        localStorage.theme = 'light';
    </script>

    {{-- Konfigurasi Tailwind CSS --}}
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

    {{-- FontAwesome dan Google Fonts --}}
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
    </style>

    {{-- Komponen Head PWA --}}
    @pwaHead
</head>
<body class="min-h-screen bg-slate-50 text-slate-800 flex flex-col justify-between antialiased selection:bg-brand-500 selection:text-white relative overflow-x-hidden">

    {{-- Elemen Background Gradien Dekoratif --}}
    <div class="fixed inset-0 pointer-events-none overflow-hidden bg-grid-subtle z-0">
        <div class="absolute -top-32 left-1/2 -translate-x-1/2 w-[600px] h-[320px] bg-gradient-to-tr from-sky-400/15 via-teal-300/15 to-emerald-300/10 blur-3xl rounded-full"></div>
        <div class="absolute bottom-0 right-0 w-[450px] h-[300px] bg-gradient-to-tl from-brand-300/15 to-sky-200/15 blur-3xl rounded-full"></div>
    </div>

    {{-- Header dan Navigasi Kembali --}}
    <header class="relative z-10 w-full max-w-4xl mx-auto px-4 sm:px-6 pt-6 pb-2 flex items-center justify-between">
        <div class="flex items-center gap-3">
            <div class="w-11 h-11 rounded-full bg-white border border-slate-200 shadow-sm flex items-center justify-center p-1.5 shrink-0">
                <img src="{{ asset('images/iconfav.png') }}" alt="Logo PT Umbulan" class="w-full h-full object-contain rounded-full">
            </div>
            <div>
                <h1 class="text-sm sm:text-base font-bold text-slate-800 leading-tight">PT META Adhya Tirta Umbulan</h1>
                <p class="text-[11px] text-slate-500">Sistem ERP & Pemulihan Akun Kepegawaian</p>
            </div>
        </div>
        <a href="{{ route('login') }}" class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-600 hover:text-brand-600 px-3 py-1.5 rounded-xl border border-slate-200 hover:border-brand-200 bg-white shadow-xs transition-colors">
            <i class="fa-solid fa-arrow-left text-[11px]"></i>
            <span>Kembali ke Login</span>
        </a>
    </header>

    {{-- Kontainer Konten Utama --}}
    <main class="relative z-10 flex-1 flex items-center justify-center p-4 sm:p-6 my-auto">
        <div class="w-full max-w-md">

            {{-- Kartu Formulir Pemulihan --}}
            <div class="bg-white rounded-3xl border border-slate-200/90 shadow-xl shadow-slate-200/60 overflow-hidden p-6 sm:p-8 transition-all">

                {{-- Judul dan Ikon Pemulihan --}}
                <div class="text-center mb-6">
                    <div class="mx-auto w-14 h-14 bg-gradient-to-br from-brand-500 to-brand-700 rounded-2xl flex items-center justify-center text-white text-2xl shadow-lg shadow-brand-500/25 mb-4">
                        <i class="fa-solid fa-key"></i>
                    </div>
                    <h2 class="text-2xl font-extrabold text-slate-800 tracking-tight">Pemulihan Kata Sandi</h2>
                    <p class="text-xs sm:text-sm text-slate-500 mt-1 leading-relaxed" id="step-desc">
                        Masukkan Email atau Nomor WhatsApp Anda untuk mencari akun dan menerima kode OTP.
                    </p>
                </div>

                {{-- Alert Feedback Box Dinamis --}}
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

                {{-- Tahap 1: Formulir Pencarian dan Identifikasi Akun --}}
                <form id="identifyForm" onsubmit="handleIdentify(event)" class="space-y-5">
                    @csrf
                    <div>
                        <label for="identity" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                            Email atau Nomor WhatsApp
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                <i class="fa-solid fa-user-shield text-base"></i>
                            </div>
                            <input type="text" id="identity" name="identity" required autofocus
                                value="{{ old('identity') }}"
                                class="block w-full pl-11 pr-4 py-3 sm:py-3.5 bg-slate-50 border border-slate-200 rounded-2xl text-slate-800 placeholder-slate-400 text-sm transition-all focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500 focus:bg-white"
                                placeholder="nama@meta.com atau 08123456789">
                        </div>
                        <p class="text-[11px] text-slate-400 mt-1.5">
                            Gunakan Email dinas atau Nomor WhatsApp yang sudah terdaftar pada sistem.
                        </p>
                    </div>

                    <button type="submit" id="btn-identify"
                        class="w-full bg-brand-600 hover:bg-brand-700 text-white font-bold py-3.5 px-4 rounded-2xl shadow-lg shadow-brand-600/25 hover:shadow-brand-600/35 transition-all active:scale-[0.98] flex items-center justify-center gap-2 cursor-pointer">
                        <span id="btn-identify-text">Lanjutkan Pencarian Akun</span>
                        <i class="fa-solid fa-arrow-right text-xs" id="btn-identify-icon"></i>
                        <i class="fa-solid fa-circle-notch fa-spin text-xs hidden" id="btn-identify-spinner"></i>
                    </button>
                </form>

                {{-- Tahap 2: Pemilihan Saluran Pengiriman OTP (Email / WhatsApp) --}}
                <div id="channelSelectionSection" class="space-y-5 hidden">
                    {{-- Informasi Akun Karyawan Teridentifikasi --}}
                    <div class="bg-slate-50 border border-slate-200/80 rounded-2xl p-4 flex items-center justify-between">
                        <div class="flex items-center gap-3 min-w-0">
                            <div class="w-10 h-10 rounded-full bg-brand-100 text-brand-700 flex items-center justify-center font-bold text-sm border border-brand-200/70 shrink-0" id="user-avatar-initials">
                                U
                            </div>
                            <div class="min-w-0 flex-1">
                                <h3 class="text-sm font-bold text-slate-800 truncate" id="user-display-name">-</h3>
                                <p class="text-[11px] text-slate-400 truncate" id="user-display-nip">NIP: -</p>
                            </div>
                        </div>
                        <button type="button" onclick="resetToIdentify()" class="text-xs text-brand-600 hover:text-brand-700 font-semibold cursor-pointer">
                            Ganti Akun
                        </button>
                    </div>

                    <form id="sendOtpForm" onsubmit="handleSendOtp(event)" class="space-y-4">
                        @csrf
                        <input type="hidden" id="selected-user-id" name="user_id">
                        
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                                Pilih Saluran Pengiriman OTP
                            </label>

                            <div class="space-y-2.5" id="channels-container">
                                {{-- Saluran Pengiriman via Email --}}
                                <label id="channel-card-email" class="relative flex items-center gap-3 p-3.5 rounded-2xl border-2 border-brand-500 bg-brand-50/40 cursor-pointer transition-all hover:bg-brand-50/80">
                                    <input type="radio" name="channel" value="email" checked class="text-brand-600 focus:ring-brand-500 w-4 h-4">
                                    <div class="w-9 h-9 rounded-xl bg-sky-100 text-sky-600 flex items-center justify-center text-base shrink-0">
                                        <i class="fa-solid fa-envelope"></i>
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <div class="flex items-center justify-between">
                                            <span class="text-xs font-bold text-slate-800">Email Kepegawaian</span>
                                            <span class="text-[10px] font-semibold text-brand-600 bg-brand-100/60 px-2 py-0.5 rounded-full">Resmi</span>
                                        </div>
                                        <p class="text-xs text-slate-500 font-mono mt-0.5 truncate" id="masked-email-display">-</p>
                                    </div>
                                </label>

                                {{-- Saluran Pengiriman via WhatsApp --}}
                                <label id="channel-card-whatsapp" class="relative flex items-center gap-3 p-3.5 rounded-2xl border border-slate-200 hover:border-wa-500 bg-white cursor-pointer transition-all hover:bg-wa-50/30">
                                    <input type="radio" name="channel" value="whatsapp" class="text-wa-600 focus:ring-wa-500 w-4 h-4">
                                    <div class="w-9 h-9 rounded-xl bg-emerald-100 text-emerald-600 flex items-center justify-center text-base shrink-0">
                                        <i class="fa-brands fa-whatsapp"></i>
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <div class="flex items-center justify-between">
                                            <span class="text-xs font-bold text-slate-800">WhatsApp Gateway</span>
                                            <span class="text-[10px] font-semibold text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded-full">Instan</span>
                                        </div>
                                        <p class="text-xs text-slate-500 font-mono mt-0.5 truncate" id="masked-whatsapp-display">-</p>
                                    </div>
                                </label>
                            </div>
                        </div>

                        <button type="submit" id="btn-send-otp"
                            class="w-full bg-brand-600 hover:bg-brand-700 text-white font-bold py-3.5 px-4 rounded-2xl shadow-lg shadow-brand-600/25 hover:shadow-brand-600/35 transition-all active:scale-[0.98] flex items-center justify-center gap-2 cursor-pointer mt-2">
                            <span id="btn-send-otp-text">Kirim Kode OTP</span>
                            <i class="fa-solid fa-paper-plane text-xs" id="btn-send-otp-icon"></i>
                            <i class="fa-solid fa-circle-notch fa-spin text-xs hidden" id="btn-send-otp-spinner"></i>
                        </button>
                    </form>
                </div>

                {{-- Link Kembali ke Halaman Login --}}
                <div class="mt-8 pt-6 border-t border-slate-100 text-center">
                    <a href="{{ route('login') }}" class="text-xs font-semibold text-slate-500 hover:text-brand-600 transition-colors inline-flex items-center gap-1.5">
                        <i class="fa-solid fa-arrow-left text-[10px]"></i>
                        <span>Kembali ke Halaman Login</span>
                    </a>
                </div>

            </div>

            {{-- Footer Keamanan --}}
            <div class="mt-6 text-center text-xs text-slate-400 flex items-center justify-center gap-2">
                <i class="fa-solid fa-shield-halved text-[11px] text-brand-600"></i>
                <span>Enkripsi Aman SSL 256-bit &bull; Hak Cipta &copy; {{ date('Y') }} PT META Adhya Tirta Umbulan</span>
            </div>

        </div>
    </main>

    {{-- Logika JavaScript Pemulihan Akun --}}
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

        // TAHAP 1: Cari Akun
        function handleIdentify(e) {
            e.preventDefault();
            hideAlert();

            const identityInput = document.getElementById('identity').value.trim();
            const btn = document.getElementById('btn-identify');
            const btnText = document.getElementById('btn-identify-text');
            const btnIcon = document.getElementById('btn-identify-icon');
            const btnSpinner = document.getElementById('btn-identify-spinner');

            if (!identityInput) {
                showAlert('error', 'Silakan masukkan Email atau Nomor WhatsApp Anda.');
                return;
            }

            btn.disabled = true;
            btnText.innerText = 'Memeriksa Akun...';
            btnIcon.classList.add('hidden');
            btnSpinner.classList.remove('hidden');

            fetch("{{ route('forgot.identify') }}", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "Accept": "application/json",
                    "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ identity: identityInput })
            })
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success') {
                    // Update user card
                    document.getElementById('user-display-name').innerText = data.user.name;
                    document.getElementById('user-display-nip').innerText = 'NIP: ' + data.user.nip;
                    document.getElementById('user-avatar-initials').innerText = data.user.initials;
                    document.getElementById('selected-user-id').value = data.user.id;

                    // Update channels
                    const emailChannel = data.channels.find(c => c.id === 'email');
                    const waChannel = data.channels.find(c => c.id === 'whatsapp');

                    if (emailChannel) {
                        document.getElementById('masked-email-display').innerText = emailChannel.target_masked;
                    }

                    const waCard = document.getElementById('channel-card-whatsapp');
                    const waRadio = waCard.querySelector('input[type="radio"]');

                    if (waChannel && waChannel.available) {
                        document.getElementById('masked-whatsapp-display').innerText = waChannel.target_masked;
                        waCard.classList.remove('opacity-50', 'pointer-events-none', 'bg-slate-100');
                        waRadio.disabled = false;
                    } else {
                        document.getElementById('masked-whatsapp-display').innerText = 'Nomor WhatsApp belum terdaftar';
                        waCard.classList.add('opacity-50', 'pointer-events-none', 'bg-slate-100');
                        waRadio.disabled = true;
                    }

                    // Tampilkan form pemilihan channel
                    document.getElementById('identifyForm').classList.add('hidden');
                    document.getElementById('channelSelectionSection').classList.remove('hidden');
                    document.getElementById('step-desc').innerText = 'Pilih saluran pengiriman kode OTP yang Anda inginkan.';

                    showAlert('success', 'Akun ditemukan! Silakan pilih saluran pengiriman kode OTP.');
                } else {
                    showAlert('error', data.message || 'Akun tidak ditemukan. Silakan periksa kembali data Anda.');
                }
            })
            .catch(() => {
                showAlert('error', 'Terjadi kendala pada koneksi ke server. Silakan coba kembali.');
            })
            .finally(() => {
                btn.disabled = false;
                btnText.innerText = 'Lanjutkan Pencarian Akun';
                btnIcon.classList.remove('hidden');
                btnSpinner.classList.add('hidden');
            });
        }

        // TAHAP 2: Kirim OTP
        function handleSendOtp(e) {
            e.preventDefault();
            hideAlert();

            const userId = document.getElementById('selected-user-id').value;
            const channel = document.querySelector('input[name="channel"]:checked')?.value || 'email';
            const btn = document.getElementById('btn-send-otp');
            const btnText = document.getElementById('btn-send-otp-text');
            const btnIcon = document.getElementById('btn-send-otp-icon');
            const btnSpinner = document.getElementById('btn-send-otp-spinner');

            btn.disabled = true;
            btnText.innerText = 'Mengirimkan Kode OTP...';
            btnIcon.classList.add('hidden');
            btnSpinner.classList.remove('hidden');

            fetch("{{ route('forgot.send_otp') }}", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "Accept": "application/json",
                    "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ user_id: userId, channel: channel })
            })
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success') {
                    // Set countdown timer di localStorage
                    const expiry = Math.floor(Date.now() / 1000) + (data.cooldown_seconds || 60);
                    localStorage.setItem("otp_resend_expiry", expiry);

                    // Arahkan ke halaman verifikasi OTP
                    window.location.href = data.redirect_url;
                } else {
                    showAlert('error', data.message || 'Gagal mengirimkan kode OTP.');
                    btn.disabled = false;
                    btnText.innerText = 'Kirim Kode OTP';
                    btnIcon.classList.remove('hidden');
                    btnSpinner.classList.add('hidden');
                }
            })
            .catch(() => {
                showAlert('error', 'Terjadi kesalahan sistem saat mengirimkan kode OTP.');
                btn.disabled = false;
                btnText.innerText = 'Kirim Kode OTP';
                btnIcon.classList.remove('hidden');
                btnSpinner.classList.add('hidden');
            });
        }

        // Kembali ke identifikasi akun
        function resetToIdentify() {
            hideAlert();
            document.getElementById('channelSelectionSection').classList.add('hidden');
            document.getElementById('identifyForm').classList.remove('hidden');
            document.getElementById('step-desc').innerText = 'Masukkan Email atau Nomor WhatsApp Anda untuk mencari akun dan menerima kode OTP.';
            document.getElementById('identity').focus();
        }

        // Stylize radio cards on click
        document.querySelectorAll('input[name="channel"]').forEach(radio => {
            radio.addEventListener('change', function() {
                const emailCard = document.getElementById('channel-card-email');
                const waCard = document.getElementById('channel-card-whatsapp');

                if (this.value === 'email') {
                    emailCard.className = "relative flex items-center gap-3 p-3.5 rounded-2xl border-2 border-brand-500 bg-brand-50/40 cursor-pointer transition-all";
                    waCard.className = "relative flex items-center gap-3 p-3.5 rounded-2xl border border-slate-200 hover:border-wa-500 bg-white cursor-pointer transition-all";
                } else {
                    waCard.className = "relative flex items-center gap-3 p-3.5 rounded-2xl border-2 border-wa-500 bg-wa-50/40 cursor-pointer transition-all";
                    emailCard.className = "relative flex items-center gap-3 p-3.5 rounded-2xl border border-slate-200 hover:border-brand-500 bg-white cursor-pointer transition-all";
                }
            });
        });

        // BFCache Buster
        window.addEventListener('pageshow', function(event) {
            if (event.persisted || (window.performance && window.performance.getEntriesByType("navigation")[0]?.type === "back_forward")) {
                window.location.reload();
            }
        });
    </script>

    {{-- Pendaftaran Skrip PWA --}}
    @laravelPwa
    @pwaInstallButton
</body>
</html>
