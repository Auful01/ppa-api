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
        Schema::create('perangkat_breakdowns', function (Blueprint $table) {
            $table->id();
            $table->string('device_category', 255)->nullable();
            $table->string('inventory_number', 255)->nullable();
            $table->string('device_name', 255)->nullable();
            $table->dateTime('start_progress')->nullable();
            $table->dateTime('end_progress')->nullable();
            $table->date('created_date')->nullable();
            $table->string('month')->nullable();
            $table->string('year', 255)->nullable();
            $table->string('location', 255)->nullable();
            $table->string('root_cause', 255)->nullable();
            $table->string('root_cause_category', 255)->nullable();
            $table->string('status', 255)->nullable();
            $table->string('pic', 255)->nullable();
            $table->string('garansion_laptop_code', 255)->nullable();

            // $table->unsignedBigInteger('garansion_laptop_code')->nullable();
            // $table->foreign('garansion_laptop_code')->references('id')->on('garansion_laptops')->cascadeOnDelete();
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('perangkat_breakdowns');
    }
};
