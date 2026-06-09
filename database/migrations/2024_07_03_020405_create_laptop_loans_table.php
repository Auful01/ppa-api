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
        Schema::create('laptop_loans', function (Blueprint $table) {
            $table->id();
            $table->string('inventory_number', 255);
            $table->string('asset_number', 255);
            $table->string('serial_number', 255);
            $table->string('laptop_name', 255);
            $table->string('laptop_brand', 255);
            $table->string('spesifikasi');
            $table->string('laptop_model', 255);
            $table->string('processor', 255);
            $table->string('hdd', 255);
            $table->string('ssd', 255);
            $table->string('ram', 255);
            $table->string('vga', 255);
            $table->string('color', 255);
            $table->string('os', 255);
            $table->string('application', 255);
            $table->string('ip_address', 255);
            $table->string('laptop_condition', 255);
            $table->string('laptop_status', 255);
            $table->string('last_borrower', 255);
            $table->timestamps();
        });

        Schema::create('garansion_laptops', function (Blueprint $table) {
            $table->id();
            $table->string('garansion_code', 255)->nullable();
            $table->unsignedBigInteger('laptop_id')->nullable();
            $table->foreign('laptop_id')->references('id')->on('inv_laptops')->cascadeOnDelete();
            $table->string('inventory_number', 255)->nullable();
            $table->string('status', 255)->nullable();
            $table->dateTime('start_progress')->nullable();
            $table->dateTime('end_progress')->nullable();
            $table->integer('month')->nullable();
            $table->integer('year')->nullable();
            $table->string('date_of_garansion')->nullable();
            $table->string('record_data', 255)->nullable();
            $table->string('hardware_damage', 255)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('garansion_laptops');
        Schema::dropIfExists('laptop_loans');
    }
};
