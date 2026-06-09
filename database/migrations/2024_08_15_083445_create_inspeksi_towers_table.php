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
        Schema::create('inspeksi_towers', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('inv_tower_id')->nullable();
            $table->foreign('inv_tower_id')->references('id')->on('inv_towers')->cascadeOnDelete()->nullable();

            $table->string('pica_number', 255)->nullable();
            $table->date('created_date')->nullable();
            $table->string('worthiness')->nullable();
            $table->string('condition')->nullable();
            $table->datetime('inspection_at')->nullable();
            $table->integer('month')->nullable();
            $table->integer('year')->nullable();
            $table->string('inspection_status', 255)->nullable();
            $table->string('device_status', 255)->nullable();
            
            $table->string('physic_condition_tower', 255)->nullable();
            $table->string('physic_condition_tower_text', 255)->nullable();
            $table->string('grounding_tower', 255)->nullable();
            $table->string('grounding_tower_text', 255)->nullable();
            $table->string('fence_tower', 255)->nullable();
            $table->string('fence_tower_text', 255)->nullable();

            $table->string('findings', 255)->nullable();
            $table->string('findings_image', 255)->nullable();
            $table->string('findings_status', 255)->nullable();
            $table->string('findings_action', 255)->nullable();
            $table->string('action_image', 255)->nullable();
            $table->date('due_date')->nullable();
            $table->string('remarks', 255)->nullable();
            $table->string('inspector', 255)->nullable();
            $table->string('pic', 255)->nullable();
            $table->string('crew', 255)->nullable();
            $table->string('list_of_needs', 255)->nullable();
            $table->string('approved_by', 255)->nullable();
            $table->string('status_approval', 255)->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inspeksi_towers');
    }
};
