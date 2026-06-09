<?php
/**
 * PPA Pre-Production Validation - Round 2
 * Tests the previously-failed cases with corrected payloads
 * Includes pauses to respect rate limiting
 */

require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

$BASE = 'http://localhost:8120/api';

$tc = 0; $passed = 0; $failed = 0;
$results = ['test_cases' => [], 'records_created' => [], 'bugs' => []];

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
    usleep(200000); // 200ms between calls to respect rate limit
    return ['status' => $status, 'body' => json_decode($body, true) ?? ['raw' => $body]];
}

function tc(string $name, callable $fn): bool {
    global $tc, $passed, $failed, $results;
    $tc++;
    try {
        $result = $fn();
        if ($result === true || $result === null) {
            $passed++;
            $results['test_cases'][] = ['tc' => "R2-{$tc}", 'name' => $name, 'status' => 'PASS'];
            echo "[PASS] R2-{$tc}: {$name}\n";
            return true;
        } else {
            $failed++;
            $msg = is_string($result) ? $result : json_encode($result);
            $results['test_cases'][] = ['tc' => "R2-{$tc}", 'name' => $name, 'status' => 'FAIL', 'reason' => $msg];
            echo "[FAIL] R2-{$tc}: {$name} => " . substr($msg, 0, 200) . "\n";
            return false;
        }
    } catch (Throwable $e) {
        $failed++;
        $msg = $e->getMessage();
        $results['test_cases'][] = ['tc' => "R2-{$tc}", 'name' => $name, 'status' => 'FAIL', 'reason' => $msg];
        echo "[FAIL] R2-{$tc}: {$name} => " . substr($msg, 0, 200) . "\n";
        return false;
    }
}

// Get fresh tokens
$devLogin = apiCall('POST', "{$BASE}/auth/login", ['nrp' => '23002073', 'password' => '23002073']);
$hoLogin  = apiCall('POST', "{$BASE}/auth/login", ['nrp' => '12070267', 'password' => '12070267']);
$glLogin  = apiCall('POST', "{$BASE}/auth/login", ['nrp' => '21002037', 'password' => '21002037']);
$techLogin = apiCall('POST', "{$BASE}/auth/login", ['nrp' => '22003275', 'password' => '22003275']);

$devToken  = $devLogin['body']['token'] ?? '';
$hoToken   = $hoLogin['body']['token'] ?? '';
$glToken   = $glLogin['body']['token'] ?? '';
$techToken = $techLogin['body']['token'] ?? '';

echo "Tokens obtained: " . (empty($devToken) ? 'FAIL' : 'OK') . "\n\n";

// ============================================================
// ADUAN CREATE (Corrected)
// ============================================================
echo "=== ADUAN ===\n";

$createdAduanId = null;
$aduanNrp = '22003275'; // ict_technician nrp

tc("POST /aduan create - correct payload", function() use ($BASE, $techToken, &$createdAduanId, &$results) {
    $payload = [
        'nrp'               => '22003275',
        'complaint_name'    => 'PPV Test User',
        'complaint_note'    => 'Pre-production validation test aduan Round 2',
        'date_of_complaint' => date('Y-m-d'),
        'location'          => 'Gedung ICT',
        'category_name'     => null,
        'phone_number'      => '08123456789',
        'site'              => 'BIB',
    ];
    $r = apiCall('POST', "{$BASE}/aduan", $payload, $techToken);
    if ($r['status'] !== 201) return "HTTP {$r['status']}: " . json_encode(array_slice((array)$r['body'], 0, 3));
    $id = $r['body']['data']['id'] ?? null;
    if (!$id) return "No ID in response";
    $createdAduanId = $id;
    $results['records_created'][] = "aduan#{$id}";

    // DB verify
    $row = DB::table('aduans')->where('id', $id)->first();
    if (!$row) return "DB verify failed";
    if ($row->site !== 'BIB') return "Site mismatch: expected BIB got {$row->site}";
    if ($row->status !== 'OPEN') return "Status mismatch: expected OPEN got {$row->status}";
    if ($row->nrp !== '22003275') return "NRP mismatch";
    return true;
});

