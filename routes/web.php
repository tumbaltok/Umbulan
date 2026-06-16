<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PengajuanCutiController;
use App\Http\Controllers\StationController;
use App\Http\Controllers\KaryawanController;
use App\Http\Controllers\AccountController;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware('guest')->group(function () {
    // Halaman & Proses Login Web
    Route::get('/login', function () {
        return view('auth.login');
    })->name('login');

    Route::post('/login', [AuthController::class, 'loginWeb'])->name('login.post');

    // Halaman & Proses Registrasi Web (Menghubungkan form register yang kita buat)
    Route::get('/register', function () {
        return view('auth.register');
    })->name('register');

    Route::post('/register', [AuthController::class, 'registerWeb'])->name('register.post');
});

// Rute khusus untuk pengguna yang SUDAH login (Auth)
Route::middleware('auth')->group(function () {
    // Anda bisa menambahkan rute dashboard atau pengajuan cuti versi web di sini nanti
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Route Baru untuk Pengaturan Akun
    Route::get('/profile', [AccountController::class, 'index'])->name('account.index');
    Route::put('/profile/update', [AccountController::class, 'update'])->name('account.update');

    Route::get('/admin/karyawan', [KaryawanController::class, 'index'])->name('karyawan.index');

    // Rute Baru untuk Manajemen Stasiun Kerja (Hanya untuk Admin/Atasan)
    Route::get('/admin/stations', [StationController::class, 'index'])->name('stations.index');
    // Route::post('/admin/stations/store', [StationController::class, 'store'])->name('stations.store');
    // Route::delete('/admin/stations/{id}', [StationController::class, 'destroy'])->name('stations.destroy');

    Route::get('/admin/persetujuan', [PengajuanCutiController::class, 'listAtasanView'])->name('admin.persetujuan');
    Route::post('/admin/persetujuan/proses/{id}', [PengajuanCutiController::class, 'prosesPersetujuan'])->name('cuti.proses-persetujuan');

    // Rute untuk pengelolaan cuti versi Web
    Route::get('/cuti/ajukan', [PengajuanCutiController::class, 'createView'])->name('cuti.ajukan');
    Route::post('/cuti/store', [PengajuanCutiController::class, 'storeWeb'])->name('cuti.storeWeb');
    Route::get('/cuti/riwayat', [PengajuanCutiController::class, 'riwayatView'])->name('cuti.riwayat');

    // Rute Atasan untuk approve cuti lewat Web
    Route::get('/admin/persetujuan', [PengajuanCutiController::class, 'listAtasanView'])->name('admin.persetujuan');

    // Proses Logout Web
    Route::post('/logout', [AuthController::class, 'logoutWeb'])->name('logout');
});

Route::middleware(['auth', 'atasan'])->group(function () {
    // Semua halaman di dalam grup ini hanya bisa dibuka oleh Supervisor & Manager
    Route::get('/admin/persetujuan', [PengajuanCutiController::class, 'listAtasanView'])->name('admin.persetujuan');
    Route::post('/admin/persetujuan/proses/{id}', [PengajuanCutiController::class, 'prosesPersetujuan'])->name('cuti.proses-persetujuan');

    // Anda bisa menambahkan rute manajemen stasiun kerja admin di sini nanti
});
