<?php

namespace App\Providers;

use App\Models\Car\PengajuanCar;
use App\Models\Cuti\PengajuanCuti;
use App\Models\Mpr\PengajuanMpr;
use App\Services\ApprovalRoutingService;
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
                $routingService = app(ApprovalRoutingService::class);

                $jumlahCuti = $routingService->applyPendingApprovalScope(PengajuanCuti::query(), $atasan, 'cuti')->count();
                $jumlahCar  = $routingService->applyPendingApprovalScope(PengajuanCar::query(), $atasan, 'car')->count();
                $jumlahMpr  = $routingService->applyPendingApprovalScope(PengajuanMpr::query(), $atasan, 'mpr')->count();

                $view->with([
                    'jumlahCuti' => $jumlahCuti,
                    'jumlahCar'  => $jumlahCar,
                    'jumlahMpr'  => $jumlahMpr,
                ]);
            }
        });
    }
}

