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

        // Auto-heal 'is_active' column on directors table if missing in SQLite/PostgreSQL
        try {
            if (\Illuminate\Support\Facades\Schema::hasTable('directors') && !\Illuminate\Support\Facades\Schema::hasColumn('directors', 'is_active')) {
                try {
                    \Illuminate\Support\Facades\DB::statement('ALTER TABLE directors ADD COLUMN is_active INTEGER NOT NULL DEFAULT 0');
                } catch (\Throwable $e2) {
                    \Illuminate\Support\Facades\Schema::table('directors', function ($table) {
                        $table->boolean('is_active')->default(false);
                    });
                }

                $target = \Illuminate\Support\Facades\DB::table('directors')->where('name', 'like', '%قادر%')->first()
                       ?? \Illuminate\Support\Facades\DB::table('directors')->first();
                if ($target) {
                    \Illuminate\Support\Facades\DB::table('directors')->where('id', $target->id)->update(['is_active' => 1]);
                }
            }
        } catch (\Throwable $e) {
            // Silently continue if database is not ready during boot
        }

        // Auto-heal 'is_cancelled' column on transactions table if missing in SQLite/PostgreSQL
        try {
            if (\Illuminate\Support\Facades\Schema::hasTable('transactions') && !\Illuminate\Support\Facades\Schema::hasColumn('transactions', 'is_cancelled')) {
                try {
                    \Illuminate\Support\Facades\DB::statement('ALTER TABLE transactions ADD COLUMN is_cancelled INTEGER NOT NULL DEFAULT 0');
                } catch (\Throwable $e2) {
                    \Illuminate\Support\Facades\Schema::table('transactions', function ($table) {
                        $table->boolean('is_cancelled')->default(false);
                    });
                }

                // Sync existing cancelled rows
                \Illuminate\Support\Facades\DB::table('transactions')
                    ->whereNotNull('cancelled_at')
                    ->update(['is_cancelled' => 1]);
            }
        } catch (\Throwable $e) {
            // Silently continue if database is not ready during boot
        }
    }
}