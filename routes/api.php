<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PengajuanCutiController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\SaldoCutiController;
use App\Http\Controllers\AbsensiController;

Route::middleware('guest')->group(function () {
    Route::post('/login', [AuthController::class, 'loginApi'])->name('api.login');
    Route::post('/register', [AuthController::class, 'registerApi'])->name('api.register');
});

Route::middleware(['auth:sanctum'])->group(function () {

    // Fitur Absensi Utama Karyawan
    Route::post('/absensi/masuk', [AbsensiController::class, 'absenMasuk']);
    Route::post('/absensi/pulang', [AbsensiController::class, 'absenPulang']);
    Route::get('/absensi/status-hari-ini', [AbsensiController::class, 'statusAbsenHariIni']);
    Route::get('/absensi/riwayat', [AbsensiController::class, 'riwayatAbsensiDiri']);

    // Fitur Saldo Cuti
    Route::get('/saldo-cuti', [SaldoCutiController::class, 'index']); // Karyawan cek saldonya
    Route::post('/saldo-cuti/generate', [SaldoCutiController::class, 'generateSaldoMassal']); // Admin/HRD isi saldo massal

    // Rute Antrean Atasan (Ditaruh di atas route {id} agar tidak bentrok)
    Route::get('/atasan/antrean-cuti', [PengajuanCutiController::class, 'listPengajuanAtasan']);

    // Rute untuk karyawan membuat pengajuan cuti
    Route::get('/pengajuan-cuti', [PengajuanCutiController::class, 'index']);
    Route::post('/pengajuan-cuti', [PengajuanCutiController::class, 'store']);
    Route::get('/pengajuan-cuti/{id}', [PengajuanCutiController::class, 'show']);

    // Rute untuk SPV/Manager melakukan approve atau reject
    Route::put('/pengajuan-cuti/{id}/approve', [PengajuanCutiController::class, 'approve']);
    Route::put('/pengajuan-cuti/{id}/reject', [PengajuanCutiController::class, 'reject']);

    //route untuk logout
    Route::post('/logout', [AuthController::class, 'logoutApi'])->name('api.logout');
});
