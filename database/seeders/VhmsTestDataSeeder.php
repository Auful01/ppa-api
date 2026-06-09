<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * VhmsTestDataSeeder
 *
 * Generates realistic VHMS test data across all required tables:
 *   - historical_vhms_downloads  (chart / monitoring data)
 *   - kpi_vhms                   (weekly KPI breakdown records)
 *   - kpi_vhms_evidence          (evidence images)
 *   - dispatch_vhms              (master unit list status)
 *   - dispatch_repair_vhms       (repair history)
 *
 * Coverage:
 *   Sites     : BIB, BA, MIFA, MHU, AMI, HO + existing sites
 *   Statuses  : OPEN / PROGRESS / CLOSED (kpi_vhms)
 *   Severities: LOW / MEDIUM / HIGH / CRITICAL (mapped to kpi_vhms.remark)
 *   Categories: Engine, Electrical, Hydraulic, Transmission, Safety
 *   Dates     : Dec 2025 | Jan–Feb 2026 (existing) | Mar–Jun 2026 | current month
 */
class VhmsTestDataSeeder extends Seeder
{
    // ── Remark/category templates ──────────────────────────────────────────────
    private const REMARKS = [
        'Engine tidak bisa distart [Severity: HIGH] [Category: Engine]',
        'Hydraulic cylinder bocor [Severity: MEDIUM] [Category: Hydraulic]',
        'Panel elektrikal konslet [Severity: CRITICAL] [Category: Electrical]',
        'Transmisi slip pada gear 3 [Severity: MEDIUM] [Category: Transmission]',
        'Sensor safety tidak aktif [Severity: HIGH] [Category: Safety]',
        'Controller VHMS error / tidak terdeteksi [Severity: CRITICAL] [Category: Controller]',
        'Engine overheat shutdown [Severity: HIGH] [Category: Engine]',
        'Brake system tidak berfungsi [Severity: CRITICAL] [Category: Safety]',
        'AC unit tidak berfungsi [Severity: LOW] [Category: Electrical]',
        'Hydraulic pump pressure rendah [Severity: MEDIUM] [Category: Hydraulic]',
        'Fuel system leak [Severity: HIGH] [Category: Engine]',
        'Steering system berat [Severity: MEDIUM] [Category: Hydraulic]',
        'Battery lemah [Severity: LOW] [Category: Electrical]',
        'Exhaust system bocor [Severity: MEDIUM] [Category: Engine]',
        'Roda kendur / lug nut lepas [Severity: HIGH] [Category: Safety]',
    ];

    private const ROOT_CAUSES = [
        'Engine Issue - overheating karena coolant habis',
        'Hydraulic Leak - seal bocor pada cylinder hoist',
        'Electrical Fault - kabel putus di harness panel',
        'Transmission Problem - oli transmisi kotor',
        'Safety System Error - sensor proximity bermasalah',
        'Controller Malfunction - firmware outdated',
        'Engine Fault - injector tersumbat',
        'Brake Hydraulic Failure - minyak rem habis',
    ];

    private const ACTIONS = [
        'Ganti coolant dan cek water pump',
        'Replace hydraulic seal kit',
        'Repair electrical harness, ganti sekring',
        'Flush dan ganti oli transmisi',
        'Kalibrasi ulang sensor proximity',
        'Update firmware controller VHMS',
        'Bersihkan injector, ganti filter bahan bakar',
        'Isi minyak rem, bleeding system',
        'Pasang kembali dan torque lug nut',
        'Ganti hydraulic pump',
    ];

    private const WEEKS = ['Minggu 1', 'Minggu 2', 'Minggu 3', 'Minggu 4'];

    private const FEEDBACK_VALUES = [
        'UNIT_BREAKDOWN',
        'UNIT_SERVICE',
        'UNIT_STANDBY',
        'CONTROLLER_PROBLEM',
    ];

    // ── Site metadata ──────────────────────────────────────────────────────────
    private array $siteUsers = [];

