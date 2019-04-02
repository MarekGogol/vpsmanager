<?php

namespace Gogol\VpsManager\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Admin;

class ViewServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->loadViewsFrom(__DIR__ . '/../Views', 'vpsmanager');
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
