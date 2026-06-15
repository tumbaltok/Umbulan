<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware('guest')->group(function () {
    // Halaman & Proses Login Web
    Route::get('/login', function () {
        return view('login');
    })->name('login');

    Route::post('/login', [AuthController::class, 'loginWeb'])->name('login.post');

    // Halaman & Proses Registrasi Web (Menghubungkan form register yang kita buat)
    Route::get('/register', function () {
        return view('register');
    })->name('register');

    Route::post('/register', [AuthController::class, 'registerWeb'])->name('register.post');
});

// Rute khusus untuk pengguna yang SUDAH login (Auth)
Route::middleware('auth')->group(function () {
    // Proses Logout Web
    Route::post('/logout', [AuthController::class, 'logoutWeb'])->name('logout');

    // Anda bisa menambahkan rute dashboard atau pengajuan cuti versi web di sini nanti
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
});
