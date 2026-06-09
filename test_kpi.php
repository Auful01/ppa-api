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

echo "=== KPI API Tests ===\n\n";

// TC-KPI-01: KPI Aduan Analysis Chart (missing required year → should 422)
[$c, $b] = api('GET', "$baseUrl/api/kpi-aduan-analysis/chart", $token);
echo "[" . ($c===422?'PASS':'FAIL') . "] GET /kpi-aduan-analysis/chart (no params → 422) → $c\n";

// TC-KPI-02: KPI Aduan Analysis Chart with year
[$c, $b] = api('GET', "$baseUrl/api/kpi-aduan-analysis/chart?year=2026", $token);
echo "[" . ($c===200?'PASS':'FAIL') . "] GET /kpi-aduan-analysis/chart?year=2026 → $c\n";
if ($c === 200) {
    echo "  data keys: " . implode(', ', array_keys($b)) . "\n";
}

// TC-KPI-03: KPI Aduan Analysis Details (all required params)
[$c, $b] = api('GET', "$baseUrl/api/kpi-aduan-analysis/details?year=2026&month=1&site=BIB&category=Hardware", $token);
echo "[" . ($c===200?'PASS':'FAIL') . "] GET /kpi-aduan-analysis/details?year=2026&month=1&site=BIB&category=Hardware → $c | count=" . count($b['data'] ?? []) . "\n";

// TC-KPI-04: KPI VHMS index
[$c, $b] = api('GET', "$baseUrl/api/kpi-vhms", $token);
echo "[" . ($c===200?'PASS':'FAIL') . "] GET /api/kpi-vhms → $c\n";
if ($c === 200) echo "  keys: " . implode(', ', array_keys($b)) . "\n";
else echo "  error: " . substr(json_encode($b), 0, 200) . "\n";

// TC-KPI-05: KPI VHMS summary
[$c, $b] = api('GET', "$baseUrl/api/kpi-vhms/summary", $token);
echo "[" . ($c===200?'PASS':'FAIL') . "] GET /api/kpi-vhms/summary → $c\n";

// TC-KPI-06: KPI VHMS filter
[$c, $b] = api('GET', "$baseUrl/api/kpi-vhms/filter", $token);
echo "[" . ($c===200?'PASS':'FAIL') . "] GET /api/kpi-vhms/filter → $c\n";

// TC-KPI-07: KPI VHMS breakdown
[$c, $b, $raw] = api('GET', "$baseUrl/api/kpi-vhms/breakdown", $token);
echo "[" . ($c===200?'PASS':'FAIL') . "] GET /api/kpi-vhms/breakdown → $c\n";
if ($c !== 200) echo "  error: " . substr($raw, 0, 200) . "\n";

// TC-KPI-08: KPI VHMS store
[$c, $b, $raw] = api('POST', "$baseUrl/api/kpi-vhms", $token, [
    'month' => date('n'),
    'year' => date('Y'),
    'week_data' => [
        ['week' => 1, 'target' => 95, 'actual' => 92],
    ],
    'site' => 'BIB',
]);
echo "[" . (in_array($c, [200,201])?'PASS':'FAIL') . "] POST /api/kpi-vhms → $c\n";
if (!in_array($c, [200,201])) echo "  error: " . substr($raw, 0, 300) . "\n";
