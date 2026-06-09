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
        Schema::create('unschedule_jobs', function (Blueprint $table) {
            $table->id();
            $table->string('assignment_id', 255)->nullable();
            $table->string('activity_code', 255)->nullable();
            $table->string('category_report', 255)->nullable();
            $table->string('category', 255)->nullable();
            $table->dateTime('start_progress')->nullable();
            $table->dateTime('end_progress')->nullable();
            $table->string('issue', 255)->nullable();
            $table->string('root_cause', 255)->nullable();
            $table->string('action', 255)->nullable();
            $table->string('crew', 255)->nullable();
            $table->string('status', 255)->nullable();
            $table->string('urgency', 255)->nullable();
            $table->string('remark', 255)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('unschedule_jobs');
    }
};
