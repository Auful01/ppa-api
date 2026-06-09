<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('daily_jobs', function (Blueprint $table) {
            $table->boolean('is_approved')->default(false)->after('updated_by');
            $table->foreignId('approved_by')->nullable()->constrained('users')->after('is_approved');
            $table->datetime('approved_at')->nullable()->after('approved_by');
        });

        // Tambah 'support' ke enum category_job (data lama sudah ada nilai ini)
        DB::statement("ALTER TABLE daily_jobs MODIFY COLUMN category_job ENUM('assignment', 'support', 'unschedule') NOT NULL");
    }

    public function down(): void
    {
        Schema::table('daily_jobs', function (Blueprint $table) {
            $table->dropForeign(['approved_by']);
            $table->dropColumn(['is_approved', 'approved_by', 'approved_at']);
        });

        DB::statement("ALTER TABLE daily_jobs MODIFY COLUMN category_job ENUM('assignment', 'unschedule') NOT NULL");
    }
};
