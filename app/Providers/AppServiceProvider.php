<?php

namespace App\Providers;

use App\Models\Car\PengajuanCar;
use App\Models\Cuti\PengajuanCuti;
use App\Models\Mpr\PengajuanMpr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        View::composer(['layouts.app', 'partials.sidebar'], function ($view) {
            if (Auth::check()) {
                $atasan = Auth::user();
                $atasanRole = $atasan->role;

                // 1. TOP LEVEL / ADMIN (Tanpa Parent): Memantau seluruh antrean global pending
                if (empty($atasanRole->parent_role_id)) {
                    $jumlahCar  = PengajuanCar::where('status_akhir', 'pending')->count();
                    $jumlahCuti = PengajuanCuti::where('status_akhir', 'pending')->count();
                    $jumlahMpr  = PengajuanMpr::where('status_akhir', 'pending')->count();
                } else {
                    // 2. ATASAN BERBASIS TREE_CODE
                    $atasanTreeCode = $atasanRole->tree_code;

                    // A. Hitung Antrean CAR (Menggunakan total_approval_levels)
                    $jumlahCar = PengajuanCar::where(function ($q) use ($atasan, $atasanTreeCode) {
                        $q->where(function ($sub) use ($atasan, $atasanTreeCode) {
                            $sub->where('status_tahap_1', 'pending')
                                ->whereHas('user', function ($uq) use ($atasan, $atasanTreeCode) {
                                    $uq->where('atasan_langsung_id', $atasan->id)
                                       ->orWhereHas('role', fn($rq) => $rq->where('tree_code', 'LIKE', $atasanTreeCode . '.%'));
                                });
                        })->orWhere(function ($sub) use ($atasan, $atasanTreeCode) {
                            $sub->where('total_approval_levels', 2)
                                ->where('status_tahap_1', 'approved')
                                ->where('status_tahap_2', 'pending')
                                ->whereHas('user', function ($uq) use ($atasan, $atasanTreeCode) {
                                    $uq->where('atasan_dua_id', $atasan->id)
                                       ->orWhereHas('role', fn($rq) => $rq->where('tree_code', 'LIKE', $atasanTreeCode . '.%'));
                                });
                        });
                    })->count();

                    // B. Hitung Antrean CUTI (Mengecek status_tahap_2 !== 'not_required')
                    $jumlahCuti = PengajuanCuti::where(function ($q) use ($atasan, $atasanTreeCode) {
                        $q->where(function ($sub) use ($atasan, $atasanTreeCode) {
                            $sub->where('status_tahap_1', 'pending')
                                ->whereHas('user', function ($uq) use ($atasan, $atasanTreeCode) {
                                    $uq->where('atasan_langsung_id', $atasan->id)
                                       ->orWhereHas('role', fn($rq) => $rq->where('tree_code', 'LIKE', $atasanTreeCode . '.%'));
                                });
                        })->orWhere(function ($sub) use ($atasan, $atasanTreeCode) {
                            $sub->where('status_tahap_1', 'approved')
                                ->where('status_tahap_2', 'pending')
                                ->where('status_tahap_2', '!=', 'not_required')
                                ->whereHas('user', function ($uq) use ($atasan, $atasanTreeCode) {
                                    $uq->where('atasan_dua_id', $atasan->id)
                                       ->orWhereHas('role', fn($rq) => $rq->where('tree_code', 'LIKE', $atasanTreeCode . '.%'));
                                });
                        });
                    })->count();

                    // C. Hitung Antrean MPR
                    $jumlahMpr = PengajuanMpr::where('status_akhir', 'pending')
                        ->whereHas('user', function ($uq) use ($atasanTreeCode) {
                            $uq->whereHas('role', fn($rq) => $rq->where('tree_code', 'LIKE', $atasanTreeCode . '.%'));
                        })->count();
                }

                $view->with([
                    'jumlahCuti' => $jumlahCuti,
                    'jumlahCar'  => $jumlahCar,
                    'jumlahMpr'  => $jumlahMpr,
                ]);
            }
        });
    }
}
