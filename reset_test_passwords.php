<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

$testUsers = [
    ['nrp' => '23002073', 'role' => 'ict_developer'],
    ['nrp' => '12070267', 'role' => 'ict_ho'],
    ['nrp' => '21002037', 'role' => 'ict_group_leader'],
    ['nrp' => '0002',     'role' => 'ict_admin'],
    ['nrp' => '22003275', 'role' => 'ict_technician'],
];

foreach ($testUsers as $u) {
    $updated = DB::table('users')
        ->where('nrp', $u['nrp'])
        ->update(['password' => Hash::make($u['nrp'])]);
    echo "Reset [{$u['role']}] nrp={$u['nrp']} password={$u['nrp']} => " . ($updated ? 'OK' : 'FAIL') . "\n";
}

// Check for soc_ho
$socHo = DB::table('users')->where('role', 'soc_ho')->first();
if (!$socHo) {
    $roles = DB::table('users')->select('role')->distinct()->orderBy('role')->pluck('role');
    echo "\nAvailable roles: " . implode(', ', $roles->toArray()) . "\n";
    echo "soc_ho: NOT FOUND\n";
} else {
    DB::table('users')->where('nrp', $socHo->nrp)->update(['password' => Hash::make($socHo->nrp)]);
    echo "soc_ho: nrp={$socHo->nrp} name={$socHo->name}\n";
}

// Also reset ict_ho
$ho = DB::table('users')->where('role', 'ict_ho')->first();
if ($ho) {
    DB::table('users')->where('id', $ho->id)->update(['password' => Hash::make($ho->nrp)]);
    echo "ict_ho reset: {$ho->nrp}\n";
}
