<?php
$baseUrl = 'http://localhost:8120';

$ch = curl_init("$baseUrl/api/auth/login");
curl_setopt_array($ch, [CURLOPT_POST=>true, CURLOPT_POSTFIELDS=>json_encode(['nrp'=>'23002073','password'=>'23002073']),
    CURLOPT_HTTPHEADER=>['Content-Type: application/json','Accept: application/json'], CURLOPT_RETURNTRANSFER=>true]);
$resp = json_decode(curl_exec($ch), true);
curl_close($ch);
$token = $resp['token'];

function api($method, $url, $token, $data = null) {
    $ch = curl_init($url);
    curl_setopt_array($ch, [CURLOPT_CUSTOMREQUEST=>$method,
        CURLOPT_HTTPHEADER=>["Content-Type: application/json","Accept: application/json","Authorization: Bearer $token"],
        CURLOPT_RETURNTRANSFER=>true, CURLOPT_POSTFIELDS=>$data ? json_encode($data) : null]);
    $body = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    return [$code, json_decode($body, true), $body];
}

echo "=== Scanner (Barcode) CRUD ===\n\n";

// TC-SC-01: List scanners
[$c, $b] = api('GET', "$baseUrl/api/inventory/scanner?site=BIB", $token);
$total = $b['data']['total'] ?? count($b['data'] ?? []);
echo "[" . ($c===200?'PASS':'FAIL') . "] GET /inventory/scanner → $c | total=$total\n";

// TC-SC-02: Create scanner
[$c, $b, $raw] = api('POST', "$baseUrl/api/inventory/scanner", $token, [
    'device_name' => 'Barcode-SCANNER-001',
    'inventory_number' => 'SC-INV-101',
    'serial_number' => 'SN-SC-101',
    'site' => 'BIB',
    'status' => 'active',
]);
$id = $b['data']['id'] ?? null;
echo "[" . ($c===201?'PASS':'FAIL') . "] POST /inventory/scanner → $c | id=$id\n";
if ($c !== 201) echo "  error: " . substr($raw, 0, 200) . "\n";

if ($id) {
    // TC-SC-03: Show
    [$c] = api('GET', "$baseUrl/api/inventory/scanner/$id", $token);
    echo "[" . ($c===200?'PASS':'FAIL') . "] GET /inventory/scanner/$id → $c\n";

    // TC-SC-04: Update
    [$c] = api('PATCH', "$baseUrl/api/inventory/scanner/$id", $token, [
        'device_name' => 'Barcode-SCANNER-001-UPDATED',
        'status' => 'inactive',
    ]);
    echo "[" . ($c===200?'PASS':'FAIL') . "] PATCH /inventory/scanner/$id → $c\n";

    // TC-SC-05: Delete
    [$c] = api('DELETE', "$baseUrl/api/inventory/scanner/$id", $token);
    echo "[" . ($c===200?'PASS':'FAIL') . "] DELETE /inventory/scanner/$id → $c\n";
}

echo "\n=== Pengalihan Asset (Mutasi) ===\n\n";

// Check mutasi routes
[$c, $b] = api('GET', "$baseUrl/api/pengalihan-asset?site=BIB", $token);
echo "[" . ($c===200?'PASS':'FAIL') . "] GET /pengalihan-asset → $c\n";
if ($c !== 200) echo "  error: " . substr(json_encode($b), 0, 200) . "\n";
else echo "  total=" . ($b['data']['total'] ?? count($b['data'] ?? [])) . "\n";

// Try create mutasi
[$c, $b, $raw] = api('POST', "$baseUrl/api/pengalihan-asset", $token, [
    'inventory_type' => 'laptop',
    'inventory_id' => null,
    'from_site' => 'BIB',
    'to_site' => 'HO',
    'reason' => 'UAT test transfer',
    'date' => date('Y-m-d'),
]);
echo "[" . (in_array($c,[200,201])?'PASS':'FAIL') . "] POST /pengalihan-asset → $c\n";
if (!in_array($c,[200,201])) echo "  error: " . substr($raw, 0, 300) . "\n";

echo "\n=== Dashboard ===\n\n";

// TC-DASH-01: Dashboard
[$c, $b] = api('GET', "$baseUrl/api/dashboard?site=BIB", $token);
echo "[" . ($c===200?'PASS':'FAIL') . "] GET /dashboard → $c\n";

// TC-DASH-02: All-site dashboard
[$c] = api('GET', "$baseUrl/api/dashboard/all-site", $token);
echo "[" . ($c===200?'PASS':'FAIL') . "] GET /dashboard/all-site → $c\n";

echo "\n=== Departments CRUD ===\n\n";

// TC-DEPT-01: List
[$c, $b] = api('GET', "$baseUrl/api/departments", $token);
$total = $b['total'] ?? count($b['data'] ?? $b ?? []);
echo "[" . ($c===200?'PASS':'FAIL') . "] GET /departments → $c | total=$total\n";

// TC-DEPT-02: Create
[$c, $b, $raw] = api('POST', "$baseUrl/api/departments", $token, [
    'department_name' => 'UAT Test Department',
    'site' => 'BIB',
]);
$deptId = $b['data']['id'] ?? $b['id'] ?? null;
echo "[" . ($c===201?'PASS':'FAIL') . "] POST /departments → $c | id=$deptId\n";
if ($c !== 201) echo "  error: " . substr($raw, 0, 200) . "\n";

if ($deptId) {
    [$c] = api('PUT', "$baseUrl/api/departments/$deptId", $token, ['department_name' => 'UAT Test Dept Updated', 'site' => 'BIB']);
    echo "[" . ($c===200?'PASS':'FAIL') . "] PUT /departments/$deptId → $c\n";
    [$c] = api('DELETE', "$baseUrl/api/departments/$deptId", $token);
    echo "[" . ($c===200?'PASS':'FAIL') . "] DELETE /departments/$deptId → $c\n";
}
