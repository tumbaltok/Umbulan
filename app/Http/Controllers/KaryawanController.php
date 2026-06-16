<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class KaryawanController extends Controller
{
    public function index()
    {
        // Mengambil data karyawan dengan melakukan join ke tabel roles dan stations
        $daftarKaryawan = DB::table('users')
            ->leftJoin('roles', 'users.role_id', '=', 'roles.id')
            ->leftJoin('stations', 'users.station_id', '=', 'stations.id') // Sesuaikan nama tabel stasiun Anda (misal: stations atau stasiuns)
            ->select(
                'users.id',
                'users.name',
                'users.email',
                'users.station_id',
                'roles.role_name',
                'stations.name as nama_stasiun' // Sesuaikan nama kolom stasiun di database Anda
            )
            ->orderBy('users.name', 'asc')
            ->get();

        return view('admin.karyawan.index', compact('daftarKaryawan'));
    }
}
