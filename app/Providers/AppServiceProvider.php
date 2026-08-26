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
                $hasAdminRole = $userRole?->id === 1;
                $hasTopRole = empty($userRole?->parent_role_id);

                if ($hasAdminRole) {
                    // Admin Sistem: Akses pantau seluruh antrean cuti global
                    $jumlahCuti = PengajuanCuti::where('status_akhir', 'pending')->count();
                    $jumlahCar  = PengajuanCar::where('status_akhir', 'pending')->count();
                    $jumlahMpr  = PengajuanMpr::where('status_akhir', 'pending')->count();
                } else {
                    // 1. HITUNG ANTREAN CUTI BERDASARKAN DYNAMIC APPROVAL RULES
                    $jumlahCuti = PengajuanCuti::where(function ($q) use ($atasanRoleId) {
                        $q->where(function ($sub) use ($atasanRoleId) {
                            $sub->where('status_tahap_1', 'pending')
                                ->whereHas('user.role', function ($rq) use ($atasanRoleId) {
                                    $rq->where(function ($jsonQ) use ($atasanRoleId) {
                                        $jsonQ->where('approval_rules->cuti->approver_1_role_id', $atasanRoleId)
                                              ->orWhere('approval_rules->approver_level_1_role_id', $atasanRoleId);
                                    });
                                });
                        })->orWhere(function ($sub) use ($atasanRoleId) {
                            $sub->where('status_tahap_1', 'approved')
                                ->where('status_tahap_2', 'pending')
                                ->where('status_tahap_2', '!=', 'not_required')
                                ->whereHas('user.role', function ($rq) use ($atasanRoleId) {
                                    $rq->where(function ($jsonQ) use ($atasanRoleId) {
                                        $jsonQ->where('approval_rules->cuti->approver_2_role_id', $atasanRoleId)
                                              ->orWhere('approval_rules->approver_level_2_role_id', $atasanRoleId);
                                    });
                                });
                        });
                    })->count();

                    // 2. HITUNG ANTREAN CAR BERDASARKAN DYNAMIC APPROVAL RULES
                    $jumlahCar = PengajuanCar::where(function ($q) use ($atasanRoleId) {
                        $q->where(function ($sub) use ($atasanRoleId) {
                            $sub->where('status_tahap_1', 'pending')
                                ->whereHas('user.role', function ($rq) use ($atasanRoleId) {
                                    $rq->where(function ($jsonQ) use ($atasanRoleId) {
                                        $jsonQ->where('approval_rules->car->approver_1_role_id', $atasanRoleId)
                                              ->orWhere('approval_rules->approver_level_1_role_id', $atasanRoleId);
                                    });
                                });
                        })->orWhere(function ($sub) use ($atasanRoleId) {
                            $sub->where('status_tahap_1', 'approved')
                                ->where('status_tahap_2', 'pending')
                                ->where('status_tahap_2', '!=', 'not_required')
                                ->whereHas('user.role', function ($rq) use ($atasanRoleId) {
                                    $rq->where(function ($jsonQ) use ($atasanRoleId) {
                                        $jsonQ->where('approval_rules->car->approver_2_role_id', $atasanRoleId)
                                              ->orWhere('approval_rules->approver_level_2_role_id', $atasanRoleId);
                                    });
                                });
                        });
                    })->count();

                    // 3. HITUNG ANTREAN MPR BERDASARKAN DYNAMIC APPROVAL RULES
                    $jumlahMpr = PengajuanMpr::where(function ($q) use ($atasanRoleId) {
                        $q->where(function ($sub) use ($atasanRoleId) {
                            $sub->where('status_tahap_1', 'pending')
                                ->whereHas('user.role', function ($rq) use ($atasanRoleId) {
                                    $rq->where(function ($jsonQ) use ($atasanRoleId) {
                                        $jsonQ->where('approval_rules->mpr->approver_1_role_id', $atasanRoleId)
                                              ->orWhere('approval_rules->approver_level_1_role_id', $atasanRoleId);
                                    });
                                });
                        })->orWhere(function ($sub) use ($atasanRoleId) {
                            $sub->where('status_tahap_1', 'approved')
                                ->where('status_tahap_2', 'pending')
                                ->where('status_tahap_2', '!=', 'not_required')
                                ->whereHas('user.role', function ($rq) use ($atasanRoleId) {
                                    $rq->where(function ($jsonQ) use ($atasanRoleId) {
                                        $jsonQ->where('approval_rules->mpr->approver_2_role_id', $atasanRoleId)
                                              ->orWhere('approval_rules->approver_level_2_role_id', $atasanRoleId);
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
