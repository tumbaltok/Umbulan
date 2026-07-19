<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistem Informasi Cuti Karyawan - PT.META</title>
    <link rel="icon" type="image/png" href="{{ asset('images/iconfav.png') }}">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght=300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
        }
        /* Custom animation for floating elements */
        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
        }
        .float-animation {
            animation: float 4s ease-in-out infinite;
        }
        /* Custom wave style */
        .wave-bg {
            background: linear-gradient(135deg, #0284c7 0%, #0369a1 50%, #075985 100%);
        }
    </style>
    <!-- PWA Head -->
    @pwaHead
</head>
<body class="bg-slate-50 min-h-screen flex items-center justify-center p-3 sm:p-6 md:p-8 overflow-x-hidden">

    <div class="bg-white w-full max-w-5xl rounded-3xl shadow-2xl overflow-hidden flex flex-col md:flex-row h-auto md:min-h-[600px] transition-all duration-300 my-4 md:my-0">

        <div class="w-full md:w-1/2 wave-bg text-white p-6 sm:p-8 md:p-12 flex flex-col justify-between relative overflow-hidden shrink-0">
            <div class="absolute bottom-0 left-0 right-0 opacity-15 pointer-events-none">
                <svg viewBox="0 0 1440 320" xmlns="http://www.w3.org/2000/svg">
                    <path fill="#ffffff" fill-opacity="1" d="M0,96L48,112C96,128,192,160,288,186.7C384,213,480,235,576,213.3C672,192,768,128,864,117.3C960,107,1056,149,1152,154.7C1248,160,1344,128,1392,112L1440,96L1440,320L1392,320C1344,320,1248,320,1152,320C1056,320,960,320,864,320C768,320,672,320,576,320C480,320,384,320,288,320C192,320,96,320,48,320L0,320Z"></path>
                </svg>
            </div>

            <div class="z-10 flex items-center space-x-3">
                <div class="bg-white/20 p-1 rounded-full backdrop-blur-md border border-white/20 w-10 h-10 sm:w-12 sm:h-12 flex items-center justify-center overflow-hidden shrink-0">
                    <img src="{{ asset('images/iconfav.png') }}" alt="Logo" class="w-full h-full object-cover rounded-full">
                </div>

                <div>
                    <h2 class="font-bold tracking-wide text-xs sm:text-sm text-cyan-200">META ADHYA TIRTA UMBULAN</h2>
                    <p class="text-[9px] sm:text-[10px] text-white/70 uppercase tracking-widest font-semibold">Penyaluran Air Bersih</p>
                </div>
            </div>

            <div class="my-auto py-8 z-10 hidden md:flex flex-col items-start text-left">
                <div class="float-animation mb-6">
                    <svg class="w-56 h-56 text-cyan-100" viewBox="0 0 200 200" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M40 100H160" stroke="currentColor" stroke-width="4" stroke-linecap="round" stroke-dasharray="8 8"/>
                        <path d="M100 40V160" stroke="currentColor" stroke-width="4" stroke-linecap="round" stroke-dasharray="8 8"/>
                        <circle cx="100" cy="100" r="30" fill="#0c4a6e" stroke="currentColor" stroke-width="4"/>
                        <circle cx="100" cy="100" r="15" fill="#38bdf8"/>
                        <circle cx="40" cy="100" r="8" fill="currentColor"/>
                        <circle cx="160" cy="100" r="8" fill="currentColor"/>
                        <circle cx="100" cy="40" r="8" fill="currentColor"/>
                        <circle cx="100" cy="160" r="8" fill="currentColor"/>
                    </svg>
                </div>
                <h1 class="text-3xl font-extrabold leading-tight mb-3">Sistem Informasi Pengajuan Cuti Karyawan</h1>
                <p class="text-white/80 text-sm max-w-sm font-light">
                    Kelola kehadiran dan pengajuan izin cuti Anda dengan cepat, efisien, demi menjaga kelancaran distribusi air bersih masyarakat.
                </p>
            </div>

            <div class="z-10 text-[10px] sm:text-xs text-white/50 hidden md:block">
                &copy; <?= date('Y') ?> PT Meta Adhya Tirta Umbulan. All rights reserved.
            </div>
        </div>

        <div class="w-full md:w-1/2 p-5 sm:p-10 md:p-12 flex flex-col justify-between bg-white">

            <div class="w-full my-auto">
                <div class="mb-6">
                    <h2 class="text-2xl sm:text-3xl font-bold text-slate-800 tracking-tight">Selamat Datang</h2>
                    <p class="text-slate-500 text-xs sm:text-sm mt-1">Silakan masuk menggunakan akun kepegawaian Anda.</p>
                </div>

                @if ($errors->any())
                    <div class="mb-6 p-4 rounded-2xl border flex items-center space-x-3 bg-rose-50 border-rose-200 text-rose-800 animate-fade-in">
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

                <form id="loginForm" class="space-y-5" onsubmit="handleLogin(event)" method="POST" action="/login">
                    <?php echo csrf_field(); ?>

                    <div>
                        <label for="employee-id" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-2">EMAIL</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                <i class="fa-solid fa-id-card text-base"></i>
                            </div>
                            <input type="email" id="employee-id" name="email" required
                                value="{{ old('email') }}"
                                class="block w-full pl-11 pr-4 py-3 sm:py-3.5 bg-slate-50 border border-slate-200 rounded-2xl text-slate-800 placeholder-slate-400 text-sm transition-all focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-sky-500 focus:bg-white"
                                placeholder="Contoh: nama@meta.com">
                        </div>
                    </div>

                    <div>
                        <div class="flex justify-between items-center mb-2">
                            <label for="password" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider">Kata Sandi</label>
                            <a href="{{ route('forgot') }}" class="text-xs font-medium text-sky-600 hover:text-sky-700 transition-colors">
                                Lupa kata sandi?
                            </a>
                        </div>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                <i class="fa-solid fa-lock text-base"></i>
                            </div>
                            <input type="password" id="password" name="password" required
                                class="block w-full pl-11 pr-11 py-3 sm:py-3.5 bg-slate-50 border border-slate-200 rounded-2xl text-slate-800 placeholder-slate-400 text-sm transition-all focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-sky-500 focus:bg-white"
                                placeholder="Masukkan kata sandi Anda">
                            <button type="button" onclick="togglePasswordVisibility()" class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-600">
                                <i id="password-toggle-icon" class="fa-solid fa-eye text-sm"></i>
                            </button>
                        </div>
                    </div>

                    <div class="flex items-center">
                        <input id="remember-me" name="remember" type="checkbox"
                            class="h-4.5 w-4.5 text-sky-600 focus:ring-sky-500 border-slate-300 rounded-lg cursor-pointer">
                        <label for="remember-me" class="ml-2 block text-sm text-slate-600 select-none cursor-pointer">
                            Ingat saya di perangkat ini
                        </label>
                    </div>

                    <button type="submit" id="submit-btn"
                        class="w-full bg-sky-600 hover:bg-sky-700 text-white font-semibold py-3.5 px-4 rounded-2xl shadow-lg shadow-sky-100 hover:shadow-xl hover:shadow-sky-200 transition-all active:scale-[0.98] flex items-center justify-center space-x-2">
                        <span>Masuk ke Portal Cuti</span>
                        <i class="fa-solid fa-arrow-right text-xs"></i>
                    </button>
                </form>

                <div class="mt-8 pt-6 border-t border-slate-100 flex flex-col sm:flex-row items-center justify-between gap-4">
                    <div class="flex items-center space-x-2 text-slate-500 text-xs">
                        <i class="fa-solid fa-circle-question text-sky-500 text-sm"></i>
                        <span>Butuh bantuan akses login?</span>
                    </div>
                    <a href="http://wa.me/+6281131132067" target="blank" class="text-xs font-semibold text-sky-600 hover:text-sky-700 border border-sky-100 hover:border-sky-200 bg-sky-50/50 hover:bg-sky-50 px-3 py-1.5 rounded-xl transition-colors">
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
            event.preventDefault();

            const employeeId = document.getElementById('employee-id').value;
            const password = document.getElementById('password').value;
            const submitBtn = document.getElementById('submit-btn');
            const notification = document.getElementById('notification');
            const notifIcon = document.getElementById('notif-icon');
            const notifMessage = document.getElementById('notif-message');

            notification.style.display = 'none';

            if (employeeId.trim().length < 4 || password.length < 4) {
                notification.style.display = 'flex';
                notification.className = "mb-6 p-4 rounded-2xl border flex items-center space-x-3 bg-rose-50 border-rose-200 text-rose-800";
                notifIcon.innerHTML = `<i class="fa-solid fa-circle-exclamation text-rose-500 text-lg"></i>`;
                notifMessage.innerText = "Format email atau password terlalu pendek.";
                return;
            }

            submitBtn.disabled = true;
            submitBtn.innerHTML = `
                <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <span>Memproses Autentikasi...</span>
            `;

            document.getElementById('loginForm').submit();
        }
    </script>
    <!-- PWA Script Registrations & Tools -->
    @laravelPwa
    @pwaUpdateNotifier
    @pwaInstallButton
</body>
</html>
