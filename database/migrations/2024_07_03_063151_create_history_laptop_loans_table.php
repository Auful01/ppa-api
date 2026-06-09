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
        Schema::create('history_laptop_loans', function (Blueprint $table) {
            $table->id();
            $table->string('inventory_number', 255);
            $table->string('asset_number', 255);
            $table->string('nik', 255);
            $table->string('name', 255);
            $table->string('position', 255);
            $table->string('department');
            $table->dateTime('date_of_loan')->nullable();
            $table->dateTime('date_of_return')->nullable();
            $table->string('remark', 255)->nullable();
            $table->string('link', 255)->nullable();
            $table->string('pic', 255);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('history_laptop_loans');
    }
};
