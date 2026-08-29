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
use App\Http\Controllers\Auth\PhoneVerificationController;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Halaman Selamat Datang / Landing Page Utama
Route::get('/', function () {
    return view('welcome3');
});

// ==========================================================
// GRUP GUEST (Belum Login)
// ==========================================================
Route::middleware(['guest', 'prevent-back-history'])->group(function () {
    Route::get('/register', [AuthController::class, 'showRegisterForm'])->name('register');
    Route::post('/register', [AuthController::class, 'registerWeb'])->name('register.post');

    Route::get('/login', function () {
        return view('auth.login');
    })->name('login');
    Route::post('/login', [AuthController::class, 'loginWeb'])->name('login.post');

    Route::get('/forgot', [AuthController::class, 'showForgotForm'])->name('forgot');
    Route::post('/forgot/send-otp-mail', [AuthController::class, 'sendOtpWeb'])->name('forgot.send_otp');
    Route::post('/forgot/verify-otp-mail', [AuthController::class, 'verifyOtpMailWeb'])->name('forgot.verify_otp');
    Route::post('/forgot/update', [AuthController::class, 'forgotWeb'])->name('forgot.update');
});

// ==========================================================
// GRUP AUTH (Sudah Login)
// ==========================================================
Route::middleware(['auth', 'prevent-back-history'])->group(function () {

    // ----------------------------------------------------------
    // 1. TIER 1: RUTE VERIFIKASI EMAIL (Dikecualikan dari 'verified')
    // ----------------------------------------------------------
    // Notice Verifikasi Email (Jika sudah verifikasi, lanjut ke verifikasi nomor telepon atau Dashboard)
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

    // Kompatibilitas URL /email/verify
    Route::get('/email/verify', function () {
        return redirect()->route('verification.notice');
    });

    // Eksekusi Link Verifikasi dari Email Pengguna
    Route::get('/auth/verify-email/{id}/{hash}', function (EmailVerificationRequest $request) {
        $request->fulfill();

        return $request->user()->hasVerifiedPhone()
            ? redirect('/dashboard')->with('message', 'Email berhasil diverifikasi!')
            : redirect()->route('verification.phone.notice')->with('message', 'Email berhasil diverifikasi! Silakan lanjutkan verifikasi nomor WhatsApp.');
    })->middleware(['signed', 'throttle:6,1'])->name('verification.verify');

    // Kompatibilitas link lama
    Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
        $request->fulfill();

        return $request->user()->hasVerifiedPhone()
            ? redirect('/dashboard')->with('message', 'Email berhasil diverifikasi!')
            : redirect()->route('verification.phone.notice')->with('message', 'Email berhasil diverifikasi! Silakan lanjutkan verifikasi nomor WhatsApp.');
    })->middleware(['signed', 'throttle:6,1']);

    // Kirim Ulang Link Verifikasi Email
    Route::post('/auth/email/verification-notification', function (Request $request) {
        $request->user()->sendEmailVerificationNotification();

        return back()->with('status', 'verification-link-sent');
    })->middleware('throttle:6,1')->name('verification.send');

    // Kompatibilitas kirim ulang lama
    Route::post('/email/verification-notification', function (Request $request) {
        $request->user()->sendEmailVerificationNotification();

        return back()->with('status', 'verification-link-sent');
    })->middleware('throttle:6,1');

    // ----------------------------------------------------------
    // 2. TIER 2: RUTE VERIFIKASI WHATSAPP OTP (Wajib Email Terverifikasi)
    // ----------------------------------------------------------
    Route::middleware('verified')->group(function () {
        Route::get('/auth/verify-phone', [PhoneVerificationController::class, 'notice'])->name('verification.phone.notice');
        Route::post('/auth/verify-phone', [PhoneVerificationController::class, 'verify'])->name('verification.phone.verify');
        Route::post('/auth/phone/send-otp', [PhoneVerificationController::class, 'sendOtp'])->middleware('throttle:3,1')->name('verification.phone.send');
        Route::post('/auth/phone/update-number', [PhoneVerificationController::class, 'updateNumber'])->name('verification.phone.update');
    });

    // Logout (Dapat diakses walau belum terverifikasi agar bisa beralih akun)
    Route::post('/logout', [AuthController::class, 'logoutWeb'])->name('logout');
    Route::get('/logout', fn () => redirect()->route('login'));

    // ----------------------------------------------------------
    // 3. SELURUH RUTE INTERNAL (Wajib Dua Tahap: Email & WhatsApp Terverifikasi)
    // ----------------------------------------------------------
    Route::middleware(['verified', 'phone.verified'])->group(function () {

        // Dashboard & Profil
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
        Route::get('/profile', [AccountController::class, 'index'])->name('account.index');
        Route::put('/profile/update', [AccountController::class, 'update'])->name('account.update');

        // Route Absensi & Pengecekan Lokasi/Wajah
        Route::post('/attendance/check-in', [KehadiranController::class, 'checkIn'])->name('attendance.checkin');
        Route::post('/attendance/check-out', [KehadiranController::class, 'checkOut'])->name('attendance.checkout');

        // Route Pengaturan Jadwal Kerja & Registrasi Wajah User
        Route::post('/user/schedule/update', [JadwalController::class, 'updateSchedule'])->name('user.schedule.update');
        Route::post('/user/face/register', [JadwalController::class, 'registerFace'])->name('user.face.register');

        // Fitur Cuti (Riwayat & Detail)
        Route::get('/cuti/riwayat', [PengajuanCutiController::class, 'riwayatView'])->name('cuti.riwayat');
        Route::get('/cuti/riwayat/{id}/detail', [PengajuanCutiController::class, 'detailCutiJSON']);

        // Fitur CAR (Riwayat)
        Route::get('/car/riwayat', [PengajuanCarController::class, 'index'])->name('car.riwayat');

        // Fitur MPR (Riwayat)
        Route::get('/mpr/riwayat', [PengajuanMprController::class, 'index'])->name('mpr.riwayat');

        // Fitur Internal (Wajib Akun Lengkap: Jadwal, TTD, & Biometrik Wajah)
        Route::middleware('account.complete')->group(function () {
            // Form Cuti
            Route::get('/cuti/ajukan', [PengajuanCutiController::class, 'create'])->name('cuti.create');
            Route::post('/cuti/store', [PengajuanCutiController::class, 'storeWeb'])->name('cuti.storeWeb');
            Route::get('/cuti/{id}/pembungkus', [PengajuanCutiController::class, 'viewSuratCuti'])->name('cuti.viewSurat');
            Route::get('/cuti/{id}/cetak', [PengajuanCutiController::class, 'cetakSuratCuti'])->name('cuti.cetak');
            Route::get('/cuti/ambil-subcuti/{id}', [PengajuanCutiController::class, 'handleSubCuti'])->name('cuti.ambilSubCuti');

            // Form & Cetak CAR
            Route::get('/car/ajukan', [PengajuanCarController::class, 'create'])->name('car.create');
            Route::post('/car/store', [PengajuanCarController::class, 'store'])->name('car.store');
            Route::get('/car/print/{id}', [DokumenCarController::class, 'print'])->name('car.print');

            // Form & Cetak MPR
            Route::get('/mpr/ajukan', [PengajuanMprController::class, 'create'])->name('mpr.create');
            Route::post('/mpr/store', [PengajuanMprController::class, 'store'])->name('mpr.store');
            Route::get('/mpr/cetak/{id}', [DokumenMprController::class, 'cetakPdf'])->name('mpr.cetak');
        });

        // Fitur Jadwal Kerja
        Route::get('/user/schedule/set-initial-shift', [JadwalController::class, 'showInitialShiftForm'])->name('user.schedule.show_initial_shift');
        Route::post('/user/schedule/set-initial-shift', [JadwalController::class, 'setInitialShift'])->name('user.schedule.set_initial_shift');

        // ==========================================================
        // GRUP ATASAN (Khusus) - Wajib 'atasan' & 'verified'
        // ==========================================================
        Route::middleware('atasan')->group(function () {
            // CRUD Role & Jabatan
            Route::get('/admin/role', [RoleController::class, 'index'])->name('admin.role.index');
            Route::post('/admin/role', [RoleController::class, 'store'])->name('admin.role.store');
            Route::put('/admin/role/{id}', [RoleController::class, 'update'])->name('admin.role.update');
            Route::delete('/admin/role/{id}', [RoleController::class, 'destroy'])->name('admin.role.destroy');

            // Kelola Jobdesk Baru
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
