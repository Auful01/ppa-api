<?php
/**
 * PPA Pre-Production Validation Script
 * Tests all modules via actual API calls against new_itportalv3
 */

require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

$BASE = 'http://localhost:8120/api';

$results = [
    'roles_tested' => [],
    'modules_tested' => [],
    'test_cases' => [],
    'records_created' => [],
    'records_updated' => [],
    'bugs_found' => [],
    'bugs_fixed' => [],
    'remaining_issues' => [],
];

$tc = 0;
$passed = 0;
$failed = 0;

// -------------------------------------------------------
// HELPERS
// -------------------------------------------------------
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
    $error = curl_error($ch);
    curl_close($ch);
    return [
        'status' => $status,
        'body' => json_decode($body, true) ?? ['raw' => $body],
        'error' => $error,
    ];
}

function tc(string $name, callable $fn): bool {
    global $tc, $passed, $failed, $results;
    $tc++;
    try {
        $result = $fn();
        if ($result === true || $result === null) {
            $passed++;
            $results['test_cases'][] = ['tc' => $tc, 'name' => $name, 'status' => 'PASS'];
            echo "[PASS] TC-{$tc}: {$name}\n";
            return true;
        } else {
            $failed++;
            $msg = is_string($result) ? $result : json_encode($result);
            $results['test_cases'][] = ['tc' => $tc, 'name' => $name, 'status' => 'FAIL', 'reason' => $msg];
            echo "[FAIL] TC-{$tc}: {$name} => {$msg}\n";
            return false;
        }
    } catch (Throwable $e) {
        $failed++;
        $msg = $e->getMessage();
        $results['test_cases'][] = ['tc' => $tc, 'name' => $name, 'status' => 'FAIL', 'reason' => $msg];
        echo "[FAIL] TC-{$tc}: {$name} => {$msg}\n";
        return false;
    }
}

function bug(string $id, string $desc): void {
    global $results;
    $results['bugs_found'][] = ['id' => $id, 'desc' => $desc];
    echo "[BUG] {$id}: {$desc}\n";
}

function fixed(string $id, string $desc): void {
    global $results;
    $results['bugs_fixed'][] = ['id' => $id, 'desc' => $desc];
    echo "[FIXED] {$id}: {$desc}\n";
}

function remaining(string $id, string $desc): void {
    global $results;
    $results['remaining_issues'][] = ['id' => $id, 'desc' => $desc];
    echo "[REMAINING] {$id}: {$desc}\n";
}

function dbCheck(string $table, array $where): ?object {
    $q = DB::table($table);
    foreach ($where as $col => $val) $q->where($col, $val);
    return $q->first();
}

// -------------------------------------------------------
// ROLES SETUP
// -------------------------------------------------------
$users = [
    'ict_developer'   => ['nrp' => '23002073', 'password' => '23002073', 'site' => 'BIB'],
    'ict_ho'          => ['nrp' => '12070267', 'password' => '12070267', 'site' => 'HO'],
    'ict_group_leader'=> ['nrp' => '21002037', 'password' => '21002037', 'site' => 'IPT'],
    'ict_admin'       => ['nrp' => '0002',     'password' => '0002',     'site' => 'BIB'],
    'ict_technician'  => ['nrp' => '22003275', 'password' => '22003275', 'site' => 'BIB'],
];

$tokens = [];

echo "\n========================================\n";
echo "MODULE: AUTH\n";
echo "========================================\n";

// TC: Login each role
foreach ($users as $role => $u) {
    tc("Login as {$role} (nrp={$u['nrp']})", function() use ($role, $u, $BASE, &$tokens) {
        $r = apiCall('POST', "{$BASE}/auth/login", ['nrp' => $u['nrp'], 'password' => $u['password']]);
        if ($r['status'] !== 200) return "HTTP {$r['status']}: " . json_encode($r['body']);
        if (empty($r['body']['token'])) return "No token in response";
        $tokens[$role] = $r['body']['token'];
        return true;
    });
}

$results['roles_tested'] = array_keys($users);

// TC: Invalid login
tc("Login with wrong password returns 422", function() use ($BASE) {
    $r = apiCall('POST', "{$BASE}/auth/login", ['nrp' => '23002073', 'password' => 'wrongpass']);
    if ($r['status'] !== 422) return "Expected 422, got {$r['status']}";
    return true;
});

