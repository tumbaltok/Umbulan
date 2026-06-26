<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PengajuanCutiController;
use App\Http\Controllers\StationController;
use App\Http\Controllers\KaryawanController;
use App\Http\Controllers\AccountController;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;

// Halaman Selamat Datang / Landing Page Utama
Route::get('/', function () {
    return view('welcome1');
});

// Grup Route untuk Pengguna yang BELUM Login (Tamu / Guest)
Route::middleware('guest')->group(function () {

    // ==========================================
    // HALAMAN & PROSES REGISTRASI WEB
    // ==========================================
    Route::get('/register', function () {
        return view('auth.register');
    })->name('register');

    Route::post('/register', [AuthController::class, 'registerWeb'])->name('register.post');


    // ==========================================
    // HALAMAN & PROSES LOGIN WEB
    // ==========================================
    Route::get('/login', function () {
        return view('auth.login');
    })->name('login');

    Route::post('/login', [AuthController::class, 'loginWeb'])->name('login.post');


    // ==========================================
    // ALUR LUPA PASSWORD (FORGOT PASSWORD)
    // ==========================================
    // 1. Tampilan Halaman Utama Lupa Password
    Route::get('/forgot', function () {
        return view('auth.forgot');
    })->name('forgot');

    // 2. Endpoint AJAX untuk Kirim OTP ke Email
    Route::post('/forgot/send-otp-mail', [AuthController::class, 'sendOtpWeb'])->name('forgot.send_otp');

    // 3. Endpoint AJAX untuk Verifikasi Kode OTP (Koreksi di sini)
    Route::post('/forgot/verify-otp-mail', [AuthController::class, 'verifyOtpMailWeb'])->name('forgot.verify_otp');

    // 4. Eksekusi Form Akhir untuk Simpan Password Baru Pilihan User
    Route::post('/forgot', [AuthController::class, 'forgotWeb'])->name('forgot.post');

});

// Grup Route untuk Pengguna yang SUDAH Login (Berbasis Session Web)
Route::middleware('auth')->group(function () {
    // Dashboard Utama Karyawan/Atasan
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Route Pengaturan Akun / Profil Karyawan
    Route::get('/profile', [AccountController::class, 'index'])->name('account.index');
    Route::put('/profile/update', [AccountController::class, 'update'])->name('account.update');

    // Manajemen Karyawan & Detail Karyawan
    Route::get('/admin/karyawan', [KaryawanController::class, 'index'])->name('karyawan.index');
    Route::get('/admin/karyawan/{id}/detail', [KaryawanController::class, 'showDetail'])->name('karyawan.detail');

    // Manajemen Stasiun Kerja / Station
    Route::get('/admin/stations', [StationController::class, 'index'])->name('stations.index');

    // Riwayat Cuti
    Route::get('/cuti/riwayat', [PengajuanCutiController::class, 'riwayatView'])->name('cuti.riwayat');
    Route::get('/cuti/riwayat/{id}/detail', [PengajuanCutiController::class, 'detailCutiJSON']);

    // ==========================================
    // ALUR VERIFIKASI EMAIL
    // ==========================================
    Route::get('/email/verify', function () {
        return view('auth.verify-email');
    })->name('verification.notice');

    Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
        $request->fulfill();
        return redirect('/dashboard')->with('message', 'Email berhasil diverifikasi!');
    })->middleware('signed')->name('verification.verify');

    Route::post('/email/verification-notification', function (Request $request) {
        $request->user()->sendEmailVerificationNotification();
        return back()->with('message', 'verification-link-sent');
    })->middleware('throttle:6,1')->name('verification.send');

    // ==========================================
    // ALUR VERIFIKASI NOMOR TELEPON (OTP)
    // ==========================================
    // Endpoint AJAX untuk Kirim & Verifikasi OTP
    Route::post('/phone/send-otp-phone', [AuthController::class, 'sendOtpPhone'])->name('phone.send-otp');
    Route::post('/phone/verify-otp-phone', [AuthController::class, 'verifyOtpPhone'])->name('phone.verify-otp');


    // ==========================================
    // FITUR YANG MEMBUTUHKAN VERIFIKASI
    // ==========================================
    Route::middleware('verified')->group(function () {

        // Menggunakan Route::group untuk membungkus inline middleware dengan benar
        Route::group(['middleware' => function ($request, $next) {
            $user = $request->user();

            // Jika nomor telepon belum diverifikasi (phone_verified_at masih NULL)
            if ($user && !$user->phone_verified_at) {
                if ($request->expectsJson()) {
                    return response()->json(['message' => 'Nomor telepon Anda belum diverifikasi.'], 403);
                }

                // Redirect langsung ke halaman profil bawaan route Anda
                return redirect()->route('account.index')
                    ->with('error', 'Silakan verifikasi nomor telepon Anda terlebih dahulu untuk mengakses fitur pengajuan cuti.');
            }

            return $next($request);
        }], function () {

            // Form & Proses Pengajuan Cuti Web
            Route::get('/cuti/ajukan', [PengajuanCutiController::class, 'create'])->name('cuti.ajukan');
            Route::post('/cuti/store', [PengajuanCutiController::class, 'storeWeb'])->name('cuti.storeWeb');

            // Fitur Cetak & Pembungkus PDF Surat Cuti
            Route::get('/cuti/{id}/pembungkus', [PengajuanCutiController::class, 'viewSuratCuti'])->name('cuti.viewSurat');
            Route::get('/cuti/{id}/cetak', [PengajuanCutiController::class, 'cetakSuratCuti'])->name('cuti.cetak');

            // Utalitas / AJAX pendukung Form Pengajuan Cuti
            Route::get('/cuti/ambil-subcuti/{id}', [PengajuanCutiController::class, 'ambilSubCuti'])->name('cuti.ambilSubCuti');

        });

    });

    // Proses Logout Web
    Route::post('/logout', [AuthController::class, 'logoutWeb'])->name('logout');
});

// Grup Route Khusus Atasan Level Web (Supervisor & Manager)
Route::middleware(['auth', 'atasan'])->group(function () {
    Route::get('/admin/persetujuan', [PengajuanCutiController::class, 'listAtasanView'])->name('admin.persetujuan');
    Route::post('/admin/persetujuan/proses/{id}', [PengajuanCutiController::class, 'prosesPersetujuan'])->name('cuti.proses-persetujuan');
});
