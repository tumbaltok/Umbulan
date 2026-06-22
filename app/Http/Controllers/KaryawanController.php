<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User; // Menggunakan model Eloquent User
use Illuminate\Http\JsonResponse;

class KaryawanController extends Controller
{
    public function index()
    {
        // AMAN DARI N+1: Pastikan 'cuti_aktif' sudah didefinisikan di app\Models\User.php
        $daftarKaryawan = User::with(['role', 'station', 'cuti_aktif'])
            ->orderBy('name', 'asc')
            ->get();

        return view('admin.karyawan.index', compact('daftarKaryawan'));
    }

    public function showDetail(int $id): JsonResponse
    {
        // Memanfaatkan Eloquent + Eager Loading untuk data detail
        $karyawan = User::with(['role', 'station'])->find($id);

        if (!$karyawan) {
            return response()->json(['message' => 'Karyawan tidak ditemukan'], 404);
        }

        // AMAN DARI LEAK PASSWORD & SAFE FROM NULL POINTER EXCEPTION
        return response()->json([
            'id'            => $karyawan->id,
            'nip'           => $karyawan->nip,
            'name'          => $karyawan->name,
            'email'         => $karyawan->email,
            'phone_number'  => $karyawan->phone_number,
            'profile_photo' => $karyawan->profile_photo,
            // Menggunakan optional() atau null-coalescing yang aman jika relasi null
            'role_name'     => optional($karyawan->role)->role_name ?? 'Tidak Ada Role',
            'nama_stasiun'  => optional($karyawan->station)->name ?? '-',
            'job_title'     => $karyawan->job_title,
        ]);
    }
}