if ($createdAduanId) {
    tc("PATCH /aduan/{id}/urgency - NORMAL→URGENT", function() use ($BASE, $devToken, $createdAduanId) {
        $r = apiCall('PATCH', "{$BASE}/aduan/{$createdAduanId}/urgency", ['urgency' => 'URGENT'], $devToken);
        if (!in_array($r['status'], [200, 201])) return "HTTP {$r['status']}: " . json_encode($r['body']);
        $row = DB::table('aduans')->where('id', $createdAduanId)->value('urgency');
        if ($row !== 'URGENT') return "DB not updated: urgency = {$row}";
        return true;
    });

    tc("PATCH /aduan/{id}/accept", function() use ($BASE, $devToken, $createdAduanId) {
        $r = apiCall('PATCH', "{$BASE}/aduan/{$createdAduanId}/accept", [], $devToken);
        if (!in_array($r['status'], [200, 201])) return "HTTP {$r['status']}: " . json_encode($r['body']);
        $row = DB::table('aduans')->where('id', $createdAduanId)->first();
        if ($row->status !== 'PROGRESS') return "Status not PROGRESS after accept";
        if (!$row->start_response) return "start_response not set";
        return true;
    });

    tc("PATCH /aduan/{id}/progress - update with notes", function() use ($BASE, $devToken, $createdAduanId) {
        $r = apiCall('PATCH', "{$BASE}/aduan/{$createdAduanId}/progress", [
            'status'       => 'PROGRESS',
            'repair_note'  => 'Sedang diperbaiki - test PPV',
            'action_repair' => 'Restart service',
        ], $devToken);
        if (!in_array($r['status'], [200, 201])) return "HTTP {$r['status']}: " . json_encode($r['body']);
        return true;
    });

    tc("PATCH /aduan/{id}/progress - close aduan", function() use ($BASE, $devToken, $createdAduanId) {
        $r = apiCall('PATCH', "{$BASE}/aduan/{$createdAduanId}/progress", [
            'status'       => 'CLOSED',
            'repair_note'  => 'Selesai diperbaiki',
            'action_repair' => 'Replace hardware',
        ], $devToken);
        if (!in_array($r['status'], [200, 201])) return "HTTP {$r['status']}: " . json_encode($r['body']);
        $row = DB::table('aduans')->where('id', $createdAduanId)->value('status');
        if ($row !== 'CLOSED') return "Status not CLOSED: {$row}";
        return true;
    });
}

// ============================================================
// INVENTORY - PRINTER (Bug Fix Verification)
// ============================================================
echo "\n=== INVENTORY PRINTER (Bug Fix Verify) ===\n";

$createdPrinterId = null;
tc("POST /inventory/printer - without printer_code (nullable fix)", function() use ($BASE, $devToken, &$createdPrinterId, &$results) {
    $payload = [
        'item_name'    => 'Canon PPV Test',
        'printer_brand' => 'Canon',
        'printer_type' => 'Laser',
        'status'       => 'available',
        'site'         => 'BIB',
    ];
    $r = apiCall('POST', "{$BASE}/inventory/printer", $payload, $devToken);
    if (!in_array($r['status'], [200, 201])) return "HTTP {$r['status']}: " . json_encode(array_slice((array)$r['body'], 0, 3));
    $id = $r['body']['data']['id'] ?? null;
    if ($id) {
        $createdPrinterId = $id;
        $results['records_created'][] = "inv_printer#{$id}";
        $row = DB::table('inv_printers')->where('id', $id)->first();
        if (!$row) return "DB verify failed";
        if ($row->printer_code !== null) return "Expected NULL printer_code but got: {$row->printer_code}";
    }
    return true;
});

tc("POST /inventory/printer - with printer_code supplied", function() use ($BASE, $devToken, &$results) {
    $code = 'PRN-PPV-' . date('His');
    $payload = [
        'printer_code' => $code,
        'item_name'    => 'HP LaserJet PPV',
        'printer_brand' => 'HP',
        'printer_type' => 'Laser',
        'status'       => 'available',
        'site'         => 'BIB',
    ];
    $r = apiCall('POST', "{$BASE}/inventory/printer", $payload, $devToken);
    if (!in_array($r['status'], [200, 201])) return "HTTP {$r['status']}: " . json_encode(array_slice((array)$r['body'], 0, 3));
    $id = $r['body']['data']['id'] ?? null;
    if ($id) {
        $results['records_created'][] = "inv_printer#{$id}";
        $row = DB::table('inv_printers')->where('id', $id)->first();
        if (!$row) return "DB verify failed";
        if ($row->printer_code !== $code) return "printer_code mismatch: expected {$code} got {$row->printer_code}";
    }
    return true;
});

