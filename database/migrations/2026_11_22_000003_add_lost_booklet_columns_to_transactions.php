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
            if (!Schema::hasColumn('transactions', 'is_booklet_lost')) {
                $table->boolean('is_booklet_lost')->default(false)->after('is_cancelled');
            }
            if (!Schema::hasColumn('transactions', 'lost_letter_number')) {
                $table->string('lost_letter_number')->nullable()->after('is_booklet_lost');
            }
            if (!Schema::hasColumn('transactions', 'lost_letter_date')) {
                $table->date('lost_letter_date')->nullable()->after('lost_letter_number');
            }
            if (!Schema::hasColumn('transactions', 'lost_directorate_name')) {
                $table->string('lost_directorate_name')->nullable()->default('بەڕێوەبەرایەتی ونبووەکان')->after('lost_letter_date');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $cols = ['is_booklet_lost', 'lost_letter_number', 'lost_letter_date', 'lost_directorate_name'];
            foreach ($cols as $col) {
                if (Schema::hasColumn('transactions', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
