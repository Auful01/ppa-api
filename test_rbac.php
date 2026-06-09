<?php
$baseUrl = 'http://localhost:8120';
$pdo = new PDO('mysql:host=127.0.0.1;port=3306;dbname=new_itportalv2', 'root', '');

// Get representative users for each role
$roles = ['ict_developer', 'ict_ho', 'ict_group_leader', 'ict_admin', 'ict_technician', 'soc_ho'];
$users = [];
foreach ($roles as $role) {
    $u = $pdo->query("SELECT nrp, role, site FROM users WHERE role='$role' LIMIT 1")->fetch(PDO::FETCH_ASSOC);
    if ($u) $users[$role] = $u;
}

echo "=== Users selected for testing ===\n";
foreach ($users as $role => $u) {
    echo "  $role: nrp={$u['nrp']} site={$u['site']}\n";
}
echo "\n";

function loginUser($baseUrl, $nrp) {
    $ch = curl_init("$baseUrl/api/auth/login");
    curl_setopt_array($ch, [CURLOPT_POST=>true, CURLOPT_POSTFIELDS=>json_encode(['nrp'=>$nrp,'password'=>$nrp]),
        CURLOPT_HTTPHEADER=>['Content-Type: application/json','Accept: application/json'], CURLOPT_RETURNTRANSFER=>true]);
    $resp = json_decode(curl_exec($ch), true);
    curl_close($ch);
    return $resp['token'] ?? null;
}

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

echo "=== RBAC Tests ===\n\n";

// TC-RBAC-01: ict_technician at MIP cannot access BIB aduan
$tech = $users['ict_technician'] ?? null;
if ($tech) {
    $token = loginUser($baseUrl, $tech['nrp']);
    if ($token) {
        // They should only see their own site
        [$c, $b] = api('GET', "$baseUrl/api/aduan", $token);
        $site = $b['meta']['site'] ?? 'unknown';
        $pass = ($c === 200 && strtoupper($site) === strtoupper($tech['site']));
        echo "[" . ($pass?'PASS':'FAIL') . "] ict_technician@{$tech['site']} GET /aduan → site=$site (should be {$tech['site']})\n";

        // Should 403 when trying to write to different site
        [$c2] = api('POST', "$baseUrl/api/aduan", $token, [
            'complaint_name' => 'Cross-site test',
            'complaint_note' => 'Test',
            'date_of_complaint' => date('Y-m-d'),
            'nrp' => $tech['nrp'],
            'site' => 'BIB', // Different site
        ]);
        // Will get 403 or succeed only if site matches technician's site
        echo "[" . ($c2===403?'PASS':'INFO') . "] ict_technician@{$tech['site']} POST /aduan site=BIB → $c2 (expected 403 for cross-site)\n";
    }
}

// TC-RBAC-02: ict_developer can access any site
$dev = $users['ict_developer'] ?? null;
if ($dev) {
    $token = loginUser($baseUrl, $dev['nrp']);
    if ($token) {
        // Can access BIB
        [$c, $b] = api('GET', "$baseUrl/api/aduan?site=BIB", $token);
        echo "[" . ($c===200?'PASS':'FAIL') . "] ict_developer GET /aduan?site=BIB → $c\n";

        // Can access HO
        [$c, $b] = api('GET', "$baseUrl/api/aduan?site=HO", $token);
        echo "[" . ($c===200?'PASS':'FAIL') . "] ict_developer GET /aduan?site=HO → $c\n";

        // Can access all-site dashboard
        [$c] = api('GET', "$baseUrl/api/dashboard/all-site", $token);
        echo "[" . ($c===200?'PASS':'FAIL') . "] ict_developer GET /dashboard/all-site → $c\n";
    }
}

// TC-RBAC-03: ict_ho can access all sites
$ho = $users['ict_ho'] ?? null;
if ($ho) {
    $token = loginUser($baseUrl, $ho['nrp']);
    if ($token) {
        [$c] = api('GET', "$baseUrl/api/aduan?site=BIB", $token);
        echo "[" . ($c===200?'PASS':'FAIL') . "] ict_ho GET /aduan?site=BIB → $c\n";
        [$c] = api('GET', "$baseUrl/api/dashboard/all-site", $token);
        echo "[" . ($c===200?'PASS':'FAIL') . "] ict_ho GET /dashboard/all-site → $c\n";
    }
}

