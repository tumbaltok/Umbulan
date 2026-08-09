<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\User\AuthController;
use App\Http\Controllers\User\DashboardController;
use App\Http\Controllers\Cuti\PengajuanCutiController;
use App\Http\Controllers\Admin\StationController;
use App\Http\Controllers\Admin\KaryawanController;
use App\Http\Controllers\User\AccountController;
use App\Http\Controllers\Admin\RecordController;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use App\Http\Controllers\Car\PengajuanCarController;
use App\Http\Controllers\Mpr\PengajuanMprController;
use App\Http\Controllers\Absen\KehadiranController;
use App\Http\Controllers\Absen\JadwalController;
use App\Http\Controllers\Admin\RoleController;
use Illuminate\Http\Request;

// Halaman Selamat Datang / Landing Page Utama
Route::get('/', function () {
    return view('welcome1');
});

// ==========================================================
// GRUP GUEST (Belum Login)
// ==========================================================
Route::middleware('guest')->group(function () {
    Route::get('/register', [AuthController::class, 'showRegisterForm'])->name('register');
    Route::post('/register', [AuthController::class, 'registerWeb'])->name('register.post');

    Route::get('/login', function () { return view('auth.login'); })->name('login');
    Route::post('/login', [AuthController::class, 'loginWeb'])->name('login.post');

    Route::get('/forgot', [AuthController::class, 'showForgotForm'])->name('forgot');
    Route::post('/forgot/send-otp-mail', [AuthController::class, 'sendOtpWeb'])->name('forgot.send_otp');
    Route::post('/forgot/verify-otp-mail', [AuthController::class, 'verifyOtpMailWeb'])->name('forgot.verify_otp');
    Route::post('/forgot/update', [AuthController::class, 'forgotWeb'])->name('forgot.update');
});

// ==========================================================
// GRUP AUTH (Sudah Login - Umum Karyawan & Atasan)
// ==========================================================
Route::middleware('auth')->group(function () {

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
            Route::get('/cuti/ambil-subcuti/{id}', [PengajuanCutiController::class, 'handleSubCuti'])->name('cuti.ambilSubCuti');

            // Form CAR
            Route::get('/car/create', [PengajuanCarController::class, 'create'])->name('car.create');
            Route::post('/car/store', [PengajuanCarController::class, 'store'])->name('car.store');
            Route::get('/car/print/{id}', [PengajuanCarController::class, 'print'])->name('car.print');

            // Form MPR
            Route::get('/mpr/create', [PengajuanMprController::class, 'create'])->name('mpr.create');
            Route::post('/mpr/store', [PengajuanMprController::class, 'store'])->name('mpr.store');
            Route::get('/mpr/cetak/{id}', [PengajuanMprController::class, 'cetakPdf'])->name('mpr.cetak');
        });
    });

    // Fitur Jadwal Kerja (Set Initial Shift)
    Route::get('/user/schedule/set-initial-shift', [JadwalController::class, 'showInitialShiftForm'])->name('user.schedule.show_initial_shift');
    Route::post('/user/schedule/set-initial-shift', [JadwalController::class, 'setInitialShift'])->name('user.schedule.set_initial_shift');

    // Logout
    Route::post('/logout', [AuthController::class, 'logoutWeb'])->name('logout');
});

// ==========================================================
// GRUP ATASAN (Khusus)
// ==========================================================
Route::middleware(['auth', 'atasan'])->group(function () {
    // CRUD Role & Jabatan
    Route::get('/admin/role', [RoleController::class, 'index'])->name('admin.role.index');
    Route::post('/admin/role', [RoleController::class, 'store'])->name('admin.role.store');
    Route::put('/admin/role/{id}', [RoleController::class, 'update'])->name('admin.role.update');
    Route::delete('/admin/role/{id}', [RoleController::class, 'destroy'])->name('admin.role.destroy');

    // Kelola Jobdesk Baru (URL diselaraskan menjadi /admin/jobdesk)
    Route::post('/admin/jobdesk', [RoleController::class, 'storeJobdesk'])->name('admin.jobdesk.store');
    Route::put('/admin/jobdesk/{id}', [RoleController::class, 'updateJobdesk'])->name('admin.jobdesk.update');
    Route::delete('/admin/jobdesk/{id}', [RoleController::class, 'destroyJobdesk'])->name('admin.jobdesk.destroy');

    // Jalur Utama Persetujuan Cuti
    Route::get('/admin/persetujuan/cuti', [PengajuanCutiController::class, 'listPengajuan'])->name('admin.persetujuan.cuti');
    Route::post('/admin/persetujuan/cuti/proses/{id}', [PengajuanCutiController::class, 'prosesPersetujuan'])->name('admin.persetujuan.cuti.proses');
    
    // Jalur Utama Persetujuan CAR
    Route::get('/admin/persetujuan/car', [PengajuanCarController::class, 'listPengajuan'])->name('admin.persetujuan.car');
    Route::post('/admin/persetujuan/car/proses/{id}', [PengajuanCarController::class, 'prosesPersetujuan'])->name('admin.persetujuan.car.process');
    
    // Jalur Utama Persetujuan MPR
    Route::get('/admin/persetujuan/mpr', [PengajuanMprController::class, 'listPengajuan'])->name('admin.persetujuan.mpr');
    Route::post('/admin/persetujuan/mpr/proses/{id}', [PengajuanMprController::class, 'prosesPersetujuan'])->name('admin.persetujuan.mpr.process');
    
    // Karyawan
    Route::get('/admin/karyawan', [KaryawanController::class, 'index'])->name('admin.karyawan.index');
    Route::get('/admin/karyawan/{id}/detail', [KaryawanController::class, 'showDetail'])->name('admin.karyawan.detail');
    Route::put('/admin/karyawan/saldo-cuti/{id}/update', [KaryawanController::class, 'updateSaldoCuti'])->name('admin.karyawan.saldo.update');
    
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
});