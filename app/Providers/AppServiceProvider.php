<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Auth;
use App\Services\MonitoringAlertService;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        View::composer('layouts.app', function ($view) {
            $roleName = Auth::user()->role->nama_role ?? '';

            if (Auth::check() && in_array($roleName, ['SuperAdmin', 'Operator_Kepegawaian', 'Pimpinan'])) {
                $view->with('monitoringAlerts', MonitoringAlertService::getAlerts());
            } else {
                $view->with('monitoringAlerts', [
                    'total'   => 0,
                    'kontrak' => 0,
                    'kgb'     => 0,
                    'kp'      => 0,
                    'items'   => collect(),
                ]);
            }
        });
    }
}