<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('user_login')->nullable()->after('name');
            $table->string('role')->default('data_entry')->after('email'); // data_entry, inspector, auditor, cashier, booklet, admin
            $table->boolean('is_active')->default(true)->after('role');
            $table->foreignId('traffic_directorate_id')->nullable()->constrained('traffic_directorates')->nullOnDelete();
        });

        Schema::table('transactions', function (Blueprint $table) {
            $table->boolean('is_submitted')->default(false)->after('is_printed');
            $table->string('submitted_by')->nullable()->after('is_submitted');
            $table->timestamp('submitted_at')->nullable()->after('submitted_by');
        });
    }

    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropColumn(['is_submitted', 'submitted_by', 'submitted_at']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['traffic_directorate_id']);
            $table->dropColumn(['user_login', 'role', 'is_active', 'traffic_directorate_id']);
        });
    }
};
