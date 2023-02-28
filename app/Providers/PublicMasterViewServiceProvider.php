<?php

namespace App\Providers;
use Illuminate\Support\ServiceProvider;
use App\Yantrana\Components\Home\HomeEngine;

class PublicMasterViewServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot(HomeEngine $homeEngine)
    {
        // Using class based composers...
        view()->composer(
            ['*'],
            '\App\Http\ViewComposers\PublicMasterComposer'
        );
    }
}
