<?php
$baseUrl = 'http://localhost:8120';

$ch = curl_init("$baseUrl/api/auth/login");
curl_setopt_array($ch, [CURLOPT_POST=>true, CURLOPT_POSTFIELDS=>json_encode(['nrp'=>'23002073','password'=>'23002073']),
    CURLOPT_HTTPHEADER=>['Content-Type: application/json','Accept: application/json'], CURLOPT_RETURNTRANSFER=>true]);
$resp = json_decode(curl_exec($ch), true);
curl_close($ch);
$token = $resp['token'];

$data = [
    'inventory_number' => 'MT-INV-003',
    'mt_code' => 'MT-TST-003',
    'type_mt' => 'Monopole',
    'location' => 'Test Tower',
    'detail_location' => 'Area A',
    'gps' => '-6.2,106.8',
    'led_lamp' => 'Yes',
    'condition' => 'Good',
    'status' => 'active',
    'padlock_code' => 'PL-001',
    'site' => 'BIB',
];

$ch = curl_init("$baseUrl/api/inventory/mobile-tower");
curl_setopt_array($ch, [CURLOPT_POST=>true, CURLOPT_POSTFIELDS=>json_encode($data),
    CURLOPT_HTTPHEADER=>["Content-Type: application/json","Accept: application/json","Authorization: Bearer $token"],
    CURLOPT_RETURNTRANSFER=>true]);
$body = curl_exec($ch);
$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);
$r = json_decode($body, true);
$id = $r['data']['id'] ?? $r['id'] ?? null;
echo ($code === 201 ? '[PASS]' : '[FAIL]') . " POST /inventory/mobile-tower → $code | " . ($id ? "id=$id" : substr($body, 0, 300)) . "\n";