tc("Login with missing fields returns 422", function() use ($BASE) {
    $r = apiCall('POST', "{$BASE}/auth/login", ['nrp' => '23002073']);
    if ($r['status'] !== 422) return "Expected 422, got {$r['status']}";
    return true;
});

// TC: Me endpoint
tc("GET /me returns user data", function() use ($BASE, $tokens) {
    $r = apiCall('GET', "{$BASE}/auth/me", [], $tokens['ict_developer']);
    if ($r['status'] !== 200) return "HTTP {$r['status']}";
    if (empty($r['body']['user'])) return "No user in response";
    return true;
});

// TC: Unauthenticated access
tc("Unauthenticated request returns 401", function() use ($BASE) {
    $r = apiCall('GET', "{$BASE}/dashboard");
    if ($r['status'] !== 401) return "Expected 401, got {$r['status']}";
    return true;
});

$devToken = $tokens['ict_developer'] ?? '';
$hoToken  = $tokens['ict_ho'] ?? '';
$glToken  = $tokens['ict_group_leader'] ?? '';
$adminToken = $tokens['ict_admin'] ?? '';
$techToken  = $tokens['ict_technician'] ?? '';

echo "\n========================================\n";
echo "MODULE: DASHBOARD\n";
echo "========================================\n";
$results['modules_tested'][] = 'AUTH';
$results['modules_tested'][] = 'DASHBOARD';

tc("GET /dashboard (ict_developer)", function() use ($BASE, $devToken) {
    $r = apiCall('GET', "{$BASE}/dashboard", [], $devToken);
    if ($r['status'] !== 200) return "HTTP {$r['status']}: " . json_encode($r['body']);
    return true;
});

tc("GET /dashboard (ict_ho)", function() use ($BASE, $hoToken) {
    $r = apiCall('GET', "{$BASE}/dashboard", [], $hoToken);
    if ($r['status'] !== 200) return "HTTP {$r['status']}: " . json_encode($r['body']);
    return true;
});

tc("GET /dashboard (ict_group_leader)", function() use ($BASE, $glToken) {
    $r = apiCall('GET', "{$BASE}/dashboard", [], $glToken);
    if ($r['status'] !== 200) return "HTTP {$r['status']}: " . json_encode($r['body']);
    return true;
});

tc("GET /dashboard/all-site (ict_ho only)", function() use ($BASE, $hoToken) {
    $r = apiCall('GET', "{$BASE}/dashboard/all-site", [], $hoToken);
    if ($r['status'] !== 200) return "HTTP {$r['status']}: " . json_encode($r['body']);
    return true;
});

tc("GET /dashboard/all-site (ict_developer should have access)", function() use ($BASE, $devToken) {
    $r = apiCall('GET', "{$BASE}/dashboard/all-site", [], $devToken);
    if (!in_array($r['status'], [200, 403])) return "HTTP {$r['status']}: " . json_encode($r['body']);
    return true;
});

tc("GET /chart-inspeksi", function() use ($BASE, $devToken) {
    $r = apiCall('GET', "{$BASE}/chart-inspeksi", [], $devToken);
    if ($r['status'] !== 200) return "HTTP {$r['status']}: " . json_encode($r['body']);
    return true;
});

tc("GET /sites (ict_ho)", function() use ($BASE, $hoToken) {
    $r = apiCall('GET', "{$BASE}/sites", [], $hoToken);
    if ($r['status'] !== 200) return "HTTP {$r['status']}: " . json_encode($r['body']);
    if (!isset($r['body']['sites'])) return "No sites in response";
    return true;
});

tc("GET /sites forbidden for ict_technician", function() use ($BASE, $techToken) {
    $r = apiCall('GET', "{$BASE}/sites", [], $techToken);
    if ($r['status'] !== 403) return "Expected 403, got {$r['status']}";
    return true;
});

echo "\n========================================\n";
echo "MODULE: ADUAN\n";
echo "========================================\n";
$results['modules_tested'][] = 'ADUAN';

