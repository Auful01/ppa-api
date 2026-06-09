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
        Schema::create('inv_cctvs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('max_id')->nullable();
            $table->string('cctv_code', 255);
            $table->string('asset_ho_number', 255)->nullable();
            $table->string('location', 255);
            $table->string('location_detail', 255)->nullable();
            $table->string('cctv_name', 255);
            $table->string('cctv_brand')->nullable();
            $table->string('type_cctv', 255)->nullable();
            $table->string('mac_address', 255)->nullable();
            $table->string('ip_address');
            $table->string('vlan', 255)->nullable();

            $table->unsignedBigInteger('nvr_id')->nullable();
            $table->foreign('nvr_id')->references('id')->on('inv_nvrs')->nullOnDelete();
            
            $table->unsignedBigInteger('switch_id')->nullable();
            $table->foreign('switch_id')->references('id')->on('inv_switches')->nullOnDelete();
            
            $table->string('uplink', 255)->nullable();
            $table->dateTime('date_of_inventory')->nullable();
            $table->string('status', 255)->nullable();
            $table->string('note', 255)->nullable();
            $table->string('last_status_ping', 255)->nullable();
            $table->dateTime('last_update_ping')->nullable();
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
        Schema::dropIfExists('inv_cctvs');
    }
};
