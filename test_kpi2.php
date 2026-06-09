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

$weekData = 'Minggu 1';
$month = (int) date('n');
$year = (int) date('Y');

// TC-KPI-05: summary with required params
[$c, $b, $raw] = api('GET', "$baseUrl/api/kpi-vhms/summary?week_data=" . urlencode($weekData) . "&month=$month&year=$year", $token);
echo "[" . ($c===200?'PASS':'FAIL') . "] GET /kpi-vhms/summary?week_data=Minggu1&month=$month&year=$year → $c\n";
if ($c===200) echo "  total_unit={$b['data']['total_unit']}\n";
else echo "  error: " . substr($raw, 0, 200) . "\n";

// TC-KPI-07: breakdown with required params
[$c, $b, $raw] = api('GET', "$baseUrl/api/kpi-vhms/breakdown?week_data=" . urlencode($weekData) . "&month=$month&year=$year", $token);
echo "[" . ($c===200?'PASS':'FAIL') . "] GET /kpi-vhms/breakdown?week_data=Minggu1&month=$month&year=$year → $c | count=" . count($b['data'] ?? []) . "\n";
if ($c!==200) echo "  error: " . substr($raw, 0, 200) . "\n";

// TC-KPI-08: store with correct format (week_data as string)
[$c, $b, $raw] = api('POST', "$baseUrl/api/kpi-vhms", $token, [
    'week_data' => $weekData,
    'unit_code' => ['UNIT-TEST-001'],
    'status' => ['normal'],
    'pic' => ['Test User'],
    'remark' => ['UAT test entry'],
]);
echo "[" . (in_array($c,[200,201])?'PASS':'FAIL') . "] POST /kpi-vhms (week_data string) → $c\n";
if (!in_array($c,[200,201])) echo "  error: " . substr($raw, 0, 300) . "\n";
else echo "  " . $b['message'] . "\n";

// TC-KPI-09: VHMS index
[$c, $b] = api('GET', "$baseUrl/api/kpi-vhms?site=BIB", $token);
echo "[" . ($c===200?'PASS':'FAIL') . "] GET /kpi-vhms?site=BIB → $c\n";