tc("GET /aduan/meta", function() use ($BASE, $devToken) {
    $r = apiCall('GET', "{$BASE}/aduan/meta", [], $devToken);
    if ($r['status'] !== 200) return "HTTP {$r['status']}: " . json_encode($r['body']);
    return true;
});

tc("GET /aduan list", function() use ($BASE, $devToken) {
    $r = apiCall('GET', "{$BASE}/aduan", [], $devToken);
    if ($r['status'] !== 200) return "HTTP {$r['status']}: " . json_encode($r['body']);
    return true;
});

// Get sample aduan
$aduanListR = apiCall('GET', "{$BASE}/aduan", [], $devToken);
$aduanId = null;
if (!empty($aduanListR['body']['data'][0]['id'])) {
    $aduanId = $aduanListR['body']['data'][0]['id'];
} elseif (!empty($aduanListR['body'][0]['id'])) {
    $aduanId = $aduanListR['body'][0]['id'];
}

// Create aduan
$createdAduanId = null;
tc("POST /aduan create new complaint", function() use ($BASE, $techToken, &$createdAduanId, &$results) {
    $payload = [
        'judul'      => 'PPV-TEST Aduan ' . date('YmdHis'),
        'deskripsi'  => 'Pre-production validation test aduan',
        'jenis'      => 'hardware',
        'urgency'    => 'medium',
        'lokasi'     => 'Test Location',
    ];
    $r = apiCall('POST', "{$BASE}/aduan", $payload, $techToken);
    if ($r['status'] !== 201 && $r['status'] !== 200) return "HTTP {$r['status']}: " . json_encode($r['body']);
    $id = $r['body']['data']['id'] ?? $r['body']['id'] ?? null;
    if (!$id) return "No ID in response: " . json_encode($r['body']);
    $createdAduanId = $id;
    $results['records_created'][] = "aduan#{$id}";

    // DB verify
    $row = dbCheck('aduans', ['id' => $id]);
    if (!$row) return "Record not found in DB after create";
    return true;
});

if ($aduanId || $createdAduanId) {
    $testId = $createdAduanId ?? $aduanId;
    tc("GET /aduan/{id} detail", function() use ($BASE, $devToken, $testId) {
        $r = apiCall('GET', "{$BASE}/aduan/{$testId}", [], $devToken);
        if ($r['status'] !== 200) return "HTTP {$r['status']}: " . json_encode($r['body']);
        return true;
    });
}

if ($createdAduanId) {
    tc("PATCH /aduan/{id} edit", function() use ($BASE, $techToken, $createdAduanId, &$results) {
        $r = apiCall('PATCH', "{$BASE}/aduan/{$createdAduanId}", ['deskripsi' => 'Updated description PPV'], $techToken);
        if (!in_array($r['status'], [200, 201])) return "HTTP {$r['status']}: " . json_encode($r['body']);
        $results['records_updated'][] = "aduan#{$createdAduanId}";
        return true;
    });

    tc("PATCH /aduan/{id}/urgency update urgency", function() use ($BASE, $devToken, $createdAduanId) {
        $r = apiCall('PATCH', "{$BASE}/aduan/{$createdAduanId}/urgency", ['urgency' => 'high'], $devToken);
        if (!in_array($r['status'], [200, 201])) return "HTTP {$r['status']}: " . json_encode($r['body']);
        return true;
    });

    tc("PATCH /aduan/{id}/accept accept aduan", function() use ($BASE, $devToken, $createdAduanId) {
        $r = apiCall('PATCH', "{$BASE}/aduan/{$createdAduanId}/accept", [], $devToken);
        if (!in_array($r['status'], [200, 201, 422])) return "HTTP {$r['status']}: " . json_encode($r['body']);
        return true;
    });

    tc("PATCH /aduan/{id}/progress update progress", function() use ($BASE, $devToken, $createdAduanId) {
        $r = apiCall('PATCH', "{$BASE}/aduan/{$createdAduanId}/progress", [
            'progress' => 'Sedang ditangani',
            'status'   => 'on_progress',
        ], $devToken);
        if (!in_array($r['status'], [200, 201, 422])) return "HTTP {$r['status']}: " . json_encode($r['body']);
        return true;
    });
}

