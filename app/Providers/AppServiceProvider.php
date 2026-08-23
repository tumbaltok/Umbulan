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

                // Ambil seluruh tree_code dan role dari semua jabatan yang diampu user
                $userRoles = $atasan->roles;
                $atasanTreeCodes = $userRoles->pluck('tree_code')->filter()->toArray();
                $hasAdminRole = $userRoles->contains('id', 1);
                $hasTopRole   = $userRoles->contains(fn($r) => empty($r->parent_role_id));

                if ($hasAdminRole || $hasTopRole) {
                    // Top level/Admin: Akses pantau seluruh antrean global
                    $jumlahCar  = PengajuanCar::where('status_akhir', 'pending')->count();
                    $jumlahCuti = PengajuanCuti::where('status_akhir', 'pending')->count();
                    $jumlahMpr  = PengajuanMpr::where('status_akhir', 'pending')->count();
                } else {
                    // 1. HITUNG ANTREAN CUTI
                    $jumlahCuti = PengajuanCuti::where(function ($q) use ($atasan, $atasanTreeCodes) {
                        $q->where(function ($sub) use ($atasan, $atasanTreeCodes) {
                            $sub->where('status_tahap_1', 'pending')
                                ->whereHas('user', function ($uq) use ($atasan, $atasanTreeCodes) {
                                    $uq->where('atasan_langsung_id', $atasan->id)
                                       ->orWhereHas('roles', function ($rq) use ($atasanTreeCodes) {
                                           $rq->where(function ($treeQ) use ($atasanTreeCodes) {
                                               foreach ($atasanTreeCodes as $code) {
                                                   $treeQ->orWhere('tree_code', 'LIKE', $code . '.%');
                                               }
                                           });
                                       });
                                });
                        })->orWhere(function ($sub) use ($atasan, $atasanTreeCodes) {
                            $sub->where('status_tahap_1', 'approved')
                                ->where('status_tahap_2', 'pending')
                                ->where('status_tahap_2', '!=', 'not_required')
                                ->whereHas('user', function ($uq) use ($atasan, $atasanTreeCodes) {
                                    $uq->where('atasan_dua_id', $atasan->id)
                                       ->orWhereHas('roles', function ($rq) use ($atasanTreeCodes) {
                                           $rq->where(function ($treeQ) use ($atasanTreeCodes) {
                                               foreach ($atasanTreeCodes as $code) {
                                                   $treeQ->orWhere('tree_code', 'LIKE', $code . '.%');
                                               }
                                           });
                                       });
                                });
                        });
                    })->count();

                    // 2. HITUNG ANTREAN CAR
                    $jumlahCar = PengajuanCar::where(function ($q) use ($atasan, $atasanTreeCodes) {
                        $q->where(function ($sub) use ($atasan, $atasanTreeCodes) {
                            $sub->where('status_tahap_1', 'pending')
                                ->whereHas('user', function ($uq) use ($atasan, $atasanTreeCodes) {
                                    $uq->where('atasan_langsung_id', $atasan->id)
                                       ->orWhereHas('roles', function ($rq) use ($atasanTreeCodes) {
                                           $rq->where(function ($treeQ) use ($atasanTreeCodes) {
                                               foreach ($atasanTreeCodes as $code) {
                                                   $treeQ->orWhere('tree_code', 'LIKE', $code . '.%');
                                               }
                                           });
                                       });
                                });
                        })->orWhere(function ($sub) use ($atasan, $atasanTreeCodes) {
                            $sub->where('total_approval_levels', 2)
                                ->where('status_tahap_1', 'approved')
                                ->where('status_tahap_2', 'pending')
                                ->whereHas('user', function ($uq) use ($atasan, $atasanTreeCodes) {
                                    $uq->where('atasan_dua_id', $atasan->id)
                                       ->orWhereHas('roles', function ($rq) use ($atasanTreeCodes) {
                                           $rq->where(function ($treeQ) use ($atasanTreeCodes) {
                                               foreach ($atasanTreeCodes as $code) {
                                                   $treeQ->orWhere('tree_code', 'LIKE', $code . '.%');
                                               }
                                           });
                                       });
                                });
                        });
                    })->count();

                    // 3. HITUNG ANTREAN MPR
                    $jumlahMpr = PengajuanMpr::where('status_akhir', 'pending')
                        ->whereHas('user', function ($uq) use ($atasanTreeCodes) {
                            $uq->whereHas('roles', function ($rq) use ($atasanTreeCodes) {
                                $rq->where(function ($treeQ) use ($atasanTreeCodes) {
                                    foreach ($atasanTreeCodes as $code) {
                                        $treeQ->orWhere('tree_code', 'LIKE', $code . '.%');
                                    }
                                });
                            });
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
