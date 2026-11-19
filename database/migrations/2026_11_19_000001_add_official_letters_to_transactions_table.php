<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            if (!Schema::hasColumn('transactions', 'no_nusraw_mrur')) {
                $table->string('no_nusraw_mrur')->nullable()->index();
            }
            if (!Schema::hasColumn('transactions', 'date_nusraw_mrur')) {
                $table->date('date_nusraw_mrur')->nullable();
            }
            if (!Schema::hasColumn('transactions', 'no_nusraw_gumrg')) {
                $table->string('no_nusraw_gumrg')->nullable()->index();
            }
            if (!Schema::hasColumn('transactions', 'date_nusraw_gumrg')) {
                $table->date('date_nusraw_gumrg')->nullable();
            }
            if (!Schema::hasColumn('transactions', 'no_nusraw_balennama')) {
                $table->string('no_nusraw_balennama')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropColumn([
                'no_nusraw_mrur',
                'date_nusraw_mrur',
                'no_nusraw_gumrg',
                'date_nusraw_gumrg',
                'no_nusraw_balennama',
            ]);
        });
    }
};