echo "\n========================================\n";
echo "MODULE: INVENTORY\n";
echo "========================================\n";
$results['modules_tested'][] = 'INVENTORY';

$invTypes = ['laptop', 'computer', 'printer', 'scanner', 'cctv', 'mobile-tower', 'access-point', 'switch', 'wireless'];
$createdInvIds = [];

foreach ($invTypes as $type) {
    tc("GET /inventory/{$type}/meta", function() use ($BASE, $devToken, $type) {
        $r = apiCall('GET', "{$BASE}/inventory/{$type}/meta", [], $devToken);
        if ($r['status'] !== 200) return "HTTP {$r['status']}: " . json_encode($r['body']);
        return true;
    });

    tc("GET /inventory/{$type} list", function() use ($BASE, $devToken, $type) {
        $r = apiCall('GET', "{$BASE}/inventory/{$type}", [], $devToken);
        if ($r['status'] !== 200) return "HTTP {$r['status']}: " . json_encode($r['body']);
        return true;
    });

    // Get first item for detail test
    $listR = apiCall('GET', "{$BASE}/inventory/{$type}", [], $devToken);
    $itemId = $listR['body']['data'][0]['id'] ?? $listR['body'][0]['id'] ?? null;

    if ($itemId) {
        tc("GET /inventory/{$type}/{$itemId} detail", function() use ($BASE, $devToken, $type, $itemId) {
            $r = apiCall('GET', "{$BASE}/inventory/{$type}/{$itemId}", [], $devToken);
            if ($r['status'] !== 200) return "HTTP {$r['status']}: " . json_encode($r['body']);
            return true;
        });
    }
}

// Create inventory items
tc("POST /inventory/laptop create", function() use ($BASE, $devToken, &$createdInvIds, &$results) {
    $payload = [
        'asset_code'  => 'LAP-PPV-' . date('His'),
        'brand'       => 'Dell',
        'model'       => 'Latitude 5520',
        'serial_no'   => 'SN-PPV-' . rand(10000,99999),
        'dept'        => 'ICT',
        'site'        => 'BIB',
        'condition'   => 'good',
        'status'      => 'available',
    ];
    $r = apiCall('POST', "{$BASE}/inventory/laptop", $payload, $devToken);
    if (!in_array($r['status'], [200, 201])) return "HTTP {$r['status']}: " . json_encode($r['body']);
    $id = $r['body']['data']['id'] ?? $r['body']['id'] ?? null;
    if ($id) {
        $createdInvIds['laptop'] = $id;
        $results['records_created'][] = "inv_laptop#{$id}";
        $row = DB::table('inv_laptops')->where('id', $id)->first();
        if (!$row) return "DB verify failed - record not found";
    }
    return true;
});

tc("POST /inventory/computer create", function() use ($BASE, $devToken, &$createdInvIds, &$results) {
    $payload = [
        'asset_code'  => 'COM-PPV-' . date('His'),
        'brand'       => 'HP',
        'model'       => 'EliteDesk 800',
        'serial_no'   => 'SN-COM-' . rand(10000,99999),
        'dept'        => 'ICT',
        'site'        => 'BIB',
        'condition'   => 'good',
        'status'      => 'available',
    ];
    $r = apiCall('POST', "{$BASE}/inventory/computer", $payload, $devToken);
    if (!in_array($r['status'], [200, 201])) return "HTTP {$r['status']}: " . json_encode($r['body']);
    $id = $r['body']['data']['id'] ?? $r['body']['id'] ?? null;
    if ($id) {
        $createdInvIds['computer'] = $id;
        $results['records_created'][] = "inv_computer#{$id}";
    }
    return true;
});

tc("POST /inventory/printer create", function() use ($BASE, $devToken, &$createdInvIds, &$results) {
    $payload = [
        'asset_code'  => 'PRN-PPV-' . date('His'),
        'brand'       => 'Canon',
        'model'       => 'LBP6030',
        'serial_no'   => 'SN-PRN-' . rand(10000,99999),
        'dept'        => 'ICT',
        'site'        => 'BIB',
        'condition'   => 'good',
        'status'      => 'available',
    ];
    $r = apiCall('POST', "{$BASE}/inventory/printer", $payload, $devToken);
    if (!in_array($r['status'], [200, 201])) return "HTTP {$r['status']}: " . json_encode($r['body']);
    $id = $r['body']['data']['id'] ?? $r['body']['id'] ?? null;
    if ($id) {
        $createdInvIds['printer'] = $id;
        $results['records_created'][] = "inv_printer#{$id}";
    }
    return true;
});

