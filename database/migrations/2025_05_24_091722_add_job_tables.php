<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('daily_jobs', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique()->nullable();
            $table->enum('category_job', ['assignment', 'unschedule']);
            $table->text('description')->nullable();
            $table->string('site');
            $table->string('category');
            // Production stores the tokens SHIFT_1/SHIFT_2 (web write-path +
            // validation in:SHIFT_1,SHIFT_2). See ALTER migration
            // 2026_06_12_000000_fix_daily_jobs_shift_values for existing DBs.
            $table->enum('shift', ['SHIFT_1', 'SHIFT_2']);
            $table->date('date');
            $table->datetime('start_progress')->nullable();
            $table->datetime('end_progress')->nullable();
            $table->text('issue')->nullable();
            $table->text('root_cause')->nullable();
            $table->text('action_taken')->nullable();
            $table->text('remark')->nullable();
            $table->enum('status', ['open', 'continue', 'closed', 'outstanding', 'cancel'])->default('open');
            $table->enum('urgency', ['low', 'medium', 'high'])->default('medium');
            $table->json('crew')->nullable(); // Stores array of user IDs
            $table->string('sarana')->nullable(); // Previously 'sarana'
            $table->foreignId('created_by')->constrained('users');
            $table->foreignId('updated_by')->nullable()->constrained('users');
            $table->timestamps();

            $table->index(['date', 'shift']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('daily_jobs');
    }
};
