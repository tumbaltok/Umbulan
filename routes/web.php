<?php

use App\Http\Controllers\Admin\HolidayController;
use App\Http\Controllers\Admin\KaryawanController;
use App\Http\Controllers\Admin\RecordController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\StationController;
use App\Http\Controllers\Car\DokumenCarController;
use App\Http\Controllers\Car\PengajuanCarController;
use App\Http\Controllers\Car\PersetujuanCarController;
use App\Http\Controllers\Cuti\PengajuanCutiController;
use App\Http\Controllers\Cuti\PersetujuanCutiController;
use App\Http\Controllers\Mpr\DokumenMprController;
use App\Http\Controllers\Mpr\PengajuanMprController;
use App\Http\Controllers\Mpr\PersetujuanMprController;
use App\Http\Controllers\User\AccountController;
use App\Http\Controllers\User\AuthController;
use App\Http\Controllers\User\DashboardController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Halaman utama: redirect ke portal jika diakses via subdomain karyawan.*, selain itu tampilkan landing page
Route::get('/', function (Request $request) {
    $host = strtolower($request->getHost());
    $portalDomain = strtolower((string) config('app.portal_domain', 'karyawan.umbulan.site'));

    if ($host === $portalDomain || str_starts_with($host, 'karyawan.')) {
        return auth()->check()
            ? redirect()->route('dashboard')
            : redirect()->route('login');
    }

    return view('welcome');
});

// ==========================================================
// GRUP PENGGUNA TAMU (Belum Login)
// ==========================================================
Route::middleware(['guest', 'prevent-back-history'])->group(function () {
    Route::get('/register', [AuthController::class, 'showRegisterForm'])->name('register');
    Route::post('/register', [AuthController::class, 'registerWeb'])->name('register.post');

    Route::get('/login', function () {
        return view('auth.login');
    })->name('login');
    Route::post('/login', [AuthController::class, 'loginWeb'])->name('login.post');

    // ======================================================
    // RUTE PEMULIHAN KATA SANDI (OTP)
    // ======================================================
    Route::get('/forgot-password', [ForgotPasswordController::class, 'showForgotForm'])->name('forgot');
    Route::get('/forgot', fn () => redirect()->route('forgot'));
    Route::post('/forgot-password/identify', [ForgotPasswordController::class, 'identify'])->name('forgot.identify');
    Route::post('/forgot-password/send-otp', [ForgotPasswordController::class, 'sendOtp'])->name('forgot.send_otp');
    Route::get('/forgot-password/verify-otp', [ForgotPasswordController::class, 'showVerifyOtpForm'])->name('forgot.verify_otp_view');
    Route::post('/forgot-password/verify-otp', [ForgotPasswordController::class, 'verifyOtp'])->name('forgot.verify_otp');
    Route::post('/forgot-password/resend-otp', [ForgotPasswordController::class, 'resendOtp'])->name('forgot.resend_otp');
    Route::get('/forgot-password/reset', [ForgotPasswordController::class, 'showResetPasswordForm'])->name('forgot.reset_password_view');
    Route::post('/forgot-password/reset', [ForgotPasswordController::class, 'resetPassword'])->name('forgot.update');

    // [SEC-12 FIX] Route legacy forgot password (dipelihara untuk backward compatibility, tetap dalam proteksi middleware guest)
    Route::post('/forgot/send-otp-mail', [ForgotPasswordController::class, 'sendOtp'])->name('legacy.forgot.send_otp');
    Route::post('/forgot/verify-otp-mail', [ForgotPasswordController::class, 'verifyOtp'])->name('legacy.forgot.verify_otp');
    Route::post('/forgot/update', [ForgotPasswordController::class, 'resetPassword'])->name('legacy.forgot.update');
});

