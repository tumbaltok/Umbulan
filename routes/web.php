<?php

use App\Http\Controllers\Absen\JadwalController;
use App\Http\Controllers\Absen\KehadiranController;
use App\Http\Controllers\Admin\AbsensiAdminController;
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
use App\Http\Controllers\Admin\WhatsAppSettingController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\PhoneVerificationController;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Halaman utama / landing page
Route::get('/', function () {
    return view('welcome3');
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

    // Kompatibilitas rute lama
    Route::post('/forgot/send-otp-mail', [ForgotPasswordController::class, 'sendOtp']);
    Route::post('/forgot/verify-otp-mail', [ForgotPasswordController::class, 'verifyOtp']);
    Route::post('/forgot/update', [ForgotPasswordController::class, 'resetPassword']);
});

// ==========================================================
// GRUP PENGGUNA TERAUTENTIKASI (Sudah Login)
// ==========================================================
Route::middleware(['auth', 'prevent-back-history'])->group(function () {

    // ----------------------------------------------------------
    // 1. Verifikasi Email (Tier 1)
    // ----------------------------------------------------------
    Route::get('/auth/verify-email', function (Request $request) {
        if ($request->user()->hasVerifiedEmail()) {
            if (!$request->user()->hasVerifiedPhone()) {
                return redirect()->route('verification.phone.notice');
            }

            $intended = session()->get('url.intended');
            if ($intended && (str_contains($intended, '/auth/verify-email') || str_contains($intended, '/auth/verify-phone'))) {
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

        return $request->user()->hasVerifiedPhone()
            ? redirect('/dashboard')->with('message', 'Email berhasil diverifikasi!')
            : redirect()->route('verification.phone.notice')->with('message', 'Email berhasil diverifikasi! Silakan lanjutkan verifikasi nomor WhatsApp.');
    })->middleware(['signed', 'throttle:6,1'])->name('verification.verify');

    Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
        $request->fulfill();

        return $request->user()->hasVerifiedPhone()
            ? redirect('/dashboard')->with('message', 'Email berhasil diverifikasi!')
            : redirect()->route('verification.phone.notice')->with('message', 'Email berhasil diverifikasi! Silakan lanjutkan verifikasi nomor WhatsApp.');
    })->middleware(['signed', 'throttle:6,1']);

    Route::post('/auth/email/verification-notification', function (Request $request) {
        $request->user()->sendEmailVerificationNotification();

        return back()->with('status', 'verification-link-sent');
    })->middleware('throttle:6,1')->name('verification.send');

    Route::post('/email/verification-notification', function (Request $request) {
        $request->user()->sendEmailVerificationNotification();

        return back()->with('status', 'verification-link-sent');
    })->middleware('throttle:6,1');

    // ----------------------------------------------------------
    // 2. Verifikasi WhatsApp OTP (Tier 2)
    // ----------------------------------------------------------
    Route::middleware('verified')->group(function () {
        Route::get('/auth/verify-phone', [PhoneVerificationController::class, 'notice'])->name('verification.phone.notice');
        Route::post('/auth/verify-phone', [PhoneVerificationController::class, 'verify'])->name('verification.phone.verify');
        Route::post('/auth/phone/send-otp', [PhoneVerificationController::class, 'sendOtp'])->middleware('throttle:3,1')->name('verification.phone.send');
        Route::post('/auth/phone/update-number', [PhoneVerificationController::class, 'updateNumber'])->name('verification.phone.update');
    });

    // Proses keluar akun (Logout)
    Route::post('/logout', [AuthController::class, 'logoutWeb'])->name('logout');
    Route::get('/logout', fn () => redirect()->route('login'));

    // ----------------------------------------------------------
    // 3. Rute Internal (Wajib Email & WhatsApp Terverifikasi)
    // ----------------------------------------------------------
    Route::middleware(['verified', 'phone.verified'])->group(function () {

        // Dashboard & Profil
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
        Route::get('/profile', [AccountController::class, 'index'])->name('account.index');
        Route::put('/profile/update', [AccountController::class, 'update'])->name('account.update');

        // Presensi Kehadiran
        Route::post('/attendance/check-in', [KehadiranController::class, 'checkIn'])->name('attendance.checkin');
        Route::post('/attendance/check-out', [KehadiranController::class, 'checkOut'])->name('attendance.checkout');

        // Jadwal Kerja & Registrasi Biometrik
        Route::post('/user/schedule/update', [JadwalController::class, 'updateSchedule'])->name('user.schedule.update');
        Route::post('/user/face/register', [JadwalController::class, 'registerFace'])->name('user.face.register');

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
        Route::get('/user/schedule/set-initial-shift', [JadwalController::class, 'showInitialShiftForm'])->name('user.schedule.show_initial_shift');
        Route::post('/user/schedule/set-initial-shift', [JadwalController::class, 'setInitialShift'])->name('user.schedule.set_initial_shift');

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
            Route::post('/admin/karyawan/{id}/reset-biometric', [KaryawanController::class, 'resetBiometric'])->name('admin.karyawan.reset_biometric');

            // CRUD Stasiun Kerja
            Route::get('/admin/stations', [StationController::class, 'index'])->name('admin.stations.index');
            Route::post('/admin/stations', [StationController::class, 'store'])->name('admin.stations.store');
            Route::put('/admin/stations/{id}', [StationController::class, 'update'])->name('admin.stations.update');
            Route::delete('/admin/stations/{id}', [StationController::class, 'destroy'])->name('admin.stations.destroy');
            Route::get('/admin/stations/{id}/karyawan', [StationController::class, 'getKaryawan'])->name('admin.stations.karyawan');

            // Record Cuti
            Route::get('/admin/record/cuti', [RecordController::class, 'cuti'])->name('admin.record.cuti');
            Route::get('/admin/record/cuti/export', [RecordController::class, 'exportCuti'])->name('admin.record.cuti.export');

            // Record CAR
            Route::get('/admin/record/car', [RecordController::class, 'car'])->name('admin.record.car');
            Route::get('/admin/record/car/export', [RecordController::class, 'exportCar'])->name('admin.record.car.export');

            // Record MPR
            Route::get('/admin/record/mpr', [RecordController::class, 'mpr'])->name('admin.record.mpr');
            Route::get('/admin/record/mpr/export', [RecordController::class, 'exportMpr'])->name('admin.record.mpr.export');

            // Rekap Absensi Harian
            Route::get('/admin/absensi', [AbsensiAdminController::class, 'index'])->name('admin.absensi.index');
            Route::get('/admin/absensi/export', [AbsensiAdminController::class, 'export'])->name('admin.absensi.export');

            // WhatsApp Gateway
            Route::get('/admin/whatsapp', [WhatsAppSettingController::class, 'index'])->name('admin.whatsapp.index');
            Route::get('/admin/whatsapp/status', [WhatsAppSettingController::class, 'status'])->name('admin.whatsapp.status');
            Route::get('/admin/whatsapp/qr', [WhatsAppSettingController::class, 'qr'])->name('admin.whatsapp.qr');
            Route::post('/admin/whatsapp/send-test', [WhatsAppSettingController::class, 'sendTest'])->name('admin.whatsapp.send_test');
            Route::post('/admin/whatsapp/disconnect', [WhatsAppSettingController::class, 'disconnect'])->name('admin.whatsapp.disconnect');

            // Update Hierarchy Matrix
            Route::post('/admin/role/hierarchy/update', [RoleController::class, 'updateHierarchyMatrix'])->name('admin.role.hierarchy.update');
        });
    });
});
