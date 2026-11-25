<?php

use App\Http\Controllers\Absen\JadwalController;
use App\Http\Controllers\Absen\KehadiranController;
use App\Http\Controllers\Api\ApprovalApiController;
use App\Http\Controllers\Api\AuthApiController;
use App\Http\Controllers\Api\DashboardApiController;
use App\Http\Controllers\Api\NotificationApiController;
use App\Http\Controllers\Api\PengajuanApiController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| REST API Routes - APK Native & Mobile Integration (Laravel Sanctum)
|--------------------------------------------------------------------------
*/

// Endpoint Publik (Tanpa Token)
Route::post('/login', [AuthApiController::class, 'login'])->name('api.login');
Route::post('/register', [AuthApiController::class, 'register'])->name('api.register');
Route::get('/master-data', [AuthApiController::class, 'masterData'])->name('api.master_data');

Route::prefix('forgot-password')->group(function () {
    Route::post('/send-otp', [AuthApiController::class, 'sendResetOtp'])->name('api.forgot.send_otp');
    Route::post('/reset', [AuthApiController::class, 'resetPassword'])->name('api.forgot.reset');
});

// Endpoint Terproteksi Bearer Token Sanctum
Route::middleware('auth:sanctum')->group(function () {
    // Autentikasi & Manajemen Perangkat FCM
    Route::post('/logout', [AuthApiController::class, 'logout'])->name('api.logout');
    Route::get('/me', [AuthApiController::class, 'me'])->name('api.me');
    Route::post('/email/verify-otp', [AuthApiController::class, 'verifyEmailOtp'])->name('api.email.verify_otp');
    Route::post('/email/resend-otp', [AuthApiController::class, 'resendEmailOtp'])->name('api.email.resend_otp');
    Route::post('/devices/fcm-token', [AuthApiController::class, 'updateFcmToken'])->name('api.devices.fcm_token');

    // Kotak Masuk Push Notification In-App
    Route::prefix('notifications')->group(function () {
        Route::get('/', [NotificationApiController::class, 'index'])->name('api.notifications.index');
        Route::post('/read-all', [NotificationApiController::class, 'markAllAsRead'])->name('api.notifications.read_all');
        Route::post('/{id}/read', [NotificationApiController::class, 'markAsRead'])->name('api.notifications.read');
    });

    // Dashboard, Kalender & Riwayat Absensi
    Route::get('/dashboard', [DashboardApiController::class, 'index'])->name('api.dashboard');
    Route::get('/calendar', [DashboardApiController::class, 'calendar'])->name('api.calendar');
    Route::get('/attendance/history', [DashboardApiController::class, 'attendanceHistory'])->name('api.attendance.history');

    // Absensi Biometrik & Geolokasi
    Route::prefix('attendance')->group(function () {
        Route::get('/challenge', [KehadiranController::class, 'getBiometricChallenge'])->name('api.attendance.challenge');
        Route::post('/check-in', [KehadiranController::class, 'checkIn'])->name('api.attendance.check_in');
        Route::post('/check-out', [KehadiranController::class, 'checkOut'])->name('api.attendance.check_out');
    });

    // Kelengkapan Akun & Profil (Biometrik Wajah, Jadwal Kerja, TTD)
    Route::post('/user/face/register', [JadwalController::class, 'registerFace'])->name('api.user.face_register');
    Route::put('/user/schedule', [PengajuanApiController::class, 'updateSchedule'])->name('api.user.schedule');
    Route::post('/user/signature', [PengajuanApiController::class, 'updateSignature'])->name('api.user.signature');

    // Master Jenis Cuti
    Route::get('/cuti/jenis', [PengajuanApiController::class, 'jenisCuti'])->name('api.cuti.jenis');

    // Pengajuan Cuti, CAR & MPR (Memerlukan 4 Syarat Kesiapan Akun)
    Route::middleware('account.complete')->group(function () {
        Route::get('/cuti', [PengajuanApiController::class, 'cutiIndex'])->name('api.cuti.index');
        Route::post('/cuti', [PengajuanApiController::class, 'cutiStore'])->name('api.cuti.store');

        Route::get('/car', [PengajuanApiController::class, 'carIndex'])->name('api.car.index');
        Route::post('/car', [PengajuanApiController::class, 'carStore'])->name('api.car.store');

        Route::get('/mpr', [PengajuanApiController::class, 'mprIndex'])->name('api.mpr.index');
        Route::post('/mpr', [PengajuanApiController::class, 'mprStore'])->name('api.mpr.store');
    });

    // Persetujuan Atasan (Dynamic Approval Routing)
    Route::prefix('approvals')->group(function () {
        Route::get('/pending', [ApprovalApiController::class, 'pending'])->name('api.approvals.pending');
        Route::post('/{module}/{id}/process', [ApprovalApiController::class, 'process'])->name('api.approvals.process');
    });
});