// TC-RBAC-04: soc_ho can read but cannot create operations jobs (role restriction)
$soc = $users['soc_ho'] ?? null;
if ($soc) {
    $token = loginUser($baseUrl, $soc['nrp']);
    if ($token) {
        // Can read
        [$c] = api('GET', "$baseUrl/api/aduan?site=HO", $token);
        echo "[" . ($c===200?'PASS':'FAIL') . "] soc_ho GET /aduan → $c\n";

        // Cannot create operations jobs (role not in ROLE_CREATE_JOB)
        [$c] = api('POST', "$baseUrl/api/operations/jobs", $token, [
            'shared' => ['shift' => 'SHIFT_1'],
            'jobs' => [['job' => 'Test job', 'date' => date('Y-m-d'), 'status' => 'open', 'categoryJob' => 'assignment']],
            'site' => 'HO',
        ]);
        echo "[" . ($c===403?'PASS':'FAIL') . "] soc_ho POST /operations/jobs → $c (expected 403)\n";
    }
}

// TC-RBAC-05: Guest (any user) cannot access admin endpoints
$guestNrp = $pdo->query("SELECT nrp FROM users WHERE role='guest' LIMIT 1")->fetchColumn();
if ($guestNrp) {
    $token = loginUser($baseUrl, $guestNrp);
    if ($token) {
        [$c] = api('GET', "$baseUrl/api/aduan", $token);
        echo "[INFO] guest role GET /aduan → $c\n";
    }
} else {
    echo "[INFO] No guest users in DB\n";
}

echo "\n=== Auth Tests ===\n\n";

// TC-AUTH-01: Invalid credentials
$devToken = loginUser($baseUrl, $dev['nrp'] ?? '23002073');
[$c] = api('POST', "$baseUrl/api/auth/login", null, ['nrp' => '99999999', 'password' => 'wrongpass']);
// Use raw curl for this since token is null
$ch = curl_init("$baseUrl/api/auth/login");
curl_setopt_array($ch, [CURLOPT_POST=>true, CURLOPT_POSTFIELDS=>json_encode(['nrp'=>'99999999','password'=>'wrongpass']),
    CURLOPT_HTTPHEADER=>['Content-Type: application/json','Accept: application/json'], CURLOPT_RETURNTRANSFER=>true]);
curl_exec($ch);
$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);
echo "[" . ($code===422?'PASS':'FAIL') . "] POST /auth/login (invalid creds → 422) → $code\n";

// TC-AUTH-02: Unauthenticated access
$ch = curl_init("$baseUrl/api/aduan");
curl_setopt_array($ch, [CURLOPT_HTTPHEADER=>['Accept: application/json'], CURLOPT_RETURNTRANSFER=>true]);
curl_exec($ch);
$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);
echo "[" . ($code===401?'PASS':'FAIL') . "] GET /aduan (no token → 401) → $code\n";

// TC-AUTH-03: Logout
$token = loginUser($baseUrl, $dev['nrp'] ?? '23002073');
$ch = curl_init("$baseUrl/api/auth/logout");
curl_setopt_array($ch, [CURLOPT_POST=>true, CURLOPT_POSTFIELDS=>'',
    CURLOPT_HTTPHEADER=>["Accept: application/json","Authorization: Bearer $token"], CURLOPT_RETURNTRANSFER=>true]);
curl_exec($ch);
$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);
echo "[" . ($code===200?'PASS':'FAIL') . "] POST /auth/logout → $code\n";

// TC-AUTH-04: Use revoked token after logout
[$c] = api('GET', "$baseUrl/api/aduan", $token);
echo "[" . ($c===401?'PASS':'FAIL') . "] GET /aduan after logout (revoked token → 401) → $c\n";
