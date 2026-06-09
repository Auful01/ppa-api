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
        Schema::create('computer_loans', function (Blueprint $table) {
            $table->id();
            $table->string('inventory_number', 255);
            $table->string('asset_number', 255);
            $table->string('serial_number', 255);
            $table->string('computer_name', 255);
            $table->string('computer_brand', 255);
            $table->string('spesifikasi');
            $table->string('computer_model', 255);
            $table->string('processor', 255);
            $table->string('hdd', 255);
            $table->string('ssd', 255);
            $table->string('ram', 255);
            $table->string('vga', 255);
            $table->string('color', 255);
            $table->string('os', 255);
            $table->string('application', 255);
            $table->string('ip_address', 255);
            $table->string('computer_condition', 255);
            $table->string('computer_status', 255);
            $table->string('last_borrower', 255);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('computer_loans');
    }
};
