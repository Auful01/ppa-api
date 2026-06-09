# UAT Role Matrix
**Date:** 2026-06-05  
**Source of Truth:** `ppa-api-old` (Laravel 11 + Inertia.js)

---

## 1. Role Definitions

| Role | `canAccessAnySite` | Write Access | Approve Jobs | Special |
|------|--------------------|--------------|--------------|---------|
| `ict_developer` | ✅ Yes | ✅ Yes | ✅ Yes | Super-admin; bypasses site filter |
| `ict_ho` | ✅ Yes (`site=HO`) | ✅ Yes | ✅ Yes | HO full access |
| `ict_section_head` | ❌ No | ✅ Yes (own site) | ❌ No | |
| `ict_group_leader` | ❌ No | ✅ Yes (own site) | ✅ Yes | Can create + approve jobs |
| `ict_admin` | ❌ No | ✅ Yes (own site) | ❌ No | |
| `ict_technician` | ❌ No | ✅ Yes (own site) | ❌ No | |
| `ict_bod` | ❌ No (`site=HO`) | ❌ No | ❌ No | Read-only at HO |
| `soc_ho` | ❌ No (`site=HO`) | ❌ No | ❌ No | SOC tickets only |
| `guest` | ❌ No | ❌ No | ❌ No | Restricted view |

---

## 2. Site Access Matrix

`canAccessAnySite = true` means the user can read/write data from any site when passing `?site=X` parameter.

| Role | Own Site Access | Cross-Site Read | Cross-Site Write |
|------|----------------|-----------------|------------------|
| `ict_developer` | ✅ | ✅ | ✅ |
| `ict_ho` | HO only | ✅ | ✅ |
| `ict_section_head` | ✅ | ❌ | ❌ |
| `ict_group_leader` | ✅ | ❌ | ❌ |
| `ict_admin` | ✅ | ❌ | ❌ |
| `ict_technician` | ✅ | ❌ | ❌ |
| `ict_bod` | HO only | ❌ | ❌ |
| `soc_ho` | HO only | ❌ | ❌ |
| `guest` | ❌ | ❌ | ❌ |

---

## 3. Module Access Matrix

### Web (Inertia) — controlled by `CheckRole:role:SITE` middleware

| Module | `ict_developer` | `ict_ho` | `ict_group_leader` | `ict_technician` | `ict_admin` | `ict_bod` | `soc_ho` | `guest` |
|--------|:---------------:|:--------:|:------------------:|:----------------:|:-----------:|:---------:|:--------:|:-------:|
| Dashboard | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ (SOC) | ❌ |
| Aduan | ✅ | ✅ | ✅ | ✅ | ✅ | ❌ | ✅ (SOC filter) | ❌ |
| Inventory | ✅ | ✅ | ✅ | ✅ | ✅ | ❌ | ❌ | ❌ |
| Inspection | ✅ | ✅ | ✅ | ✅ | ✅ | ❌ | ❌ | ❌ |
| PICA Inspeksi | ✅ | ✅ | ✅ | ✅ | ✅ | ❌ | ❌ | ❌ |
| Operations (Jobs) | ✅ | ✅ | ✅ | ✅ | ✅ | ❌ | ❌ | ❌ |
| KPI VHMS | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ❌ | ❌ |
| KPI Aduan | ✅ | ✅ | ✅ | ✅ | ✅ | ❌ | ❌ | ❌ |
| Pengalihan Asset | ✅ | ✅ | ✅ | ✅ | ❌ | ❌ | ❌ | ❌ |
| Site Switcher | ✅ | ✅ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ |

### API — controlled by `auth:sanctum` + `SiteContext::authorizeWrite()`

| Module | Read (any auth) | Write (`authorizeWrite`) | Notes |
|--------|:--------------:|:------------------------:|-------|
| Dashboard | ✅ | N/A | No write operations |
| Aduan | ✅ | ✅ (`ict_*` roles + section_head) | `soc_ho`/`bod`/`guest` can only read |
| Inventory | ✅ | ✅ | Same as aduan |
| Inspection | ✅ | ✅ | Same as aduan |
| PICA | ✅ | ✅ | Same as aduan |
| Operations Jobs | ✅ (limited to crew) | ✅ create: `ict_developer`/`ict_group_leader` | Non-crew members see only own jobs |
| Operations Approve | N/A | ✅ `ict_developer`/`ict_group_leader` only | |
| KPI VHMS | ✅ | ✅ | |
| Pengalihan Asset | ✅ | ✅ | |
| Sites | `ict_ho`/`ict_developer` only | N/A | |
| Dashboard All-Site | `ict_ho`/`ict_developer` only | N/A | |

---

## 4. Operations Job Visibility Rules

| Condition | Jobs Visible |
|-----------|-------------|
| Role is `ict_developer` OR `ict_group_leader` | All jobs at resolved site |
| Any other authenticated role | Only jobs where user's NRP is in the `crew` JSON array |

---

## 5. Site Switcher Access (Flutter)

The `AuthService.canSwitchSite` flag governs whether the Flutter site switcher UI is enabled.

| Role | `canSwitchSite` |
|------|:--------------:|
| `ict_ho` | ✅ |
| `ict_developer` | ✅ |
| All others | ❌ |

---

## 6. Token Abilities

At login, `AuthApiController::login()` creates a Sanctum token with ability `role:<role_name>`.  
Example: a user with `role = ict_technician` gets token ability `role:ict_technician`.

The API does not use token abilities for authorization checks — it uses `$request->user()->role` directly. Token abilities are stored for future use.

---

## 7. Web Route Group Examples

The web application routes use `checkRole:role:SITE` middleware. Each site has its own route group. Example for site `AMI`:

```
Route::middleware('checkRole:ict_technician:AMI,ict_group_leader:AMI,...')
    ->prefix('AMI')
    ->group(function() {
        // All AMI routes
    })
```

The `CheckRole` middleware checks: `$userRole === $role && $userSite === $site`. Only exact role+site pairs are accepted.

`ict_developer` and `ict_ho` are granted access to routes on all sites via separate route groups at the beginning of `web.php`.

---

## 8. Known Role Gaps (Flutter vs API)

| Gap | Severity | Status |
|-----|----------|--------|
| Flutter has no role-based menu visibility | MEDIUM | Open — all 25+ menus shown to all roles |
| Site switcher only shown to `ict_ho`/`ict_developer` (correct) | OK | ✅ Implemented correctly in `AuthService.canSwitchSite` |
| `soc_ho` cannot create/update aduan via API | N/A | Expected — SOC is read-only per web behavior |
| `ict_bod` cannot create/update via API | N/A | Expected — BOD is read-only per web behavior |
