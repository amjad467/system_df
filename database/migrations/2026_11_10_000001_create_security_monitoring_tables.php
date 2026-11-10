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
        // 1. ڕووداوە ئەمنییەکان و هێرشە دەستنیشانکراوەکان
        Schema::create('security_incidents', function (Blueprint $table) {
            $table->id();
            $table->string('type'); // brute_force, sqli_attempt, xss_attempt, off_hours_access, new_device, bulk_delete, fraud_attempt, bot_trap, manual_block
            $table->string('severity')->default('medium'); // low, medium, high, critical
            $table->string('ip_address');
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('user_name')->nullable();
            $table->text('user_agent')->nullable();
            $table->text('details');
            $table->text('payload')->nullable(); // هێرشەکە یان دەقی تێپەڕەوشە یان پارامیتەری مەترسیدار
            $table->boolean('is_blocked')->default(false);
            $table->timestamp('blocked_until')->nullable();
            $table->boolean('is_resolved')->default(false);
            $table->string('resolved_by')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();
        });

        // 2. ئایپییە بلۆککراوەکان (لیستی ڕەش)
        Schema::create('blocked_ips', function (Blueprint $table) {
            $table->id();
            $table->string('ip_address')->unique();
            $table->string('reason');
            $table->timestamp('blocked_until')->nullable(); // null = هەمیشەیی
            $table->string('blocked_by')->nullable();
            $table->timestamps();
        });

        // 3. ڕێکخستنە ئەمنییەکان (تەلەگرام، بەرگری، کاتەکان)
        Schema::create('security_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->string('description')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('security_settings');
        Schema::dropIfExists('blocked_ips');
        Schema::dropIfExists('security_incidents');
    }
};

