<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('blocked_receipt_numbers', function (Blueprint $table) {
            $table->id();
            $table->string('receipt_number')->unique();
            $table->text('cancellation_reason')->nullable();
            $table->string('blocked_by')->nullable();
            $table->foreignId('transaction_id')->nullable()->constrained('transactions')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('blocked_receipt_numbers');
    }
};
