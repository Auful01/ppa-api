<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();
$roles = \DB::table('users')->select('role')->distinct()->orderBy('role')->pluck('role');
echo json_encode($roles->toArray());
