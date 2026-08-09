<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Cuti\SaldoCuti;
use App\Models\User\User;
use App\Models\Cuti\JenisCuti;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class SaldoCutiController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $tahunSekarang = Carbon::now()->year;

        $saldo = SaldoCuti::with('jenisCuti')
            ->where('user_id', $user->id)
            ->where('tahun', $tahunSekarang)
            ->get();

        return view('saldo.index', compact('saldo', 'tahunSekarang'));
    }

    public function generateSaldoMassal(Request $request)
    {
        /** @var \App\Models\User\User $user */
        $user = Auth::user();

        if ($user) {
            $user->load('role');
        }

        // PERBAIKAN: Gunakan $user (bukan $operator)
        $roleName = strtolower($user->role->role_name ?? '');

        if (!in_array($roleName, ['manager', 'admin', 'hrd', 'full akses'])) {
            return redirect()->back()->with('error', 'Akses ditolak! Hanya manager atau admin yang bisa generate saldo.');
        }

        $tahunTarget = Carbon::now()->year;
        $karyawanLolos = 0;

        $users = User::all();
        $jenisCutis = JenisCuti::all();

        foreach ($users as $u) { // Mengubah variabel iterasi menjadi $u agar tidak bentrok dengan $user
            foreach ($jenisCutis as $jc) {

                $namaCuti = strtolower($jc->name_cuti ?? '');
                if (str_contains($namaCuti, 'sakit') || str_contains($namaCuti, 'melahirkan')) {
                    continue;
                }

                $cekSaldo = SaldoCuti::where('user_id', $u->id)
                    ->where('jenis_cuti_id', $jc->id)
                    ->where('tahun', $tahunTarget)
                    ->exists();

                if (!$cekSaldo) {
                    SaldoCuti::create([
                        'user_id'       => $u->id,
                        'jenis_cuti_id' => $jc->id,
                        'tahun'         => $tahunTarget,
                        'sisa_saldo'    => $jc->kuota_default ?? 12,
                    ]);
                    $karyawanLolos++;
                }
            }
        }

        return redirect()->back()->with('success', "Proses generate saldo cuti massal berhasil dilakukan. Total data baru terbuat: {$karyawanLolos}");
    }
}