<?php
$pdo = new PDO('mysql:host=127.0.0.1;port=3306;dbname=new_itportalv2', 'root', '');
$cols = $pdo->query("DESCRIBE users")->fetchAll(PDO::FETCH_COLUMN);
echo "users columns: " . implode(', ', $cols) . "\n\n";
$rows = $pdo->query("SELECT nrp, role, site FROM users LIMIT 15")->fetchAll(PDO::FETCH_ASSOC);
foreach ($rows as $r) {
    echo "{$r['nrp']} | {$r['role']} | {$r['site']}\n";
}
