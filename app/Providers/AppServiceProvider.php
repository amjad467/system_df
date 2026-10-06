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
        // <-- ٢. لێرە لە ناو کەوانەی boot ئەم دوو دێڕە دابنێ:
        if (app()->environment('production')) {
            URL::forceScheme('https');
        }
    }
}