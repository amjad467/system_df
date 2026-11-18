<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('directors') && !Schema::hasColumn('directors', 'is_active')) {
            try {
                DB::statement('ALTER TABLE directors ADD COLUMN is_active INTEGER NOT NULL DEFAULT 0');
            } catch (\Throwable $e) {
                Schema::table('directors', function (Blueprint $table) {
                    $table->boolean('is_active')->default(false);
                });
            }

            // Set the first director as active
            $target = DB::table('directors')->where('name', 'like', '%قادر%')->first()
                   ?? DB::table('directors')->first();
            if ($target) {
                DB::table('directors')->where('id', $target->id)->update(['is_active' => 1]);
            }
        }
    }

    public function down(): void
    {
        // Safe no-op
    }
};