    public function run(): void
    {
        $this->buildSiteUserMap();

        $this->command->info('Phase 1: Seeding historical_vhms_downloads (multi-site, multi-date)...');
        $this->seedHistoricalDownloads();

        $this->command->info('Phase 2: Seeding kpi_vhms (OPEN / PROGRESS / CLOSED)...');
        $this->seedKpiVhms();

        $this->command->info('Phase 3: Seeding kpi_vhms_evidence...');
        $this->seedKpiVhmsEvidence();

        $this->command->info('Phase 4: Updating dispatch_vhms statuses (some PROBLEM)...');
        $this->updateDispatchVhmsStatuses();

        $this->command->info('Phase 5: Seeding dispatch_repair_vhms...');
        $this->seedDispatchRepairVhms();

        $this->command->info('Phase 6: Adding feedback to historical records...');
        $this->addHistoricalFeedback();

        $this->command->info('Done. Run: php artisan db:seed --class=VhmsTestDataSeeder');
    }

    // ── Phase 1: historical_vhms_downloads ─────────────────────────────────────

    private function seedHistoricalDownloads(): void
    {
        // Date ranges to fill (Jan–Feb 2026 already exist)
        $ranges = [
            ['2025-12-01', '2025-12-31'],  // Previous year
            ['2026-03-01', '2026-03-31'],  // Q1 extension
            ['2026-04-01', '2026-04-30'],  // Q2
            ['2026-05-01', '2026-05-31'],  // Previous month
            ['2026-06-01', '2026-06-06'],  // Current month
        ];

        // Collect existing unit definitions per site (max 30 per site)
        $existingSites = ['BIB', 'BA', 'MIFA', 'MHU', 'ADW', 'IPT', 'MIP', 'PIK', 'BGE', 'SBS'];
        $unitsBySite = [];
        foreach ($existingSites as $site) {
            $unitsBySite[$site] = DB::table('historical_vhms_downloads')
                ->where('site', $site)
                ->select('sn', 'cn', 'model')
                ->distinct()
                ->limit(30)
                ->get()
                ->toArray();
        }

        // Define synthetic units for new sites
        $unitsBySite['AMI'] = $this->buildSyntheticUnits('AMI', 'A', 15, [
            'HD785-7'   => 7,
            'PC1250-8R' => 4,
            'PC2000-8'  => 4,
        ]);

        $unitsBySite['HO'] = $this->buildSyntheticUnits('HO', 'H0', 8, [
            'HD785-7'  => 5,
            'PC2000-8' => 3,
        ]);

        $chunkSize = 500;
        $rows = [];
        $totalInserted = 0;

        foreach ($ranges as [$from, $to]) {
            $cursor = Carbon::parse($from);
            $endDate = Carbon::parse($to);

            while ($cursor->lte($endDate)) {
                $dateStr   = $cursor->toDateString();
                $month     = (int) $cursor->month;
                $year      = (int) $cursor->year;
                $nowTs     = now()->toDateTimeString();

                foreach ($unitsBySite as $site => $units) {
                    foreach ($units as $unit) {
                        // Skip if record already exists for this sn+date
                        // (use lightweight existence check via prior collected dates)
                        $status = $this->randomStatus();
                        $lastDnld = $status !== 'not_update'
                            ? $cursor->copy()->subDays(rand(0, 2))->toDateString()
                            : $cursor->copy()->subDays(rand(3, 30))->toDateString();
                        $lastOp = $cursor->copy()->subDays(rand(0, 5))->toDateString();

                        $rows[] = [
                            'sn'               => is_array($unit) ? $unit['sn'] : $unit->sn,
                            'cn'               => is_array($unit) ? $unit['cn'] : $unit->cn,
                            'model'            => is_array($unit) ? $unit['model'] : $unit->model,
                            'status'           => $status,
                            'last_download'    => $lastDnld,
                            'last_operation'   => $lastOp,
                            'pld_last_record'  => $cursor->copy()->subDays(rand(0, 3))->format('Y-m-d H:i:s'),
                            'trend_last_record'=> $cursor->copy()->subDays(rand(0, 4))->format('Y-m-d H:i:s'),
                            'fault_last_record'=> $cursor->copy()->subDays(rand(0, 5))->format('Y-m-d H:i:s'),
                            'his_last_record'  => $cursor->copy()->subDays(rand(0, 3))->format('Y-m-d H:i:s'),
                            'last_histori'     => $cursor->copy()->subDays(rand(0, 2))->format('Y-m-d H:i:s'),
                            'date'             => $dateStr,
                            'month'            => $month,
                            'year'             => $year,
                            'site'             => $site,
                            'feedback'         => null,
                            'created_at'       => $nowTs,
                            'updated_at'       => $nowTs,
                        ];

                        if (count($rows) >= $chunkSize) {
                            DB::table('historical_vhms_downloads')->insert($rows);
                            $totalInserted += count($rows);
                            $rows = [];
                        }
                    }
                }

                $cursor->addDay();
            }
        }

        if (!empty($rows)) {
            DB::table('historical_vhms_downloads')->insert($rows);
            $totalInserted += count($rows);
        }

        $this->command->info("  Inserted {$totalInserted} historical_vhms_downloads records.");
    }

