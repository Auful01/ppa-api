<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('schedule_laptop', function (Blueprint $table) {
            $table->id(); // Auto-increment primary key
            $table->uuid('id_laptop'); // FK to laptop.id (char 36)
            $table->date('tanggal_inspection');
            $table->string('laptop_code');
            $table->string('dept');
            $table->string('site');
            $table->timestamps();

            // Optional: foreign key constraint if 'laptop.id' is UUID
            // $table->foreign('id_laptop')->references('id')->on('laptop')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('schedule_laptop');
    }
};
