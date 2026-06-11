<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('daily_jobs') || Schema::hasColumn('daily_jobs', 'approval_status')) {
            return;
        }

        Schema::table('daily_jobs', function (Blueprint $table) {
            $table->string('approval_status')->nullable()->after('status');
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('daily_jobs') || ! Schema::hasColumn('daily_jobs', 'approval_status')) {
            return;
        }

        Schema::table('daily_jobs', function (Blueprint $table) {
            $table->dropColumn('approval_status');
        });
    }
};
