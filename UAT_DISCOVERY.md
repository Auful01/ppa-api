# UAT Discovery Report
**Date:** 2026-06-05  
**Source of Truth:** `ppa-api-old` (Laravel 11 + Inertia.js)  
**Mobile API:** `ppa-api-old/routes/api.php`  
**Flutter App:** `ppa_apps`

---

## 1. System Overview

| Component | Stack |
|-----------|-------|
| Web (Source of Truth) | Laravel 11, Inertia.js (Vue 3), Sanctum sessions |
| Mobile API | Laravel 11, Sanctum token auth (`auth:sanctum`) |
| Mobile App | Flutter (Dart), HTTP via `ApiClient` |

---

## 2. Module Inventory

| Module | Web Controller(s) | API Controller(s) | Flutter Screen(s) |
|--------|-------------------|-------------------|-------------------|
| **Auth** | `AuthController` | `AuthApiController` | `LoginScreen`, `SplashScreen` |
| **Dashboard** | Site-specific controllers | `DashboardApiController`, `DashboardAllSiteApiController` | `DashboardPage` |
| **Aduan** | `AduanAmiController` (+ other site controllers) | `AduanApiController` | `AduanPage` |
| **Inventory** | Site-specific per type | `InventoryApiController` | `InventoryPages` (per type) |
| **Inspection** | Site-specific per type | `InspectionApiController` | `InspectionPages` |
| **PICA Inspeksi** | Site-specific | `PicaInspeksiApiController` | `PicaInspeksiPage` |
| **Operations** | `DailyJobController`, `UnscheduleJobController` | `OperationsApiController` | `OperationPages` |
| **KPI VHMS** | `KpiVhmsController` | `KpiVhmsApiController` | `KpiVhmsPage` |
| **KPI Aduan Analysis** | `AduanController` | `KpiAduanAnalysisApiController` | `KpiInspeksiPage` |
| **Chart Inspeksi** | Web views | `ChartInspeksiApiController` | `ChartInspeksiPage` |
| **Pengalihan Asset** | `PengalihanAssetController` | `PengalihanAssetApiController` | `PengalihanPage` |
| **Scanner** | Dedicated | `ScannerApiController` | `ScannerPage` |
| **Inspection Schedule** | Dedicated | `InspectionScheduleApiController` | `JadwalInspeksiPage` |
| **Departments** | Dedicated | `DepartmentApiController` | (meta usage) |
| **Sites** | N/A (web uses session) | `SiteApiController` | Site switcher |

---

## 3. Inventory Type Registry

9 types, all accessed via `/api/inventory/{type}`:

| Type | Model | Code Column | Has Inspection | Soft Delete |
|------|-------|-------------|----------------|-------------|
| `access-point` | `InvAp` | `inventory_number` | No | Yes |
| `cctv` | `InvCctv` | `cctv_code` | No | Yes |
| `computer` | `InvComputer` | `computer_code` | Yes (`InspeksiComputer`) | Yes |
| `laptop` | `InvLaptop` | `laptop_code` | Yes (`InspeksiLaptop`) | Yes |
| `mobile-tower` | `InvMobileTower` | `mt_code` | Yes (`InspeksiMobileTower`) | Yes |
| `printer` | `InvPrinter` | `printer_code` | Yes (`InspeksiPrinter`) | Yes |
| `scanner` | `InvScanner` | `scanner_code` | No | No |
| `switch` | `InvSwitch` | `inventory_number` | No | Yes |
| `wireless` | `InvWirelless` | `inventory_number` | No | Yes |

**Key:** `InvMobileTower` uses `bigint AUTO_INCREMENT` PK (not UUID). All others with SoftDeletes use UUID string PKs.

---

## 4. Inspection Type Registry

4 types, accessed via `/api/inspections/{type}`:

| Type | Model | Inventory FK | Inventory Model |
|------|-------|-------------|-----------------|
| `computer` | `InspeksiComputer` | `inv_computer_id` | `InvComputer` |
| `laptop` | `InspeksiLaptop` | `inv_laptop_id` | `InvLaptop` |
| `mobile-tower` | `InspeksiMobileTower` | `inv_mt_id` | `InvMobileTower` |
| `printer` | `InspeksiPrinter` | `inv_printer_id` | `InvPrinter` |

