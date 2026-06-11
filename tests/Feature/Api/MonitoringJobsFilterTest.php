<?php

namespace Tests\Feature\Api;

use App\Models\DailyJob;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class MonitoringJobsFilterTest extends TestCase
{
    use RefreshDatabase;

    public function test_monitoring_filter_includes_the_entire_end_date_and_changes_the_response(): void
    {
        $user = User::create([
            'name' => 'Monitoring Filter Test',
            'nrp' => 'MONITORING-FILTER-TEST',
            'password' => 'password',
            'role' => 'ict_group_leader',
            'site' => 'IPT',
        ]);
        Sanctum::actingAs($user);

        $first = $this->createJob($user, 'JOB-ONE', '2026-06-10 15:30:00');
        $second = $this->createJob($user, 'JOB-TWO', '2026-06-11 23:59:59');

        $firstResponse = $this->getJson(
            '/api/operations/monitoring-jobs?site=IPT&start_date=2026-06-10&end_date=2026-06-10'
        );

        $firstResponse
            ->assertOk()
            ->assertJsonPath('meta.filters.start_date', '2026-06-10')
            ->assertJsonPath('meta.filters.end_date', '2026-06-10')
            ->assertJsonCount(1, 'data.scheduledJobs')
            ->assertJsonPath('data.scheduledJobs.0.code', $first->code);

        $secondResponse = $this->getJson(
            '/api/operations/monitoring-jobs?site=IPT&start_date=2026-06-11&end_date=2026-06-11'
        );

        $secondResponse
            ->assertOk()
            ->assertJsonCount(1, 'data.scheduledJobs')
            ->assertJsonPath('data.scheduledJobs.0.code', $second->code);
    }

    public function test_group_leader_can_approve_visible_jobs_and_unlock_export(): void
    {
        $user = $this->createUser('ict_group_leader', 'GL-APPROVAL');
        Sanctum::actingAs($user);
        $job = $this->createJob($user, 'JOB-APPROVAL', '2026-06-11 10:00:00');
        $filter = '?site=IPT&start_date=2026-06-11&end_date=2026-06-11';

        $this->getJson("/api/operations/monitoring-jobs/export{$filter}&format=csv")
            ->assertForbidden();

        $this->postJson('/api/operations/monitoring-jobs/approve', [
            'site' => 'IPT',
            'start_date' => '2026-06-11',
            'end_date' => '2026-06-11',
        ])->assertOk();

        $this->assertDatabaseHas('daily_jobs', [
            'id' => $job->id,
            'approval_status' => 'approved',
        ]);

        $this->getJson("/api/operations/monitoring-jobs{$filter}")
            ->assertOk()
            ->assertJsonPath('meta.can_approve', true)
            ->assertJsonPath('meta.all_approved', true)
            ->assertJsonPath('meta.export_ready', true);

        $this->get("/api/operations/monitoring-jobs/export{$filter}&format=csv")
            ->assertOk();
    }

    public function test_non_group_leader_cannot_approve_jobs(): void
    {
        $user = $this->createUser('ict_technician', 'TECH-NO-APPROVAL');
        Sanctum::actingAs($user);
        $job = $this->createJob($user, 'JOB-NOT-APPROVED', '2026-06-11 10:00:00');

        $this->postJson('/api/operations/monitoring-jobs/approve', [
            'site' => 'IPT',
            'start_date' => '2026-06-11',
            'end_date' => '2026-06-11',
        ])->assertForbidden();

        $this->assertDatabaseMissing('daily_jobs', [
            'id' => $job->id,
            'approval_status' => 'approved',
        ]);

        $this->getJson('/api/operations/monitoring-jobs?site=IPT')
            ->assertOk()
            ->assertJsonPath('meta.can_approve', false);
    }

    private function createUser(string $role, string $nrp): User
    {
        return User::create([
            'name' => $nrp,
            'nrp' => $nrp,
            'password' => 'password',
            'role' => $role,
            'site' => 'IPT',
        ]);
    }

    private function createJob(User $user, string $code, string $updatedAt): DailyJob
    {
        $job = DailyJob::create([
            'code' => $code,
            'category_job' => 'assignment',
            'description' => $code,
            'site' => 'IPT',
            'category' => 'support',
            'shift' => 'pagi',
            'date' => substr($updatedAt, 0, 10),
            'status' => 'open',
            'created_by' => $user->id,
        ]);

        $job->timestamps = false;
        $job->updated_at = $updatedAt;
        $job->save();

        return $job->refresh();
    }
}
