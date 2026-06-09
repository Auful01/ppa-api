<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$tables = ['inv_laptops','inv_computers','inv_printers','inv_scanners','inv_cctvs','inv_aps','inv_switches','inv_wirellesses','inv_mobile_towers','daily_jobs','unschedule_jobs','departments','pengajuan_akses_user','mutasi_assets','pica_inspeksis'];
foreach ($tables as $table) {
    try {
        $cols = DB::select("SHOW COLUMNS FROM `$table` WHERE Field='id'");
        if ($cols) {
            $col = $cols[0];
            echo $table . ": " . $col->Type . " | " . ($col->Extra ?? 'no-extra') . "\n";
        }
    } catch(Exception $e) {
        echo $table . ": ERROR - " . $e->getMessage() . "\n";
    }
}