<?php
$baseUrl = 'http://localhost:8120';

// Login
$ch = curl_init("$baseUrl/api/auth/login");
curl_setopt_array($ch, [
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => json_encode(['nrp' => '23002073', 'password' => '23002073']),
    CURLOPT_HTTPHEADER => ['Content-Type: application/json', 'Accept: application/json'],
    CURLOPT_RETURNTRANSFER => true,
]);
$resp = json_decode(curl_exec($ch), true);
curl_close($ch);
$token = $resp['token'] ?? null;
echo "Login: " . ($token ? "OK ({$resp['user']['role']}@{$resp['user']['site']})" : "FAIL") . "\n\n";

if (!$token) exit(1);

function apiCall($method, $url, $token, $data = null) {
    $ch = curl_init($url);
    $opts = [
        CURLOPT_CUSTOMREQUEST => $method,
        CURLOPT_HTTPHEADER => [
            "Content-Type: application/json",
            "Accept: application/json",
            "Authorization: Bearer $token",
        ],
        CURLOPT_RETURNTRANSFER => true,
    ];
    if ($data) {
        $opts[CURLOPT_POSTFIELDS] = json_encode($data);
    }
    curl_setopt_array($ch, $opts);
    $body = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    return [$code, json_decode($body, true)];
}

$tests = [
    ['type' => 'computer', 'data' => [
        'computer_name' => 'PC-TEST-001', 'computer_code' => 'PC-TST-001',
        'serial_number' => 'SN-TST-001', 'site' => 'BIB',
        'status' => 'active', 'ip_address' => '192.168.1.1',
    ]],
    ['type' => 'laptop', 'data' => [
        'laptop_name' => 'LP-TEST-001', 'laptop_code' => 'LP-TST-001',
        'serial_number' => 'SN-LP-001', 'site' => 'BIB', 'status' => 'active',
    ]],
    ['type' => 'printer', 'data' => [
        'item_name' => 'PR-TEST-001', 'printer_code' => 'PR-TST-001',
        'serial_number' => 'SN-PR-001', 'site' => 'BIB', 'status' => 'active',
    ]],
    ['type' => 'cctv', 'data' => [
        'cctv_name' => 'CCTV-TEST-001', 'cctv_code' => 'CC-TST-001',
        'location' => 'Main Gate', 'site' => 'BIB', 'status' => 'active',
    ]],
    ['type' => 'access-point', 'data' => [
        'device_name' => 'AP-TEST-001', 'inventory_number' => 'AP-INV-001',
        'serial_number' => 'SN-AP-001', 'site' => 'BIB', 'status' => 'active',
    ]],
    ['type' => 'switch', 'data' => [
        'device_name' => 'SW-TEST-001', 'inventory_number' => 'SW-INV-001',
        'serial_number' => 'SN-SW-001', 'site' => 'BIB', 'status' => 'active',
    ]],
    ['type' => 'wireless', 'data' => [
        'device_name' => 'WL-TEST-001', 'inventory_number' => 'WL-INV-001',
        'serial_number' => 'SN-WL-001', 'site' => 'BIB', 'status' => 'active',
    ]],
    ['type' => 'mobile-tower', 'data' => [
        'mt_code' => 'MT-TST-001', 'inventory_number' => 'MT-INV-001',
        'location' => 'Test Tower', 'site' => 'BIB', 'status' => 'active',
    ]],
    ['type' => 'scanner', 'data' => [
        'device_name' => 'SC-TEST-001', 'inventory_number' => 'SC-INV-001',
        'serial_number' => 'SN-SC-001', 'site' => 'BIB', 'status' => 'active',
    ]],
];

foreach ($tests as $t) {
    $type = $t['type'];
    [$code, $body] = apiCall('POST', "$baseUrl/api/inventory/$type", $token, $t['data']);
    $status = $code === 201 ? 'PASS' : 'FAIL';
    $id = $body['data']['id'] ?? $body['id'] ?? null;
    $err = ($code !== 201) ? (json_encode($body)) : "id=$id";
    echo "[$status] POST /inventory/$type → $code | $err\n";
}
