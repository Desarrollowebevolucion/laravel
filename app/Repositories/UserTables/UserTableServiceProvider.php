<?php
namespace App\Repositories\UserTables;

use Illuminate\Support\ServiceProvider;

class UserTableServiceProvider extends ServiceProvider
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
        $this->app->bind('App\Repositories\UserTables\UserTableInterface', 'App\Repositories\UserTables\UserTableRepository');
    }
}