    /** Returns a weighted random status (realistic distribution) */
    private function randomStatus(): string
    {
        $r = rand(1, 1000);
        if ($r <= 850) return 'update';       // 85%
        if ($r <= 940) return 'waiting';      // 9%
        return 'not_update';                  // 6%
    }

    /** Build synthetic unit list for new sites */
    private function buildSyntheticUnits(string $site, string $prefix, int $total, array $modelCounts): array
    {
        $units = [];
        $idx = 1;
        foreach ($modelCounts as $model => $count) {
            for ($i = 1; $i <= $count; $i++) {
                $sn = strtoupper($prefix) . str_pad($idx, 5, '0', STR_PAD_LEFT);
                $cn = strtoupper($prefix) . str_pad($idx, 4, '0', STR_PAD_LEFT) . strtoupper($site);
                $units[] = ['sn' => $sn, 'cn' => $cn, 'model' => $model, 'site' => $site];
                $idx++;
                if ($idx > $total) break 2;
            }
        }
        return $units;
    }

    // ── Phase 2: kpi_vhms ──────────────────────────────────────────────────────

    private function seedKpiVhms(): void
    {
        // Fetch actual unit codes from dispatch_vhms
        $allUnits = DB::table('dispatch_vhms')->pluck('unit_code')->toArray();
        if (empty($allUnits)) {
            $this->command->warn('  dispatch_vhms is empty, kpi_vhms seeding skipped.');
            return;
        }

        // User names grouped loosely (any user can be PIC)
        $pics = DB::table('users')->pluck('name')->toArray();

        // Build 80 records: 20 OPEN, 20 PROGRESS, 40 CLOSED
        $distribution = [
            'OPEN'     => 20,
            'PROGRESS' => 20,
            'CLOSED'   => 40,
        ];

        // Spread across months
        $periods = [
            ['month' => 12, 'year' => 2025],
            ['month' => 1,  'year' => 2026],
            ['month' => 2,  'year' => 2026],
            ['month' => 3,  'year' => 2026],
            ['month' => 4,  'year' => 2026],
            ['month' => 5,  'year' => 2026],
            ['month' => 6,  'year' => 2026],
        ];

        $rows = [];
        $now = now()->toDateTimeString();
        $periodIdx = 0;

        foreach ($distribution as $status => $count) {
            for ($i = 0; $i < $count; $i++) {
                $period   = $periods[$periodIdx % count($periods)];
                $week     = self::WEEKS[$i % 4];
                $unitCode = $allUnits[$i % count($allUnits)];
                $remark   = self::REMARKS[$i % count(self::REMARKS)];
                $pic      = $pics[$i % count($pics)];

                $rows[] = [
                    'week_data'  => $week,
                    'unit_code'  => $unitCode,
                    'remark'     => $remark,
                    'month'      => $period['month'],
                    'year'       => $period['year'],
                    'status'     => $status,
                    'pic'        => $pic,
                    'created_by' => $pics[($i + 3) % count($pics)],
                    'created_at' => $now,
                    'updated_at' => $now,
                ];

                $periodIdx++;
            }
        }

        DB::table('kpi_vhms')->insert($rows);
        $this->command->info('  Inserted ' . count($rows) . ' kpi_vhms records.');
    }

    // ── Phase 3: kpi_vhms_evidence ─────────────────────────────────────────────

    private function seedKpiVhmsEvidence(): void
    {
        $now = now()->toDateTimeString();
        $rows = [];

        $periods = [
            ['month' => 1, 'year' => 2026, 'week' => 'Minggu 1'],
            ['month' => 2, 'year' => 2026, 'week' => 'Minggu 2'],
            ['month' => 3, 'year' => 2026, 'week' => 'Minggu 1'],
            ['month' => 4, 'year' => 2026, 'week' => 'Minggu 3'],
            ['month' => 5, 'year' => 2026, 'week' => 'Minggu 1'],
            ['month' => 6, 'year' => 2026, 'week' => 'Minggu 1'],
        ];

        foreach ($periods as $p) {
            $rows[] = [
                'week_data'      => $p['week'],
                'evidence_image' => 'storage/images/vhms_evidence_' . $p['year'] . '_' . str_pad($p['month'], 2, '0', STR_PAD_LEFT) . '.jpg',
                'month'          => $p['month'],
                'year'           => $p['year'],
                'created_at'     => $now,
                'updated_at'     => $now,
            ];
        }

        DB::table('kpi_vhms_evidence')->insert($rows);
        $this->command->info('  Inserted ' . count($rows) . ' kpi_vhms_evidence records.');
    }

