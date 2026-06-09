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
        Schema::create('inspeksi_printers', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('inv_printer_id')->nullable();
            $table->foreign('inv_printer_id')->references('id')->on('inv_printers')->cascadeOnDelete()->nullable();

            $table->string('pica_number', 255)->nullable();
            $table->date('created_date')->nullable();
            $table->string('condition')->nullable();
            $table->datetime('inspection_at')->nullable();
            $table->integer('month')->nullable();
            $table->integer('year')->nullable();
            $table->string('inspection_status', 255)->nullable();
            $table->string('inspector', 255)->nullable();

            $table->string('tinta_cyan', 255)->nullable();
            $table->string('tinta_magenta', 255)->nullable();
            $table->string('tinta_yellow', 255)->nullable();
            $table->string('tinta_black', 255)->nullable();
            $table->string('body_condition', 255)->nullable();
            $table->string('usb_cable_condition', 255)->nullable();
            $table->string('power_cable_condition', 255)->nullable();
            $table->string('performing_physical_power_cleaning', 255)->nullable();
            $table->string('performing_cleaning_on_the_printer_waste_box', 255)->nullable();
            $table->string('performing_cleaning_head', 255)->nullable();
            $table->string('performing_print_quality_test', 255)->nullable();
            $table->string('do_replacing_cable', 255)->nullable();
            $table->string('findings', 255)->nullable();
            $table->string('findings_image', 255)->nullable();
            $table->string('findings_action', 255)->nullable();
            $table->string('action_image', 255)->nullable();
            $table->string('findings_status', 255)->nullable();
            $table->string('remarks', 255)->nullable();
            $table->date('due_date')->nullable();
            $table->string('inventory_status', 255)->nullable();
            $table->string('approved_by', 255)->nullable();
            $table->string('status_approval', 255)->nullable();
            $table->string('inspection_image', 255)->nullable();
            $table->string('nozle_image', 255)->nullable();
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
        Schema::dropIfExists('inspeksi_printers');
    }
};