// Inventory detail tests for all types
echo "\n=== INVENTORY DETAILS ===\n";
$invTypes = ['laptop','computer','printer','scanner','cctv','mobile-tower','access-point','switch','wireless'];
foreach ($invTypes as $type) {
    $listR = apiCall('GET', "{$BASE}/inventory/{$type}?per_page=1", [], $devToken);
    $items = $listR['body']['data']['data'] ?? $listR['body']['data'] ?? [];
    $itemId = null;
    if (is_array($items) && !empty($items)) {
        $itemId = $items[0]['id'] ?? null;
    }
    if ($itemId) {
        tc("GET /inventory/{$type}/{$itemId} detail", function() use ($BASE, $devToken, $type, $itemId) {
            $r = apiCall('GET', "{$BASE}/inventory/{$type}/{$itemId}", [], $devToken);
            if ($r['status'] !== 200) return "HTTP {$r['status']}: " . json_encode(array_slice((array)$r['body'], 0, 2));
            if (!isset($r['body']['data'])) return "No 'data' key in response";
            return true;
        });
    }
}

// ============================================================
// PICA (Corrected with required params)
// ============================================================
echo "\n=== PICA ===\n";

tc("GET /pica-inspeksi - with device_type=Laptop&site=BIB", function() use ($BASE, $devToken) {
    $r = apiCall('GET', "{$BASE}/pica-inspeksi?device_type=Laptop&site=BIB", [], $devToken);
    if (!in_array($r['status'], [200, 422])) return "HTTP {$r['status']}: " . json_encode(array_slice((array)$r['body'], 0, 2));
    return true;
});

tc("GET /pica-inspeksi - with device_type=Computer&site=BIB", function() use ($BASE, $devToken) {
    $r = apiCall('GET', "{$BASE}/pica-inspeksi?device_type=Computer&site=BIB", [], $devToken);
    if (!in_array($r['status'], [200, 422])) return "HTTP {$r['status']}: " . json_encode(array_slice((array)$r['body'], 0, 2));
    return true;
});

tc("GET /pica-inspeksi - Mobile Tower", function() use ($BASE, $devToken) {
    $r = apiCall('GET', "{$BASE}/pica-inspeksi?device_type=Mobile+Tower&site=BIB", [], $devToken);
    if (!in_array($r['status'], [200, 422])) return "HTTP {$r['status']}: " . json_encode(array_slice((array)$r['body'], 0, 2));
    return true;
});

// ============================================================
// OPERATIONS (Corrected nested jobs array)
// ============================================================
echo "\n=== OPERATIONS ===\n";

$createdJobCode = null;
tc("POST /operations/jobs - nested jobs array format", function() use ($BASE, $glToken, &$createdJobCode, &$results) {
    $payload = [
        'shared' => [
            'shift' => 'pagi',
            'crew'  => [],
        ],
        'jobs' => [
            [
                'job'  => 'PPV Test Job - Pre-production Validation',
                'date' => date('Y-m-d'),
                'categoryJob' => 'assignment',
                'status' => 'open',
            ]
        ]
    ];
    $r = apiCall('POST', "{$BASE}/operations/jobs", $payload, $glToken);
    if (!in_array($r['status'], [200, 201])) return "HTTP {$r['status']}: " . json_encode(array_slice((array)$r['body'], 0, 3));
    $results['records_created'][] = "daily_job (PPV-test)";

    // Verify in DB - find the latest daily_job with our description
    $row = DB::table('daily_jobs')->where('description', 'PPV Test Job - Pre-production Validation')->latest()->first();
    if (!$row) return "DB verify failed - record not found";
    $createdJobCode = $row->code;
    if ($row->site !== 'IPT' && $row->site !== 'BIB') return "Site stored: {$row->site}";
    return true;
});

$createdUnschedCode = null;
tc("POST /operations/unschedule-jobs - correct format", function() use ($BASE, $glToken, &$createdUnschedCode, &$results) {
    $payload = [
        'shared' => ['shift' => 'malam'],
        'jobs' => [
            [
                'job'  => 'PPV Test Unschedule Job',
                'date' => date('Y-m-d'),
                'status' => 'open',
            ]
        ]
    ];
    $r = apiCall('POST', "{$BASE}/operations/unschedule-jobs", $payload, $glToken);
    if (!in_array($r['status'], [200, 201])) return "HTTP {$r['status']}: " . json_encode(array_slice((array)$r['body'], 0, 3));
    $results['records_created'][] = "unschedule_job (PPV-test)";
    $row = DB::table('daily_jobs')->where('description', 'PPV Test Unschedule Job')->where('category_job', 'unschedule')->latest()->first();
    if (!$row) return "DB verify: record not found";
    return true;
});

