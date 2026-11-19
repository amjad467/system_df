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
        Schema::create('booklet_notices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('transaction_id')->constrained('transactions')->cascadeOnDelete();
            $table->string('batch_code', 50)->index();
            $table->string('batch_name', 150)->nullable();
            $table->string('phone_number', 50)->nullable();
            $table->string('visitor_name', 150)->nullable();
            $table->string('plate_number', 50)->nullable()->index();
            $table->string('chassis_number', 100)->nullable()->index();
            $table->string('booklet_number', 50)->nullable()->index();
            $table->date('end_date')->nullable();
            $table->string('channel', 30)->default('whatsapp'); // 'whatsapp', 'viber', 'sms'
            $table->text('message_text')->nullable();
            $table->foreignId('sent_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('sent_at')->nullable()->index();
            
            // Status: 'sent' (notice dispatched), 'attended' (citizen came and renewed/cancelled), 'cancelled'
            $table->string('status', 30)->default('sent')->index();
            $table->timestamp('attended_at')->nullable()->index();
            $table->foreignId('attended_transaction_id')->nullable()->constrained('transactions')->nullOnDelete();
            $table->string('attended_type', 50)->nullable(); // e.g. تازەکردنەوە, پووچەڵکردنەوە
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('booklet_notices');
    }
};
