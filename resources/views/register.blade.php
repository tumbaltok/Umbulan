<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pendaftaran Karyawan - Portal Cuti META</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- FontAwesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts -->
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
            background: linear-gradient(135deg, #0284c7 0%, #0369a1 50%, #075985 100%);
        }
    </style>
</head>
<body class="bg-slate-50 min-h-screen flex items-center justify-center p-3 sm:p-6 md:p-8 overflow-x-hidden">

    <!-- Main Container -->
    <div class="bg-white w-full max-w-6xl rounded-3xl shadow-2xl overflow-hidden flex flex-col lg:flex-row min-h-[700px] transition-all duration-300">

        <!-- Sisi Kiri: Branding & Informasi -->
        <div class="lg:w-5/12 wave-bg text-white p-8 lg:p-12 flex flex-col justify-between relative overflow-hidden">
            <!-- SVG Wave Dekoratif di Latar Belakang -->
            <div class="absolute bottom-0 left-0 right-0 opacity-15 pointer-events-none">
                <svg viewBox="0 0 1440 320" xmlns="http://www.w3.org/2000/svg">
                    <path fill="#ffffff" fill-opacity="1" d="M0,96L48,112C96,128,192,160,288,186.7C384,213,480,235,576,213.3C672,192,768,128,864,117.3C960,107,1056,149,1152,154.7C1248,160,1344,128,1392,112L1440,96L1440,320L1392,320C1344,320,1248,320,1152,320C1056,320,960,320,864,320C768,320,672,320,576,320C480,320,384,320,288,320C192,320,96,320,48,320L0,320Z"></path>
                </svg>
            </div>

            <!-- Logo Brand Bagian Atas -->
            <div class="z-10 flex items-center space-x-3">
                <div class="bg-white/20 p-2.5 rounded-2xl backdrop-blur-md border border-white/10">
                    <svg class="w-6 h-6 text-cyan-200 fill-current" viewBox="0 0 24 24">
                        <path d="M12,2.69C12,2.69 19,10 19,14C19,17.86 15.86,21 12,21C8.14,21 5,17.86 5,14C5,10 12,2.69 12,2.69M12,5.18C9.53,8.71 7,12.16 7,14A5,5 0 0,0 12,19A5,5 0 0,0 17,14C17,12.16 14.47,8.71 12,5.18Z"/>
                    </svg>
                </div>
                <div>
                    <h2 class="font-bold tracking-wide text-sm text-cyan-200">META ADHYA TIRTA UMBULAN</h2>
                    <p class="text-[10px] text-white/70 uppercase tracking-widest font-semibold">Penyaluran Air Bersih</p>
                </div>
            </div>

            <!-- Konten Tengah -->
            <div class="my-auto py-8 z-10 hidden lg:flex flex-col items-start">
                <div class="float-animation mb-6">
                    <svg class="w-48 h-48 text-cyan-100" viewBox="0 0 200 200" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M30 70H170" stroke="currentColor" stroke-width="4" stroke-linecap="round" stroke-dasharray="8 8"/>
                        <path d="M100 30V150" stroke="currentColor" stroke-width="4" stroke-linecap="round" stroke-dasharray="8 8"/>
                        <rect x="75" y="75" width="50" height="50" rx="12" fill="#0c4a6e" stroke="currentColor" stroke-width="4"/>
                        <circle cx="100" cy="100" r="12" fill="#22d3ee" class="animate-pulse"/>
                        <path d="M100 135L105 145H95L100 135Z" fill="currentColor"/>
                        <path d="M155 70L145 65V75L155 70Z" fill="currentColor"/>
                    </svg>
                </div>
                <h1 class="text-3xl font-extrabold leading-tight mb-4">Langkah Awal Bergabung bersama Kami</h1>
                <p class="text-white/80 text-sm max-w-sm font-light leading-relaxed">
                    Daftarkan akun kepegawaian Anda untuk mengakses pengajuan cuti terintegrasi. Pastikan data atasan dan penempatan stasiun sesuai dengan SK penugasan Anda.
                </p>
            </div>

            <!-- Informasi Kaki (Footer) -->
            <div class="z-10 text-xs text-white/50">
                &copy; <?= date('Y') ?> PT Meta Adhya Tirta Umbulan. All rights reserved.
            </div>
        </div>

        <!-- Sisi Kanan: Formulir Registrasi -->
        <div class="w-full lg:w-7/12 p-5 sm:p-10 md:p-12 flex flex-col justify-between">

            <!-- Header Mobile (Hanya tampil di perangkat mobile/tablet) -->
            <div class="flex items-center space-x-3 mb-6 lg:hidden">
                <div class="bg-sky-100 p-2 rounded-xl">
                    <svg class="w-5 h-5 text-sky-600 fill-current" viewBox="0 0 24 24">
                        <path d="M12,2.69C12,2.69 19,10 19,14C19,17.86 15.86,21 12,21C8.14,21 5,17.86 5,14C5,10 12,2.69 12,2.69M12,5.18C9.53,8.71 7,12.16 7,14A5,5 0 0,0 12,19A5,5 0 0,0 17,14C17,12.16 14.47,8.71 12,5.18Z"/>
                    </svg>
                </div>
                <div>
                    <h2 class="font-bold tracking-wide text-xs text-sky-950">META ADHYA TIRTA UMBULAN</h2>
                    <p class="text-[8px] text-sky-600 uppercase tracking-widest font-bold">Penyaluran Air Bersih</p>
                </div>
            </div>

            <!-- Wrapper Form -->
            <div>
                <div class="mb-6">
                    <h2 class="text-2xl sm:text-3xl font-extrabold text-slate-800 tracking-tight">Registrasi Karyawan</h2>
                    <p class="text-slate-500 text-xs sm:text-sm mt-1">Lengkapi data di bawah sesuai dengan database kepegawaian Anda.</p>
                </div>

                <!-- Box Notifikasi Validasi / Error -->
                <div id="notification" style="display: none;" class="mb-6 p-4 rounded-xl border flex items-center space-x-3 transition-all duration-300">
                    <div id="notif-icon"></div>
                    <div class="text-sm font-medium" id="notif-message"></div>
                </div>

                <!-- Form Registrasi -->
                <!-- Deteksi Error dari Laravel -->
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
                <form id="registerForm" class="space-y-4" onsubmit="handleRegistration(event)" method="POST" action="/register">
                    <?php echo csrf_field(); ?>

                    <!-- Section 1: Data Utama Karyawan -->
                    <div class="border-b border-slate-100 pb-4">
                        <span class="text-xs font-bold text-sky-600 uppercase tracking-wider block mb-3">
                            <i class="fa-solid fa-user-gear mr-1"></i> Data Utama & Akun
                        </span>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <!-- NIP Input -->
                            <div>
                                <label for="nip" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">NIP / ID Karyawan</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                                        <i class="fa-solid fa-id-card text-xs"></i>
                                    </div>
                                    <!-- PERBAIKAN: Ditambahkan name="nip" -->
                                    <input type="text" id="nip" name="nip" required
                                        class="block w-full pl-9 pr-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-slate-800 placeholder-slate-400 text-xs sm:text-sm focus:ring-2 focus:ring-sky-500 focus:border-sky-500 focus:bg-white focus:outline-none transition-all"
                                        placeholder="Contoh: TA-2026045">
                                </div>
                            </div>

                            <!-- Name Input -->
                            <div>
                                <label for="name" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">Nama Lengkap</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                                        <i class="fa-solid fa-user text-xs"></i>
                                    </div>
                                    <!-- PERBAIKAN: Ditambahkan name="name" -->
                                    <input type="text" id="name" name="name" required
                                        class="block w-full pl-9 pr-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-slate-800 placeholder-slate-400 text-xs sm:text-sm focus:ring-2 focus:ring-sky-500 focus:border-sky-500 focus:bg-white focus:outline-none transition-all"
                                        placeholder="Nama sesuai KTP">
                                </div>
                            </div>

                            <!-- Email Input -->
                            <div>
                                <label for="email" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">Alamat Email</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                                        <i class="fa-solid fa-envelope text-xs"></i>
                                    </div>
                                    <!-- PERBAIKAN: Ditambahkan name="email" -->
                                    <input type="email" id="email" name="email" required
                                        class="block w-full pl-9 pr-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-slate-800 placeholder-slate-400 text-xs sm:text-sm focus:ring-2 focus:ring-sky-500 focus:border-sky-500 focus:bg-white focus:outline-none transition-all"
                                        placeholder="email@tirtaamerta.co.id">
                                </div>
                            </div>

                            <!-- Gender Selection -->
                            <div>
                                <label for="gender_id" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">Jenis Kelamin</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                                        <i class="fa-solid fa-venus-mars text-xs"></i>
                                    </div>
                                    <!-- PERBAIKAN: Ditambahkan name="gender_id" -->
                                    <select id="gender_id" name="gender_id" required class="block w-full pl-9 pr-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-slate-800 text-xs sm:text-sm focus:ring-2 focus:ring-sky-500 focus:border-sky-500 focus:bg-white focus:outline-none transition-all appearance-none">
                                        <option value="" disabled selected>Pilih Jenis Kelamin</option>
                                        <option value="1">Laki-laki</option>
                                        <option value="2">Perempuan</option>
                                    </select>
                                    <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-slate-400">
                                        <i class="fa-solid fa-chevron-down text-[10px]"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Section 2: Penempatan & Struktur -->
                    <div class="border-b border-slate-100 pb-4">
                        <span class="text-xs font-bold text-sky-600 uppercase tracking-wider block mb-3">
                            <i class="fa-solid fa-network-wired mr-1"></i> Penempatan & Struktur Kerja
                        </span>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <!-- Role Selection -->
                            <div>
                                <label for="role_id" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">Jabatan</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                                        <i class="fa-solid fa-briefcase text-xs"></i>
                                    </div>
                                    <!-- PERBAIKAN: Ditambahkan name="role_id" -->
                                    <select id="role_id" name="role_id" required class="block w-full pl-9 pr-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-slate-800 text-xs sm:text-sm focus:ring-2 focus:ring-sky-500 focus:border-sky-500 focus:bg-white focus:outline-none transition-all appearance-none">
                                        <option value="" disabled selected>Pilih Peran Jabatan</option>
                                        <option value="4">Staff Operasional / Lapangan</option>
                                        <option value="3">Supervisor</option>
                                        <option value="2">Manager</option>
                                        <option class="hidden" value="1">Admin</option>
                                    </select>
                                    <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-slate-400">
                                        <i class="fa-solid fa-chevron-down text-[10px]"></i>
                                    </div>
                                </div>
                            </div>

                            <!-- Station Selection -->
                            <div>
                                <label for="station_id" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">Stasiun / Wilayah Kerja</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                                        <i class="fa-solid fa-faucet-drip text-xs"></i>
                                    </div>
                                    <!-- PERBAIKAN: Ditambahkan name="station_id" -->
                                    <select id="station_id" name="station_id" required class="block w-full pl-9 pr-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-slate-800 text-xs sm:text-sm focus:ring-2 focus:ring-sky-500 focus:border-sky-500 focus:bg-white focus:outline-none transition-all appearance-none">
                                        <option value="" disabled selected>Pilih Lokasi Stasiun</option>
                                        <option value="1">Stasiun Umbulan</option>
                                        <option value="2">Stasiun Booster-M</option>
                                    </select>
                                    <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-slate-400">
                                        <i class="fa-solid fa-chevron-down text-[10px]"></i>
                                    </div>
                                </div>
                            </div>

                            <!-- Tipe Karyawan -->
                            <div>
                                <label for="tipe_id" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">Tipe Pekerjaan</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                                        <i class="fa-solid fa-tags text-xs"></i>
                                    </div>
                                    <!-- PERBAIKAN: Ditambahkan name="tipe_id" -->
                                    <select id="tipe_id" name="tipe_id" required class="block w-full pl-9 pr-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-slate-800 text-xs sm:text-sm focus:ring-2 focus:ring-sky-500 focus:border-sky-500 focus:bg-white focus:outline-none transition-all appearance-none">
                                        <option value="" disabled selected>Pilih Tipe Jobs</option>
                                        <option value="1">Operator</option>
                                        <option value="2">Maintenance</option>
                                        <option value="3">Safety (HSE)</option>
                                        <option value="4">Documenter</option>
                                    </select>
                                    <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-slate-400">
                                        <i class="fa-solid fa-chevron-down text-[10px]"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Section 3: Keamanan Sandi -->
                    <div>
                        <span class="text-xs font-bold text-sky-600 uppercase tracking-wider block mb-3">
                            <i class="fa-solid fa-shield-halved mr-1"></i> Keamanan Akun
                        </span>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <!-- Password Input -->
                            <div>
                                <label for="password" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">Kata Sandi Baru</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                                        <i class="fa-solid fa-lock text-xs"></i>
                                    </div>
                                    <!-- PERBAIKAN: Ditambahkan name="password" dan mengubah typo text-slate-880 menjadi text-slate-800 -->
                                    <input type="password" id="password" name="password" required
                                        class="block w-full pl-9 pr-10 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-slate-800 placeholder-slate-400 text-xs sm:text-sm focus:ring-2 focus:ring-sky-500 focus:border-sky-500 focus:bg-white focus:outline-none transition-all"
                                        placeholder="Min. 8 karakter">
                                    <button type="button" onclick="togglePasswordVisibility('password', 'password-icon')" class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-600">
                                        <i id="password-icon" class="fa-solid fa-eye text-xs"></i>
                                    </button>
                                </div>
                            </div>

                            <!-- Confirm Password Input -->
                            <div>
                                <label for="confirm_password" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">Konfirmasi Kata Sandi</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                                        <i class="fa-solid fa-lock text-xs"></i>
                                    </div>
                                    <!-- PERBAIKAN: Ditambahkan name="password_confirmation" (Standar Laravel) -->
                                    <input type="password" id="confirm_password" name="password_confirmation" required
                                        class="block w-full pl-9 pr-10 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-slate-800 placeholder-slate-400 text-xs sm:text-sm focus:ring-2 focus:ring-sky-500 focus:border-sky-500 focus:bg-white focus:outline-none transition-all"
                                        placeholder="Ulangi kata sandi">
                                    <button type="button" onclick="togglePasswordVisibility('confirm_password', 'confirm-icon')" class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-600">
                                        <i id="confirm-icon" class="fa-solid fa-eye text-xs"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Terms & Agreement -->
                    <div class="flex items-start pt-2">
                        <input id="agreement" required type="checkbox"
                            class="mt-1 h-4 w-4 text-sky-600 focus:ring-sky-500 border-slate-300 rounded cursor-pointer">
                        <label for="agreement" class="ml-2 block text-xs text-slate-500 select-none leading-relaxed">
                            Saya menyatakan bahwa seluruh informasi yang diisi adalah benar, sesuai dengan Surat Keputusan (SK) dan status aktif kedinasan saya di PT Meta Adhya Tirta Umbulan.
                        </label>
                    </div>

                    <!-- Tombol Submit -->
                    <button type="submit" id="submit-btn"
                        class="w-full mt-2 bg-sky-600 hover:bg-sky-700 text-white font-semibold py-3 px-4 rounded-xl shadow-lg shadow-sky-100 hover:shadow-xl hover:shadow-sky-200 transition-all active:scale-[0.99] flex items-center justify-center space-x-2">
                        <span>Daftarkan Akun Pegawai</span>
                        <i class="fa-solid fa-user-plus text-xs"></i>
                    </button>
                </form>

                <!-- Link Alternatif Masuk -->
                <div class="mt-6 text-center text-xs sm:text-sm text-slate-500">
                    Sudah memiliki akun portal cuti?
                    <a href="login" class="font-bold text-sky-600 hover:text-sky-700 hover:underline">Masuk Sekarang</a>
                </div>
            </div>

        </div>

    </div>

    <!-- Script Operasi Frontend -->
    <script>
        // Mengubah visibilitas kata sandi (Sembunyikan/Tampilkan)
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

        // Proses Submit Registrasi
        function handleRegistration(event) {
            event.preventDefault(); // Menghentikan pengiriman otomatis instan untuk divalidasi terlebih dahulu

            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            const submitBtn = document.getElementById('submit-btn');
            const notification = document.getElementById('notification');
            const notifIcon = document.getElementById('notif-icon');
            const notifMessage = document.getElementById('notif-message');

            // Validasi Kesesuaian Kata Sandi Baru dan Konfirmasi
            if (password !== confirmPassword) {
                notification.style.display = 'flex';
                notification.className = "mb-6 p-4 rounded-xl border flex items-center space-x-3 bg-rose-50 border-rose-200 text-rose-800";
                notifIcon.innerHTML = `<i class="fa-solid fa-circle-exclamation text-rose-500 text-lg"></i>`;
                notifMessage.innerText = "Error: Konfirmasi Kata Sandi tidak cocok dengan Kata Sandi Baru.";
                return;
            }

            // Validasi panjang kata sandi minimal 8 karakter
            if (password.length < 8) {
                notification.style.display = 'flex';
                notification.className = "mb-6 p-4 rounded-xl border flex items-center space-x-3 bg-rose-50 border-rose-200 text-rose-800";
                notifIcon.innerHTML = `<i class="fa-solid fa-circle-exclamation text-rose-500 text-lg"></i>`;
                notifMessage.innerText = "Error: Kata Sandi Baru minimal harus berukuran 8 karakter.";
                return;
            }

            // Mengubah tombol menjadi state loading
            submitBtn.disabled = true;
            submitBtn.innerHTML = `
                <svg class="animate-spin -ml-1 mr-3 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <span>Mendaftarkan data Anda...</span>
            `;

            // PERBAIKAN UTAMA: Setelah validasi frontend sukses, teruskan form ke backend (PHP/Laravel)
            // Ini akan memicu Request POST asli ke Route Laravel di `/register` Anda
            event.target.submit();
        }
    </script>
</body>
</html>