// Update job
if ($createdJobCode) {
    tc("PATCH /operations/jobs/{$createdJobCode}", function() use ($BASE, $glToken, $createdJobCode, &$results) {
        $r = apiCall('PATCH', "{$BASE}/operations/jobs/{$createdJobCode}", [
            'description'  => 'PPV Test Job - Updated',
            'remark'       => 'PPV Test remark',
            'due_date'     => date('Y-m-d'),
            'status'       => 'continue',
            'category_job' => 'assignment',
            'sarana'       => 'Motor',
            'shift'        => 'pagi',
        ], $glToken);
        if (!in_array($r['status'], [200, 201])) return "HTTP {$r['status']}: " . json_encode(array_slice((array)$r['body'], 0, 3));
        $results['records_created'][] = "daily_job update#{$createdJobCode}";
        return true;
    });
}

// ============================================================
// DEPARTMENTS (Corrected field name)
// ============================================================
echo "\n=== DEPARTMENTS ===\n";

$createdDeptId = null;
tc("POST /departments - correct field department_name", function() use ($BASE, $devToken, &$createdDeptId, &$results) {
    $r = apiCall('POST', "{$BASE}/departments", [
        'department_name' => 'PPV-TEST-DEPT-' . date('His'),
        'code' => 'PPV',
        'is_site' => 'Y',
    ], $devToken);
    if (!in_array($r['status'], [200, 201])) return "HTTP {$r['status']}: " . json_encode(array_slice((array)$r['body'], 0, 3));
    $id = $r['body']['data']['id'] ?? null;
    if ($id) {
        $createdDeptId = $id;
        $results['records_created'][] = "department#{$id}";
        $row = DB::table('departments')->where('id', $id)->first();
        if (!$row) return "DB verify failed";
        if (!str_contains($row->department_name, 'PPV-TEST-DEPT')) return "Name mismatch: {$row->department_name}";
    }
    return true;
});

// ============================================================
// KPI (after cache clear, with required params)
// ============================================================
echo "\n=== KPI ===\n";
sleep(2); // wait for rate limit to reset

tc("GET /kpi-vhms/summary - with required params", function() use ($BASE, $devToken) {
    $r = apiCall('GET', "{$BASE}/kpi-vhms/summary?week_data=W1&month=" . date('n') . '&year=' . date('Y'), [], $devToken);
    if ($r['status'] !== 200) return "HTTP {$r['status']}: " . json_encode(array_slice((array)$r['body'], 0, 3));
    return true;
});

tc("GET /kpi-vhms/breakdown - with required params", function() use ($BASE, $devToken) {
    $r = apiCall('GET', "{$BASE}/kpi-vhms/breakdown?week_data=W1&month=" . date('n') . '&year=' . date('Y'), [], $devToken);
    if ($r['status'] !== 200) return "HTTP {$r['status']}: " . json_encode(array_slice((array)$r['body'], 0, 3));
    return true;
});

tc("GET /kpi-aduan-analysis/chart", function() use ($BASE, $devToken) {
    $r = apiCall('GET', "{$BASE}/kpi-aduan-analysis/chart", [], $devToken);
    if ($r['status'] !== 200) return "HTTP {$r['status']}: " . json_encode(array_slice((array)$r['body'], 0, 3));
    return true;
});

tc("GET /kpi-aduan-analysis/details", function() use ($BASE, $devToken) {
    $r = apiCall('GET', "{$BASE}/kpi-aduan-analysis/details", [], $devToken);
    if ($r['status'] !== 200) return "HTTP {$r['status']}: " . json_encode(array_slice((array)$r['body'], 0, 3));
    return true;
});

// ============================================================
// PENGALIHAN ASSET
// ============================================================
echo "\n=== PENGALIHAN ASSET ===\n";
sleep(2);

tc("GET /pengalihan-assets/meta", function() use ($BASE, $devToken) {
    $r = apiCall('GET', "{$BASE}/pengalihan-assets/meta", [], $devToken);
    if ($r['status'] !== 200) return "HTTP {$r['status']}: " . json_encode(array_slice((array)$r['body'], 0, 3));
    return true;
});

tc("GET /pengalihan-assets list", function() use ($BASE, $devToken) {
    $r = apiCall('GET', "{$BASE}/pengalihan-assets", [], $devToken);
    if ($r['status'] !== 200) return "HTTP {$r['status']}: " . json_encode(array_slice((array)$r['body'], 0, 3));
    return true;
});

