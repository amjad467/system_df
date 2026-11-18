<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('transactions') && !Schema::hasColumn('transactions', 'is_cancelled')) {
            try {
                DB::statement('ALTER TABLE transactions ADD COLUMN is_cancelled INTEGER NOT NULL DEFAULT 0');
            } catch (\Throwable $e) {
                Schema::table('transactions', function (Blueprint $table) {
                    $table->boolean('is_cancelled')->default(false);
                });
            }

            try {
                DB::table('transactions')
                    ->whereNotNull('cancelled_at')
                    ->update(['is_cancelled' => 1]);
            } catch (\Throwable $e) {}
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Safe no-op for SQLite
    }
};
