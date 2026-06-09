<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('schedule_mobile_tower', function (Blueprint $table) {
            $table->id();

            $table->uuid('id_mobile_tower');
            $table->date('tanggal_inspection');
            $table->date('actual_inspection')->nullable();

            $table->integer('bulan')->nullable();
            $table->integer('tahun')->nullable();

            $table->string('mobile_tower_code');
            $table->string('dept');
            $table->string('site');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('schedule_mobile_tower');
    }
};
