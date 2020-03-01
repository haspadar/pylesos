<?php

namespace App\Providers;

use App\Library\Services\EnvProxiesSitesList;
use Illuminate\Support\ServiceProvider;

class ProxiesSitesListServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind('App\Library\Services\ProxiesSitesList', function ($app) {
            return new EnvProxiesSitesList();
        });
    }
}
