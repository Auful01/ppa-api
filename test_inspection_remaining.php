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

echo "=== Inspection Schedule Tests ===\n\n";

// TC-IS-01: List inspection schedules
[$c, $b] = api('GET', "$baseUrl/api/inspection-schedules?site=BIB", $token);
$count = count($b['data'] ?? []);
echo "[" . ($c===200?'PASS':'FAIL') . "] GET /inspection-schedules → $c | count=$count\n";
$firstId = $b['data'][0]['id'] ?? null;

// TC-IS-02: Update a schedule if exists
if ($firstId) {
    [$c, $b, $raw] = api('PATCH', "$baseUrl/api/inspection-schedules/$firstId", $token, [
        'status' => 'done',
        'remark' => 'UAT test inspection done',
        'inspector' => 'UAT Inspector',
    ]);
    echo "[" . ($c===200?'PASS':'FAIL') . "] PATCH /inspection-schedules/$firstId → $c\n";
    if ($c !== 200) echo "  error: " . substr($raw, 0, 200) . "\n";
}

echo "\n=== Inspections Tests ===\n\n";

// TC-INS-01: List inspections by type (computer)
[$c, $b] = api('GET', "$baseUrl/api/inspections/computer?site=BIB", $token);
$count = count($b['data'] ?? []);
$firstId = $b['data'][0]['id'] ?? null;
echo "[" . ($c===200?'PASS':'FAIL') . "] GET /inspections/computer → $c | count=$count\n";

// TC-INS-02: Show inspection
if ($firstId) {
    [$c] = api('GET', "$baseUrl/api/inspections/computer/$firstId", $token);
    echo "[" . ($c===200?'PASS':'FAIL') . "] GET /inspections/computer/$firstId → $c\n";

    // TC-INS-03: Update inspection
    [$c, $b, $raw] = api('PUT', "$baseUrl/api/inspections/computer/$firstId", $token, [
        'condition' => 'Good',
        'remark' => 'UAT inspection test update',
    ]);
    echo "[" . ($c===200?'PASS':'FAIL') . "] PUT /inspections/computer/$firstId → $c\n";
    if ($c !== 200) echo "  error: " . substr($raw, 0, 200) . "\n";
}

// TC-INS-04: Laptop inspections
[$c, $b] = api('GET', "$baseUrl/api/inspections/laptop?site=BIB", $token);
echo "[" . ($c===200?'PASS':'FAIL') . "] GET /inspections/laptop → $c | count=" . count($b['data'] ?? []) . "\n";

// TC-INS-05: Printer inspections
[$c, $b] = api('GET', "$baseUrl/api/inspections/printer?site=BIB", $token);
echo "[" . ($c===200?'PASS':'FAIL') . "] GET /inspections/printer → $c | count=" . count($b['data'] ?? []) . "\n";

// TC-INS-06: Mobile Tower inspections
[$c, $b] = api('GET', "$baseUrl/api/inspections/mobile-tower?site=BIB", $token);
echo "[" . ($c===200?'PASS':'FAIL') . "] GET /inspections/mobile-tower → $c | count=" . count($b['data'] ?? []) . "\n";

echo "\n=== Chart Inspeksi Tests ===\n\n";

// TC-CI-01: Chart inspeksi
[$c, $b, $raw] = api('GET', "$baseUrl/api/chart-inspeksi?site=BIB", $token);
echo "[" . ($c===200?'PASS':'FAIL') . "] GET /chart-inspeksi → $c\n";
if ($c !== 200) echo "  error: " . substr($raw, 0, 200) . "\n";

echo "\n=== Aduan urgency (corrected) ===\n\n";

// Create a fresh aduan and test urgency update with correct values
[$c, $b] = api('POST', "$baseUrl/api/aduan", $token, [
    'complaint_name' => 'UAT Urgency Test',
    'complaint_note' => 'Testing urgency update',
    'date_of_complaint' => date('Y-m-d'),
    'nrp' => '23002073',
    'site' => 'BIB',
]);
$id = $b['data']['id'] ?? null;
if ($id) {
    // Test with URGENT (correct value)
    [$c2] = api('PATCH', "$baseUrl/api/aduan/$id/urgency", $token, ['urgency' => 'URGENT']);
    echo "[" . ($c2===200?'PASS':'FAIL') . "] PATCH /aduan/$id/urgency (URGENT) → $c2\n";

    // Test with NORMAL
    [$c3] = api('PATCH', "$baseUrl/api/aduan/$id/urgency", $token, ['urgency' => 'NORMAL']);
    echo "[" . ($c3===200?'PASS':'FAIL') . "] PATCH /aduan/$id/urgency (NORMAL) → $c3\n";

    // Test updateProgress
    [$c4] = api('PATCH', "$baseUrl/api/aduan/$id/progress", $token, [
        'status' => 'CLOSED',
        'action_repair' => 'Hardware replaced',
        'repair_note' => 'Fixed successfully',
    ]);
    echo "[" . ($c4===200?'PASS':'FAIL') . "] PATCH /aduan/$id/progress (CLOSED) → $c4\n";

    // Cleanup
    api('DELETE', "$baseUrl/api/aduan/$id", $token);
}
