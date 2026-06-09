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
        Schema::create('inv_printers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('max_id')->nullable();
            $table->string('asset_ho_number', 255)->nullable();
            $table->string('printer_code', 255);
            $table->string('serial_number', 255)->nullable();
            $table->string('mac_address', 255)->nullable();
            $table->string('ip_address', 255)->nullable();
            $table->string('item_name', 255)->nullable();
            $table->string('printer_brand', 255)->nullable();
            $table->string('printer_type', 255)->nullable();
            $table->string('location', 255)->nullable();
            $table->dateTime('date_of_inventory')->nullable();
            $table->string('status', 255)->nullable();
            $table->string('note', 255)->nullable();
            $table->string('division', 255)->nullable();
            $table->string('department', 255)->nullable();
            $table->string('inspection_remark', 255)->nullable();
            $table->string('site', 255)->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inv_printers');
    }
};
