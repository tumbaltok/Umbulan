<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PengajuanCutiController;
use App\Http\Controllers\StationController;
use App\Http\Controllers\KaryawanController;
use App\Http\Controllers\AccountController;
use App\Http\Controllers\RecordController;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use App\Http\Controllers\PengajuanCarController;
use Illuminate\Http\Request;

// Halaman Selamat Datang / Landing Page Utama
Route::get('/', function () {
    return view('welcome1');
});

// ==========================================================
// GRUP GUEST (Belum Login)
// ==========================================================
Route::middleware('guest')->group(function () {
    Route::get('/register', function () { return view('auth.register'); })->name('register');
    Route::post('/register', [AuthController::class, 'registerWeb'])->name('register.post');

    Route::get('/login', function () { return view('auth.login'); })->name('login');
    Route::post('/login', [AuthController::class, 'loginWeb'])->name('login.post');

    Route::get('/forgot', function () { return view('auth.forgot'); })->name('forgot');
    Route::post('/forgot/send-otp-mail', [AuthController::class, 'sendOtpWeb'])->name('forgot.send_otp');
    Route::post('/forgot/verify-otp-mail', [AuthController::class, 'verifyOtpMailWeb'])->name('forgot.verify_otp');
    Route::post('/forgot', [AuthController::class, 'forgotWeb'])->name('forgot.post');
});

// ==========================================================
// GRUP AUTH (Sudah Login - Umum Karyawan & Atasan)
// ==========================================================
Route::middleware('auth')->group(function () {

    // Dashboard & Profil
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/profile', [AccountController::class, 'index'])->name('account.index');
    Route::put('/profile/update', [AccountController::class, 'update'])->name('account.update');

    // Fitur Cuti (Riwayat & Detail)
    Route::get('/cuti/riwayat', [PengajuanCutiController::class, 'riwayatView'])->name('cuti.riwayat');
    Route::get('/cuti/riwayat/{id}/detail', [PengajuanCutiController::class, 'detailCutiJSON']);

    // Fitur CAR (Riwayat)
    Route::get('/car/riwayat', [PengajuanCarController::class, 'index'])->name('car.riwayat');

    // Verifikasi Email & Phone
    Route::get('/email/verify', function () { return view('auth.verify-email'); })->name('verification.notice');
    Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
        $request->fulfill();
        return redirect('/dashboard')->with('message', 'Email berhasil diverifikasi!');
    })->middleware('signed')->name('verification.verify');

    Route::post('/email/verification-notification', function (Request $request) {
        $request->user()->sendEmailVerificationNotification();
        return back()->with('message', 'verification-link-sent');
    })->middleware('throttle:6,1')->name('verification.send');

    Route::post('/phone/send-otp-phone', [AuthController::class, 'sendOtpPhone'])->name('phone.send-otp');
    Route::post('/phone/verify-otp-phone', [AuthController::class, 'verifyOtpPhone'])->name('phone.verify-otp');

    // Fitur Internal (Wajib Verified)
    Route::middleware('verified')->group(function () {
        Route::group(['middleware' => function ($request, $next) {
            $user = $request->user();
            if ($user && !$user->phone_verified_at) {
                if ($request->expectsJson()) {
                    return response()->json(['message' => 'Nomor telepon Anda belum diverifikasi.'], 403);
                }
                return redirect()->route('account.index')->with('error', 'Silakan verifikasi nomor telepon Anda terlebih dahulu.');
            }
            return $next($request);
        }], function () {
            // Form Cuti
            Route::get('/cuti/ajukan', [PengajuanCutiController::class, 'create'])->name('cuti.ajukan');
            Route::post('/cuti/store', [PengajuanCutiController::class, 'storeWeb'])->name('cuti.storeWeb');
            Route::get('/cuti/{id}/pembungkus', [PengajuanCutiController::class, 'viewSuratCuti'])->name('cuti.viewSurat');
            Route::get('/cuti/{id}/cetak', [PengajuanCutiController::class, 'cetakSuratCuti'])->name('cuti.cetak');
            Route::get('/cuti/ambil-subcuti/{id}', [PengajuanCutiController::class, 'ambilSubCuti'])->name('cuti.ambilSubCuti');

            // Form CAR
            Route::get('/car/create', [PengajuanCarController::class, 'create'])->name('car.create');
            Route::post('/car/store', [PengajuanCarController::class, 'store'])->name('car.store');
            Route::get('/car/print/{id}', [PengajuanCarController::class, 'print'])->name('car.print');
        });
    });

    // Logout
    Route::post('/logout', [AuthController::class, 'logoutWeb'])->name('logout');
});

// ==========================================================
// GRUP ATASAN (Khusus Supervisor & Manager)
// ==========================================================
Route::middleware(['auth', 'atasan'])->group(function () {
    // Jalur Utama Persetujuan Cuti
    Route::get('/admin/persetujuan/cuti', [PengajuanCutiController::class, 'listPengajuan'])->name('admin.persetujuan.cuti');
    Route::post('/admin/persetujuan/cuti/proses/{id}', [PengajuanCutiController::class, 'prosesPersetujuan'])->name('admin.persetujuan.cuti.proses');

    // Jalur Utama Persetujuan CAR (Taruh Paling Atas di Grup Ini)
    Route::get('/admin/persetujuan/car', [PengajuanCarController::class, 'listPengajuan'])->name('admin.persetujuan.car');
    Route::post('/admin/persetujuan/car/proses/{id}', [PengajuanCarController::class, 'prosesPersetujuan'])->name('admin.persetujuan.car.process');

    // Administrasi Lainnya
    Route::get('/admin/karyawan', [KaryawanController::class, 'index'])->name('admin.karyawan.index');
    Route::get('/admin/karyawan/{id}/detail', [KaryawanController::class, 'showDetail'])->name('admin.karyawan.detail');
    Route::get('/admin/stations', [StationController::class, 'index'])->name('admin.stations.index');
    Route::get('/admin/record', [RecordController::class, 'index'])->name('admin.record.index');
    Route::get('/admin/record/export', [RecordController::class, 'export'])->name('admin.record.export');
});
