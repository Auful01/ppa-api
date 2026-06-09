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
        Schema::create('inv_gps', function (Blueprint $table) {
            $table->id();
            $table->string('gps_code', 255);
            $table->string('location', 255);
            $table->string('brand_gps', 255);
            $table->string('model_gps', 255);
            $table->longText('type_gps');
            $table->string('phone_number', 255);
            $table->string('serial_number', 255);
            $table->dateTime('date_of_inventory');
            $table->string('status', 255);
            $table->string('note', 255);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inv_gps');
    }
};
