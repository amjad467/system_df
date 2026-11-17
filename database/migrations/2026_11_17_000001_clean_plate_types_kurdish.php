<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $types = DB::table('plate_types')->get();
        foreach ($types as $type) {
            $cleaned = trim(preg_replace('/\s*\(.*?\)/', '', $type->name_kurdish));
            DB::table('plate_types')->where('id', $type->id)->update([
                'name_kurdish' => $cleaned
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No-op
    }
};
