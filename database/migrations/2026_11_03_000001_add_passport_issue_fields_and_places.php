<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Add passport issue date and place to transactions
        Schema::table('transactions', function (Blueprint $table) {
            $table->date('passport_issue_date')->nullable()->after('address');
            $table->string('passport_issue_place')->nullable()->after('passport_issue_date');
        });

        // 2. Create passport_issue_places lookup table
        if (!Schema::hasTable('passport_issue_places')) {
            Schema::create('passport_issue_places', function (Blueprint $table) {
                $table->id();
                $table->string('name')->unique();
                $table->timestamps();
            });

            // Seed default places
            $defaults = [
                'سلێمانی',
                'هەولێر',
                'دهۆک',
                'هەڵەبجە',
                'کەرکووک',
                'بەغداد',
                'گەرمیان',
                'ڕاپەڕین',
                'نەینەوا (مووسڵ)',
                'بەسڕە',
                'بابل',
                'کەربەلا',
                'نەجەف',
            ];

            foreach ($defaults as $place) {
                DB::table('passport_issue_places')->insertOrIgnore([
                    'name' => $place,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropColumn(['passport_issue_date', 'passport_issue_place']);
        });

        Schema::dropIfExists('passport_issue_places');
    }
};
