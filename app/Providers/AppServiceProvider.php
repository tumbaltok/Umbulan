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
                $roleName = $user->role ? strtolower($user->role->role_name) : '';

                if ($roleName === 'manager') {
                    $jumlahCuti = PengajuanCuti::where('status_manager', 'pending')->count();
                    $jumlahCar = PengajuanCar::where('status_manager', 'pending')->count();

                } elseif ($roleName === 'supervisor') {
                    $jumlahCuti = PengajuanCuti::where('status_supervisor', 'pending')->count();
                    $jumlahCar = PengajuanCar::where('status_supervisor', 'pending')->count();

                } elseif ($roleName === 'admin') {
                    // Cuti untuk Admin (Sudah Benar)
                    $jumlahCuti = PengajuanCuti::where(function($q) {
                        $q->where('status_supervisor', 'pending')
                          ->orWhere('status_manager', 'pending')
                          ->orWhere('status_akhir', 'pending');
                    })->count();

                    // PEMBETULAN: CAR untuk Admin juga harus digabung menggunakan orWhere
                    $jumlahCar = PengajuanCar::where(function($q) {
                        $q->where('status_supervisor', 'pending')
                          ->orWhere('status_manager', 'pending')
                          ->orWhere('status_akhir', 'pending');
                    })->count();
                }
            }

            $view->with([
                'jumlahSaranCuti' => $jumlahCuti,
                'jumlahSaranCar'  => $jumlahCar
            ]);
        });
    }
}
