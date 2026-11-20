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
        if (!Schema::hasColumn('transactions', 'is_bus')) {
            Schema::table('transactions', function (Blueprint $table) {
                $table->boolean('is_bus')->default(false)->after('car_color_id');
            });
        }

        // Set default bus_inspection_pay to 30,000 in transaction_types
        if (Schema::hasColumn('transaction_types', 'bus_inspection_pay')) {
            DB::table('transaction_types')
                ->whereIn('id', [1, 2, 3, 4, 5, 8])
                ->update(['bus_inspection_pay' => 30000]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('transactions', 'is_bus')) {
            Schema::table('transactions', function (Blueprint $table) {
                $table->dropColumn('is_bus');
            });
        }
    }
};
