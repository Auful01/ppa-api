<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Realtime push checkpoint for the `aduan:watch` daemon.
 *
 * The web (Inertia) project and this API project are SEPARATE Laravel apps that
 * share one database, so this project's AduanObserver never sees a web-created
 * row. The watcher instead polls `aduans` for rows it has not pushed yet, keyed
 * on `push_sent_at`:
 *   - NULL  => not yet notified (a fresh create, web or mobile) -> push once.
 *   - set   => already notified -> skip.
 *
 * Existing rows are backfilled to NOW() so the watcher never blasts historical
 * aduans on first start or after a restart. The column is additive and nullable;
 * the web project does not reference it, so no business flow changes.
 */
return new class extends Migration {
    public function up(): void
    {
        if (! Schema::hasColumn('aduans', 'push_sent_at')) {
            Schema::table('aduans', function (Blueprint $table) {
                // Indexed so the watcher query (WHERE push_sent_at IS NULL) never
                // scans the whole table.
                $table->timestamp('push_sent_at')->nullable()->index()->after('updated_at');
            });
        }

        // Treat everything that already exists as already-notified.
        DB::table('aduans')->whereNull('push_sent_at')->update(['push_sent_at' => now()]);
    }

    public function down(): void
    {
        if (Schema::hasColumn('aduans', 'push_sent_at')) {
            Schema::table('aduans', function (Blueprint $table) {
                $table->dropIndex(['push_sent_at']);
                $table->dropColumn('push_sent_at');
            });
        }
    }
};