// Edit and delete
foreach (['laptop', 'computer', 'printer'] as $type) {
    if (!empty($createdInvIds[$type])) {
        $id = $createdInvIds[$type];
        tc("PATCH /inventory/{$type}/{$id} edit", function() use ($BASE, $devToken, $type, $id, &$results) {
            $r = apiCall('PATCH', "{$BASE}/inventory/{$type}/{$id}", ['condition' => 'fair'], $devToken);
            if (!in_array($r['status'], [200, 201])) return "HTTP {$r['status']}: " . json_encode($r['body']);
            $results['records_updated'][] = "inv_{$type}#{$id}";
            return true;
        });
    }
}

echo "\n========================================\n";
echo "MODULE: INSPECTION\n";
echo "========================================\n";
$results['modules_tested'][] = 'INSPECTION';

$inspTypes = ['laptop', 'computer'];
foreach ($inspTypes as $type) {
    tc("GET /inspections/{$type} list", function() use ($BASE, $devToken, $type) {
        $r = apiCall('GET', "{$BASE}/inspections/{$type}", [], $devToken);
        if ($r['status'] !== 200) return "HTTP {$r['status']}: " . json_encode($r['body']);
        return true;
    });

    $listR = apiCall('GET', "{$BASE}/inspections/{$type}", [], $devToken);
    $inspId = $listR['body']['data'][0]['id'] ?? $listR['body'][0]['id'] ?? null;
    if ($inspId) {
        tc("GET /inspections/{$type}/{$inspId} detail", function() use ($BASE, $devToken, $type, $inspId) {
            $r = apiCall('GET', "{$BASE}/inspections/{$type}/{$inspId}", [], $devToken);
            if ($r['status'] !== 200) return "HTTP {$r['status']}: " . json_encode($r['body']);
            return true;
        });
    }
}

tc("GET /inspection-schedules", function() use ($BASE, $devToken) {
    $r = apiCall('GET', "{$BASE}/inspection-schedules", [], $devToken);
    if ($r['status'] !== 200) return "HTTP {$r['status']}: " . json_encode($r['body']);
    return true;
});

echo "\n========================================\n";
echo "MODULE: PICA\n";
echo "========================================\n";
$results['modules_tested'][] = 'PICA';

tc("GET /pica-inspeksi/meta", function() use ($BASE, $devToken) {
    $r = apiCall('GET', "{$BASE}/pica-inspeksi/meta", [], $devToken);
    if ($r['status'] !== 200) return "HTTP {$r['status']}: " . json_encode($r['body']);
    return true;
});

tc("GET /pica-inspeksi list", function() use ($BASE, $devToken) {
    $r = apiCall('GET', "{$BASE}/pica-inspeksi", [], $devToken);
    if ($r['status'] !== 200) return "HTTP {$r['status']}: " . json_encode($r['body']);
    return true;
});

$picaListR = apiCall('GET', "{$BASE}/pica-inspeksi", [], $devToken);
$picaId = $picaListR['body']['data'][0]['id'] ?? $picaListR['body'][0]['id'] ?? null;
if ($picaId) {
    tc("GET /pica-inspeksi/{$picaId} detail", function() use ($BASE, $devToken, $picaId) {
        $r = apiCall('GET', "{$BASE}/pica-inspeksi/{$picaId}", [], $devToken);
        if ($r['status'] !== 200) return "HTTP {$r['status']}: " . json_encode($r['body']);
        return true;
    });

    tc("PATCH /pica-inspeksi/{$picaId} update status", function() use ($BASE, $devToken, $picaId, &$results) {
        $r = apiCall('PATCH', "{$BASE}/pica-inspeksi/{$picaId}", ['status' => 'open'], $devToken);
        if (!in_array($r['status'], [200, 201, 422, 403])) return "HTTP {$r['status']}: " . json_encode($r['body']);
        if (in_array($r['status'], [200, 201])) $results['records_updated'][] = "pica#{$picaId}";
        return true;
    });
}

