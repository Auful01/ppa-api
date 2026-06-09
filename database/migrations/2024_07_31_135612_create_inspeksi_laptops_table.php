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
        Schema::create('inspeksi_laptops', function (Blueprint $table) {
            $table->id();
            
            $table->unsignedBigInteger('inv_laptop_id')->nullable();
            $table->foreign('inv_laptop_id')->references('id')->on('inv_laptops')->cascadeOnDelete()->nullable();

            $table->string('pica_number', 255)->nullable();
            $table->date('created_date')->nullable();
            $table->string('condition')->nullable();
            $table->datetime('inspection_at')->nullable();
            $table->integer('year')->nullable();
            $table->string('inspection_status', 255)->nullable();
            $table->string('inspector', 255)->nullable();

            $table->string('software_defrag', 255)->nullable();
            $table->string('software_check_system_restore', 255)->nullable();
            $table->string('software_clean_cache_data', 255)->nullable();
            $table->string('software_check_ilegal_software', 255)->nullable();
            $table->string('software_change_password', 255)->nullable();
            $table->string('software_windows_license', 255)->nullable();
            $table->string('software_office_license', 255)->nullable();
            $table->string('software_standaritation_software', 255)->nullable();
            $table->string('software_update_sinology', 255)->nullable();
            $table->string('software_turn_off_windows_update', 255)->nullable();
            $table->string('software_cheking_ssd_health', 255)->nullable();
            $table->string('software_percentage_ssd_health', 255)->nullable();
            $table->string('software_standaritation_device_name', 255)->nullable();
            $table->string('hardware_fan_cleaning', 255)->nullable();
            $table->string('hardware_change_pasta', 255)->nullable();
            $table->string('hardware_any_maintenance', 255)->nullable();
            $table->string('hardware_any_maintenance_explain', 255)->nullable();
            $table->string('security_change_password', 255)->nullable();
            $table->string('security_auto_lock', 255)->nullable();
            $table->string('security_input_password', 255)->nullable();
            $table->string('findings', 255)->nullable();
            $table->string('findings_image', 255)->nullable();
            $table->string('findings_action', 255)->nullable();
            $table->string('action_image', 255)->nullable();
            $table->string('findings_status', 255)->nullable();
            $table->string('inspection_image', 255)->nullable();
            $table->string('remarks', 255)->nullable();
            $table->date('due_date')->nullable();
            $table->string('inventory_status', 255)->nullable();
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
        Schema::dropIfExists('inspeksi_laptops');
    }
};
