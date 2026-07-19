<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lupa Kata Sandi - PT.META</title>
    <link rel="icon" type="image/png" href="{{ asset('images/iconfav.png') }}">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght=300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        .wave-bg { background: linear-gradient(135deg, #0284c7 0%, #0369a1 50%, #075985 100%); }
    </style>
    <!-- PWA Head -->
    @pwaHead
</head>
<body class="bg-slate-50 min-h-screen flex items-center justify-center p-3 sm:p-6 md:p-8 overflow-x-hidden">

    <div class="bg-white w-full max-w-md rounded-3xl shadow-2xl overflow-hidden p-6 sm:p-10 transition-all duration-300 my-4">

        <div class="mb-6 text-center">
            <div class="mx-auto w-12 h-12 wave-bg rounded-2xl flex items-center justify-center text-white text-xl shadow-lg shadow-sky-100 mb-4">
                <i class="fa-solid fa-key"></i>
            </div>
            <h2 class="text-2xl font-bold text-slate-800 tracking-tight">Pemulihan Kata Sandi</h2>
            <p class="text-slate-500 text-xs sm:text-sm mt-1" id="form-desc">Masukkan email terdaftar Anda untuk menerima kode OTP.</p>
        </div>

        <div id="alert-box" style="display: none;" class="mb-6 p-4 rounded-2xl border flex items-center space-x-3 text-sm font-medium">
            <div id="alert-icon"></div>
            <div id="alert-message"></div>
        </div>

        @if($errors->any())
            <div class="mb-6 p-4 rounded-2xl border flex items-center space-x-3 bg-rose-50 border-rose-200 text-rose-800">
                <i class="fa-solid fa-circle-exclamation text-rose-500 text-lg"></i>
                <div class="text-sm font-medium">{{ $errors->first() }}</div>
            </div>
        @endif

        <form id="emailForm" onsubmit="sendOtp(event)" class="space-y-5">
            @csrf
            <div>
                <label for="email" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-2">EMAIL KEPEGAWAIAN</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                        <i class="fa-solid fa-envelope text-base"></i>
                    </div>
                    <input type="email" id="email" name="email" required
                        class="block w-full pl-11 pr-4 py-3 sm:py-3.5 bg-slate-50 border border-slate-200 rounded-2xl text-slate-800 placeholder-slate-400 text-sm transition-all focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-sky-500 focus:bg-white"
                        placeholder="nama@meta.com">
                </div>
            </div>

            <button type="submit" id="btn-email"
                class="w-full bg-sky-600 hover:bg-sky-700 text-white font-semibold py-3.5 px-4 rounded-2xl shadow-lg shadow-sky-100 hover:shadow-xl hover:shadow-sky-200 transition-all active:scale-[0.98] flex items-center justify-center space-x-2">
                <span>Kirim Kode OTP</span>
                <i class="fa-solid fa-paper-plane text-xs"></i>
            </button>
        </form>

        <form id="otpForm" onsubmit="verifyOtp(event)" class="space-y-5 hidden">
            @csrf
            <div>
                <label for="otp" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-2">MASUKKAN KODE OTP</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                        <i class="fa-solid fa-shield-halved text-base"></i>
                    </div>
                    <input type="text" id="otp" name="otp" required maxlength="6"
                        class="block w-full pl-11 pr-4 py-3 sm:py-3.5 bg-slate-50 border border-slate-200 rounded-2xl text-slate-800 tracking-[0.5em] font-mono text-center text-lg placeholder-slate-400 transition-all focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-sky-500 focus:bg-white"
                        placeholder="******">
                </div>
            </div>

            <button type="submit" id="btn-otp"
                class="w-full bg-sky-600 hover:bg-sky-700 text-white font-semibold py-3.5 px-4 rounded-2xl shadow-lg shadow-sky-100 hover:shadow-xl hover:shadow-sky-200 transition-all active:scale-[0.98] flex items-center justify-center space-x-2">
                <span>Verifikasi Kode OTP</span>
                <i class="fa-solid fa-circle-check text-xs"></i>
            </button>
        </form>

        <form id="passwordForm" method="POST" action="{{ route('forgot.update') }}" class="space-y-5 hidden">
            @csrf
            <input type="hidden" id="hidden-email" name="email">
            <input type="hidden" id="hidden-otp" name="otp">

            <div>
                <label for="password" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-2">KATA SANDI BARU</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                        <i class="fa-solid fa-lock text-base"></i>
                    </div>
                    <input type="password" id="password" name="password" required minlength="6"
                        class="block w-full pl-11 pr-4 py-3 sm:py-3.5 bg-slate-50 border border-slate-200 rounded-2xl text-slate-800 placeholder-slate-400 text-sm transition-all focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-sky-500 focus:bg-white"
                        placeholder="Minimal 6 karakter">
                </div>
            </div>

            <div>
                <label for="password_confirmation" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-2">KONFIRMASI KATA SANDI</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                        <i class="fa-solid fa-lock text-base"></i>
                    </div>
                    <input type="password" id="password_confirmation" name="password_confirmation" required minlength="6"
                        class="block w-full pl-11 pr-4 py-3 sm:py-3.5 bg-slate-50 border border-slate-200 rounded-2xl text-slate-800 placeholder-slate-400 text-sm transition-all focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-sky-500 focus:bg-white"
                        placeholder="Ulangi kata sandi baru">
                </div>
            </div>

            <button type="submit"
                class="w-full bg-emerald-600 hover:bg-emerald-700 text-white font-semibold py-3.5 px-4 rounded-2xl shadow-lg shadow-emerald-100 hover:shadow-xl hover:shadow-emerald-200 transition-all active:scale-[0.98] flex items-center justify-center space-x-2">
                <span>Simpan Kata Sandi Baru</span>
                <i class="fa-solid fa-save text-xs"></i>
            </button>
        </form>

        <div class="mt-8 pt-6 border-t border-slate-100 text-center">
            <a href="{{ route('login') }}" class="text-xs font-semibold text-slate-500 hover:text-sky-600 transition-colors">
                <i class="fa-solid fa-arrow-left mr-1"></i> Kembali ke Halaman Login
            </a>
        </div>

    </div>

    <script>
        const alertBox = document.getElementById('alert-box');
        const alertIcon = document.getElementById('alert-icon');
        const alertMessage = document.getElementById('alert-message');

        function showAlert(type, message) {
            alertBox.style.display = 'flex';
            if(type === 'success') {
                alertBox.className = "mb-6 p-4 rounded-2xl border flex items-center space-x-3 text-sm font-medium bg-emerald-50 border-emerald-200 text-emerald-800";
                alertIcon.innerHTML = `<i class="fa-solid fa-circle-check text-emerald-500 text-lg"></i>`;
            } else {
                alertBox.className = "mb-6 p-4 rounded-2xl border flex items-center space-x-3 text-sm font-medium bg-rose-50 border-rose-200 text-rose-800";
                alertIcon.innerHTML = `<i class="fa-solid fa-circle-exclamation text-rose-500 text-lg"></i>`;
            }
            alertMessage.innerText = message;
        }

        // TAHAP 1: Kirim OTP
        function sendOtp(event) {
            event.preventDefault();
            const email = document.getElementById('email').value;
            const btn = document.getElementById('btn-email');

            btn.disabled = true;
            btn.innerHTML = `Memproses...`;

            fetch("{{ route('forgot.send_otp') }}", {
                method: "POST",
                headers: { "Content-Type": "application/json", "X-CSRF-TOKEN": "{{ csrf_token() }}" },
                body: JSON.stringify({ email: email })
            })
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success') {
                    document.getElementById('emailForm').classList.add('hidden');
                    document.getElementById('otpForm').classList.remove('hidden');
                    document.getElementById('hidden-email').value = email;
                    document.getElementById('form-desc').innerText = "Masukkan 6 digit kode OTP yang dikirim ke " + email;
                    showAlert('success', data.message);
                } else {
                    showAlert('error', data.message);
                    btn.disabled = false;
                    btn.innerHTML = `<span>Kirim Kode OTP</span><i class="fa-solid fa-paper-plane text-xs"></i>`;
                }
            }).catch(() => { showAlert('error', 'Terjadi kesalahan sistem.'); btn.disabled = false; });
        }

        // TAHAP 2: Verifikasi OTP (AJAX)
        function verifyOtp(event) {
            event.preventDefault();
            const email = document.getElementById('hidden-email').value;
            const otp = document.getElementById('otp').value;
            const btn = document.getElementById('btn-otp');

            btn.disabled = true;
            btn.innerHTML = `Memverifikasi...`;

            fetch("{{ route('forgot.verify_otp') }}", {
                method: "POST",
                headers: { "Content-Type": "application/json", "X-CSRF-TOKEN": "{{ csrf_token() }}" },
                body: JSON.stringify({ email: email, otp: otp })
            })
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success') {
                    document.getElementById('otpForm').classList.add('hidden');
                    document.getElementById('passwordForm').classList.remove('hidden');
                    document.getElementById('hidden-otp').value = otp;
                    document.getElementById('form-desc').innerText = "Buat kata sandi baru yang aman untuk akun Anda.";
                    showAlert('success', data.message);
                } else {
                    showAlert('error', data.message);
                    btn.disabled = false;
                    btn.innerHTML = `<span>Verifikasi Kode OTP</span><i class="fa-solid fa-circle-check text-xs"></i>`;
                }
            }).catch(() => { showAlert('error', 'Terjadi kesalahan sistem.'); btn.disabled = false; });
        }
    </script>
    <!-- PWA Script Registrations & Tools -->
    @laravelPwa
    @pwaUpdateNotifier
    @pwaInstallButton
</body>
</html>
