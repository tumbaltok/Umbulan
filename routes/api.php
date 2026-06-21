<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PengajuanCutiController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Route Auth API (Dapat diakses tanpa login untuk mendapatkan Token)
Route::post('/login', [AuthController::class, 'loginAPI']); // Endpoint: /api/login
Route::post('/register', [AuthController::class, 'registerAPI']); // Endpoint: /api/register

// Grup Route API yang Membutuhkan Validasi Token (Stateless)
Route::middleware('auth:sanctum')->group(function () {

    // 1. KARYAWAN: Mengambil riwayat cuti milik diri sendiri (Menerima data JSON)
    // Endpoint: GET /api/cuti
    Route::get('/cuti', [PengajuanCutiController::class, 'index']);

    // 2. KARYAWAN: Mengirimkan form pengajuan cuti baru via Mobile Aplikasi
    // Endpoint: POST /api/cuti/store
    Route::post('/cuti/store', [PengajuanCutiController::class, 'store']);

    // 3. KARYAWAN/ATASAN: Melihat data detail satu pengajuan berdasarkan ID tertentu
    // Endpoint: GET /api/cuti/{id}
    Route::get('/cuti/{id}', [PengajuanCutiController::class, 'show']);

    // 4. ATASAN: Mendapatkan daftar pengajuan cuti masuk dari bawahan yang harus diproses
    // Endpoint: GET /api/cuti/atasan/list
    Route::get('/cuti/atasan/list', [PengajuanCutiController::class, 'listPengajuanAtasan']);

    // 5. ATASAN: Menyetujui atau Menolak cuti bawahan (Aksi: approved / rejected)
    // Endpoint: POST /api/cuti/{id}/approve
    Route::post('/cuti/{id}/approve', [PengajuanCutiController::class, 'approve']);

    // Proses Logout API (Menghapus Token Aktif)
    Route::post('/logout', [AuthController::class, 'logoutAPI']); // Endpoint: /api/logout
});
