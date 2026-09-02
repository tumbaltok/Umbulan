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

                $atasanRoleIds = $atasan->roles->pluck('id')->toArray();
                if (empty($atasanRoleIds) && !empty($atasan->role_id)) {
                    $atasanRoleIds = [$atasan->role_id];
                }

                $hasAdminRole = $atasan->isLevel1() || in_array(1, $atasanRoleIds) || $atasan->hasRole('ADMIN');

                if ($hasAdminRole) {
                    // Admin Sistem: Menghitung seluruh antrean pengajuan global
                    $jumlahCuti = PengajuanCuti::where('status_akhir', 'pending')->count();
                    $jumlahCar  = PengajuanCar::where('status_akhir', 'pending')->count();
                    $jumlahMpr  = PengajuanMpr::where('status_akhir', 'pending')->count();
                } else {
                    // 1. Hitung antrean persetujuan cuti relevan untuk atasan (Dynamic Approval Rules)
                    $jumlahCuti = PengajuanCuti::where('user_id', '!=', $atasan->id)
                        ->where(function ($q) use ($atasanRoleIds) {
                            $q->where(function ($sub) use ($atasanRoleIds) {
                                $sub->where('status_tahap_1', 'pending')
                                    ->whereHas('user.roles', function ($rq) use ($atasanRoleIds) {
                                        $rq->where(function ($jsonQ) use ($atasanRoleIds) {
                                            foreach ($atasanRoleIds as $roleId) {
                                                $jsonQ->orWhere('approval_rules->cuti->approver_1_role_id', $roleId)
                                                      ->orWhere('approval_rules->approver_level_1_role_id', $roleId);
                                            }
                                        });
                                    });
                            })->orWhere(function ($sub) use ($atasanRoleIds) {
                                $sub->where('status_tahap_1', 'approved')
                                    ->where('status_tahap_2', 'pending')
                                    ->where('status_tahap_2', '!=', 'not_required')
                                    ->whereHas('user.roles', function ($rq) use ($atasanRoleIds) {
                                        $rq->where(function ($jsonQ) use ($atasanRoleIds) {
                                            foreach ($atasanRoleIds as $roleId) {
                                                $jsonQ->orWhere('approval_rules->cuti->approver_2_role_id', $roleId)
                                                      ->orWhere('approval_rules->approver_level_2_role_id', $roleId);
                                            }
                                        });
                                    });
                            });
                        })->count();

                    // 2. Hitung antrean persetujuan CAR relevan untuk atasan (Dynamic Approval Rules)
                    $jumlahCar = PengajuanCar::where('user_id', '!=', $atasan->id)
                        ->where(function ($q) use ($atasanRoleIds) {
                            $q->where(function ($sub) use ($atasanRoleIds) {
                                $sub->where('status_tahap_1', 'pending')
                                    ->whereHas('user.roles', function ($rq) use ($atasanRoleIds) {
                                        $rq->where(function ($jsonQ) use ($atasanRoleIds) {
                                            foreach ($atasanRoleIds as $roleId) {
                                                $jsonQ->orWhere('approval_rules->car->approver_1_role_id', $roleId)
                                                      ->orWhere('approval_rules->approver_level_1_role_id', $roleId);
                                            }
                                        });
                                    });
                            })->orWhere(function ($sub) use ($atasanRoleIds) {
                                $sub->where('status_tahap_1', 'approved')
                                    ->where('status_tahap_2', 'pending')
                                    ->where('status_tahap_2', '!=', 'not_required')
                                    ->whereHas('user.roles', function ($rq) use ($atasanRoleIds) {
                                        $rq->where(function ($jsonQ) use ($atasanRoleIds) {
                                            foreach ($atasanRoleIds as $roleId) {
                                                $jsonQ->orWhere('approval_rules->car->approver_2_role_id', $roleId)
                                                      ->orWhere('approval_rules->approver_level_2_role_id', $roleId);
                                            }
                                        });
                                    });
                            });
                        })->count();

                    // 3. Hitung antrean persetujuan MPR relevan untuk atasan (Dynamic Approval Rules)
                    $jumlahMpr = PengajuanMpr::where('user_id', '!=', $atasan->id)
                        ->where(function ($q) use ($atasanRoleIds) {
                            $q->where(function ($sub) use ($atasanRoleIds) {
                                $sub->where('status_tahap_1', 'pending')
                                    ->whereHas('user.roles', function ($rq) use ($atasanRoleIds) {
                                        $rq->where(function ($jsonQ) use ($atasanRoleIds) {
                                            foreach ($atasanRoleIds as $roleId) {
                                                $jsonQ->orWhere('approval_rules->mpr->approver_1_role_id', $roleId)
                                                      ->orWhere('approval_rules->approver_level_1_role_id', $roleId);
                                            }
                                        });
                                    });
                            })->orWhere(function ($sub) use ($atasanRoleIds) {
                                $sub->where('status_tahap_1', 'approved')
                                    ->where('status_tahap_2', 'pending')
                                    ->where('status_tahap_2', '!=', 'not_required')
                                    ->whereHas('user.roles', function ($rq) use ($atasanRoleIds) {
                                        $rq->where(function ($jsonQ) use ($atasanRoleIds) {
                                            foreach ($atasanRoleIds as $roleId) {
                                                $jsonQ->orWhere('approval_rules->mpr->approver_2_role_id', $roleId)
                                                      ->orWhere('approval_rules->approver_level_2_role_id', $roleId);
                                            }
                                        });
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
