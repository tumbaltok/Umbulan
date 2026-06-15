<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    /**
     * Menampilkan halaman utama dashboard setelah pengguna berhasil login.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        // Mengambil data pengguna yang sedang login saat ini
        $user = Auth::user();

        // Mengirimkan data user ke view 'dashboard'
        // Pastikan Anda sudah memiliki file view di: resources/views/auth/dashboard.blade.php
        return view('auth.dashboard', compact('user'));
    }
}
