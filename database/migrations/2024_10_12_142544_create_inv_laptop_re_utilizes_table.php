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
        Schema::create('inv_laptop_re_utilizes', function (Blueprint $table) {
            $table->id();
            $table->string('laptop_code', 255);
            $table->string('laptop_name', 255);
            $table->string('number_asset_ho', 255);
            $table->string('assets_category', 255);
            $table->longText('spesifikasi');
            $table->string('serial_number', 255);
            $table->string('aplikasi', 255);
            
            $table->unsignedBigInteger('user_alls_id');
            $table->foreign('user_alls_id')->references('id')->on('user_alls')->cascadeOnDelete();

            $table->string('license', 255);
            $table->string('ip_address', 255);
            $table->dateTime('date_of_inventory');
            $table->dateTime('date_of_deploy');
            $table->string('location', 255);
            $table->string('status', 255);
            $table->string('condition', 255);
            $table->string('note', 255);
            $table->string('link_documentation_asset_image', 255);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inv_laptop_re_utilizes');
    }
};
