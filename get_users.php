<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();
use Illuminate\Support\Facades\DB;

$roles = ['ict_developer','ict_ho','ict_group_leader','ict_admin','ict_technician','soc_ho'];
foreach ($roles as $role) {
    $u = DB::table('users')->where('role', $role)->first();
    if ($u) {
        echo "[$role] nrp={$u->nrp} name={$u->name} site=" . ($u->site ?? 'NULL') . "\n";
    } else {
        echo "[$role] NO USER FOUND\n";
    }
}

echo "\nUsers table columns: ";
$cols = DB::select("SHOW COLUMNS FROM users");
echo implode(', ', array_map(fn($c) => $c->Field, $cols)) . "\n";
echo "\nTotal users: " . DB::table('users')->count() . "\n";
echo "Sample roles: ";
$roleCounts = DB::table('users')->select('role', DB::raw('count(*) as cnt'))->groupBy('role')->get();
foreach ($roleCounts as $r) echo "{$r->role}:{$r->cnt} ";
echo "\n";
