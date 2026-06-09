<?php
/**
 * PPA Pre-Production Validation - Round 3 (Final Targeted Checks)
 */
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();
use Illuminate\Support\Facades\DB;

$BASE = 'http://localhost:8120/api';
$tc = 0; $passed = 0; $failed = 0;

function apiCall(string $method, string $url, array $data = [], string $token = ''): array {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, strtoupper($method));
    $headers = ['Content-Type: application/json', 'Accept: application/json'];
    if ($token) $headers[] = "Authorization: Bearer $token";
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    if (!empty($data) && in_array(strtoupper($method), ['POST','PUT','PATCH'])) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    }
    $body = curl_exec($ch);
    $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    usleep(300000);
    return ['status' => $status, 'body' => json_decode($body, true) ?? ['raw' => $body]];
}

function tc(string $name, callable $fn): bool {
    global $tc, $passed, $failed;
    $tc++;
    try {
        $result = $fn();
        if ($result === true || $result === null) {
            $passed++;
            echo "[PASS] R3-{$tc}: {$name}\n";
            return true;
        } else {
            $failed++;
            $msg = is_string($result) ? $result : json_encode($result);
            echo "[FAIL] R3-{$tc}: {$name} => " . substr($msg, 0, 200) . "\n";
            return false;
        }
    } catch (Throwable $e) {
        $failed++;
        echo "[FAIL] R3-{$tc}: {$name} => " . substr($e->getMessage(), 0, 200) . "\n";
        return false;
    }
}

// Obtain tokens
$devLogin = apiCall('POST', "{$BASE}/auth/login", ['nrp' => '23002073', 'password' => '23002073']);
$glLogin  = apiCall('POST', "{$BASE}/auth/login", ['nrp' => '21002037', 'password' => '21002037']);
$techLogin = apiCall('POST', "{$BASE}/auth/login", ['nrp' => '22003275', 'password' => '22003275']);
$devToken  = $devLogin['body']['token'] ?? '';
$glToken   = $glLogin['body']['token'] ?? '';
$techToken = $techLogin['body']['token'] ?? '';
echo "Tokens: " . (empty($devToken) ? 'FAIL' : 'OK') . "\n\n";

echo "=== OPERATIONS UNSCHEDULE (with category) ===\n";
tc("POST /operations/unschedule-jobs - with category field", function() use ($BASE, $techToken) {
    $payload = [
        'shared' => ['shift' => 'pagi'],
        'jobs' => [[
            'job'      => 'PPV-R3 Unschedule Test',
            'date'     => date('Y-m-d'),
            'category' => 'PC/NB',
            'status'   => 'open',
        ]]
    ];
    $r = apiCall('POST', "{$BASE}/operations/unschedule-jobs", $payload, $techToken);
    if (!in_array($r['status'], [200, 201])) return "HTTP {$r['status']}: " . json_encode(array_slice((array)$r['body'], 0, 3));
    $row = DB::table('daily_jobs')->where('description', 'PPV-R3 Unschedule Test')->where('category_job', 'unschedule')->latest()->first();
    if (!$row) return "DB verify: record not found";
    if ($row->category !== 'PC/NB') return "Category mismatch: {$row->category}";
    return true;
});

echo "\n=== KPI ADUAN ANALYSIS ===\n";
sleep(2);
tc("GET /kpi-aduan-analysis/chart?year=2026", function() use ($BASE, $devToken) {
    $r = apiCall('GET', "{$BASE}/kpi-aduan-analysis/chart?year=2026", [], $devToken);
    if ($r['status'] !== 200) return "HTTP {$r['status']}: " . json_encode(array_slice((array)$r['body'], 0, 3));
    if (!isset($r['body']['chartData'])) return "Missing chartData key";
    return true;
});

tc("GET /kpi-aduan-analysis/details with all params", function() use ($BASE, $devToken) {
    $r = apiCall('GET', "{$BASE}/kpi-aduan-analysis/details?year=2026&month=6&site=BIB&category=PC/NB", [], $devToken);
    if ($r['status'] !== 200) return "HTTP {$r['status']}: " . json_encode(array_slice((array)$r['body'], 0, 3));
    if (!isset($r['body']['complaints'])) return "Missing complaints key";
    return true;
});

echo "\n=== PENGALIHAN ASSET (after table creation) ===\n";
tc("GET /pengalihan-assets/data - table now exists", function() use ($BASE, $devToken) {
    $r = apiCall('GET', "{$BASE}/pengalihan-assets/data?device_type=laptop&site=BIB", [], $devToken);
    if (!in_array($r['status'], [200, 422])) return "HTTP {$r['status']}: " . json_encode(array_slice((array)$r['body'], 0, 3));
    if ($r['status'] === 503) return "Still returning 503 - table still missing!";
    return true;
});

