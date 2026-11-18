<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('directors', 'is_active')) {
            Schema::table('directors', function (Blueprint $table) {
                $table->boolean('is_active')->default(false)->after('name');
            });

            // Set the first director (or one with 'قادر' or first record) as active by default
            $target = DB::table('directors')->where('name', 'like', '%قادر%')->first()
                   ?? DB::table('directors')->first();
            if ($target) {
                DB::table('directors')->where('id', $target->id)->update(['is_active' => true]);
            }
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('directors', 'is_active')) {
            Schema::table('directors', function (Blueprint $table) {
                $table->dropColumn('is_active');
            });
        }
    }
};
