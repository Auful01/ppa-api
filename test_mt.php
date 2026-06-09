<?php
$pdo = new PDO('mysql:host=127.0.0.1;port=3306;dbname=new_itportalv2', 'root', '');
$cols = $pdo->query("DESCRIBE inv_mobile_towers")->fetchAll(PDO::FETCH_ASSOC);
foreach ($cols as $c) {
    $null = $c['Null'] === 'YES' ? 'nullable' : 'NOT NULL';
    $default = $c['Default'] !== null ? "default={$c['Default']}" : 'no-default';
    echo "{$c['Field']} | {$c['Type']} | $null | $default\n";
}
