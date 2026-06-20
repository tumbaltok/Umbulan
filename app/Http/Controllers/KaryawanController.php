<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class KaryawanController extends Controller
{
    public function index()
    {

        $hariIni = now()->format('Y-m-d'); // Mengambil tanggal hari ini

        // Mengambil data karyawan dengan melakukan join ke tabel roles dan stations
        $daftarKaryawan = DB::table('users')
            ->leftJoin('roles', 'users.role_id', '=', 'roles.id')
            ->leftJoin('stations', 'users.station_id', '=', 'stations.id') // Sesuaikan nama tabel stasiun Anda (misal: stations atau stasiuns)
            ->leftJoin('pengajuan_cutis', function($join) use ($hariIni) {
                $join->on('users.id', '=', 'pengajuan_cutis.user_id')
                    ->where('pengajuan_cutis.status_akhir', '=', 'Approved') // Jika menggunakan kolom status
                    ->whereDate('pengajuan_cutis.tanggal_mulai', '<=', $hariIni)
                    ->whereDate('pengajuan_cutis.tanggal_selesai', '>=', $hariIni);
            })
            ->select(
                'users.nip',
                'users.id',
                'users.name',
                'users.email',
                'users.station_id',
                'roles.role_name',
                'users.job_title',
                'users.profile_photo',
                'stations.name as nama_stasiun', // Sesuaikan nama kolom stasiun di database Anda
                // 'users.status_jadwal',
                'pengajuan_cutis.id as sedang_cuti'
            )
            ->orderBy('users.name', 'asc')
            ->get();

        return view('admin.karyawan.index', compact('daftarKaryawan'));
    }

    public function showDetail($id)
    {
        // Menggunakan Query Builder yang disesuaikan dengan query Anda sebelumnya
        $karyawan = DB::table('users')
            ->leftJoin('roles', 'users.role_id', '=', 'roles.id')
            ->leftJoin('stations', 'users.station_id', '=', 'stations.id')
            ->select([
                'users.*',
                'roles.role_name',
                'stations.name as nama_stasiun'
            ])
            ->where('users.id', $id)
            ->first();

        if (!$karyawan) {
            return response()->json(['message' => 'Karyawan tidak ditemukan'], 404);
        }

        // Mengembalikan data berupa JSON agar bisa ditangkap oleh JavaScript/AJAX
        return response()->json($karyawan);
    }
}
