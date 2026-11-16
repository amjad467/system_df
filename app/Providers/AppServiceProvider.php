<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL; // <-- ١. دڵنیابەرەوە ئەم دێڕە لە سەرەوە زیاد کراوە

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if (app()->environment('production') && (env('FORCE_HTTPS', false) || str_starts_with(env('APP_URL', ''), 'https://'))) {
            URL::forceScheme('https');
        }
    }
}