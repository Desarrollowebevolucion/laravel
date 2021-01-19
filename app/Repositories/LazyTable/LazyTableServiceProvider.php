<?php
namespace App\Repositories\LazyTable;

use Illuminate\Support\ServiceProvider;

class LazyTableServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     * @return void
     */
    public function boot()
    {

    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind('App\Repositories\LazyTable\LazyTableInterface', 'App\Repositories\LazyTable\LazyTableRepository');
    }
}