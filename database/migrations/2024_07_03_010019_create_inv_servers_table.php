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
        Schema::create('inv_servers', function (Blueprint $table) {
            $table->id();
            $table->string('server_name', 255);
            $table->string('server_brand', 255);
            $table->string('type_server', 255);
            $table->string('category_server', 255);
            $table->string('ip_address');
            $table->string('number_asset_ho', 255);
            $table->string('service_tag', 255);
            $table->string('cpu', 255);
            $table->string('ram', 255);
            $table->string('hdd', 255);
            $table->string('os', 255);
            $table->string('status', 255);
            $table->string('remark', 255);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inv_servers');
    }
};
