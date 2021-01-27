<?php

namespace App\Providers;
use Illuminate\Support\Facades\App;

use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;

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
        Paginator::useBootstrap();
    }

}
App::singleton('prefix_aws', function(){
    return 'https://fileslyflow.s3-us-west-2.amazonaws.com/';
        }); 