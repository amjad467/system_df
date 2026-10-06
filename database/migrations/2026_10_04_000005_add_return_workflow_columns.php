<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->boolean('is_returned')->default(false)->after('current_stage');
            $table->text('return_reason')->nullable()->after('is_returned');
            $table->foreignId('returned_by_user_id')->nullable()->after('return_reason')->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropForeign(['returned_by_user_id']);
            $table->dropColumn(['is_returned', 'return_reason', 'returned_by_user_id']);
        });
    }
};