// ==========================================================
// GRUP PENGGUNA TERAUTENTIKASI (Sudah Login)
// ==========================================================
Route::middleware(['auth', 'prevent-back-history'])->group(function () {

    // ----------------------------------------------------------
    // 1. Verifikasi Email
    // ----------------------------------------------------------
    Route::get('/auth/verify-email', function (Request $request) {
        if ($request->user()->hasVerifiedEmail()) {
            $intended = session()->get('url.intended');
            if ($intended && str_contains($intended, '/auth/verify-email')) {
                session()->forget('url.intended');
            }

            return redirect()->intended('/dashboard');
        }

        return view('auth.verify-email');
    })->name('verification.notice');

    Route::get('/email/verify', function () {
        return redirect()->route('verification.notice');
    });

    Route::get('/auth/verify-email/{id}/{hash}', function (EmailVerificationRequest $request) {
        $request->fulfill();

        return redirect('/dashboard')->with('message', 'Email berhasil diverifikasi!');
    })->middleware(['signed', 'throttle:6,1'])->name('verification.verify');

    Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
        $request->fulfill();

        return redirect('/dashboard')->with('message', 'Email berhasil diverifikasi!');
    })->middleware(['signed', 'throttle:6,1']);

    Route::post('/auth/email/verification-notification', function (Request $request) {
        $request->user()->sendEmailVerificationNotification();

        return back()->with('status', 'verification-link-sent');
    })->middleware('throttle:6,1')->name('verification.send');

    Route::post('/email/verification-notification', function (Request $request) {
        $request->user()->sendEmailVerificationNotification();

        return back()->with('status', 'verification-link-sent');
    })->middleware('throttle:6,1');

    // Proses keluar akun (Logout)
    Route::post('/logout', [AuthController::class, 'logoutWeb'])->name('logout');
    Route::get('/logout', fn () => redirect()->route('login'));

    // ----------------------------------------------------------
    // 2. Rute Internal (Wajib Email Terverifikasi)
    // ----------------------------------------------------------
    Route::middleware(['verified'])->group(function () {

        // Dashboard & Profil
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
        Route::get('/profile', [AccountController::class, 'index'])->name('account.index');
        Route::put('/profile/update', [AccountController::class, 'update'])->name('account.update');

        // Jadwal Kerja
        Route::post('/user/schedule/update', [AccountController::class, 'updateSchedule'])->name('user.schedule.update');

        // Modul Cuti
        Route::get('/cuti/riwayat', [PengajuanCutiController::class, 'riwayatView'])->name('cuti.riwayat');
        Route::get('/cuti/riwayat/{id}/detail', [PengajuanCutiController::class, 'detailCutiJSON']);

        // Modul CAR
        Route::get('/car/riwayat', [PengajuanCarController::class, 'index'])->name('car.riwayat');

        // Modul MPR
        Route::get('/mpr/riwayat', [PengajuanMprController::class, 'index'])->name('mpr.riwayat');

        // Pengajuan Modul (Wajib 5 Syarat Akun Lengkap)
        Route::middleware('account.complete')->group(function () {
            // Pengajuan Cuti
            Route::get('/cuti/ajukan', [PengajuanCutiController::class, 'create'])->name('cuti.create');
            Route::post('/cuti/store', [PengajuanCutiController::class, 'storeWeb'])->name('cuti.storeWeb');
            Route::get('/cuti/{id}/pembungkus', [PengajuanCutiController::class, 'viewSuratCuti'])->name('cuti.viewSurat');
            Route::get('/cuti/{id}/cetak', [PengajuanCutiController::class, 'cetakSuratCuti'])->name('cuti.cetak');
            Route::get('/cuti/ambil-subcuti/{id}', [PengajuanCutiController::class, 'handleSubCuti'])->name('cuti.ambilSubCuti');

            // Pengajuan & Cetak CAR
            Route::get('/car/ajukan', [PengajuanCarController::class, 'create'])->name('car.create');
            Route::post('/car/store', [PengajuanCarController::class, 'store'])->name('car.store');
            Route::get('/car/print/{id}', [DokumenCarController::class, 'print'])->name('car.print');

            // Pengajuan & Cetak MPR
            Route::get('/mpr/ajukan', [PengajuanMprController::class, 'create'])->name('mpr.create');
            Route::post('/mpr/store', [PengajuanMprController::class, 'store'])->name('mpr.store');
            Route::get('/mpr/cetak/{id}', [DokumenMprController::class, 'cetakPdf'])->name('mpr.cetak');
        });

        // Pengaturan Shift Awal Roster
        Route::post('/user/schedule/set-initial-shift', [AccountController::class, 'setInitialShift'])->name('user.schedule.set_initial_shift');

        // ==========================================================
        // GRUP KHUSUS ATASAN & ADMINISTRATOR
        // ==========================================================
        Route::middleware('atasan')->group(function () {
            // Manajemen Peran (Role)
            Route::get('/admin/role', [RoleController::class, 'index'])->name('admin.role.index');
            Route::post('/admin/role', [RoleController::class, 'store'])->name('admin.role.store');
            Route::put('/admin/role/{id}', [RoleController::class, 'update'])->name('admin.role.update');
            Route::delete('/admin/role/{id}', [RoleController::class, 'destroy'])->name('admin.role.destroy');

            // Manajemen Jobdesk
            Route::post('/admin/jobdesk', [RoleController::class, 'storeJobdesk'])->name('admin.jobdesk.store');
            Route::put('/admin/jobdesk/{id}', [RoleController::class, 'updateJobdesk'])->name('admin.jobdesk.update');
            Route::delete('/admin/jobdesk/{id}', [RoleController::class, 'destroyJobdesk'])->name('admin.jobdesk.destroy');

            // Jalur Utama Persetujuan Cuti
            Route::get('/admin/persetujuan/cuti', [PersetujuanCutiController::class, 'listAtasanView'])->name('admin.persetujuan.cuti');
            Route::post('/admin/persetujuan/cuti/proses/{id}', [PersetujuanCutiController::class, 'prosesPersetujuan'])->name('admin.persetujuan.cuti.proses');

            // Jalur Utama Persetujuan CAR
            Route::get('/admin/persetujuan/car', [PersetujuanCarController::class, 'listPengajuan'])->name('admin.persetujuan.car');
            Route::post('/admin/persetujuan/car/proses/{id}', [PersetujuanCarController::class, 'prosesPersetujuan'])->name('admin.persetujuan.car.process');

            // Jalur Utama Persetujuan MPR
            Route::get('/admin/persetujuan/mpr', [PersetujuanMprController::class, 'listPengajuan'])->name('admin.persetujuan.mpr');
            Route::post('/admin/persetujuan/mpr/proses/{id}', [PersetujuanMprController::class, 'prosesPersetujuan'])->name('admin.persetujuan.mpr.process');

            // Karyawan
            Route::get('/admin/karyawan', [KaryawanController::class, 'index'])->name('admin.karyawan.index');
            Route::get('/admin/karyawan/{id}/detail', [KaryawanController::class, 'showDetail'])->name('admin.karyawan.detail');
            Route::put('/admin/karyawan/saldo-cuti/{id}/update', [KaryawanController::class, 'updateSaldoCuti'])->name('admin.karyawan.saldo.update');
            Route::put('/admin/karyawan/{id}/roles', [KaryawanController::class, 'updateRoles'])->name('admin.karyawan.roles.update');
            Route::put('/admin/karyawan/{id}/station', [KaryawanController::class, 'updateStation'])->name('admin.karyawan.station.update');

            // CRUD Stasiun Kerja
            Route::get('/admin/stations', [StationController::class, 'index'])->name('admin.stations.index');
            Route::post('/admin/stations', [StationController::class, 'store'])->name('admin.stations.store');
            Route::put('/admin/stations/{id}', [StationController::class, 'update'])->name('admin.stations.update');
            Route::delete('/admin/stations/{id}', [StationController::class, 'destroy'])->name('admin.stations.destroy');
            Route::get('/admin/stations/{id}/karyawan', [StationController::class, 'getKaryawan'])->name('admin.stations.karyawan');

            // CRUD Hari Libur & Kalender SKB 3 Menteri
            Route::get('/admin/holidays', [HolidayController::class, 'index'])->name('admin.holidays.index');
            Route::post('/admin/holidays', [HolidayController::class, 'store'])->name('admin.holidays.store');
            Route::put('/admin/holidays/{id}', [HolidayController::class, 'update'])->name('admin.holidays.update');
            Route::delete('/admin/holidays/{id}', [HolidayController::class, 'destroy'])->name('admin.holidays.destroy');
            Route::post('/admin/holidays/sync', [HolidayController::class, 'sync'])->name('admin.holidays.sync');

            // Record Cuti
            Route::get('/admin/record/cuti', [RecordController::class, 'cuti'])->name('admin.record.cuti');
            Route::get('/admin/record/cuti/export', [RecordController::class, 'exportCuti'])->name('admin.record.cuti.export');

            // Record CAR
            Route::get('/admin/record/car', [RecordController::class, 'car'])->name('admin.record.car');
            Route::get('/admin/record/car/export', [RecordController::class, 'exportCar'])->name('admin.record.car.export');

            // Record MPR
            Route::get('/admin/record/mpr', [RecordController::class, 'mpr'])->name('admin.record.mpr');
            Route::get('/admin/record/mpr/export', [RecordController::class, 'exportMpr'])->name('admin.record.mpr.export');

            // Update Hierarchy Matrix
            Route::post('/admin/role/hierarchy/update', [RoleController::class, 'updateHierarchyMatrix'])->name('admin.role.hierarchy.update');
        });
    });
});
