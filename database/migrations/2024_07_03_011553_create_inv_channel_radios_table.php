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
        Schema::create('inv_channel_radios', function (Blueprint $table) {
            $table->id();
            $table->string('channel_name', 255);
            $table->string('rx', 255);
            $table->string('tx', 255);
            $table->string('note', 255);
            $table->string('isr_id')->nullable();
            $table->string('status_isr', 255);
            $table->string('maintenance_by', 255);
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
        Schema::dropIfExists('inv_channel_radios');
    }
};
