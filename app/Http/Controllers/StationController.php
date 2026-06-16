<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StationController extends Controller
{
    public function index()
    {
        // Mengambil daftar stasiun dan menghitung jumlah karyawan di tiap stasiun
        $daftarStasiun = DB::table('stations')
            ->leftJoin('users', 'stations.id', '=', 'users.station_id')
            ->select(
                'stations.id',
                'stations.name', // Sesuaikan dengan nama kolom asli Anda (misal: name atau name_stasiun)
                DB::raw('COUNT(users.id) as total_karyawan')
            )
            ->groupBy('stations.id', 'stations.name') // Sesuaikan kolom groupBy jika nama kolom berbeda
            ->orderBy('stations.name', 'asc')
            ->get();

        return view('admin.stations.index', compact('daftarStasiun'));
    }
}