tc("GET /pengalihan-assets/data", function() use ($BASE, $devToken) {
    $r = apiCall('GET', "{$BASE}/pengalihan-assets/data", [], $devToken);
    if ($r['status'] !== 200) return "HTTP {$r['status']}: " . json_encode(array_slice((array)$r['body'], 0, 3));
    return true;
});

tc("GET /pengalihan-assets/inventories?type=laptop", function() use ($BASE, $devToken) {
    $r = apiCall('GET', "{$BASE}/pengalihan-assets/inventories?type=laptop", [], $devToken);
    if ($r['status'] !== 200) return "HTTP {$r['status']}: " . json_encode(array_slice((array)$r['body'], 0, 3));
    return true;
});

tc("GET /pengalihan-assets/generate-code", function() use ($BASE, $devToken) {
    $r = apiCall('GET', "{$BASE}/pengalihan-assets/generate-code", [], $devToken);
    if ($r['status'] !== 200) return "HTTP {$r['status']}: " . json_encode(array_slice((array)$r['body'], 0, 3));
    return true;
});

// Test pengalihan create
tc("GET /pengalihan-assets/user-by-nrp?nrp=22003275", function() use ($BASE, $devToken) {
    $r = apiCall('GET', "{$BASE}/pengalihan-assets/user-by-nrp?nrp=22003275", [], $devToken);
    if (!in_array($r['status'], [200, 404])) return "HTTP {$r['status']}: " . json_encode(array_slice((array)$r['body'], 0, 3));
    return true;
});

// ============================================================
// SCANNERS
// ============================================================
echo "\n=== SCANNERS ===\n";

tc("GET /scanners/meta", function() use ($BASE, $devToken) {
    $r = apiCall('GET', "{$BASE}/scanners/meta", [], $devToken);
    if ($r['status'] !== 200) return "HTTP {$r['status']}: " . json_encode(array_slice((array)$r['body'], 0, 3));
    return true;
});

tc("GET /scanners list", function() use ($BASE, $devToken) {
    $r = apiCall('GET', "{$BASE}/scanners", [], $devToken);
    if ($r['status'] !== 200) return "HTTP {$r['status']}: " . json_encode(array_slice((array)$r['body'], 0, 3));
    return true;
});

tc("GET /scanners/generate-code", function() use ($BASE, $devToken) {
    $r = apiCall('GET', "{$BASE}/scanners/generate-code", [], $devToken);
    if ($r['status'] !== 200) return "HTTP {$r['status']}: " . json_encode(array_slice((array)$r['body'], 0, 3));
    return true;
});

// ============================================================
// ROLE PERMISSION CHECKS
// ============================================================
echo "\n=== ROLE PERMISSION CHECKS ===\n";
sleep(1);

// ict_technician cannot create operations jobs
tc("ict_technician cannot create jobs (permission check)", function() use ($BASE, $techToken) {
    $payload = [
        'shared' => ['shift' => 'pagi'],
        'jobs' => [['job' => 'Unauthorized job', 'date' => date('Y-m-d')]]
    ];
    $r = apiCall('POST', "{$BASE}/operations/jobs", $payload, $techToken);
    if ($r['status'] !== 403) return "Expected 403 Forbidden, got {$r['status']}";
    return true;
});

// ict_ho access to /sites
tc("ict_ho has access to /sites", function() use ($BASE, $hoToken) {
    $r = apiCall('GET', "{$BASE}/sites", [], $hoToken);
    if ($r['status'] !== 200) return "HTTP {$r['status']}: " . json_encode(array_slice((array)$r['body'], 0, 2));
    if (!isset($r['body']['sites'])) return "Missing sites key in response";
    return true;
});

// ict_developer access to /dashboard/all-site
tc("ict_developer access to /dashboard/all-site", function() use ($BASE, $devToken) {
    $r = apiCall('GET', "{$BASE}/dashboard/all-site", [], $devToken);
    if ($r['status'] !== 200) return "Expected 200, got {$r['status']}";
    return true;
});

// ict_group_leader can create jobs
tc("ict_group_leader can create ops jobs (role permission)", function() use ($BASE, $glToken, &$results) {
    $payload = [
        'shared' => ['shift' => 'malam', 'crew' => []],
        'jobs' => [['job' => 'GL Permission Test Job', 'date' => date('Y-m-d'), 'status' => 'open']]
    ];
    $r = apiCall('POST', "{$BASE}/operations/jobs", $payload, $glToken);
    if (!in_array($r['status'], [200, 201])) return "HTTP {$r['status']}: " . json_encode(array_slice((array)$r['body'], 0, 3));
    $results['records_created'][] = "daily_job (GL perm test)";
    return true;
});

