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
        Schema::create('inv_mobile_towers', function (Blueprint $table) {
            $table->id();
            $table->string('inventory_number', 255);
            $table->string('mt_code', 255);
            $table->string('type_mt', 255);
            $table->string('location', 255);
            $table->string('detail_location');
            $table->string('gps', 255);
            $table->string('led_lamp', 255);
            $table->string('condition', 255);
            $table->string('status', 255);
            $table->string('note', 255);
            $table->string('padlock_code', 255);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inv_mobile_towers');
    }
};
