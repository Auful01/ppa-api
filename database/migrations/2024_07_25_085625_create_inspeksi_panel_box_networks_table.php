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
        Schema::create('inspeksi_panel_box_networks', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('inv_panel_box_id')->nullable();
            $table->foreign('inv_panel_box_id')->references('id')->on('inv_panel_boxes')->cascadeOnDelete()->nullable();

            $table->date('created_date')->nullable();
            $table->integer('month')->nullable();
            $table->integer('year')->nullable();
            $table->string('inspection_status', 255)->nullable();
            $table->string('pica_number', 255)->nullable();
            $table->string('findings', 255)->nullable();
            $table->string('findings_image', 255)->nullable();
            $table->string('findings_action', 255)->nullable();
            $table->string('action_image', 255)->nullable();
            $table->string('findings_status', 255)->nullable();
            $table->date('due_date')->nullable();
            $table->string('cleanliness', 255)->nullable();
            $table->string('conditions', 255)->nullable();
            $table->string('remarks', 255)->nullable();
            $table->string('cable_arrangement', 255)->nullable();
            $table->string('inspection_by', 255)->nullable();
            $table->dateTime('inspection_at')->nullable();
            $table->string('approvred_by', 255)->nullable();
            $table->string('status_approval', 255)->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inspeksi_panel_box_networks');
    }
};
