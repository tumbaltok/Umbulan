<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistem Informasi Cuti Karyawan - Distribusi Air Bersih</title>
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
</head>
<body class="bg-slate-50 min-h-screen flex items-center justify-center p-4 sm:p-6 md:p-8 overflow-x-hidden">

    <!-- Main Container -->
    <div class="bg-white w-full max-w-5xl rounded-3xl shadow-2xl overflow-hidden flex flex-col md:flex-row min-h-[600px] transition-all duration-300">

        <!-- Left Side: Information & Branding (Hidden on mobile, beautiful on desktop) -->
        <div class="md:w-1/2 wave-bg text-white p-8 md:p-12 flex flex-col justify-between relative overflow-hidden">
            <!-- Decorative Wave SVG in background -->
            <div class="absolute bottom-0 left-0 right-0 opacity-15 pointer-events-none">
                <svg viewBox="0 0 1440 320" xmlns="http://www.w3.org/2000/svg">
                    <path fill="#ffffff" fill-opacity="1" d="M0,96L48,112C96,128,192,160,288,186.7C384,213,480,235,576,213.3C672,192,768,128,864,117.3C960,107,1056,149,1152,154.7C1248,160,1344,128,1392,112L1440,96L1440,320L1392,320C1344,320,1248,320,1152,320C1056,320,960,320,864,320C768,320,672,320,576,320C480,320,384,320,288,320C192,320,96,320,48,320L0,320Z"></path>
                </svg>
            </div>

            <!-- Top Header Brand -->
            <div class="z-10 flex items-center space-x-3">
                <div class="bg-white/20 p-2.5 rounded-2xl backdrop-blur-md border border-white/10">
                    <!-- SVG Water Drop Icon -->
                    <svg class="w-6 h-6 text-cyan-200 fill-current" viewBox="0 0 24 24">
                        <path d="M12,2.69C12,2.69 19,10 19,14C19,17.86 15.86,21 12,21C8.14,21 5,17.86 5,14C5,10 12,2.69 12,2.69M12,5.18C9.53,8.71 7,12.16 7,14A5,5 0 0,0 12,19A5,5 0 0,0 17,14C17,12.16 14.47,8.71 12,5.18Z"/>
                    </svg>
                </div>
                <div>
                    <h2 class="font-bold tracking-wide text-sm text-cyan-200">META ADHYA TIRTA UMBULAN</h2>
                    <p class="text-[10px] text-white/70 uppercase tracking-widest font-semibold">Penyaluran Air Bersih</p>
                </div>
            </div>

            <!-- Center Illustration and Slogan -->
            <div class="my-auto py-8 z-10 flex flex-col items-center md:items-start text-center md:text-left">
                <!-- Float Illustration -->
                <div class="float-animation mb-6 hidden md:block">
                    <!-- Custom SVG representing water distribution and management -->
                    <svg class="w-56 h-56 text-cyan-100" viewBox="0 0 200 200" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <!-- Water Pipes Network Line representation -->
                        <path d="M40 100H160" stroke="currentColor" stroke-width="4" stroke-linecap="round" stroke-dasharray="8 8"/>
                        <path d="M100 40V160" stroke="currentColor" stroke-width="4" stroke-linecap="round" stroke-dasharray="8 8"/>
                        <!-- Central Water Station Indicator -->
                        <circle cx="100" cy="100" r="30" fill="#0c4a6e" stroke="currentColor" stroke-width="4"/>
                        <circle cx="100" cy="100" r="15" fill="#38bdf8"/>
                        <!-- Little droplet decorative nodes -->
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

            <!-- Bottom Footer Info -->
            <div class="z-10 text-xs text-white/50 hidden md:block">
                &copy; <?= date('Y') ?> PT Meta Adhya Tirta Umbulan. All rights reserved.
            </div>
        </div>

        <!-- Right Side: Login Form -->
        <div class="w-full md:w-1/2 p-8 sm:p-12 md:p-16 flex flex-col justify-between">

            <!-- Mobile Header (Visible only on mobile devices) -->
            <div class="flex items-center space-x-3 mb-8 md:hidden">
                <div class="bg-sky-100 p-2 rounded-xl">
                    <svg class="w-6 h-6 text-sky-600 fill-current" viewBox="0 0 24 24">
                        <path d="M12,2.69C12,2.69 19,10 19,14C19,17.86 15.86,21 12,21C8.14,21 5,17.86 5,14C5,10 12,2.69 12,2.69M12,5.18C9.53,8.71 7,12.16 7,14A5,5 0 0,0 12,19A5,5 0 0,0 17,14C17,12.16 14.47,8.71 12,5.18Z"/>
                    </svg>
                </div>
                <div>
                    <h2 class="font-bold tracking-wide text-sm text-sky-950">META ADHYA TIRTA UMBULAN</h2>
                    <p class="text-[9px] text-sky-600 uppercase tracking-widest font-bold">Penyaluran Air Bersih</p>
                </div>
            </div>

            <!-- Form Wrapper -->
            <div class="my-auto">
                <div class="mb-8">
                    <h2 class="text-2xl sm:text-3xl font-bold text-slate-800 tracking-tight">Selamat Datang</h2>
                    <p class="text-slate-500 text-sm mt-2">Silakan masuk menggunakan akun kepegawaian Anda.</p>
                </div>

                <!-- Live Notification Box -->
                <div id="notification" style="display: none;" class="mb-6 p-4 rounded-xl border flex items-center space-x-3 transition-all duration-300">
                    <div id="notif-icon"></div>
                    <div class="text-sm font-medium" id="notif-message"></div>
                </div>

                <!-- Form -->
                <form id="loginForm" class="space-y-5" onsubmit="handleLogin(event)" method="POST" action="/login">
                    <?php echo csrf_field(); ?>

                    <!-- Email / ID Karyawan Input (Ditambahkan name="email") -->
                    <div>
                        <label for="employee-id" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-2">EMAIL</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                <i class="fa-solid fa-id-card text-base"></i>
                            </div>
                            <input type="text" id="employee-id" name="email" required
                                class="block w-full pl-11 pr-4 py-3 sm:py-3.5 bg-slate-50 border border-slate-200 rounded-2xl text-slate-800 placeholder-slate-400 text-sm transition-all focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-sky-500 focus:bg-white"
                                placeholder="Contoh: john.doe@company.com">
                        </div>
                    </div>

                    <!-- Password Input (Ditambahkan name="password") -->
                    <div>
                        <div class="flex justify-between items-center mb-2">
                            <label for="password" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider">Kata Sandi</label>
                            <a href="#" class="text-xs font-semibold text-sky-600 hover:text-sky-700 hover:underline">Lupa Sandi?</a>
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

                    <!-- Remember Me Option -->
                    <div class="flex items-center">
                        <input id="remember-me" name="remember" type="checkbox"
                            class="h-4.5 w-4.5 text-sky-600 focus:ring-sky-500 border-slate-300 rounded-lg cursor-pointer">
                        <label for="remember-me" class="ml-2 block text-sm text-slate-600 select-none cursor-pointer">
                            Ingat saya di perangkat ini
                        </label>
                    </div>

                    <!-- Submit Button -->
                    <button type="submit" id="submit-btn"
                        class="w-full bg-sky-600 hover:bg-sky-700 text-white font-semibold py-3.5 px-4 rounded-2xl shadow-lg shadow-sky-100 hover:shadow-xl hover:shadow-sky-200 transition-all active:scale-[0.98] flex items-center justify-center space-x-2">
                        <span>Masuk ke Portal Cuti</span>
                        <i class="fa-solid fa-arrow-right text-xs"></i>
                    </button>
                </form>

                <!-- Help Desk Section -->
                <div class="mt-8 pt-6 border-t border-slate-100 flex flex-col sm:flex-row items-center justify-between gap-4">
                    <div class="flex items-center space-x-2 text-slate-500 text-xs">
                        <i class="fa-solid fa-circle-question text-sky-500 text-sm"></i>
                        <span>Butuh bantuan akses login?</span>
                    </div>
                    <a href="tel:021123456" class="text-xs font-semibold text-sky-600 hover:text-sky-700 border border-sky-100 hover:border-sky-200 bg-sky-50/50 hover:bg-sky-50 px-3 py-1.5 rounded-xl transition-colors">
                        <i class="fa-solid fa-headset mr-1"></i> Kontak HR / IT Helpdesk
                    </a>
                </div>
            </div>

            <!-- Mobile Footer (Visible only on mobile devices) -->
            <div class="text-center text-[10px] text-slate-400 mt-8 md:hidden">
                &copy; <?= date('Y') ?> PT Meta Adhya Tirta Umbulan. All rights reserved.
            </div>

        </div>

    </div>

    <!-- Simple Notification & Interaction Script -->
    <script>
        // Toggle password visibility
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

        // Handle Login Submission
        function handleLogin(event) {
            event.preventDefault(); // Hentikan submit langsung untuk menampilkan animasi loading

            const employeeId = document.getElementById('employee-id').value;
            const password = document.getElementById('password').value;
            const submitBtn = document.getElementById('submit-btn');
            const notification = document.getElementById('notification');
            const notifIcon = document.getElementById('notif-icon');
            const notifMessage = document.getElementById('notif-message');

            // Sembunyikan notifikasi sebelumnya jika ada
            notification.style.display = 'none';

            // Tampilkan status loading pada tombol
            submitBtn.disabled = true;
            submitBtn.innerHTML = `
                <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <span>Memproses Autentikasi...</span>
            `;

            // Simulasi animasi visual sebelum benar-benar mengirim data ke server Laravel
            setTimeout(() => {
                notification.style.display = 'flex';

                // Validasi minimal karakter di sisi Client-Side (opsional)
                if (employeeId.trim().length >= 4 && password.length >= 4) {

                    // Notifikasi sukses visual sementara
                    notification.className = "mb-6 p-4 rounded-2xl border flex items-center space-x-3 bg-emerald-50 border-emerald-200 text-emerald-800";
                    notifIcon.innerHTML = `<i class="fa-solid fa-circle-check text-emerald-500 text-lg"></i>`;
                    notifMessage.innerText = "Kredensial valid secara format! Menghubungkan ke server...";

                    submitBtn.className = "w-full bg-emerald-600 text-white font-semibold py-3.5 px-4 rounded-2xl flex items-center justify-center space-x-2";
                    submitBtn.innerHTML = `
                        <i class="fa-solid fa-spinner animate-spin mr-2"></i>
                        <span>Sedang Masuk...</span>
                    `;

                    // PENTING: Kirimkan form secara nyata ke backend Laravel setelah jeda singkat
                    setTimeout(() => {
                        document.getElementById('loginForm').submit();
                    }, 800);

                } else {
                    // Notifikasi gagal (Jika validasi frontend tidak lolos sebelum dikirim ke backend)
                    notification.className = "mb-6 p-4 rounded-2xl border flex items-center space-x-3 bg-rose-50 border-rose-200 text-rose-800";
                    notifIcon.innerHTML = `<i class="fa-solid fa-circle-exclamation text-rose-500 text-lg"></i>`;
                    notifMessage.innerText = "Format email atau password terlalu pendek.";

                    // Reset tombol kembali ke awal
                    submitBtn.disabled = false;
                    submitBtn.className = "w-full bg-sky-600 hover:bg-sky-700 text-white font-semibold py-3.5 px-4 rounded-2xl shadow-lg shadow-sky-100 hover:shadow-xl hover:shadow-sky-200 transition-all active:scale-[0.98] flex items-center justify-center space-x-2";
                    submitBtn.innerHTML = `
                        <span>Masuk ke Portal Cuti</span>
                        <i class="fa-solid fa-arrow-right text-xs"></i>
                    `;
                }
            }, 1200);
        }
    </script>
</body>
</html>
