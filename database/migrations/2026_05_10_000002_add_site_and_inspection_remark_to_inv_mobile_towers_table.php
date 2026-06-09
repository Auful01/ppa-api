<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('inv_mobile_towers', function (Blueprint $table) {
            if (! Schema::hasColumn('inv_mobile_towers', 'site')) {
                $table->string('site', 255)->nullable()->after('padlock_code');
            }

            if (! Schema::hasColumn('inv_mobile_towers', 'inspection_remark')) {
                $table->string('inspection_remark', 255)->nullable()->after('site');
            }
        });

        if (Schema::hasColumn('inv_mobile_towers', 'site')) {
            DB::table('inv_mobile_towers')
                ->whereNull('site')
                ->update(['site' => 'IPT']);
        }
    }

    public function down(): void
    {
        Schema::table('inv_mobile_towers', function (Blueprint $table) {
            if (Schema::hasColumn('inv_mobile_towers', 'inspection_remark')) {
                $table->dropColumn('inspection_remark');
            }

            if (Schema::hasColumn('inv_mobile_towers', 'site')) {
                $table->dropColumn('site');
            }
        });
    }
};
