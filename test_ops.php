<?php
$pdo = new PDO('mysql:host=127.0.0.1;port=3306;dbname=new_itportalv2', 'root', '');

// Check daily_jobs schema
$cols = $pdo->query("DESCRIBE daily_jobs")->fetchAll(PDO::FETCH_ASSOC);
echo "=== daily_jobs schema ===\n";
foreach ($cols as $c) {
    $null = $c['Null'] === 'YES' ? 'nullable' : 'NOT NULL';
    $default = $c['Default'] !== null ? "default='{$c['Default']}'" : 'no-default';
    echo "{$c['Field']} | {$c['Type']} | $null | $default\n";
}

echo "\n=== Test jobsStore ===\n";
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

// TC-OPS-01: Create job with all required fields
$data = [
    'shared' => ['shift' => 'SHIFT_1', 'crew' => [], 'sarana' => 'PC'],
    'jobs' => [[
        'job' => 'Check server performance daily',
        'date' => date('Y-m-d'),
        'status' => 'open',
        'categoryJob' => 'assignment',
    ]],
    'site' => 'BIB',
];
[$code, $body] = api('POST', "$baseUrl/api/operations/jobs", $token, $data);
echo "[" . ($code === 201 ? 'PASS' : 'FAIL') . "] POST /operations/jobs → $code | " . ($code === 201 ? $body['message'] : substr(json_encode($body), 0, 300)) . "\n";

// TC-OPS-02: Create unschedule job
$data2 = [
    'shared' => ['shift' => 'SHIFT_1', 'crew' => []],
    'jobs' => [[
        'job' => 'Unscheduled network issue fix',
        'date' => date('Y-m-d'),
        'status' => 'open',
        'category' => 'Network',
    ]],
    'site' => 'BIB',
];
[$code2, $body2] = api('POST', "$baseUrl/api/operations/unschedule", $token, $data2);
echo "[" . ($code2 === 201 ? 'PASS' : 'FAIL') . "] POST /operations/unschedule → $code2 | " . ($code2 === 201 ? $body2['message'] : substr(json_encode($body2), 0, 300)) . "\n";

// TC-OPS-03: List jobs
[$code3, $body3] = api('GET', "$baseUrl/api/operations/jobs?site=BIB", $token);
$count = $body3['data']['total'] ?? 0;
echo "[" . ($code3 === 200 ? 'PASS' : 'FAIL') . "] GET /operations/jobs → $code3 | total=$count\n";
