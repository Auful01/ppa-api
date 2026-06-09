<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('historical_vhms_downloads', function (Blueprint $table) {
            $table->id();
            $table->string('sn')->index();
            $table->string('cn')->nullable();
            $table->string('model')->nullable();
            $table->string('status'); // update, waiting, not_update
            $table->date('last_download')->nullable();
            $table->date('last_operation')->nullable();
            $table->dateTime('pld_last_record')->nullable();
            $table->dateTime('trend_last_record')->nullable();
            $table->dateTime('fault_last_record')->nullable();
            $table->dateTime('his_last_record')->nullable();
            $table->dateTime('last_histori')->nullable(); // the job execution time (23:59)
            $table->date('date')->nullable();
            $table->unsignedTinyInteger('month')->nullable();
            $table->unsignedSmallInteger('year')->nullable();
            $table->string('site')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('historical_vhms_downloads');
    }
};
