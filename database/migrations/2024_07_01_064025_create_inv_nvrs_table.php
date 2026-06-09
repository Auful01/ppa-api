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
        Schema::create('inv_nvrs', function (Blueprint $table) {
            $table->id();
            $table->string('nvr_code', 255);
            $table->string('nvr_name', 255);
            $table->string('nvr_brand', 255);
            $table->string('type_nvr', 255);
            $table->integer('number_of_channel');
            $table->string('mac_address', 255);
            $table->string('ip_address', 255);
            $table->string('location', 255);
            $table->integer('channel_free');
            $table->string('remarks', 255);
            $table->string('status', 255);
            $table->string('last_status_ping', 255);
            $table->dateTime('last_update_ping');
            $table->string('details', 255);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inv_nvrs');
    }
};
