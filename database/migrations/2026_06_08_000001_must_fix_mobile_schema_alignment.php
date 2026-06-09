<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Fix 1: pica_inspeksis.pica_number
        // LIVE: varchar(255) DEFAULT NULL
        // DEV:  int(11) NOT NULL  ← will corrupt string codes like 'PICA/PB/2025/01'
        if (Schema::hasTable('pica_inspeksis')) {
            $column = DB::selectOne("SHOW COLUMNS FROM `pica_inspeksis` LIKE 'pica_number'");
            if ($column && str_contains(strtolower($column->Type), 'int')) {
                Schema::table('pica_inspeksis', function (Blueprint $table) {
                    $table->string('pica_number', 255)->nullable()->change();
                });
            }
        }

        // Fix 2a: inspeksi_panel_box_networks — rename typo column
        // DEV has 'approvred_by' (typo); LIVE has 'approved_by'
        // Controller writes 'approved_by' → silently fails on DEV
        if (Schema::hasTable('inspeksi_panel_box_networks')) {
            if (Schema::hasColumn('inspeksi_panel_box_networks', 'approvred_by')
                && ! Schema::hasColumn('inspeksi_panel_box_networks', 'approved_by')) {
                Schema::table('inspeksi_panel_box_networks', function (Blueprint $table) {
                    $table->renameColumn('approvred_by', 'approved_by');
                });
            }

            // Fix 2b: Add inventory_status column
            // InspeksiPanelBoxNetworkController::store() checks $request->inventory_status
            // and uses it to write back to inv_panel_boxes.status
            if (! Schema::hasColumn('inspeksi_panel_box_networks', 'inventory_status')) {
                Schema::table('inspeksi_panel_box_networks', function (Blueprint $table) {
                    $table->string('inventory_status', 255)->nullable()->after('cable_arrangement');
                });
            }
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('pica_inspeksis')) {
            Schema::table('pica_inspeksis', function (Blueprint $table) {
                $table->integer('pica_number')->nullable(false)->change();
            });
        }

        if (Schema::hasTable('inspeksi_panel_box_networks')) {
            if (Schema::hasColumn('inspeksi_panel_box_networks', 'approved_by')
                && ! Schema::hasColumn('inspeksi_panel_box_networks', 'approvred_by')) {
                Schema::table('inspeksi_panel_box_networks', function (Blueprint $table) {
                    $table->renameColumn('approved_by', 'approvred_by');
                });
            }

            if (Schema::hasColumn('inspeksi_panel_box_networks', 'inventory_status')) {
                Schema::table('inspeksi_panel_box_networks', function (Blueprint $table) {
                    $table->dropColumn('inventory_status');
                });
            }
        }
    }
};
