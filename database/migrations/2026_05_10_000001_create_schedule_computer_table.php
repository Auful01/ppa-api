<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('schedule_computer')) {
            return;
        }

        Schema::create('schedule_computer', function (Blueprint $table) {
            $table->id();
            $table->uuid('id_computer');
            $table->date('tanggal_inspection');
            $table->date('actual_inspection')->nullable();
            $table->string('quarter', 10)->nullable();
            $table->integer('bulan')->nullable();
            $table->integer('tahun')->nullable();
            $table->string('computer_code');
            $table->string('dept');
            $table->string('site');
            $table->timestamps();

            $table->index(['site', 'tahun', 'quarter']);
            $table->index('id_computer');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('schedule_computer');
    }
};
