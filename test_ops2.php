<?php
$baseUrl = 'http://localhost:8120';

$ch = curl_init("$baseUrl/api/auth/login");
curl_setopt_array($ch, [CURLOPT_POST=>true, CURLOPT_POSTFIELDS=>json_encode(['nrp'=>'23002073','password'=>'23002073']),
    CURLOPT_HTTPHEADER=>['Content-Type: application/json','Accept: application/json'], CURLOPT_RETURNTRANSFER=>true]);
$resp = json_decode(curl_exec($ch), true);
curl_close($ch);
$token = $resp['token'];
echo "Login: ict_developer@BIB\n\n";

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

function result($code, $body, $raw, $label, $expected = 200) {
    $ok = $code === $expected;
    $detail = $ok ? '' : ' | ' . substr($raw, 0, 200);
    echo "[" . ($ok ? 'PASS' : 'FAIL') . "] $label → $code$detail\n";
    return $ok;
}

// TC-OPS-01: Create job (with date)
[$c, $b] = api('POST', "$baseUrl/api/operations/jobs", $token, [
    'shared' => ['shift' => 'SHIFT_1', 'crew' => [], 'sarana' => 'Server'],
    'jobs' => [['job' => 'Monitor server health', 'date' => date('Y-m-d'), 'status' => 'open', 'categoryJob' => 'assignment']],
    'site' => 'BIB',
]);
result($c, $b, json_encode($b), 'POST /operations/jobs (with date)', 201);

// TC-OPS-02: Create job (without date — tests BUG-004 fix)
[$c2, $b2, $raw2] = api('POST', "$baseUrl/api/operations/jobs", $token, [
    'shared' => ['shift' => 'SHIFT_1', 'crew' => []],
    'jobs' => [['job' => 'No-date job test', 'status' => 'open', 'categoryJob' => 'assignment']],
    'site' => 'BIB',
]);
result($c2, $b2, $raw2, 'POST /operations/jobs (no date - BUG004 fix)', 201);

// TC-OPS-03: List jobs
[$c3, $b3] = api('GET', "$baseUrl/api/operations/jobs?site=BIB", $token);
$total = $b3['data']['total'] ?? 0;
$firstCode = $b3['data']['data'][0]['code'] ?? null;
echo "[" . ($c3 === 200 ? 'PASS' : 'FAIL') . "] GET /operations/jobs → $c3 | total=$total, firstCode=$firstCode\n";

// TC-OPS-04: Show job
if ($firstCode) {
    [$c4, $b4] = api('GET', "$baseUrl/api/operations/jobs/$firstCode?site=BIB", $token);
    result($c4, $b4, json_encode($b4), "GET /operations/jobs/$firstCode");
}

// TC-OPS-05: Create unschedule job (without date — also tests BUG-004 fix)
[$c5, $b5, $raw5] = api('POST', "$baseUrl/api/operations/unschedule-jobs", $token, [
    'shared' => ['shift' => 'SHIFT_1', 'crew' => []],
    'jobs' => [[
        'job' => 'Unschedule: Network switch replaced',
        'category' => 'Network',
        'status' => 'open',
    ]],
    'site' => 'BIB',
]);
result($c5, $b5, $raw5, 'POST /operations/unschedule-jobs (no date)', 201);

// TC-OPS-06: List unschedule jobs
[$c6, $b6] = api('GET', "$baseUrl/api/operations/unschedule-jobs?site=BIB", $token);
$total6 = $b6['data']['total'] ?? 0;
$firstCode6 = $b6['data']['data'][0]['code'] ?? null;
echo "[" . ($c6 === 200 ? 'PASS' : 'FAIL') . "] GET /operations/unschedule-jobs → $c6 | total=$total6, firstCode=$firstCode6\n";

// TC-OPS-07: Jobs meta
[$c7] = api('GET', "$baseUrl/api/operations/jobs/meta?site=BIB", $token);
echo "[" . ($c7 === 200 ? 'PASS' : 'FAIL') . "] GET /operations/jobs/meta → $c7\n";

// TC-OPS-08: Unschedule problems
[$c8, $b8] = api('GET', "$baseUrl/api/operations/unschedule-jobs/problems?category=Network&site=BIB", $token);
echo "[" . ($c8 === 200 ? 'PASS' : 'FAIL') . "] GET /operations/unschedule-jobs/problems → $c8 | count=" . count($b8['data'] ?? []) . "\n";

// TC-OPS-09: Monitoring jobs index
[$c9] = api('GET', "$baseUrl/api/operations/monitoring-jobs?site=BIB", $token);
echo "[" . ($c9 === 200 ? 'PASS' : 'FAIL') . "] GET /operations/monitoring-jobs → $c9\n";
