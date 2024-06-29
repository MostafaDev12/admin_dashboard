<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Blade;

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

         //Blade directive to convert.
         Blade::directive('format_date', function ($date) {
            if (!empty($date)) {
                return "\Carbon::createFromTimestamp(strtotime($date))->format('H:i d-m-Y')";
            } else {
                return null;
            }
        });
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
        Schema::defaultStringLength(191);
        // if($this->app->environment('production')) {
        //     URL::forceScheme('https');
        // }
  
    }
}
