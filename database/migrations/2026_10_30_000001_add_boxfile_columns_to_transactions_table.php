<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * زیادکردنی فیلدی ژمارەی بۆکسی دەرهێنان و بۆکسی پووچەڵ بۆ تەیبلی transactions
     * 
     * NO_boxfile        : ژمارەی بۆکسی فیزیکی کە فایل و نوسراوەکانی دەرهێنانی دەفتەر تێدا ھەڵدەگیرێت
     * NO_boxfile_puchal : ژمارەی بۆکسی پووچەڵ — کاتی پووچەڵکردنەوە، فایلەکان لە بۆکسی دەرهێنان 
     *                     دەردەهێنرێت و دەکرێتە ئەم بۆکسەوە
     */
    public function up(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            // ژمارەی بۆکسی دەرهێنانی دەفتەر (فیزیکی)
            $table->integer('NO_boxfile')->nullable()->default(null)->after('notes')
                ->comment('ژمارەی بۆکسی کە فایل و نوسراوەکانی دەرهێنانی دەفتەری تێدایە');

            // ژمارەی بۆکسی پووچەڵکردنەوە
            $table->integer('NO_boxfile_puchal')->nullable()->default(null)->after('NO_boxfile')
                ->comment('ژمارەی بۆکسی پووچەڵ — پر دەبێتەوە کاتێک دەفتەر پووچەڵ دەکرێتەوە');
        });
    }

    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropColumn(['NO_boxfile', 'NO_boxfile_puchal']);
        });
    }
};

