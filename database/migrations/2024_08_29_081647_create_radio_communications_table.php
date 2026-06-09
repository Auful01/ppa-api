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
        Schema::create('radio_communications', function (Blueprint $table) {
            $table->id();
            
            $table->string('activity_code', 255)->nullable();
            
            $table->dateTime('start_report')->nullable();
            $table->dateTime('start_response')->nullable();
            $table->dateTime('start_time')->nullable();
            $table->dateTime('finish_time')->nullable();
            $table->date('report_date')->nullable();

            $table->string('problem', 255)->nullable();
            $table->string('action', 255)->nullable();
            $table->string('nrp_operator', 255)->nullable();
            $table->string('unit_code', 255)->nullable();
            $table->string('findings', 255)->nullable();
            $table->string('issue_details', 255)->nullable();
            $table->string('swr_results', 255)->nullable();
            $table->string('job_category', 255)->nullable();
            $table->string('shift', 255)->nullable();
            $table->string('repair_note', 255)->nullable();
            $table->string('status', 255)->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('radio_communications');
    }
};
