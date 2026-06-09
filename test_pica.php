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

echo "=== PICA Inspeksi Tests ===\n\n";

// TC-PICA-01: Meta
[$c, $b] = api('GET', "$baseUrl/api/pica-inspeksi/meta?site=BIB", $token);
echo "[" . ($c===200?'PASS':'FAIL') . "] GET /pica-inspeksi/meta → $c | crew_count=" . count($b['crew'] ?? []) . "\n";

// TC-PICA-02 to 05: Index by device type
foreach (['Laptop', 'Computer', 'Printer', 'Mobile Tower'] as $dtype) {
    $enc = urlencode($dtype);
    [$c, $b] = api('GET', "$baseUrl/api/pica-inspeksi?device_type=$enc&site=BIB", $token);
    $count = count($b['data'] ?? []);
    echo "[" . ($c===200?'PASS':'FAIL') . "] GET /pica-inspeksi?device_type=$dtype → $c | count=$count\n";

    // If we find records, test show
    if ($c === 200 && $count > 0) {
        $id = $b['data'][0]['id'] ?? null;
        if ($id) {
            [$cs, $bs] = api('GET', "$baseUrl/api/pica-inspeksi/$id", $token);
            $devType = $bs['data']['device_type'] ?? 'N/A';
            echo "  [" . ($cs===200?'PASS':'FAIL') . "] GET /pica-inspeksi/$id → $cs | device_type=$devType\n";

            // TC-PICA-UPDATE: Update PICA record
            [$cu, $bu] = api('PATCH', "$baseUrl/api/pica-inspeksi/$id", $token, [
                'device_type' => $dtype,
                'temuan' => 'Test finding from UAT',
                'tindakan' => 'Cleaning and adjustment',
                'due_date' => date('Y-m-d', strtotime('+7 days')),
                'findings_status' => 'open',
                'remark' => 'UAT test remark',
                'inspector' => 'Test Inspector',
            ]);
            echo "  [" . ($cu===200?'PASS':'FAIL') . "] PATCH /pica-inspeksi/$id → $cu\n";
        }
    }
}

// TC-PICA-WRONG-TYPE: Should return 422 for invalid device_type (lowercase)
[$c, $b] = api('GET', "$baseUrl/api/pica-inspeksi?device_type=laptop&site=BIB", $token);
echo "[" . ($c===422?'PASS':'FAIL') . "] GET /pica-inspeksi?device_type=laptop (lowercase → should be 422) → $c\n";
