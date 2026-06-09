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
        Schema::create('dispatch_vhms', function (Blueprint $table) {
            $table->id();
 
            $table->string('unit_code', 255)->nullable();
            $table->string('unit_model', 255)->nullable();
            $table->string('sn_unit', 255)->nullable();
            $table->string('ip_unit', 255)->nullable();
            $table->string('wirelless_type', 255)->nullable();
            $table->string('mac_wirelless', 255)->nullable();
            $table->string('status', 255)->nullable();
            $table->string('remark', 255)->nullable();
            $table->integer('bulan')->nullable();
            $table->integer('tahun')->nullable();

            $table->timestamps();
        });

        Schema::create('dispatch_repair_vhms', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('unit_vhms_id')->nullable();
            $table->foreign('unit_vhms_id')->references('id')->on('dispatch_vhms')->cascadeOnDelete()->nullable();

            $table->string('status_vhms', 255)->nullable();
            $table->string('root_cause', 255)->nullable();
            $table->string('other', 255)->nullable();
            $table->string('action', 255)->nullable();
            $table->string('update_by', 255)->nullable();
            $table->string('repair_note', 255)->nullable();
            $table->string('checking_by', 255)->nullable();
            $table->dateTime('date')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dispatch_repair_vhms');
        Schema::dropIfExists('dispatch_vhms');
    }
};
