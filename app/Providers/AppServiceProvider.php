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
        View::composer(['layouts.app', 'partials.sidebar', 'dashboard.dashboardindex'], function ($view) {
            if (Auth::check()) {
                $atasan = Auth::user();

                // Ambil single role dari user yang sedang login
                $userRole = $atasan->role;
                $atasanRoleId = $atasan->role_id;
                $treeCode = $userRole?->tree_code;
                $hasAdminRole = $userRole?->id === 1;
                $hasTopRole = empty($userRole?->parent_role_id);

                if ($hasAdminRole || $hasTopRole) {
                    // Top level/Admin: Akses pantau seluruh antrean global
                    $jumlahCar  = PengajuanCar::where('status_akhir', 'pending')->count();
                    $jumlahCuti = PengajuanCuti::where('status_akhir', 'pending')->count();
                    $jumlahMpr  = PengajuanMpr::where('status_akhir', 'pending')->count();
                } else {
                    // 1. HITUNG ANTREAN CUTI
                    $jumlahCuti = PengajuanCuti::where(function ($q) use ($atasanRoleId, $treeCode) {
                        $q->where(function ($sub) use ($atasanRoleId, $treeCode) {
                            $sub->where('status_tahap_1', 'pending')
                                ->whereHas('user', function ($uq) use ($atasanRoleId, $treeCode) {
                                    $uq->whereHas('role', function ($rq) use ($atasanRoleId, $treeCode) {
                                        $rq->where('parent_role_id', $atasanRoleId);
                                        if ($treeCode) {
                                            $rq->orWhere('tree_code', 'LIKE', $treeCode . '.%');
                                        }
                                    });
                                });
                        })->orWhere(function ($sub) use ($atasanRoleId, $treeCode) {
                            $sub->where('status_tahap_1', 'approved')
                                ->where('status_tahap_2', 'pending')
                                ->where('status_tahap_2', '!=', 'not_required')
                                ->whereHas('user', function ($uq) use ($atasanRoleId, $treeCode) {
                                    $uq->whereHas('role', function ($rq) use ($atasanRoleId, $treeCode) {
                                        $rq->where('parent_role_id', $atasanRoleId);
                                        if ($treeCode) {
                                            $rq->orWhere('tree_code', 'LIKE', $treeCode . '.%');
                                        }
                                    });
                                });
                        });
                    })->count();

                    // 2. HITUNG ANTREAN CAR
                    $jumlahCar = PengajuanCar::where(function ($q) use ($atasanRoleId, $treeCode) {
                        $q->where(function ($sub) use ($atasanRoleId, $treeCode) {
                            $sub->where('status_tahap_1', 'pending')
                                ->whereHas('user', function ($uq) use ($atasanRoleId, $treeCode) {
                                    $uq->whereHas('role', function ($rq) use ($atasanRoleId, $treeCode) {
                                        $rq->where('parent_role_id', $atasanRoleId);
                                        if ($treeCode) {
                                            $rq->orWhere('tree_code', 'LIKE', $treeCode . '.%');
                                        }
                                    });
                                });
                        })->orWhere(function ($sub) use ($atasanRoleId, $treeCode) {
                            $sub->where('status_tahap_1', 'approved')
                                ->where('status_tahap_2', 'pending')
                                ->where('status_tahap_2', '!=', 'not_required')
                                ->whereHas('user', function ($uq) use ($atasanRoleId, $treeCode) {
                                    $uq->whereHas('role', function ($rq) use ($atasanRoleId, $treeCode) {
                                        $rq->where('parent_role_id', $atasanRoleId);
                                        if ($treeCode) {
                                            $rq->orWhere('tree_code', 'LIKE', $treeCode . '.%');
                                        }
                                    });
                                });
                        });
                    })->count();

                    // 3. HITUNG ANTREAN MPR
                    $jumlahMpr = PengajuanMpr::where('status_akhir', 'pending')
                        ->whereHas('user', function ($uq) use ($atasanRoleId, $treeCode) {
                            $uq->whereHas('role', function ($rq) use ($atasanRoleId, $treeCode) {
                                $rq->where('parent_role_id', $atasanRoleId);
                                if ($treeCode) {
                                    $rq->orWhere('tree_code', 'LIKE', $treeCode . '.%');
                                }
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
