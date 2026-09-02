<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Kata Sandi Baru - PT META Adhya Tirta Umbulan</title>

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

    {{-- Header dan Navigasi Batal --}}
    <header class="relative z-10 w-full max-w-4xl mx-auto px-4 sm:px-6 pt-6 pb-2 flex items-center justify-between">
        <div class="flex items-center gap-3">
            <div class="w-11 h-11 rounded-full bg-white border border-slate-200 shadow-sm flex items-center justify-center p-1.5 shrink-0">
                <img src="{{ asset('images/iconfav.png') }}" alt="Logo PT Umbulan" class="w-full h-full object-contain rounded-full">
            </div>
            <div>
                <h1 class="text-sm sm:text-base font-bold text-slate-800 leading-tight">PT META Adhya Tirta Umbulan</h1>
                <p class="text-[11px] text-slate-500">Sistem ERP & Pembaruan Kredensial Akun</p>
            </div>
        </div>
        <a href="{{ route('login') }}" class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-600 hover:text-brand-600 px-3 py-1.5 rounded-xl border border-slate-200 hover:border-brand-200 bg-white shadow-xs transition-colors">
            <i class="fa-solid fa-arrow-left text-[11px]"></i>
            <span>Batal & Login</span>
        </a>
    </header>

    {{-- Kontainer Konten Utama --}}
    <main class="relative z-10 flex-1 flex items-center justify-center p-4 sm:p-6 my-auto">
        <div class="w-full max-w-md">

            {{-- Kartu Reset Sandi --}}
            <div class="bg-white rounded-3xl border border-slate-200/90 shadow-xl shadow-slate-200/60 overflow-hidden p-6 sm:p-8 transition-all">

                {{-- Header Ikon dan Judul --}}
                <div class="text-center mb-6">
                    <div class="mx-auto w-14 h-14 bg-gradient-to-br from-brand-500 to-indigo-600 rounded-2xl flex items-center justify-center text-white text-2xl shadow-lg shadow-brand-500/25 mb-4">
                        <i class="fa-solid fa-lock-open"></i>
                    </div>
                    <h2 class="text-2xl font-extrabold text-slate-800 tracking-tight">Buat Kata Sandi Baru</h2>
                    <p class="text-xs sm:text-sm text-slate-500 mt-1 leading-relaxed">
                        Tentukan kata sandi baru yang kuat dan aman untuk akun Anda.
                    </p>
                </div>

                {{-- Identitas Akun Karyawan --}}
                <div class="bg-slate-50 border border-slate-200/80 rounded-2xl p-4 mb-6 flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-brand-100 text-brand-700 flex items-center justify-center font-bold text-sm border border-brand-200/70 shrink-0">
                        {{ strtoupper(substr($user->name, 0, 1)) }}
                    </div>
                    <div class="min-w-0 flex-1">
                        <div class="text-xs font-bold text-slate-800 truncate">{{ $user->name }}</div>
                        <div class="text-[11px] text-slate-500 truncate font-mono">{{ $user->email }}</div>
                    </div>
                    <div class="shrink-0">
                        <span class="inline-flex items-center gap-1 text-[10px] font-bold text-emerald-700 bg-emerald-100/70 border border-emerald-200 px-2 py-0.5 rounded-full">
                            <i class="fa-solid fa-check text-[9px]"></i> Terverifikasi
                        </span>
                    </div>
                </div>

                {{-- Notifikasi Error Validasi Server --}}
                @if($errors->any())
                    <div class="mb-5 p-4 rounded-2xl border flex items-start gap-3 bg-rose-50 border-rose-200 text-rose-800 text-xs sm:text-sm shadow-xs">
                        <i class="fa-solid fa-circle-exclamation text-rose-500 text-base shrink-0 mt-0.5"></i>
                        <div class="font-medium">{{ $errors->first() }}</div>
                    </div>
                @endif

                <div id="alert-box" style="display: none;" class="mb-5 p-4 rounded-2xl border text-xs sm:text-sm flex items-start gap-3 transition-all">
                    <div id="alert-icon" class="shrink-0 mt-0.5 text-base"></div>
                    <div id="alert-message" class="font-medium"></div>
                </div>

                {{-- Formulir Pembuatan Sandi Baru --}}
                <form id="resetPasswordForm" onsubmit="handleResetPassword(event)" method="POST" action="{{ route('forgot.update') }}" class="space-y-5">
                    @csrf

                    {{-- Field Input Kata Sandi Baru --}}
                    <div>
                        <label for="password" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                            Kata Sandi Baru
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                <i class="fa-solid fa-lock text-base"></i>
                            </div>
                            <input type="password" id="password" name="password" required minlength="8" autofocus
                                class="block w-full pl-11 pr-11 py-3 sm:py-3.5 bg-slate-50 border border-slate-200 rounded-2xl text-slate-800 placeholder-slate-400 text-sm transition-all focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500 focus:bg-white"
                                placeholder="Minimal 8 karakter (huruf & angka)">
                            <button type="button" onclick="togglePasswordVisibility('password', 'toggle-icon-1')" class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-400 hover:text-slate-600 cursor-pointer">
                                <i id="toggle-icon-1" class="fa-solid fa-eye text-sm"></i>
                            </button>
                        </div>

                        {{-- Indikator Kekuatan Sandi --}}
                        <div class="mt-2 space-y-1">
                            <div class="w-full bg-slate-200 h-1.5 rounded-full overflow-hidden">
                                <div id="strength-bar" class="h-full w-0 transition-all duration-300 rounded-full bg-slate-300"></div>
                            </div>
                            <div class="flex justify-between items-center text-[10px] text-slate-400">
                                <span>Kekuatan kata sandi: <strong id="strength-text" class="text-slate-600">-</strong></span>
                                <span>Min. 8 karakter</span>
                            </div>
                        </div>
                    </div>

                    {{-- Field Konfirmasi Kata Sandi Baru --}}
                    <div>
                        <label for="password_confirmation" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                            Konfirmasi Kata Sandi Baru
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                <i class="fa-solid fa-shield-halved text-base"></i>
                            </div>
                            <input type="password" id="password_confirmation" name="password_confirmation" required minlength="8"
                                class="block w-full pl-11 pr-11 py-3 sm:py-3.5 bg-slate-50 border border-slate-200 rounded-2xl text-slate-800 placeholder-slate-400 text-sm transition-all focus:outline-none focus:ring-2 focus:ring-brand-500 focus:border-brand-500 focus:bg-white"
                                placeholder="Ulangi kata sandi baru Anda">
                            <button type="button" onclick="togglePasswordVisibility('password_confirmation', 'toggle-icon-2')" class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-400 hover:text-slate-600 cursor-pointer">
                                <i id="toggle-icon-2" class="fa-solid fa-eye text-sm"></i>
                            </button>
                        </div>
                        <p id="match-indicator" class="text-[11px] mt-1.5 hidden"></p>
                    </div>

                    {{-- Tombol Simpan Perubahan Sandi --}}
                    <button type="submit" id="btn-submit"
                        class="w-full bg-brand-600 hover:bg-brand-700 text-white font-bold py-3.5 px-4 rounded-2xl shadow-lg shadow-brand-600/25 hover:shadow-brand-600/35 transition-all active:scale-[0.98] flex items-center justify-center gap-2 cursor-pointer mt-3">
                        <span id="btn-submit-text">Perbarui Kata Sandi</span>
                        <i class="fa-solid fa-circle-check text-xs" id="btn-submit-icon"></i>
                        <i class="fa-solid fa-circle-notch fa-spin text-xs hidden" id="btn-submit-spinner"></i>
                    </button>
                </form>

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

    {{-- Logika JavaScript Reset Kata Sandi --}}
    <script>
        function togglePasswordVisibility(inputId, iconId) {
            const input = document.getElementById(inputId);
            const icon = document.getElementById(iconId);

            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }

        const passwordInput = document.getElementById('password');
        const confirmInput = document.getElementById('password_confirmation');
        const strengthBar = document.getElementById('strength-bar');
        const strengthText = document.getElementById('strength-text');
        const matchIndicator = document.getElementById('match-indicator');

        // Live Password Strength Check
        passwordInput.addEventListener('input', function() {
            const val = this.value;
            let score = 0;

            if (val.length >= 8) score++;
            if (/[A-Z]/.test(val) && /[a-z]/.test(val)) score++;
            if (/[0-9]/.test(val)) score++;
            if (/[^A-Za-z0-9]/.test(val)) score++;

            if (val.length === 0) {
                strengthBar.style.width = '0%';
                strengthBar.className = 'h-full w-0 transition-all duration-300 rounded-full bg-slate-300';
                strengthText.innerText = '-';
                strengthText.className = 'text-slate-600';
            } else if (score <= 1) {
                strengthBar.style.width = '25%';
                strengthBar.className = 'h-full transition-all duration-300 rounded-full bg-rose-500';
                strengthText.innerText = 'Lemah';
                strengthText.className = 'text-rose-600 font-bold';
            } else if (score === 2) {
                strengthBar.style.width = '50%';
                strengthBar.className = 'h-full transition-all duration-300 rounded-full bg-amber-500';
                strengthText.innerText = 'Sedang';
                strengthText.className = 'text-amber-600 font-bold';
            } else if (score === 3) {
                strengthBar.style.width = '75%';
                strengthBar.className = 'h-full transition-all duration-300 rounded-full bg-sky-500';
                strengthText.innerText = 'Kuat';
                strengthText.className = 'text-sky-600 font-bold';
            } else {
                strengthBar.style.width = '100%';
                strengthBar.className = 'h-full transition-all duration-300 rounded-full bg-emerald-500';
                strengthText.innerText = 'Sangat Kuat';
                strengthText.className = 'text-emerald-600 font-bold';
            }

            checkMatch();
        });

        // Live Match Check
        confirmInput.addEventListener('input', checkMatch);

        function checkMatch() {
            const pass = passwordInput.value;
            const confirm = confirmInput.value;

            if (!confirm) {
                matchIndicator.classList.add('hidden');
                return;
            }

            matchIndicator.classList.remove('hidden');
            if (pass === confirm) {
                matchIndicator.className = 'text-[11px] mt-1.5 text-emerald-600 font-semibold flex items-center gap-1';
                matchIndicator.innerHTML = '<i class="fa-solid fa-check text-xs"></i> Konfirmasi kata sandi cocok.';
            } else {
                matchIndicator.className = 'text-[11px] mt-1.5 text-rose-500 font-semibold flex items-center gap-1';
                matchIndicator.innerHTML = '<i class="fa-solid fa-xmark text-xs"></i> Kata sandi belum cocok.';
            }
        }

        // Form Submit Handler
        function handleResetPassword(e) {
            e.preventDefault();

            const pass = passwordInput.value;
            const confirm = confirmInput.value;

            if (pass.length < 8) {
                alert('Kata sandi harus terdiri dari minimal 8 karakter.');
                passwordInput.focus();
                return;
            }

            if (pass !== confirm) {
                alert('Konfirmasi kata sandi tidak cocok. Silakan periksa kembali.');
                confirmInput.focus();
                return;
            }

            const btn = document.getElementById('btn-submit');
            const btnText = document.getElementById('btn-submit-text');
            const btnIcon = document.getElementById('btn-submit-icon');
            const btnSpinner = document.getElementById('btn-submit-spinner');

            btn.disabled = true;
            btnText.innerText = 'Menyimpan Kata Sandi...';
            btnIcon.classList.add('hidden');
            btnSpinner.classList.remove('hidden');

            fetch("{{ route('forgot.update') }}", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "Accept": "application/json",
                    "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ password: pass, password_confirmation: confirm })
            })
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success') {
                    window.location.href = data.redirect_url;
                } else {
                    alert(data.message || 'Gagal memperbarui kata sandi.');
                    btn.disabled = false;
                    btnText.innerText = 'Perbarui Kata Sandi';
                    btnIcon.classList.remove('hidden');
                    btnSpinner.classList.add('hidden');
                }
            })
            .catch(() => {
                // Fallback to normal form submit if fetch fails
                document.getElementById('resetPasswordForm').submit();
            });
        }

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
