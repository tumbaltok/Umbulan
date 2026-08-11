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
            $jumlahMpr = 0;

            if (Auth::check()) {
                $user = Auth::user();
                $roleName = $user->role ? strtolower($user->role->role_name) : '';

                if ($roleName === 'manager') {
                    $jumlahCuti = PengajuanCuti::where('status_supervisor', 'approved')
                        ->where('status_manager', 'pending')
                        ->count();

                    $jumlahCar = PengajuanCar::where('status_supervisor', 'approved')
                        ->where('status_manager', 'pending')
                        ->count();

                    // Jika ada model MPR
                    if (class_exists('App\Models\PengajuanMpr')) {
                        $jumlahMpr = PengajuanMpr::where('status_supervisor', 'approved')
                            ->where('status_manager', 'pending')
                            ->count();
                    }

                } elseif ($roleName === 'supervisor') {
                    $jumlahCuti = PengajuanCuti::where('status_supervisor', 'pending')->count();
                    $jumlahCar = PengajuanCar::where('status_supervisor', 'pending')->count();

                    if (class_exists('App\Models\PengajuanMpr')) {
                        $jumlahMpr = PengajuanMpr::where('status_supervisor', 'pending')->count();
                    }

                } elseif ($roleName === 'admin') {
                    $jumlahCuti = PengajuanCuti::where(function ($q) {
                        $q->where('status_supervisor', 'pending')
                            ->orWhere('status_manager', 'pending')
                            ->orWhere('status_akhir', 'pending');
                    })->count();

                    $jumlahCar = PengajuanCar::where(function ($q) {
                        $q->where('status_supervisor', 'pending')
                            ->orWhere('status_manager', 'pending')
                            ->orWhere('status_akhir', 'pending');
                    })->count();

                    if (class_exists('App\Models\PengajuanMpr')) {
                        $jumlahMpr = PengajuanMpr::where(function ($q) {
                            $q->where('status_supervisor', 'pending')
                                ->orWhere('status_manager', 'pending')
                                ->orWhere('status_akhir', 'pending');
                        })->count();
                    }
                }
            }

            $view->with([
                'jumlahSaranCuti' => $jumlahCuti,
                'jumlahSaranCar' => $jumlahCar,
                'jumlahSaranMpr' => $jumlahMpr,
            ]);
        });
    }
}
