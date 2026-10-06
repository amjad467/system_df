<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            
            // Foreign Keys
            $table->foreignId('traffic_directorate_id')->nullable()->constrained('traffic_directorates')->nullOnDelete();
            $table->foreignId('director_id')->nullable()->constrained('directors')->nullOnDelete();
            $table->foreignId('transaction_type_id')->constrained('transaction_types')->cascadeOnDelete();
            $table->foreignId('plate_type_id')->nullable()->constrained('plate_types')->nullOnDelete();
            $table->foreignId('car_make_id')->nullable()->constrained('car_makes')->nullOnDelete();
            $table->foreignId('car_color_id')->nullable()->constrained('car_colors')->nullOnDelete();

            // Drivers Info
            $table->string('visitor_name'); // Kurdish/Arabic Driver 1
            $table->string('visitor_name_eng')->nullable(); // English Driver 1
            $table->string('second_driver_name')->nullable(); // Driver 2 Kurdish/Arabic
            $table->string('second_driver_name_eng')->nullable(); // Driver 2 English

            // Vehicle Details
            $table->string('plate_number');
            $table->string('model_year')->nullable();
            $table->string('chassis_number'); // VIN
            $table->integer('piston_count')->default(4);
            $table->string('salana_number')->nullable();
            $table->date('transaction_date')->useCurrent();
            $table->text('notes')->nullable();

            // Transaction & Financial Details
            $table->string('barcode')->unique();
            $table->integer('num_years')->nullable()->default(1);
            $table->string('receipt_37a_number')->nullable();
            $table->decimal('pay_amount_years', 12, 2)->default(0);
            $table->decimal('pay_stamp', 12, 2)->default(0);
            $table->decimal('pay_form', 12, 2)->default(0);
            $table->decimal('pay_fine', 12, 2)->default(0);
            $table->decimal('pay_inspection', 12, 2)->default(0);
            $table->decimal('total_pay', 12, 2)->default(0);

            // Validity dates
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();

            // Immutable Vehicle Conditions
            $table->string('zedabar')->nullable();
            $table->string('zedabar_eng')->nullable();
            $table->string('kamukurty')->nullable();
            $table->string('kamukurty_eng')->nullable();
            $table->string('bary_gshty')->nullable();
            $table->string('bary_gshty_eng')->nullable();

            // Cancellation specifics
            $table->string('no_nusraw_puchal')->nullable();
            $table->date('date_nusraw_puchal')->nullable();
            $table->string('cancellation_code')->nullable();
            $table->text('cancellation_reason')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->string('cancelled_by')->nullable();

            // Workflow Stages & Audits
            $table->string('user_input');
            $table->string('user_edit_input')->nullable();
            $table->timestamp('user_edit_date')->nullable();

            $table->boolean('is_inspected')->default(false);
            $table->string('inspected_by')->nullable();
            $table->timestamp('inspected_at')->nullable();

            $table->boolean('is_audited')->default(false);
            $table->string('audited_by')->nullable();
            $table->timestamp('audited_at')->nullable();

            $table->boolean('is_paid')->default(false);
            $table->string('paid_by')->nullable();
            $table->timestamp('paid_at')->nullable();

            $table->boolean('is_booklet_completed')->default(false);
            $table->string('booklet_number')->nullable();
            $table->string('booklet_by')->nullable();
            $table->timestamp('booklet_completed_at')->nullable();

            $table->boolean('is_pledge_completed')->default(false);
            $table->boolean('is_printed')->default(false);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
