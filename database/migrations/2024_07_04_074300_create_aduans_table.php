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
        Schema::create('aduans', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('max_id')->nullable();
            $table->string('complaint_code', 255)->nullable();
            $table->string('complaint_image', 255)->nullable();
            $table->string('inventory_number', 255)->nullable();
            $table->dateTime('date_of_complaint')->nullable();
            $table->date('created_date')->nullable();
            $table->dateTime('start_response')->nullable();
            $table->dateTime('start_progress')->nullable();
            $table->dateTime('end_progress')->nullable();
            $table->string('response_time', 255)->nullable();
            $table->string('category_name', 255)->nullable();
            $table->longText('complaint_note')->nullable();
            $table->string('complaint_name', 255)->nullable();
            $table->string('complaint_position', 255)->nullable();
            $table->string('phone_number', 255)->nullable();
            $table->string('nrp', 255)->nullable();
            $table->string('location', 255)->nullable();
            $table->string('detail_location', 255)->nullable();
            $table->longText('repair_note')->nullable();
            $table->string('repair_image', 255)->nullable();
            $table->string('status', 255)->nullable();
            $table->unsignedBigInteger('root_cause_id')->nullable();
            $table->string('action_repair', 255)->nullable();
            $table->string('crew', 255)->nullable();
            $table->string('urgency', 255)->nullable();
            $table->string('site', 255)->nullable();
            $table->string('site_pelapor', 255)->nullable();
            $table->unsignedTinyInteger('user_rating')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('aduans');
    }
};
