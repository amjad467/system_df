<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('transaction_types')->where('id', 6)->update([
            'pay_1_year' => 0,
            'stamp_pay' => 4000,
            'form_pay' => 4000,
            'fine_pay' => 0,
            'inspection_pay' => 0,
        ]);
    }

    public function down(): void
    {
        DB::table('transaction_types')->where('id', 6)->update([
            'stamp_pay' => 3000,
            'form_pay' => 3000,
        ]);
    }
};