---

## 5. Role Inventory

| Role | Site Constraint | Description |
|------|----------------|-------------|
| `ict_developer` | Any site | Full access; `canAccessAnySite = true` |
| `ict_ho` | Must be `site = HO` | HO full access; `canAccessAnySite = true` |
| `ict_bod` | Must be `site = HO` | Board-level read (HO) |
| `ict_section_head` | Any site | Section-level manager |
| `ict_group_leader` | Site-specific | Site team lead; can approve jobs |
| `ict_admin` | Site-specific | Site admin |
| `ict_technician` | Site-specific | Site technician |
| `soc_ho` | Must be `site = HO` | SOC (Security Operations Center) HO |
| `guest` | Restricted | Read-only guest |

---

## 6. Site Inventory

| Site Code | Type | Notes |
|-----------|------|-------|
| `HO` | Head Office | Central hub; `isHo()` returns true for HO, null, or '' |
| `BIB` | Operations Site | |
| `BA` | Operations Site | |
| `MIFA` | Operations Site | |
| `MHU` | Operations Site | |
| `AMI` | Operations Site | |
| `ADW` | Operations Site | |
| `PIK` | Operations Site | |
| `IPT` | Operations Site | |
| `MLP` | Operations Site | |
| `MIP` | Operations Site | |
| `VIB` | Operations Site | |
| `SBS` | Operations Site | |
| `SKS` | Operations Site | |
| `BGE` | Operations Site | |

---

## 7. Permission Matrix (API Write Operations)

| Role | `SiteContext::authorizeWrite()` | Site Scope | Notes |
|------|---------------------------------|------------|-------|
| `ict_developer` | ✅ Allowed | Any | `canAccessAnySite = true` |
| `ict_ho` | ✅ Allowed | Any (`isHo = true`) | `canAccessAnySite = true` |
| `ict_section_head` | ✅ Allowed | Own site only | |
| `ict_group_leader` | ✅ Allowed | Own site only | Can also approve jobs |
| `ict_admin` | ✅ Allowed | Own site only | |
| `ict_technician` | ✅ Allowed | Own site only | |
| `ict_bod` | ❌ Blocked | N/A | Read only |
| `soc_ho` | ❌ Blocked | N/A | SOC read only via API |
| `guest` | ❌ Blocked | N/A | |

---

## 8. API Endpoint Matrix

### Auth
| Method | Endpoint | Auth | Description |
|--------|----------|------|-------------|
| POST | `/api/auth/login` | None | Login; returns Sanctum token |
| GET | `/api/auth/me` | Sanctum | Get current user |
| POST | `/api/auth/logout` | Sanctum | Revoke token |

### Sites
| Method | Endpoint | Auth | Description |
|--------|----------|------|-------------|
| GET | `/api/sites` | Sanctum | List sites (`ict_ho`/`ict_developer` only) |

### Dashboard
| Method | Endpoint | Auth | Description |
|--------|----------|------|-------------|
| GET | `/api/dashboard` | Sanctum | Per-site dashboard (complaints, inventory, inspection stats) |
| GET | `/api/dashboard/all-site` | Sanctum | HO/dev only; cross-site aggregated dashboard |
| GET | `/api/chart-inspeksi` | Sanctum | Inspection achievement chart data |

### Aduan
| Method | Endpoint | Auth | Description |
|--------|----------|------|-------------|
| GET | `/api/aduan/meta` | Sanctum | Categories, crew, ticket code |
| GET | `/api/aduan` | Sanctum | List (paginated, filterable by status/category) |
| POST | `/api/aduan` | Sanctum | Create; sets `urgency=NORMAL`, `status=OPEN` |
| GET | `/api/aduan/{id}` | Sanctum | Detail + root cause options |
| PATCH | `/api/aduan/{id}` | Sanctum | General edit (name, note, location, etc.) |
| PATCH | `/api/aduan/{id}/accept` | Sanctum | Accept → sets `status=PROGRESS`, records `response_time` |
| PATCH | `/api/aduan/{id}/progress` | Sanctum | Update progress fields; triggers `PerangkatBreakdown` on CLOSED |
| PATCH | `/api/aduan/{id}/urgency` | Sanctum | Toggle NORMAL/URGENT |
| DELETE | `/api/aduan/{id}` | Sanctum | Soft delete |