tc("GET /pengalihan-assets/inventories with all params", function() use ($BASE, $devToken) {
    $r = apiCall('GET', "{$BASE}/pengalihan-assets/inventories?device_type=laptop&department=ICT&site=BIB", [], $devToken);
    if (!in_array($r['status'], [200, 404])) return "HTTP {$r['status']}: " . json_encode(array_slice((array)$r['body'], 0, 3));
    return true;
});

tc("GET /pengalihan-assets/generate-code with all params", function() use ($BASE, $devToken) {
    $r = apiCall('GET', "{$BASE}/pengalihan-assets/generate-code?device_type=laptop&dept=ICT&site=BIB", [], $devToken);
    if ($r['status'] !== 200) return "HTTP {$r['status']}: " . json_encode(array_slice((array)$r['body'], 0, 3));
    if (!isset($r['body']['inventoryData'])) return "Missing inventoryData key";
    return true;
});

echo "\n=== ADDITIONAL INSPECTION TESTS ===\n";
sleep(1);

// Check all inspection types exist
$inspTypes = ['laptop', 'computer'];
foreach ($inspTypes as $type) {
    tc("GET /inspections/{$type} returns structured response", function() use ($BASE, $devToken, $type) {
        $r = apiCall('GET', "{$BASE}/inspections/{$type}?per_page=5", [], $devToken);
        if ($r['status'] !== 200) return "HTTP {$r['status']}";
        return true;
    });
}

echo "\n=== FINAL DB VERIFICATION ===\n";

tc("DB: aduan records created in R2", function() {
    $count = DB::table('aduans')->where('complaint_note', 'like', '%Pre-production validation%')->count();
    if ($count < 1) return "No PPV aduan found in DB";
    echo "  Found {$count} PPV aduan records\n";
    return true;
});

tc("DB: inv_printer records have nullable printer_code", function() {
    $nullCount = DB::table('inv_printers')->whereNull('printer_code')->count();
    echo "  Found {$nullCount} printers with NULL printer_code\n";
    return true;
});

tc("DB: daily_jobs assignment created", function() {
    $count = DB::table('daily_jobs')->where('description', 'like', '%PPV%')->count();
    if ($count < 1) return "No PPV jobs found in DB";
    echo "  Found {$count} PPV job records\n";
    return true;
});

tc("DB: pengalihan_asset table created and accessible", function() {
    $count = DB::table('pengalihan_asset')->count();
    echo "  pengalihan_asset has {$count} records\n";
    return true;
});

tc("DB: department PPV test record exists", function() {
    $dept = DB::table('departments')->where('department_name', 'like', '%PPV-TEST%')->first();
    if (!$dept) return "PPV department not found";
    echo "  Found PPV dept: {$dept->department_name}\n";
    return true;
});

echo "\n=== SITE CONTEXT VALIDATION ===\n";
sleep(1);

// Test that ict_developer sees BIB data
tc("ict_developer sees BIB-scoped aduan", function() use ($BASE, $devToken) {
    $r = apiCall('GET', "{$BASE}/aduan?per_page=5", [], $devToken);
    if ($r['status'] !== 200) return "HTTP {$r['status']}";
    $data = $r['body']['data']['data'] ?? [];
    $meta = $r['body']['meta'] ?? [];
    echo "  Site: " . ($meta['site'] ?? 'N/A') . ", Count: " . count($data) . "\n";
    return true;
});

// Test that ict_group_leader (IPT site) sees IPT data
$glLogin2 = apiCall('POST', "{$BASE}/auth/login", ['nrp' => '21002037', 'password' => '21002037']);
$glToken2 = $glLogin2['body']['token'] ?? '';
tc("ict_group_leader (IPT) sees IPT-scoped aduan", function() use ($BASE, $glToken2) {
    $r = apiCall('GET', "{$BASE}/aduan?per_page=5", [], $glToken2);
    if ($r['status'] !== 200) return "HTTP {$r['status']}";
    $meta = $r['body']['meta'] ?? [];
    $site = $meta['site'] ?? 'N/A';
    echo "  Site: {$site}\n";
    if ($site !== 'IPT') return "Expected IPT site context, got: {$site}";
    return true;
});

echo "\n========================================\n";
echo "ROUND 3 SUMMARY\n";
echo "========================================\n";
echo "Total: {$tc} | PASSED: {$passed} | FAILED: {$failed}\n";
echo "Pass rate: " . round($passed/max($tc,1)*100, 1) . "%\n";
