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
        Schema::create('root_cause_categories', function (Blueprint $table) {
            $table->id();
            $table->string('category_root_cause', 255)->nullable();
            $table->string('site_type', 255)->nullable();
            $table->string('created_by', 255)->nullable();
            $table->timestamps();
        });

        Schema::create('root_cause_problems', function (Blueprint $table) {
            $table->id('id_cause');

            $table->unsignedBigInteger('category_root_cause_id')->nullable();
            $table->foreign('category_root_cause_id')->references('id')->on('root_cause_categories')->cascadeOnDelete()->nullable();
            $table->string('kategori_name', 255)->nullable();
            $table->string('root_cause_problem', 255)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('root_cause_problems');
        Schema::dropIfExists('root_cause_categories');
    }
};
