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
        Schema::create('inv_towers', function (Blueprint $table) {
            $table->id();
            $table->string('tower_code', 255);
            $table->string('location', 255);
            $table->string('detail_location', 255);
            $table->string('tower_type', 255);
            $table->string('status');
            $table->string('note', 255);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inv_towers');
    }
};
