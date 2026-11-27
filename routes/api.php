<?php

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

    // Dashboard & Kalender Kerja
    Route::get('/dashboard', [DashboardApiController::class, 'index'])->name('api.dashboard');
    Route::get('/calendar', [DashboardApiController::class, 'calendar'])->name('api.calendar');

    // Kelengkapan Akun & Profil (Jadwal Kerja & TTD Digital)
    Route::put('/user/schedule', [PengajuanApiController::class, 'updateSchedule'])->name('api.user.schedule');
    Route::post('/user/signature', [PengajuanApiController::class, 'updateSignature'])->name('api.user.signature');

    // Master Jenis Cuti
    Route::get('/cuti/jenis', [PengajuanApiController::class, 'jenisCuti'])->name('api.cuti.jenis');

    // Pengajuan Cuti, CAR & MPR (Memerlukan 3 Syarat Kesiapan Akun)
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
