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
        Schema::create('inv_radios', function (Blueprint $table) {
            $table->id();
            $table->string('radio_code', 255);
            $table->string('sarana_number', 255);
            $table->string('category_radio', 255);
            $table->string('radio_brand', 255);
            $table->string('model_radio');
            $table->string('serial_number', 255);
            $table->string('date_of_inventory', 255);
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
        Schema::dropIfExists('inv_radios');
    }
};