### Inventory
| Method | Endpoint | Auth | Description |
|--------|----------|------|-------------|
| GET | `/api/inventory/{type}/meta` | Sanctum | Departments + site users |
| GET | `/api/inventory/{type}` | Sanctum | Paginated list for type |
| POST | `/api/inventory/{type}` | Sanctum | Create inventory record |
| GET | `/api/inventory/{type}/{id}` | Sanctum | Detail |
| PUT/PATCH | `/api/inventory/{type}/{id}` | Sanctum | Update |
| DELETE | `/api/inventory/{type}/{id}` | Sanctum | Soft delete |

### Inspection
| Method | Endpoint | Auth | Description |
|--------|----------|------|-------------|
| GET | `/api/inspections/{type}` | Sanctum | List; filterable by year/month/triwulan/inspection_status |
| GET | `/api/inspections/{type}/{id}` | Sanctum | Detail |
| PUT/PATCH | `/api/inspections/{type}/{id}` | Sanctum | Update; propagates `inventory_status` to parent inventory |

### PICA Inspeksi
| Method | Endpoint | Auth | Description |
|--------|----------|------|-------------|
| GET | `/api/pica-inspeksi/meta` | Sanctum | Site crew list |
| GET | `/api/pica-inspeksi` | Sanctum | List by device_type; supports date/status filter |
| GET | `/api/pica-inspeksi/{id}` | Sanctum | Detail with device info |
| PUT/PATCH/POST | `/api/pica-inspeksi/{id}` | Sanctum | Update + creates/updates `PicaInspeksi` record |

### Operations
| Method | Endpoint | Auth | Description |
|--------|----------|------|-------------|
| GET | `/api/operations/jobs/meta` | Sanctum | Crew, sarana, shift options |
| GET | `/api/operations/jobs` | Sanctum | Job list (assignment category) |
| POST | `/api/operations/jobs` | Sanctum | Create job (requires `ROLE_CREATE_JOB`) |
| GET | `/api/operations/jobs/{code}` | Sanctum | Job detail |
| PUT/PATCH | `/api/operations/jobs/{code}` | Sanctum | Update job |
| DELETE | `/api/operations/jobs/{code}` | Sanctum | Delete job |
| PATCH | `/api/operations/jobs/{code}/approve` | Sanctum | Approve single job |
| POST | `/api/operations/monitoring-jobs/approve` | Sanctum | Batch approve |
| GET | `/api/operations/monitoring-jobs` | Sanctum | Monitoring list |
| GET | `/api/operations/monitoring-jobs/export` | Sanctum | Export PDF/Excel |
| GET | `/api/operations/unschedule-jobs/meta` | Sanctum | Unschedule meta |
| GET | `/api/operations/unschedule-jobs/problems` | Sanctum | Problem categories |
| GET | `/api/operations/unschedule-jobs` | Sanctum | Unschedule list |
| POST | `/api/operations/unschedule-jobs` | Sanctum | Create unschedule job |
| GET | `/api/operations/unschedule-jobs/{code}` | Sanctum | Detail |
| PUT/PATCH | `/api/operations/unschedule-jobs/{code}` | Sanctum | Update |
| DELETE | `/api/operations/unschedule-jobs/{code}` | Sanctum | Delete |

### Pengalihan Asset
| Method | Endpoint | Auth | Description |
|--------|----------|------|-------------|
| GET | `/api/pengalihan-assets` | Sanctum | Crew list for site |
| GET | `/api/pengalihan-assets/data` | Sanctum | Transfer history |
| GET | `/api/pengalihan-assets/meta` | Sanctum | Departments + users |
| GET | `/api/pengalihan-assets/inventories` | Sanctum | Available inventories by dept/type |
| GET | `/api/pengalihan-assets/inventory-detail` | Sanctum | Inventory detail for transfer |
| GET | `/api/pengalihan-assets/user-by-nrp` | Sanctum | Look up user by NRP |
| GET | `/api/pengalihan-assets/generate-code` | Sanctum | Generate next inventory code |
| POST | `/api/pengalihan-assets` | Sanctum | Create transfer (creates new inv, soft-deletes old) |

