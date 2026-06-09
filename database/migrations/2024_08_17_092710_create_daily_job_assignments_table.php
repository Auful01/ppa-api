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
        Schema::create('daily_job_assignments', function (Blueprint $table) {
            $table->id();
            $table->string('assignment_code', 255)->nullable();
            $table->string('job_assignment', 255)->nullable();
            $table->string('remark', 255)->nullable();
            $table->string('remark_technician', 255)->nullable();
            $table->string('job', 255)->nullable();
            $table->string('crew', 255)->nullable();
            $table->string('sarana', 255)->nullable();
            $table->date('due_date')->nullable();
            $table->string('status', 255)->nullable();
            $table->string('action', 255)->nullable();
            $table->string('shift', 255)->nullable();
            $table->string('crew_partner', 255)->nullable();
            $table->string('job_category', 255)->nullable();
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('daily_job_assignments');
    }
};
