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
        Schema::create('inv_computers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('max_id')->nullable();
            $table->string('computer_code', 255)->nullable();
            $table->string('computer_name', 255)->nullable();
            $table->string('number_asset_ho', 255)->nullable();
            $table->string('assets_category', 255)->nullable();
            $table->longText('spesifikasi')->nullable();
            $table->string('serial_number', 255)->nullable();
            $table->string('aplikasi', 255)->nullable();
            
            $table->unsignedBigInteger('user_alls_id')->nullable();
            $table->foreign('user_alls_id')->references('id')->on('user_alls')->nullOnDelete();

            $table->string('license', 255)->nullable();
            $table->string('ip_address', 255)->nullable();
            $table->dateTime('date_of_inventory')->nullable();
            $table->dateTime('date_of_deploy')->nullable();
            $table->string('location', 255)->nullable();
            $table->string('status', 255)->nullable();
            $table->string('condition', 255)->nullable();
            $table->longText('note')->nullable();
            $table->string('link_documentation_asset_image', 255)->nullable();
            $table->string('dept', 255)->nullable();
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
        Schema::dropIfExists('inv_computers');
    }
};
