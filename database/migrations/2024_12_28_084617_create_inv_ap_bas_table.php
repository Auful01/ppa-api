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
        Schema::create('inv_ap_bas', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->unsignedBigInteger('max_id');

            $table->string('device_name', 255);
            $table->string('inventory_number', 255);
            $table->string('asset_ho_number', 255);
            $table->string('serial_number', 255)->nullable();
            $table->string('frequency', 255)->nullable();
            $table->string('ip_address', 255)->nullable();
            $table->string('mac_address', 255)->nullable();
            $table->string('device_brand', 255)->nullable();
            $table->string('device_type', 255)->nullable();
            $table->string('device_model', 255)->nullable();
            $table->string('location', 255)->nullable();
            $table->string('status', 255)->nullable();
            $table->longText('note')->nullable();
            $table->string('inspection_remark', 255)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inv_ap_bas');
    }
};
