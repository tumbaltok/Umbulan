<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pendaftaran Akun Karyawan - PT.META</title>
    <link rel="icon" type="image/png" href="{{ asset('images/iconfav.png') }}">
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
    <!-- PWA Head -->
    @pwaHead
</head>
<body class="bg-slate-50 min-h-screen flex items-center justify-center p-3 sm:p-6 md:p-8 overflow-x-hidden">

    <!-- Main Container -->
    <div class="bg-white w-full max-w-6xl rounded-3xl shadow-2xl overflow-hidden flex flex-col lg:flex-row min-h-[700px] transition-all duration-300">

        <!-- Sisi Kiri: Branding & Informasi -->
        <div class="lg:w-5/12 wave-bg text-white p-8 lg:p-12 flex flex-col justify-between relative overflow-hidden">
            <!-- SVG Wave Dekoratif -->
            <div class="absolute bottom-0 left-0 right-0 opacity-15 pointer-events-none">
                <svg viewBox="0 0 1440 320" xmlns="http://www.w3.org/2000/svg">
                    <path fill="#ffffff" fill-opacity="1" d="M0,96L48,112C96,128,192,160,288,186.7C384,213,480,235,576,213.3C672,192,768,128,864,117.3C960,107,1056,149,1152,154.7C1248,160,1344,128,1392,112L1440,96L1440,320L1392,320C1344,320,1248,320,1152,320C1056,320,960,320,864,320C768,320,672,320,576,320C480,320,384,320,288,320C192,320,96,320,48,320L0,320Z"></path>
                </svg>
            </div>

            <!-- Logo Brand Bagian Atas -->
            <div class="z-10 flex items-center space-x-3">
                <div class="bg-white/20 p-1 rounded-full backdrop-blur-md border border-white/20 w-12 h-12 flex items-center justify-center overflow-hidden shrink-0">
                    <img src="{{ asset('images/iconfav.png') }}" alt="Logo" class="w-full h-full object-cover rounded-full">
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

            <!-- Footer -->
            <div class="z-10 text-xs text-white/50">
                &copy; <?= date('Y') ?> PT Meta Adhya Tirta Umbulan. All rights reserved.
            </div>
        </div>

        <!-- Sisi Kanan: Formulir Registrasi -->
        <div class="w-full lg:w-7/12 p-5 sm:p-10 md:p-12 flex flex-col justify-between">

            <div>
                <div class="mb-6">
                    <h2 class="text-2xl sm:text-3xl font-extrabold text-slate-800 tracking-tight">Registrasi Karyawan</h2>
                    <p class="text-slate-500 text-xs sm:text-sm mt-1">Lengkapi data di bawah sesuai dengan database kepegawaian Anda.</p>
                </div>

                <!-- Box Notifikasi JS -->
                <div id="notification" style="display: none;" class="mb-6 p-4 rounded-xl border flex items-center space-x-3 transition-all duration-300">
                    <div id="notif-icon"></div>
                    <div class="text-xs sm:text-sm font-medium" id="notif-message"></div>
                </div>

                <!-- Notifikasi Error Server/Laravel -->
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
                    @csrf

                    <!-- Section 1: Data Utama Karyawan -->
                    <div class="border-b border-slate-100 pb-4">
                        <span class="text-xs font-bold text-sky-600 uppercase tracking-wider block mb-3">
                            <i class="fa-solid fa-user-gear mr-1"></i> Data Utama & Akun
                        </span>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <!-- Nama Lengkap -->
                            <div>
                                <label for="name" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">Nama Lengkap</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                                        <i class="fa-solid fa-user text-xs"></i>
                                    </div>
                                    <input type="text" id="name" name="name" value="{{ old('name') }}" required
                                        class="block w-full pl-9 pr-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-slate-800 placeholder-slate-400 text-xs sm:text-sm focus:ring-2 focus:ring-sky-500 focus:border-sky-500 focus:bg-white focus:outline-none transition-all"
                                        placeholder="Nama sesuai KTP">
                                </div>
                            </div>

                            <!-- Alamat Email -->
                            <div>
                                <label for="email" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">Alamat Email</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                                        <i class="fa-solid fa-envelope text-xs"></i>
                                    </div>
                                    <input type="email" id="email" name="email" value="{{ old('email') }}" required
                                        class="block w-full pl-9 pr-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-slate-800 placeholder-slate-400 text-xs sm:text-sm focus:ring-2 focus:ring-sky-500 focus:border-sky-500 focus:bg-white focus:outline-none transition-all"
                                        placeholder="mail@meta.com">
                                </div>
                            </div>

                            <!-- Jenis Kelamin (Database Select) -->
                            <div class="md:col-span-2">
                                <label for="gender_id" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">Jenis Kelamin</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                                        <i class="fa-solid fa-venus-mars text-xs"></i>
                                    </div>
                                    <select id="gender_id" name="gender_id" required class="block w-full pl-9 pr-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-slate-800 text-xs sm:text-sm focus:ring-2 focus:ring-sky-500 focus:border-sky-500 focus:bg-white focus:outline-none transition-all appearance-none cursor-pointer">
                                        <option value="" disabled {{ old('gender_id') ? '' : 'selected' }}>Pilih Jenis Kelamin</option>
                                        @if(isset($daftarGender) && count($daftarGender) > 0)
                                            @foreach($daftarGender as $gender)
                                                <option value="{{ $gender->id }}" {{ old('gender_id') == $gender->id ? 'selected' : '' }}>
                                                    {{ $gender->name ?? $gender->gender_name }}
                                                </option>
                                            @endforeach
                                        @else
                                            <option value="1" {{ old('gender_id') == '1' ? 'selected' : '' }}>Laki-laki</option>
                                            <option value="2" {{ old('gender_id') == '2' ? 'selected' : '' }}>Perempuan</option>
                                        @endif
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
                            <!-- Station / Penempatan (Database Select) -->
                            <div class="md:col-span-2">
                                <label for="station_id" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">
                                    Penempatan Kerja
                                </label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                                        <i class="fa-solid fa-location-dot text-xs"></i>
                                    </div>
                                    <select id="station_id" name="station_id" required class="block w-full pl-9 pr-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-slate-800 text-xs sm:text-sm focus:ring-2 focus:ring-sky-500 focus:border-sky-500 focus:bg-white focus:outline-none transition-all appearance-none cursor-pointer">
                                        <option value="" disabled {{ old('station_id') ? '' : 'selected' }}>Pilih Tempat Kerja</option>
                                        @foreach($daftarStasiun as $stasiun)
                                            <option value="{{ $stasiun->id }}" {{ old('station_id') == $stasiun->id ? 'selected' : '' }}>
                                                {{ $stasiun->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-slate-400">
                                        <i class="fa-solid fa-chevron-down text-[10px]"></i>
                                    </div>
                                </div>
                            </div>

                            <!-- Jabatan / Role (Database Select) -->
                            <div class="md:col-span-2">
                                <label for="role_id" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">Jabatan</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                                        <i class="fa-solid fa-briefcase text-xs"></i>
                                    </div>
                                    <select id="role_id" name="role_id" required class="block w-full pl-9 pr-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-slate-800 text-xs sm:text-sm focus:ring-2 focus:ring-sky-500 focus:border-sky-500 focus:bg-white focus:outline-none transition-all appearance-none cursor-pointer">
                                        <option value="" disabled {{ old('role_id') ? '' : 'selected' }}>Pilih Peran Jabatan</option>
                                        @if(isset($daftarRole) && count($daftarRole) > 0)
                                            @foreach($daftarRole as $role)
                                                <option value="{{ $role->id }}" {{ old('role_id') == $role->id ? 'selected' : '' }}>
                                                    {{ $role->role_name }}
                                                </option>
                                            @endforeach
                                        @else
                                            <option value="" disabled>Tidak ada data role tersedia</option>
                                        @endif
                                    </select>
                                    <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-slate-400">
                                        <i class="fa-solid fa-chevron-down text-[10px]"></i>
                                    </div>
                                </div>
                            </div>

                            <!-- Jobdesk / Bidang Tugas (Custom Multi-Select Dropdown) -->
                            <div class="md:col-span-2">
                                <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">
                                    Jobdesk / Bidang
                                </label>

                                <div class="relative" id="custom-jobdesk-dropdown">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400 z-10">
                                        <i class="fa-solid fa-list-check text-xs"></i>
                                    </div>

                                    <button type="button" id="jobdesk-btn" class="w-full text-left pl-9 pr-8 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-slate-800 text-xs sm:text-sm focus:ring-2 focus:ring-sky-500 focus:border-sky-500 focus:bg-white focus:outline-none transition-all flex items-center justify-between cursor-pointer">
                                        <span id="jobdesk-text" class="truncate text-slate-400">Pilih Jobdesk / Bidang</span>
                                    </button>

                                    <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-slate-400 z-10">
                                        <i class="fa-solid fa-chevron-down text-[10px]"></i>
                                    </div>

                                    <div id="jobdesk-menu" class="hidden absolute z-30 mt-1 w-full bg-white border border-slate-200 rounded-xl shadow-lg max-h-48 overflow-y-auto p-1.5 space-y-1">
                                        @if(isset($daftarJobdesk) && count($daftarJobdesk) > 0)
                                            @foreach($daftarJobdesk as $jobdesk)
                                                @php
                                                    $jobdeskId = $jobdesk->id;
                                                    $jobdeskNama = $jobdesk->name ?? $jobdesk->nama_jobdesk ?? $jobdesk->job_title ?? $jobdesk->nama;
                                                    $oldJobdesks = old('jobdesk', []);
                                                    $isChecked = is_array($oldJobdesks) && in_array($jobdeskId, $oldJobdesks);
                                                @endphp
                                                <label class="flex items-center space-x-2.5 px-3 py-2 hover:bg-slate-50 rounded-lg cursor-pointer text-xs sm:text-sm text-slate-700 select-none">
                                                    <input type="checkbox" name="jobdesk[]" value="{{ $jobdeskId }}" data-nama="{{ $jobdeskNama }}" class="jobdesk-checkbox rounded text-sky-600 focus:ring-sky-500 border-slate-300 w-4 h-4 cursor-pointer" {{ $isChecked ? 'checked' : '' }}>
                                                    <span class="font-medium">{{ $jobdeskNama }}</span>
                                                </label>
                                            @endforeach
                                        @else
                                            <div class="p-2 text-xs text-slate-400 text-center">Tidak ada data Jobdesk tersedia</div>
                                        @endif
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
                                    <input type="password" id="password" name="password" required onkeyup="checkPasswordStrength()"
                                        class="block w-full pl-9 pr-10 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-slate-800 placeholder-slate-400 text-xs sm:text-sm focus:ring-2 focus:ring-sky-500 focus:border-sky-500 focus:bg-white focus:outline-none transition-all"
                                        placeholder="Kombinasi sandi kuat">

                                    <button type="button" tabindex="-1" onclick="togglePasswordVisibility('password', 'password-icon')" class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-600">
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
                                    <input type="password" id="confirm_password" name="password_confirmation" required
                                        class="block w-full pl-9 pr-10 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-slate-800 placeholder-slate-400 text-xs sm:text-sm focus:ring-2 focus:ring-sky-500 focus:border-sky-500 focus:bg-white focus:outline-none transition-all"
                                        placeholder="Ulangi kata sandi">

                                    <button type="button" tabindex="-1" onclick="togglePasswordVisibility('confirm_password', 'confirm-icon')" class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-600">
                                        <i id="confirm-icon" class="fa-solid fa-eye text-xs"></i>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Indikator Validasi Password Real-Time -->
                        <div class="mt-3 p-3 bg-slate-50 rounded-xl border border-slate-200/80 text-[11px] space-y-1">
                            <div class="mt-2 space-y-1.5">
                                <div class="flex items-center justify-between text-[11px] font-medium text-slate-500">
                                    <span>Kekuatan Kata Sandi:</span>
                                    <span id="pwd-status-text" class="font-bold text-slate-400">Belum diisi</span>
                                </div>

                                <div class="w-full h-1.5 bg-slate-100 rounded-full overflow-hidden flex gap-1">
                                    <div id="pwd-bar-1" class="h-full w-1/4 bg-slate-200 transition-all duration-300"></div>
                                    <div id="pwd-bar-2" class="h-full w-1/4 bg-slate-200 transition-all duration-300"></div>
                                    <div id="pwd-bar-3" class="h-full w-1/4 bg-slate-200 transition-all duration-300"></div>
                                    <div id="pwd-bar-4" class="h-full w-1/4 bg-slate-200 transition-all duration-300"></div>
                                </div>

                                <p id="pwd-hint" class="text-[10px] text-slate-400 flex items-center gap-1">
                                    <i class="fa-solid fa-circle-info text-[9px]"></i>
                                    <span>Wajib min. 8 karakter, kombinasi huruf besar, kecil, angka & simbol.</span>
                                </p>
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

                <div class="mt-6 text-center text-xs sm:text-sm text-slate-500">
                    Sudah memiliki akun portal cuti?
                    <a href="login" class="font-bold text-sky-600 hover:text-sky-700 hover:underline">Masuk Sekarang</a>
                </div>
            </div>

        </div>

    </div>

    <!-- Script Operasi Frontend -->
    <script>
        // JS HANDLER UNTUK CUSTOM MULTI-SELECT JOBDESK
        document.addEventListener("DOMContentLoaded", function () {
            const btn = document.getElementById('jobdesk-btn');
            const menu = document.getElementById('jobdesk-menu');
            const textSpan = document.getElementById('jobdesk-text');
            const checkboxes = document.querySelectorAll('.jobdesk-checkbox');
            const container = document.getElementById('custom-jobdesk-dropdown');

            if (btn && menu) {
                btn.addEventListener('click', function (e) {
                    e.stopPropagation();
                    menu.classList.toggle('hidden');
                });

                function updateText() {
                    const selected = Array.from(checkboxes)
                        .filter(cb => cb.checked)
                        .map(cb => cb.getAttribute('data-nama'));

                    if (selected.length === 0) {
                        textSpan.textContent = 'Pilih Jobdesk / Bidang';
                        textSpan.className = 'truncate text-slate-400';
                    } else {
                        textSpan.textContent = selected.join(', ');
                        textSpan.className = 'truncate text-slate-800 font-normal';
                    }
                }

                checkboxes.forEach(cb => {
                    cb.addEventListener('change', updateText);
                });

                document.addEventListener('click', function (e) {
                    if (container && !container.contains(e.target)) {
                        menu.classList.add('hidden');
                    }
                });

                updateText();
            }
        });

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

        // Pengecekan Kriteria Password Real-Time
        function checkPasswordStrength() {
            const password = document.getElementById('password').value;

            const hasMinLen = password.length >= 8;
            const hasUpper  = /[A-Z]/.test(password);
            const hasLower  = /[a-z]/.test(password);
            const hasNumber = /[0-9]/.test(password);
            const hasSymbol = /[@$!%*?&]/.test(password);

            let score = 0;
            if (hasMinLen) score++;
            if (hasUpper && hasLower) score++;
            if (hasNumber) score++;
            if (hasSymbol) score++;

            const statusText = document.getElementById('pwd-status-text');
            const hintText   = document.getElementById('pwd-hint');
            const bars       = [
                document.getElementById('pwd-bar-1'),
                document.getElementById('pwd-bar-2'),
                document.getElementById('pwd-bar-3'),
                document.getElementById('pwd-bar-4')
            ];

            bars.forEach(bar => bar.className = "h-full w-1/4 bg-slate-200 transition-all duration-300");

            if (password.length === 0) {
                statusText.innerText = "Belum diisi";
                statusText.className = "font-bold text-slate-400";
                hintText.className = "text-[10px] text-slate-400 flex items-center gap-1";
                return false;
            }

            if (score <= 1) {
                statusText.innerText = "Sangat Lemah";
                statusText.className = "font-bold text-rose-500";
                bars[0].className = "h-full w-1/4 bg-rose-500 transition-all duration-300";
            } else if (score === 2) {
                statusText.innerText = "Sedang";
                statusText.className = "font-bold text-amber-500";
                bars[0].className = bars[1].className = "h-full w-1/4 bg-amber-500 transition-all duration-300";
            } else if (score === 3) {
                statusText.innerText = "Bagus";
                statusText.className = "font-bold text-sky-500";
                bars[0].className = bars[1].className = bars[2].className = "h-full w-1/4 bg-sky-500 transition-all duration-300";
            } else if (score === 4 && hasMinLen && hasUpper && hasLower && hasNumber && hasSymbol) {
                statusText.innerText = "Sangat Kuat ✓";
                statusText.className = "font-bold text-emerald-500";
                bars.forEach(bar => bar.className = "h-full w-1/4 bg-emerald-500 transition-all duration-300");
            }

            const isAllValid = hasMinLen && hasUpper && hasLower && hasNumber && hasSymbol;

            if (isAllValid) {
                hintText.className = "text-[10px] text-emerald-600 font-semibold flex items-center gap-1";
                hintText.innerHTML = `<i class="fa-solid fa-circle-check text-emerald-500"></i> Kata sandi memenuhi standar keamanan.`;
            } else {
                hintText.className = "text-[10px] text-slate-400 flex items-center gap-1";
                hintText.innerHTML = `<i class="fa-solid fa-circle-info text-[9px]"></i> Wajib min. 8 karakter, kombinasi huruf besar, kecil, angka & simbol.`;
            }

            return isAllValid;
        }

        function handleRegistration(event) {
            event.preventDefault();

            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            const submitBtn = document.getElementById('submit-btn');
            const notification = document.getElementById('notification');
            const notifIcon = document.getElementById('notif-icon');
            const notifMessage = document.getElementById('notif-message');

            // Cek apakah minimal ada 1 Jobdesk yang dicentang
            const checkedJobdesks = document.querySelectorAll('.jobdesk-checkbox:checked');
            if (checkedJobdesks.length === 0) {
                notification.style.display = 'flex';
                notification.className = "mb-6 p-4 rounded-xl border flex items-center space-x-3 bg-rose-50 border-rose-200 text-rose-800";
                notifIcon.innerHTML = `<i class="fa-solid fa-list-check text-rose-500 text-lg"></i>`;
                notifMessage.innerText = "Error: Anda wajib memilih minimal 1 Jobdesk / Bidang.";
                return;
            }

            const isPasswordValid = checkPasswordStrength();

            if (!isPasswordValid) {
                notification.style.display = 'flex';
                notification.className = "mb-6 p-4 rounded-xl border flex items-center space-x-3 bg-rose-50 border-rose-200 text-rose-800";
                notifIcon.innerHTML = `<i class="fa-solid fa-shield-halved text-rose-500 text-lg"></i>`;
                notifMessage.innerText = "Error: Kata Sandi Baru belum memenuhi semua standar keamanan (Huruf Besar, Huruf Kecil, Angka, & Simbol).";
                return;
            }

            if (password !== confirmPassword) {
                notification.style.display = 'flex';
                notification.className = "mb-6 p-4 rounded-xl border flex items-center space-x-3 bg-rose-50 border-rose-200 text-rose-800";
                notifIcon.innerHTML = `<i class="fa-solid fa-circle-exclamation text-rose-500 text-lg"></i>`;
                notifMessage.innerText = "Error: Konfirmasi Kata Sandi tidak cocok dengan Kata Sandi Baru.";
                return;
            }

            submitBtn.disabled = true;
            submitBtn.innerHTML = `
                <svg class="animate-spin -ml-1 mr-3 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <span>Mendaftarkan data Anda...</span>
            `;

            event.target.submit();
        }
    </script>

    @laravelPwa
    @pwaInstallButton
</body>
</html>