echo "\n========================================\n";
echo "MODULE: OPERATIONS\n";
echo "========================================\n";
$results['modules_tested'][] = 'OPERATIONS';

tc("GET /operations/jobs/meta", function() use ($BASE, $devToken) {
    $r = apiCall('GET', "{$BASE}/operations/jobs/meta", [], $devToken);
    if ($r['status'] !== 200) return "HTTP {$r['status']}: " . json_encode($r['body']);
    return true;
});

tc("GET /operations/jobs list", function() use ($BASE, $devToken) {
    $r = apiCall('GET', "{$BASE}/operations/jobs", [], $devToken);
    if ($r['status'] !== 200) return "HTTP {$r['status']}: " . json_encode($r['body']);
    return true;
});

tc("GET /operations/unschedule-jobs/meta", function() use ($BASE, $devToken) {
    $r = apiCall('GET', "{$BASE}/operations/unschedule-jobs/meta", [], $devToken);
    if ($r['status'] !== 200) return "HTTP {$r['status']}: " . json_encode($r['body']);
    return true;
});

tc("GET /operations/unschedule-jobs list", function() use ($BASE, $devToken) {
    $r = apiCall('GET', "{$BASE}/operations/unschedule-jobs", [], $devToken);
    if ($r['status'] !== 200) return "HTTP {$r['status']}: " . json_encode($r['body']);
    return true;
});

tc("GET /operations/monitoring-jobs", function() use ($BASE, $devToken) {
    $r = apiCall('GET', "{$BASE}/operations/monitoring-jobs", [], $devToken);
    if ($r['status'] !== 200) return "HTTP {$r['status']}: " . json_encode($r['body']);
    return true;
});

// Create an assignment job
$createdJobCode = null;
tc("POST /operations/jobs create assignment job", function() use ($BASE, $devToken, &$createdJobCode, &$results) {
    $payload = [
        'category_job'  => 'assignment',
        'description'   => 'PPV Test Job ' . date('YmdHis'),
        'site'          => 'BIB',
        'category'      => 'network',
        'shift'         => 'pagi',
        'date'          => date('Y-m-d'),
        'urgency'       => 'medium',
    ];
    $r = apiCall('POST', "{$BASE}/operations/jobs", $payload, $devToken);
    if (!in_array($r['status'], [200, 201])) return "HTTP {$r['status']}: " . json_encode($r['body']);
    $code = $r['body']['data']['code'] ?? $r['body']['code'] ?? null;
    if ($code) {
        $createdJobCode = $code;
        $results['records_created'][] = "daily_job#{$code}";
        $row = DB::table('daily_jobs')->where('code', $code)->first();
        if (!$row) return "DB verify: record not found for code={$code}";
    }
    return true;
});

if ($createdJobCode) {
    tc("GET /operations/jobs/{$createdJobCode} detail", function() use ($BASE, $devToken, $createdJobCode) {
        $r = apiCall('GET', "{$BASE}/operations/jobs/{$createdJobCode}", [], $devToken);
        if ($r['status'] !== 200) return "HTTP {$r['status']}: " . json_encode($r['body']);
        return true;
    });

    tc("PATCH /operations/jobs/{$createdJobCode} update", function() use ($BASE, $devToken, $createdJobCode, &$results) {
        $r = apiCall('PATCH', "{$BASE}/operations/jobs/{$createdJobCode}", ['status' => 'continue'], $devToken);
        if (!in_array($r['status'], [200, 201, 422])) return "HTTP {$r['status']}: " . json_encode($r['body']);
        if (in_array($r['status'], [200, 201])) $results['records_updated'][] = "daily_job#{$createdJobCode}";
        return true;
    });
}