### KPI & Scanner
| Method | Endpoint | Auth | Description |
|--------|----------|------|-------------|
| GET | `/api/kpi-vhms` | Sanctum | VHMS list |
| GET | `/api/kpi-vhms/filter` | Sanctum | Filtered VHMS |
| PATCH | `/api/kpi-vhms/feedback` | Sanctum | Update feedback |
| POST | `/api/kpi-vhms` | Sanctum | Create VHMS entry |
| GET | `/api/kpi-vhms/breakdown` | Sanctum | Breakdown stats |
| GET | `/api/kpi-vhms/summary` | Sanctum | Summary stats |
| GET | `/api/kpi-aduan-analysis/chart` | Sanctum | Aduan KPI chart |
| GET | `/api/kpi-aduan-analysis/details` | Sanctum | Aduan KPI details |
| GET | `/api/inspection-schedules` | Sanctum | Inspection schedule list |
| PUT/PATCH | `/api/inspection-schedules/{id}` | Sanctum | Update schedule |
| GET | `/api/scanners/meta` | Sanctum | Scanner meta |
| GET | `/api/scanners/generate-code` | Sanctum | Generate scanner code |
| GET | `/api/scanners` | Sanctum | Scanner list |
| POST | `/api/scanners` | Sanctum | Create scanner |
| GET | `/api/scanners/{id}` | Sanctum | Scanner detail |
| PUT/PATCH | `/api/scanners/{id}` | Sanctum | Update scanner |
| DELETE | `/api/scanners/{id}` | Sanctum | Delete scanner |
| GET | `/api/departments` | Sanctum | Department list |

---

## 9. Business Rules Summary

### Authentication
- Sanctum token auth for API; ability `role:<role_name>` set on token at login
- `SiteContext::canAccessAnySite()`: role is `ict_developer` OR `ict_ho` OR `site = 'HO'`
- Site-restricted users can only read/write their own site

### Aduan Lifecycle
- Create: status=OPEN, urgency=NORMAL
- Accept → status=PROGRESS, records `start_response` + calculates `response_time`
- UpdateProgress → updates fields; on CLOSED triggers `PerangkatBreakdown` side effect
- PerangkatBreakdown only created for categories with valid root cause mappings (PC/NB, TELKOMSEL, NETWORK, SERVER, CCTV, PRINTER, NETWORK MT, GPS)
- SoftDeletes: deleted records excluded from all queries via `whereNull('deleted_at')`

### Inventory Lifecycle
- Status values: `READY_USED`, `READY_STANDBY`, `BREAKDOWN`, `SCRAP`
- UUID PKs on all inventory models except `InvMobileTower` (bigint AUTO_INCREMENT)
- SoftDeletes on: Aduan, InvLaptop, InvComputer, InvPrinter, InvSwitch, InvAp, InvCctv, InvWirelless

### Pengalihan Asset (Transfer)
- Creates `PengalihanAsset` record
- Creates NEW inventory entry with same specs and new user assignment
- Soft-deletes the PREVIOUS inventory entry
- Both Laptop and Computer supported; image stored as full URL

### Inspection Update
- `inventory_status` field propagated back to parent inventory record when updated
- HO dashboard shows `laptop` + `komputer` inspection achievements
- Site dashboard shows all 7 inspection types including tower/panel_box_network (no site filter)

### Operations
- Jobs statuses: `open`, `continue`, `closed`, `outstanding`, `cancel`
- Non-privileged users only see jobs where they appear in `crew` JSON array
- Only `ict_developer` or `ict_group_leader` can create/approve jobs
- Export requires all visible jobs to be approved first

---

## 10. Flutter ↔ API Field Mapping Notes

| Area | Flutter Key | API Key | Notes |
|------|-------------|---------|-------|
| Aduan edit | `location_detail` | `detail_location` | Mapped in `AduanApiController::update()` |
| Operation status | `continue` | `continue` | Fixed (was `progress`) |
| Dashboard inventory | `computer`/`komputer` | `computer` | Flutter handles both spellings |
| Aduan status | lowercase | uppercase | API does `strtoupper()` on filter; Flutter normalizes to title case |
| Image URLs | full URL expected | full URL stored | All API controllers now use `url('storage/...')` |