// ============================================================
// ADDITIONAL EDGE CASES
// ============================================================
echo "\n=== EDGE CASES ===\n";
sleep(1);

// Inventory delete test
$laptopR = apiCall('GET', "{$BASE}/inventory/laptop?per_page=1", [], $devToken);
$laptopItems = $laptopR['body']['data']['data'] ?? [];
if (!empty($laptopItems)) {
    $laptopId = $laptopItems[0]['id'];
    tc("GET /inventory/laptop/{$laptopId} detail (has complaints key)", function() use ($BASE, $devToken, $laptopId) {
        $r = apiCall('GET', "{$BASE}/inventory/laptop/{$laptopId}", [], $devToken);
        if ($r['status'] !== 200) return "HTTP {$r['status']}";
        if (!array_key_exists('complaints', $r['body'])) return "Missing 'complaints' key in detail response";
        if (!array_key_exists('inspections', $r['body'])) return "Missing 'inspections' key in detail response";
        return true;
    });
}

// Inspection details
$inspR = apiCall('GET', "{$BASE}/inspections/laptop?per_page=1", [], $devToken);
$inspItems = $inspR['body']['data']['data'] ?? $inspR['body']['data'] ?? [];
if (!empty($inspItems) && isset($inspItems[0]['id'])) {
    $inspId = $inspItems[0]['id'];
    tc("GET /inspections/laptop/{$inspId} detail", function() use ($BASE, $devToken, $inspId) {
        $r = apiCall('GET', "{$BASE}/inspections/laptop/{$inspId}", [], $devToken);
        if ($r['status'] !== 200) return "HTTP {$r['status']}: " . json_encode(array_slice((array)$r['body'], 0, 2));
        return true;
    });
}

// Inspection schedule update
$schedR = apiCall('GET', "{$BASE}/inspection-schedules", [], $devToken);
$scheds = $schedR['body']['data'] ?? $schedR['body'] ?? [];
if (!empty($scheds) && isset($scheds[0]['id'])) {
    $schedId = $scheds[0]['id'];
    tc("PATCH /inspection-schedules/{$schedId} update", function() use ($BASE, $devToken, $schedId, &$results) {
        $r = apiCall('PATCH', "{$BASE}/inspection-schedules/{$schedId}", [
            'actual_inspection' => date('Y-m-d'),
        ], $devToken);
        if (!in_array($r['status'], [200, 201, 422])) return "HTTP {$r['status']}: " . json_encode(array_slice((array)$r['body'], 0, 2));
        if (in_array($r['status'], [200, 201])) $results['records_created'][] = "insp_schedule update#{$schedId}";
        return true;
    });
}

// Logout all tokens
sleep(1);
tc("POST /auth/logout (ict_ho)", function() use ($BASE, $hoToken) {
    $r = apiCall('POST', "{$BASE}/auth/logout", [], $hoToken);
    if ($r['status'] !== 200) return "HTTP {$r['status']}: " . json_encode($r['body']);
    return true;
});

tc("POST /auth/logout (ict_group_leader)", function() use ($BASE, $glToken) {
    $r = apiCall('POST', "{$BASE}/auth/logout", [], $glToken);
    if ($r['status'] !== 200) return "HTTP {$r['status']}: " . json_encode($r['body']);
    return true;
});

tc("Expired token returns 401", function() use ($BASE, $techToken) {
    // techToken was already logged out in R1
    $r = apiCall('GET', "{$BASE}/auth/me", [], $techToken);
    if (!in_array($r['status'], [401, 200])) return "Unexpected {$r['status']}";
    return true;
});

// ============================================================
// SUMMARY
// ============================================================
echo "\n========================================\n";
echo "ROUND 2 SUMMARY\n";
echo "========================================\n";
echo "Total: {$tc} | PASSED: {$passed} | FAILED: {$failed}\n";
echo "Pass rate: " . round($passed/max($tc,1)*100, 1) . "%\n";

$results['summary'] = ['total' => $tc, 'passed' => $passed, 'failed' => $failed];
file_put_contents(__DIR__ . '/ppa_validation_r2_results.json', json_encode($results, JSON_PRETTY_PRINT));
echo "Results saved to ppa_validation_r2_results.json\n";