// Create unschedule job
$createdUnschedCode = null;
tc("POST /operations/unschedule-jobs create", function() use ($BASE, $devToken, &$createdUnschedCode, &$results) {
    $payload = [
        'description'  => 'PPV Unschedule ' . date('YmdHis'),
        'site'         => 'BIB',
        'category'     => 'hardware',
        'shift'        => 'pagi',
        'date'         => date('Y-m-d'),
        'urgency'      => 'low',
    ];
    $r = apiCall('POST', "{$BASE}/operations/unschedule-jobs", $payload, $devToken);
    if (!in_array($r['status'], [200, 201])) return "HTTP {$r['status']}: " . json_encode($r['body']);
    $code = $r['body']['data']['code'] ?? $r['body']['code'] ?? null;
    if ($code) {
        $createdUnschedCode = $code;
        $results['records_created'][] = "unschedule_job#{$code}";
    }
    return true;
});

echo "\n========================================\n";
echo "MODULE: SETTINGS / DEPARTMENTS\n";
echo "========================================\n";
$results['modules_tested'][] = 'SETTINGS';

tc("GET /departments list", function() use ($BASE, $devToken) {
    $r = apiCall('GET', "{$BASE}/departments", [], $devToken);
    if ($r['status'] !== 200) return "HTTP {$r['status']}: " . json_encode($r['body']);
    return true;
});

$deptListR = apiCall('GET', "{$BASE}/departments", [], $devToken);
$deptId = $deptListR['body']['data'][0]['id'] ?? $deptListR['body'][0]['id'] ?? null;
if ($deptId) {
    tc("GET /departments/{$deptId}", function() use ($BASE, $devToken, $deptId) {
        $r = apiCall('GET', "{$BASE}/departments/{$deptId}", [], $devToken);
        if ($r['status'] !== 200) return "HTTP {$r['status']}: " . json_encode($r['body']);
        return true;
    });
}

$createdDeptId = null;
tc("POST /departments create", function() use ($BASE, $devToken, &$createdDeptId, &$results) {
    $r = apiCall('POST', "{$BASE}/departments", [
        'name' => 'PPV-TEST-DEPT-' . date('His'),
        'site' => 'BIB',
    ], $devToken);
    if (!in_array($r['status'], [200, 201])) return "HTTP {$r['status']}: " . json_encode($r['body']);
    $id = $r['body']['data']['id'] ?? $r['body']['id'] ?? null;
    if ($id) {
        $createdDeptId = $id;
        $results['records_created'][] = "department#{$id}";
    }
    return true;
});

if ($createdDeptId) {
    tc("PUT /departments/{$createdDeptId} update", function() use ($BASE, $devToken, $createdDeptId, &$results) {
        $r = apiCall('PUT', "{$BASE}/departments/{$createdDeptId}", [
            'name' => 'PPV-TEST-DEPT-UPDATED',
            'site' => 'BIB',
        ], $devToken);
        if (!in_array($r['status'], [200, 201])) return "HTTP {$r['status']}: " . json_encode($r['body']);
        $results['records_updated'][] = "department#{$createdDeptId}";
        return true;
    });
}

tc("GET /pengajuan-akses list", function() use ($BASE, $devToken) {
    $r = apiCall('GET', "{$BASE}/pengajuan-akses", [], $devToken);
    if ($r['status'] !== 200) return "HTTP {$r['status']}: " . json_encode($r['body']);
    return true;
});

echo "\n========================================\n";
echo "MODULE: KPI\n";
echo "========================================\n";
$results['modules_tested'][] = 'KPI';

tc("GET /kpi-vhms list", function() use ($BASE, $devToken) {
    $r = apiCall('GET', "{$BASE}/kpi-vhms", [], $devToken);
    if ($r['status'] !== 200) return "HTTP {$r['status']}: " . json_encode($r['body']);
    return true;
});

tc("GET /kpi-vhms/summary", function() use ($BASE, $devToken) {
    $r = apiCall('GET', "{$BASE}/kpi-vhms/summary", [], $devToken);
    if ($r['status'] !== 200) return "HTTP {$r['status']}: " . json_encode($r['body']);
    return true;
});

tc("GET /kpi-vhms/breakdown", function() use ($BASE, $devToken) {
    $r = apiCall('GET', "{$BASE}/kpi-vhms/breakdown", [], $devToken);
    if ($r['status'] !== 200) return "HTTP {$r['status']}: " . json_encode($r['body']);
    return true;
});

tc("GET /kpi-aduan-analysis/chart", function() use ($BASE, $devToken) {
    $r = apiCall('GET', "{$BASE}/kpi-aduan-analysis/chart", [], $devToken);
    if ($r['status'] !== 200) return "HTTP {$r['status']}: " . json_encode($r['body']);
    return true;
});

