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
        //
        //Helpers & other additional resources from Angulara & __Laraware
        require app_path('Yantrana/__Laraware/Support/helpers.php');
        require app_path('Yantrana/Support/app-helpers.php');
        require app_path('Yantrana/Support/extended-validations.php');

        // config items requires gettext helper function to work
        require app_path('Yantrana/Support/custom-tech-config.php');
    }
}
