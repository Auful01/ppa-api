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
        Schema::create('inspeksi_computers', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('inv_computer_id')->nullable();
            $table->foreign('inv_computer_id')->references('id')->on('inv_computers')->cascadeOnDelete()->nullable();

            $table->string('pica_number', 255)->nullable();
            $table->date('created_date')->nullable();
            $table->integer('month')->nullable();
            $table->integer('year')->nullable();
            $table->string('inspection_status', 255)->nullable();
            $table->string('triwulan', 255)->nullable();
            $table->string('inspector', 255)->nullable();
            $table->string('conditions', 255)->nullable();
            $table->string('physique_condition_cpu', 255)->nullable();
            $table->string('physique_condition_internal_cpu', 255)->nullable();
            $table->string('physique_condition_monitor', 255)->nullable();
            $table->string('software_license', 255)->nullable();
            $table->string('software_standaritation', 255)->nullable();
            $table->string('software_device_name_standaritation', 255)->nullable();
            $table->string('software_clear_cache', 255)->nullable();
            $table->string('software_system_restore', 255)->nullable();
            $table->string('software_windows_update', 255)->nullable();
            $table->string('software_storage_health', 255)->nullable();
            $table->string('defrag', 255)->nullable();
            $table->string('hard_maintenance', 255)->nullable();
            $table->string('security_change_password', 255)->nullable();
            $table->string('security_auto_lock', 255)->nullable();
            $table->string('security_input_password', 255)->nullable();
            $table->string('crew', 255)->nullable();
            $table->string('findings', 255)->nullable();
            $table->string('findings_image', 255)->nullable();
            $table->string('findings_action', 255)->nullable();
            $table->string('action_image', 255)->nullable();
            $table->string('findings_status', 255)->nullable();
            $table->string('inspection_image', 255)->nullable();
            $table->string('remarks', 255)->nullable();
            $table->date('due_date')->nullable();
            $table->string('inventory_status', 255)->nullable();
            $table->string('ip_address', 255)->nullable();
            $table->string('location', 255)->nullable();
            $table->string('approved_by', 255)->nullable();
            $table->string('status_approval', 255)->nullable();
            $table->string('site', 255)->nullable();
            $table->string('last_edited_by', 255)->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inspeksi_computers');
    }
};