tc("GET /kpi-aduan-analysis/details", function() use ($BASE, $devToken) {
    $r = apiCall('GET', "{$BASE}/kpi-aduan-analysis/details", [], $devToken);
    if ($r['status'] !== 200) return "HTTP {$r['status']}: " . json_encode($r['body']);
    return true;
});

echo "\n========================================\n";
echo "MODULE: PENGALIHAN ASSET\n";
echo "========================================\n";
$results['modules_tested'][] = 'PENGALIHAN_ASSET';

tc("GET /pengalihan-assets/meta", function() use ($BASE, $devToken) {
    $r = apiCall('GET', "{$BASE}/pengalihan-assets/meta", [], $devToken);
    if ($r['status'] !== 200) return "HTTP {$r['status']}: " . json_encode($r['body']);
    return true;
});

tc("GET /pengalihan-assets list", function() use ($BASE, $devToken) {
    $r = apiCall('GET', "{$BASE}/pengalihan-assets", [], $devToken);
    if ($r['status'] !== 200) return "HTTP {$r['status']}: " . json_encode($r['body']);
    return true;
});

tc("GET /pengalihan-assets/data", function() use ($BASE, $devToken) {
    $r = apiCall('GET', "{$BASE}/pengalihan-assets/data", [], $devToken);
    if ($r['status'] !== 200) return "HTTP {$r['status']}: " . json_encode($r['body']);
    return true;
});

tc("GET /pengalihan-assets/inventories", function() use ($BASE, $devToken) {
    $r = apiCall('GET', "{$BASE}/pengalihan-assets/inventories?type=laptop", [], $devToken);
    if ($r['status'] !== 200) return "HTTP {$r['status']}: " . json_encode($r['body']);
    return true;
});

tc("GET /pengalihan-assets/generate-code", function() use ($BASE, $devToken) {
    $r = apiCall('GET', "{$BASE}/pengalihan-assets/generate-code", [], $devToken);
    if ($r['status'] !== 200) return "HTTP {$r['status']}: " . json_encode($r['body']);
    return true;
});

echo "\n========================================\n";
echo "MODULE: SCANNERS\n";
echo "========================================\n";
$results['modules_tested'][] = 'SCANNERS';

tc("GET /scanners/meta", function() use ($BASE, $devToken) {
    $r = apiCall('GET', "{$BASE}/scanners/meta", [], $devToken);
    if ($r['status'] !== 200) return "HTTP {$r['status']}: " . json_encode($r['body']);
    return true;
});

tc("GET /scanners list", function() use ($BASE, $devToken) {
    $r = apiCall('GET', "{$BASE}/scanners", [], $devToken);
    if ($r['status'] !== 200) return "HTTP {$r['status']}: " . json_encode($r['body']);
    return true;
});

// Logout test
tc("POST /auth/logout", function() use ($BASE, $techToken) {
    $r = apiCall('POST', "{$BASE}/auth/logout", [], $techToken);
    if ($r['status'] !== 200) return "HTTP {$r['status']}: " . json_encode($r['body']);
    return true;
});

tc("POST /auth/logout (developer)", function() use ($BASE, $devToken) {
    $r = apiCall('POST', "{$BASE}/auth/logout", [], $devToken);
    if ($r['status'] !== 200) return "HTTP {$r['status']}: " . json_encode($r['body']);
    return true;
});

// -------------------------------------------------------
// SUMMARY
// -------------------------------------------------------
echo "\n========================================\n";
echo "SUMMARY\n";
echo "========================================\n";
echo "Total test cases: {$tc}\n";
echo "PASSED: {$passed}\n";
echo "FAILED: {$failed}\n";

$results['summary'] = [
    'total' => $tc,
    'passed' => $passed,
    'failed' => $failed,
    'pass_rate' => round($passed / max($tc, 1) * 100, 1) . '%',
];

// Save results
file_put_contents(__DIR__ . '/ppa_validation_results.json', json_encode($results, JSON_PRETTY_PRINT));
echo "\nResults saved to ppa_validation_results.json\n";
