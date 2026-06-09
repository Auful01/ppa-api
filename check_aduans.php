<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$cols = DB::select('SHOW CREATE TABLE aduans');
echo $cols[0]->{'Create Table'};
echo "\n\n";
$cnt = DB::table('aduans')->count();
echo "aduans count: " . $cnt . "\n";
$first = DB::table('aduans')->select('id')->orderBy('id','desc')->first();
echo "max id: " . ($first ? $first->id : 'none') . "\n";