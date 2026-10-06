<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('security_levels', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });

        Schema::create('traffic_directorates', function (Blueprint $table) {
            $table->id();
            $table->string('name_kurdish');
            $table->string('name_english')->nullable();
            $table->timestamps();
        });

        Schema::create('directors', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });

        Schema::create('plate_types', function (Blueprint $table) {
            $table->id();
            $table->string('name_kurdish');
            $table->string('name_english')->nullable();
            $table->timestamps();
        });

        Schema::create('car_makes', function (Blueprint $table) {
            $table->id();
            $table->string('name_kurdish');
            $table->string('name_english')->nullable();
            $table->timestamps();
        });

        Schema::create('car_colors', function (Blueprint $table) {
            $table->id();
            $table->string('name_kurdish');
            $table->string('name_english')->nullable();
            $table->timestamps();
        });

        Schema::create('transaction_types', function (Blueprint $table) {
            $table->id();
            $table->string('name_kurdish');
            $table->string('name_english')->nullable();
            $table->decimal('pay_1_year', 12, 2)->default(80000);
            $table->decimal('pay_2_year', 12, 2)->default(120000);
            $table->decimal('pay_3_year', 12, 2)->default(140000);
            $table->decimal('stamp_pay', 12, 2)->default(3000);
            $table->decimal('form_pay', 12, 2)->default(4000);
            $table->decimal('fine_pay', 12, 2)->default(0);
            $table->decimal('inspection_pay', 12, 2)->default(20000);
            $table->decimal('bus_inspection_pay', 12, 2)->default(27000);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transaction_types');
        Schema::dropIfExists('car_colors');
        Schema::dropIfExists('car_makes');
        Schema::dropIfExists('plate_types');
        Schema::dropIfExists('directors');
        Schema::dropIfExists('traffic_directorates');
        Schema::dropIfExists('security_levels');
    }
};
