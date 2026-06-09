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
        Schema::create('kpi_vhms', function (Blueprint $table) {
            $table->id();

            $table->string('week_data', 255)->nullable();
            $table->string('unit_code', 255)->nullable();
            $table->string('remark', 255)->nullable();
            $table->integer('month')->nullable();
            $table->integer('year')->nullable();
            $table->string('status', 255)->nullable();
            $table->string('pic', 255)->nullable();
            $table->string('created_by', 255)->nullable();

            $table->timestamps();
        });

        Schema::create('kpi_vhms_evidence', function (Blueprint $table) {
            $table->id();

            $table->string('week_data', 255)->nullable();
            $table->string('evidence_image', 255)->nullable();
            $table->integer('month')->nullable();
            $table->integer('year')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kpi_vhms_evidence');
        Schema::dropIfExists('kpi_vhms');
    }
};
