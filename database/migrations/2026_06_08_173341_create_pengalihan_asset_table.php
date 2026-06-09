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
        Schema::create('pengalihan_asset', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('id_inventory')->nullable();
            $table->string('id_inv_prev')->nullable();
            $table->string('nrp_user_prev')->nullable();
            $table->string('nrp_user_new')->nullable();
            $table->string('inv_number_next')->nullable();
            $table->date('tanggal_pengalihan')->nullable();
            $table->string('foto_pengalihan')->nullable();
            $table->text('remark')->nullable();
            $table->string('device')->nullable();
            $table->string('site')->nullable();
            $table->string('dept')->nullable();
            $table->string('dept_prev')->nullable();
            $table->text('spek')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pengalihan_asset');
    }
};
