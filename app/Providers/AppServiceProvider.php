<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Auth;
use App\Models\PengajuanCuti;
use App\Models\PengajuanCar;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        View::composer(['layouts.app', 'partials.sidebar'], function ($view) {
            $jumlahCuti = 0;
            $jumlahCar = 0;

            if (Auth::check()) {
                $user = Auth::user();

                if ($user->role->role_name === 'Manager') {
                    $jumlahCuti = PengajuanCuti::where('status_manager', 'pending')->count();
                    $jumlahCar = PengajuanCar::where('status_manager', 'pending')->count();

                } elseif ($user->role->role_name === 'Supervisor') {
                    $jumlahCuti = PengajuanCuti::where('status_supervisor', 'pending')->count();
                    $jumlahCar = PengajuanCar::where('status_supervisor', 'pending')->count();

                } elseif ($user->role->role_name === 'Admin') {
                    $jumlahCuti = PengajuanCuti::where('status_supervisor', 'pending')->count();
                    $jumlahCar = PengajuanCar::where('status_supervisor', 'pending')->count();
                    $jumlahCuti = PengajuanCuti::where('status_manager', 'pending')->count();
                    $jumlahCar = PengajuanCar::where('status_manager', 'pending')->count();
                    $jumlahCuti = PengajuanCuti::where('status_akhir', 'pending')->count();
                    $jumlahCar = PengajuanCar::where('status_akhir', 'pending')->count();
                }
            }

            $view->with([
                'jumlahSaranCuti' => $jumlahCuti,
                'jumlahSaranCar'  => $jumlahCar
            ]);
        });
    }
}
