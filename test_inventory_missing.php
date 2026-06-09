<?php
$baseUrl = 'http://localhost:8120';

$ch = curl_init("$baseUrl/api/auth/login");
curl_setopt_array($ch, [
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => json_encode(['nrp' => '23002073', 'password' => '23002073']),
    CURLOPT_HTTPHEADER => ['Content-Type: application/json', 'Accept: application/json'],
    CURLOPT_RETURNTRANSFER => true,
]);
$resp = json_decode(curl_exec($ch), true);
curl_close($ch);
$token = $resp['token'];

function apiCall($method, $url, $token, $data = null) {
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_CUSTOMREQUEST => $method,
        CURLOPT_HTTPHEADER => ["Content-Type: application/json", "Accept: application/json", "Authorization: Bearer $token"],
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POSTFIELDS => $data ? json_encode($data) : null,
    ]);
    $body = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    return [$code, json_decode($body, true)];
}

$tests = [
    ['type' => 'access-point', 'data' => [
        'device_name' => 'AP-TEST-002', 'inventory_number' => 'AP-INV-002',
        'asset_ho_number' => 'HO-AP-002', 'serial_number' => 'SN-AP-002',
        'mac_address' => 'AA:BB:CC:DD:EE:01',
        'site' => 'BIB', 'status' => 'active',
    ]],
    ['type' => 'switch', 'data' => [
        'device_name' => 'SW-TEST-002', 'inventory_number' => 'SW-INV-002',
        'asset_ho_number' => 'HO-SW-002', 'serial_number' => 'SN-SW-002',
        'mac_address' => 'AA:BB:CC:DD:EE:02',
        'site' => 'BIB', 'status' => 'active',
    ]],
    ['type' => 'wireless', 'data' => [
        'device_name' => 'WL-TEST-002', 'inventory_number' => 'WL-INV-002',
        'asset_ho_number' => 'HO-WL-002', 'serial_number' => 'SN-WL-002',
        'mac_address' => 'AA:BB:CC:DD:EE:03',
        'site' => 'BIB', 'status' => 'active',
    ]],
    ['type' => 'mobile-tower', 'data' => [
        'mt_code' => 'MT-TST-002', 'inventory_number' => 'MT-INV-002',
        'type_mt' => 'Monopole', 'location' => 'Test Tower 2',
        'site' => 'BIB', 'status' => 'active',
    ]],
];

foreach ($tests as $t) {
    [$code, $body] = apiCall('POST', "$baseUrl/api/inventory/{$t['type']}", $token, $t['data']);
    $id = $body['data']['id'] ?? $body['id'] ?? null;
    $err = $code !== 201 ? substr(json_encode($body), 0, 200) : "id=$id";
    echo "[" . ($code === 201 ? 'PASS' : 'FAIL') . "] POST /inventory/{$t['type']} → $code | $err\n";
}