    // ── Phase 4: dispatch_vhms status updates ──────────────────────────────────

    private function updateDispatchVhmsStatuses(): void
    {
        // Mark ~15 units as PROBLEM for realistic testing
        $unitIds = DB::table('dispatch_vhms')
            ->limit(15)
            ->pluck('id')
            ->toArray();

        foreach ($unitIds as $id) {
            DB::table('dispatch_vhms')
                ->where('id', $id)
                ->update(['status' => 'PROBLEM', 'updated_at' => now()]);
        }

        $this->command->info('  Updated 15 dispatch_vhms units to PROBLEM status.');
    }

    // ── Phase 5: dispatch_repair_vhms ──────────────────────────────────────────

    private function seedDispatchRepairVhms(): void
    {
        $problemUnits = DB::table('dispatch_vhms')
            ->where('status', 'PROBLEM')
            ->pluck('id')
            ->toArray();

        if (empty($problemUnits)) {
            $this->command->warn('  No PROBLEM units found, skipping dispatch_repair_vhms.');
            return;
        }

        $users = DB::table('users')->pluck('name')->toArray();
        $rows = [];
        $now = now()->toDateTimeString();

        foreach ($problemUnits as $idx => $unitId) {
            $rcIdx = $idx % count(self::ROOT_CAUSES);
            $actIdx = $idx % count(self::ACTIONS);
            $repairDate = Carbon::now()->subDays(rand(1, 60))->format('Y-m-d H:i:s');

            $rows[] = [
                'unit_vhms_id' => $unitId,
                'status_vhms'  => $idx < 5  ? 'IN_PROGRESS' : ($idx < 10 ? 'FIXED' : 'PROBLEM'),
                'root_cause'   => self::ROOT_CAUSES[$rcIdx],
                'other'        => 'Pengecekan lanjutan diperlukan',
                'action'       => self::ACTIONS[$actIdx],
                'update_by'    => $users[$idx % count($users)],
                'repair_note'  => 'Unit diperiksa oleh teknisi lapangan. ' . self::ACTIONS[$actIdx],
                'checking_by'  => $users[($idx + 2) % count($users)] . ', ' . $users[($idx + 4) % count($users)],
                'date'         => $repairDate,
                'created_at'   => $now,
                'updated_at'   => $now,
            ];
        }

        DB::table('dispatch_repair_vhms')->insert($rows);
        $this->command->info('  Inserted ' . count($rows) . ' dispatch_repair_vhms records.');
    }

    // ── Phase 6: Feedback on historical records ────────────────────────────────

    private function addHistoricalFeedback(): void
    {
        // Pick ~60 not_update records (spread across sites/dates) and assign feedback
        $targets = DB::table('historical_vhms_downloads')
            ->where('status', 'not_update')
            ->whereNull('feedback')
            ->orderByRaw('RAND()')
            ->limit(60)
            ->pluck('id')
            ->toArray();

        $feedbackDist = array_merge(
            array_fill(0, 25, 'UNIT_BREAKDOWN'),
            array_fill(0, 15, 'UNIT_SERVICE'),
            array_fill(0, 12, 'UNIT_STANDBY'),
            array_fill(0, 8,  'CONTROLLER_PROBLEM'),
        );

        foreach ($targets as $idx => $id) {
            DB::table('historical_vhms_downloads')
                ->where('id', $id)
                ->update([
                    'feedback'   => $feedbackDist[$idx % count($feedbackDist)],
                    'updated_at' => now(),
                ]);
        }

        $this->command->info('  Assigned feedback to ' . count($targets) . ' historical records.');
    }

    // ── Helpers ────────────────────────────────────────────────────────────────

    private function buildSiteUserMap(): void
    {
        $users = DB::table('users')->select('name', 'site')->get();
        foreach ($users as $u) {
            $this->siteUsers[$u->site][] = $u->name;
        }
    }
}
