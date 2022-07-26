<?php

namespace App\Providers;

use App\Models\CustomPersonalAccessToken;
use Illuminate\Support\ServiceProvider;
use Laravel\Sanctum\Sanctum;
use Maatwebsite\Excel\Imports\HeadingRowFormatter;
use function str_replace;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        if ($this->app->environment('local')) {
           $this->app->register(\Laravel\Telescope\TelescopeServiceProvider::class);
           $this->app->register(TelescopeServiceProvider::class);
        }
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Sanctum::usePersonalAccessTokenModel(CustomPersonalAccessToken::class);
        HeadingRowFormatter::extend('chinese', function($value, $key) {
            return str_replace('*', '', $value);
        });
    }
}
