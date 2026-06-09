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
        Schema::create('komputers', function (Blueprint $table) {
            $table->id();
            $table->string('computer_code', 255);
            $table->string('computer_name', 255);
            $table->string('number_asset_ho', 255);
            $table->string('assets_category', 255);
            $table->longText('spesifikasi');
            $table->string('serial_number', 255);
            $table->string('aplikasi', 255);
            
            // $table->foreign('users_id')
            // ->references('id')
            // ->on('users')
            // ->onDelete('cascade')
            // ->onUpdate('cascade');
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('komputers');
    }
};
