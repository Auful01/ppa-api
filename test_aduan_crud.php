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
    return [$code, json_decode($body, true)];
}

echo "=== Aduan CRUD Tests ===\n\n";

// TC-A01: List
[$c, $b] = api('GET', "$baseUrl/api/aduan?site=BIB", $token);
$total = $b['data']['total'] ?? 0;
echo "[" . ($c===200?'PASS':'FAIL') . "] GET /aduan → $c | total=$total\n";

// TC-A02: Meta
[$c] = api('GET', "$baseUrl/api/aduan/meta?site=BIB", $token);
echo "[" . ($c===200?'PASS':'FAIL') . "] GET /aduan/meta → $c\n";

// TC-A03: Create
[$c, $b] = api('POST', "$baseUrl/api/aduan", $token, [
    'complaint_name' => 'UAT Test User',
    'complaint_note' => 'UAT test complaint note',
    'category_name' => 'Hardware',
    'location' => 'Server Room',
    'location_detail' => 'Rack A',
    'date_of_complaint' => date('Y-m-d'),
    'site' => 'BIB',
    'nrp' => '23002073',
    'phone_number' => '08123456789',
]);
$id = $b['data']['id'] ?? null;
echo "[" . ($c===201?'PASS':'FAIL') . "] POST /aduan → $c | id=$id\n";
if ($c !== 201) echo "  error: " . substr(json_encode($b), 0, 300) . "\n";

if ($id) {
    // TC-A04: Show
    [$c, $b] = api('GET', "$baseUrl/api/aduan/$id", $token);
    $code = $b['data']['complaint_code'] ?? 'N/A';
    echo "[" . ($c===200?'PASS':'FAIL') . "] GET /aduan/$id → $c | code=$code\n";

    // TC-A05: Update
    [$c] = api('PATCH', "$baseUrl/api/aduan/$id", $token, [
        'status' => 'on_progress',
        'repair_note' => 'In process of fixing',
    ]);
    echo "[" . ($c===200?'PASS':'FAIL') . "] PATCH /aduan/$id → $c\n";

    // TC-A06: Accept
    [$c] = api('PATCH', "$baseUrl/api/aduan/$id/accept", $token);
    echo "[" . (in_array($c,[200,201])?'PASS':'FAIL') . "] PATCH /aduan/$id/accept → $c\n";

    // TC-A07: Update urgency
    [$c] = api('PATCH', "$baseUrl/api/aduan/$id/urgency", $token, ['urgency' => 'high']);
    echo "[" . (in_array($c,[200,201])?'PASS':'FAIL') . "] PATCH /aduan/$id/urgency → $c\n";

    // TC-A08: Delete (soft delete)
    [$c] = api('DELETE', "$baseUrl/api/aduan/$id", $token);
    echo "[" . ($c===200?'PASS':'FAIL') . "] DELETE /aduan/$id → $c\n";

    // TC-A09: Verify soft-deleted (should return 404)
    [$c] = api('GET', "$baseUrl/api/aduan/$id", $token);
    echo "[" . ($c===404?'PASS':'FAIL') . "] GET /aduan/$id (after delete → 404) → $c\n";
}

// TC-A10: DB verify latest aduan
$pdo = new PDO('mysql:host=127.0.0.1;port=3306;dbname=new_itportalv2', 'root', '');
$stmt = $pdo->query("SELECT id, complaint_code, status, deleted_at FROM aduans ORDER BY created_at DESC LIMIT 3");
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo "\nDB verify (last 3 aduans):\n";
foreach ($rows as $r) {
    echo "  id={$r['id']} | code={$r['complaint_code']} | status={$r['status']} | deleted_at=" . ($r['deleted_at'] ?? 'null') . "\n";
}
