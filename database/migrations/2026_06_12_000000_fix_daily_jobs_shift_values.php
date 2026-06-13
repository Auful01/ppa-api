<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Aligns daily_jobs.shift with the authoritative production values SHIFT_1/SHIFT_2.
 *
 * The original create migration declared enum('pagi','malam'), but the entire
 * web stack writes and validates SHIFT_1/SHIFT_2 (DailyJobController::store /
 * UnscheduleJobController::store default 'SHIFT_1', validation in:SHIFT_1,SHIFT_2;
 * every Create/Edit/List Vue uses SHIFT_1/SHIFT_2). The monitoring/assignment/
 * unschedule filters run where('shift', $request->shift) with NO mapping, so the
 * stored value must be SHIFT_1/SHIFT_2. This converts the column accordingly and
 * folds any stray legacy 'pagi'/'malam' rows onto the canonical tokens.
 */
return new class extends Migration {
    public function up(): void
    {
        if (! Schema::hasColumn('daily_jobs', 'shift')) {
            return;
        }

        // Widen to varchar first so the legacy enum can't reject the new tokens.
        DB::statement("ALTER TABLE daily_jobs MODIFY COLUMN shift VARCHAR(20) NULL");
        DB::table('daily_jobs')->whereIn('shift', ['pagi', 'PAGI'])->update(['shift' => 'SHIFT_1']);
        DB::table('daily_jobs')->whereIn('shift', ['malam', 'MALAM'])->update(['shift' => 'SHIFT_2']);
        DB::table('daily_jobs')->whereNull('shift')->update(['shift' => 'SHIFT_1']);
        DB::table('daily_jobs')
            ->whereNotIn('shift', ['SHIFT_1', 'SHIFT_2'])
            ->update(['shift' => 'SHIFT_1']);

        // Lock to the authoritative enum.
        DB::statement("ALTER TABLE daily_jobs MODIFY COLUMN shift ENUM('SHIFT_1','SHIFT_2') NOT NULL");
    }

    public function down(): void
    {
        if (! Schema::hasColumn('daily_jobs', 'shift')) {
            return;
        }

        DB::statement("ALTER TABLE daily_jobs MODIFY COLUMN shift VARCHAR(20) NULL");
        DB::table('daily_jobs')->where('shift', 'SHIFT_1')->update(['shift' => 'pagi']);
        DB::table('daily_jobs')->where('shift', 'SHIFT_2')->update(['shift' => 'malam']);
        DB::statement("ALTER TABLE daily_jobs MODIFY COLUMN shift ENUM('pagi','malam') NOT NULL");
    }
};
