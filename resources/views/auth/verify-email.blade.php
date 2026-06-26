@extends('layouts.app')

@section('content')
<div class="min-h-[75vh] flex items-center justify-center px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8 bg-white p-8 rounded-3xl border border-slate-100 shadow-sm text-center">

        {{-- Ikon Ilustrasi --}}
        <div class="mx-auto h-24 w-24 bg-sky-50 text-sky-600 rounded-full flex items-center justify-center animate-bounce">
            <i class="fa-solid fa-paper-plane text-4xl"></i>
        </div>

        {{-- Judul & Deskripsi --}}
        <div class="space-y-2">
            <h2 class="text-2xl font-black text-slate-800 tracking-tight">
                Verifikasi Email Anda
            </h2>
            <p class="text-sm text-slate-500 leading-relaxed">
                Terima kasih telah bergabung! Kami telah mengirimkan tautan verifikasi ke alamat email Anda. Silakan periksa kotak masuk (atau folder spam) Anda.
            </p>
        </div>

        {{-- Alert Status saat Berhasil Kirim Ulang Email --}}
        @if (session('message'))
            <div class="bg-emerald-50 border border-emerald-200 text-emerald-700 text-xs font-medium p-3.5 rounded-xl flex items-center space-x-2 justify-center transition-all animate-fade-in">
                <i class="fa-solid fa-circle-check text-emerald-500 text-sm"></i>
                <span>Tautan verifikasi baru telah berhasil dikirim ke email Anda!</span>
            </div>
        @endif

        <hr class="border-slate-100 my-6">

        {{-- Aksi / Tombol Interaksi --}}
        <div class="flex flex-col space-y-3">

            {{-- Form Kirim Ulang Tautan --}}
            <form method="POST" action="{{ route('verification.send') }}" class="w-full" id="resend-email-form">
                @csrf
                <button type="submit"
                    id="btn-resend-email"
                    class="w-full bg-sky-600 hover:bg-sky-700 disabled:bg-sky-400 disabled:opacity-70 disabled:cursor-not-allowed text-white font-semibold py-3 px-4 rounded-xl text-sm transition-all duration-200 shadow-sm hover:shadow-md flex items-center justify-center space-x-2 focus:ring-2 focus:ring-sky-500 focus:ring-offset-2">
                    <i class="fa-solid fa-rotate-right text-xs"></i>
                    <span id="btn-text">Kirim Ulang Email Verifikasi</span>
                </button>
            </form>

            <div class="flex items-center justify-between pt-2">
                {{-- Kembali ke Dashboard Utama --}}
                <a href="{{ route('dashboard') }}" class="text-xs font-semibold text-slate-500 hover:text-slate-800 transition-colors flex items-center space-x-1">
                    <i class="fa-solid fa-arrow-left text-[10px]"></i>
                    <span>Kembali ke Dashboard</span>
                </a>

                {{-- Tombol Keluar / Logout --}}
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="text-xs font-semibold text-rose-500 hover:text-rose-700 transition-colors flex items-center space-x-1">
                        <span>Keluar Akun</span>
                        <i class="fa-solid fa-right-from-bracket text-[10px]"></i>
                    </button>
                </form>
            </div>

        </div>

    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener("DOMContentLoaded", function () {
        const form = document.getElementById("resend-email-form");
        const btnResend = document.getElementById("btn-resend-email");
        const btnText = document.getElementById("btn-text");
        const COOLDOWN_TIME = 60; // Durasi jeda dalam detik

        // Fungsi utama hitung mundur
        function startCooldown(duration) {
            btnResend.disabled = true;
            let timeLeft = duration;

            const timer = setInterval(function () {
                if (timeLeft <= 0) {
                    clearInterval(timer);
                    localStorage.removeItem("email_cooldown_expiry"); // Hapus memori simpanan jika habis
                    btnResend.disabled = false;
                    btnText.innerText = "Kirim Ulang Email Verifikasi";
                } else {
                    btnText.innerText = `Tunggu (${timeLeft}s)`;
                    timeLeft--;
                }
            }, 1000);
        }

        // Cek saat halaman dimuat: Apakah masih dalam masa cooldown sebelumnya?
        const expiryTime = localStorage.getItem("email_cooldown_expiry");
        if (expiryTime) {
            const currentTime = Math.floor(Date.now() / 1000);
            const remainingTime = expiryTime - currentTime;

            if (remainingTime > 0) {
                startCooldown(remainingTime);
            } else {
                localStorage.removeItem("email_cooldown_expiry");
            }
        }

        // Jalankan pemicu ketika form disubmit/diklik pertama kali
        if (form) {
            form.addEventListener("submit", function () {
                // Set waktu kadaluarsa 60 detik dari sekarang ke localStorage
                const expiryTimestamp = Math.floor(Date.now() / 1000) + COOLDOWN_TIME;
                localStorage.setItem("email_cooldown_expiry", expiryTimestamp);

                // Ubah UI tombol sementara sebelum halaman beralih/muat ulang
                btnResend.disabled = true;
                btnText.innerText = "Mengirim...";
            });
        }
    });
</script>
@endpush
