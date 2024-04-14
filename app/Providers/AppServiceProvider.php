<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

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
        app('view')->composer('admin._layouts.master', function($view){
            $routeName  = isset($_SESSION['ROUTENAME']) ? ($_SESSION['ROUTENAME']) : '';
            $view->with(compact('routeName'));
        });
    }
}
