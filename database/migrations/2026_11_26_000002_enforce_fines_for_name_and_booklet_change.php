<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Name Change (ناوگۆڕین) -> Fine = 25,000
        DB::table('transaction_types')->where('id', 3)->update([
            'pay_1_year' => 0,
            'stamp_pay' => 4000,
            'form_pay' => 5000,
            'fine_pay' => 25000,
            'inspection_pay' => 0,
        ]);

        // 2. Booklet Replacement (دەفتەرگۆڕین) -> Fine = 25,000
        DB::table('transaction_types')->where('id', 4)->update([
            'pay_1_year' => 0,
            'stamp_pay' => 4000,
            'form_pay' => 5000,
            'fine_pay' => 25000,
            'inspection_pay' => 0,
        ]);

        // 3. Name & Booklet Change Combined (ناو و دەفتەر گۆڕین) -> Fine = 50,000
        DB::table('transaction_types')->where('id', 7)->update([
            'pay_1_year' => 0,
            'stamp_pay' => 4000,
            'form_pay' => 5000,
            'fine_pay' => 5000,
            'fine_pay' => 50000,
            'inspection_pay' => 0,
        ]);

        // Ensure other types have fine_pay = 0
        DB::table('transaction_types')->whereIn('id', [1, 2, 5, 6, 8])->update([
            'fine_pay' => 0,
        ]);
    }

    public function down(): void
    {
        // No down required
    }
};
