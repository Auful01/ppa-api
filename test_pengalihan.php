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

echo "=== Pengalihan Asset Tests ===\n\n";

// Get a real laptop ID from DB for testing
$pdo = new PDO('mysql:host=127.0.0.1;port=3306;dbname=new_itportalv2', 'root', '');
$laptop = $pdo->query("SELECT id, laptop_code, user_alls_id, site FROM inv_laptops WHERE site='BIB' AND deleted_at IS NULL LIMIT 1")->fetch(PDO::FETCH_ASSOC);
$user = $pdo->query("SELECT nrp, username FROM user_alls WHERE site='BIB' LIMIT 1")->fetch(PDO::FETCH_ASSOC);
echo "Test laptop: " . ($laptop['laptop_code'] ?? 'N/A') . " id=" . ($laptop['id'] ?? 'N/A') . "\n";
echo "Test user NRP: " . ($user['nrp'] ?? 'N/A') . "\n\n";

// TC-PA-01: Index (returns crew list)
[$c, $b] = api('GET', "$baseUrl/api/pengalihan-assets?site=BIB", $token);
echo "[" . ($c===200?'PASS':'FAIL') . "] GET /pengalihan-assets → $c | crew_count=" . count($b['crew'] ?? []) . "\n";

// TC-PA-02: Meta
[$c, $b] = api('GET', "$baseUrl/api/pengalihan-assets/meta?site=BIB", $token);
echo "[" . ($c===200?'PASS':'FAIL') . "] GET /pengalihan-assets/meta → $c | dept_count=" . count($b['departments'] ?? []) . "\n";

// TC-PA-03: Data (list existing pengalihans)
[$c, $b] = api('GET', "$baseUrl/api/pengalihan-assets/data?device_type=Laptop&site=BIB", $token);
echo "[" . ($c===200?'PASS':'FAIL') . "] GET /pengalihan-assets/data?device_type=Laptop → $c | count=" . count($b['data'] ?? []) . "\n";

// TC-PA-04: Inventories
[$c, $b] = api('GET', "$baseUrl/api/pengalihan-assets/inventories?device_type=Laptop&department=IT&site=BIB", $token);
echo "[" . ($c===200?'PASS':'FAIL') . "] GET /pengalihan-assets/inventories → $c | count=" . count($b['inventoryData'] ?? []) . "\n";

// TC-PA-05: Generate code
[$c, $b] = api('GET', "$baseUrl/api/pengalihan-assets/generate-code?device_type=Laptop&dept=IT&site=BIB", $token);
echo "[" . ($c===200?'PASS':'FAIL') . "] GET /pengalihan-assets/generate-code → $c | code=" . ($b['inventoryData'] ?? 'N/A') . "\n";

// TC-PA-06: Store validation test (should 422 without inventory_id)
[$c, $b, $raw] = api('POST', "$baseUrl/api/pengalihan-assets", $token, [
    'deviceType' => 'Laptop',
    // intentionally missing idInvPrev, invNumberNext, prevNrp, userNext, deptNext, site
]);
echo "[" . ($c===422?'PASS':'FAIL') . "] POST /pengalihan-assets (empty → 422) → $c\n";

// TC-PA-07: Store with real laptop if available
if ($laptop && $user) {
    [$c, $b, $raw] = api('POST', "$baseUrl/api/pengalihan-assets", $token, [
        'deviceType' => 'Laptop',
        'idInvPrev' => $laptop['id'],
        'invNumberNext' => 'BIB-NB-IT-UAT-001',
        'prevNrp' => $user['nrp'],
        'userNext' => $user['username'] ?? 'UAT User',
        'deptNext' => 'IT',
        'site' => 'BIB',
        'remark' => 'UAT Test Transfer',
    ]);
    echo "[" . (in_array($c,[200,201])?'PASS':'FAIL') . "] POST /pengalihan-assets (real data) → $c\n";
    if (!in_array($c,[200,201])) echo "  error: " . substr($raw, 0, 300) . "\n";
}

// TC-PA-08: User by NRP
[$c, $b] = api('GET', "$baseUrl/api/pengalihan-assets/user-by-nrp?nrp=" . ($user['nrp'] ?? '23002073') . "&site=BIB", $token);
echo "[" . ($c===200?'PASS':'FAIL') . "] GET /pengalihan-assets/user-by-nrp → $c | name=" . ($b['userData']['username'] ?? 'N/A') . "\n";
