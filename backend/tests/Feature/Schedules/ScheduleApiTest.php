<?php

namespace Tests\Feature\Schedules;

use App\Jobs\PublishScheduledPostJob;
use App\Models\Schedule;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class ScheduleApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_queue_a_schedule_for_their_tenant(): void
    {
        Queue::fake();

        $tenant = Tenant::factory()->create();
        $user = User::factory()->create([
            'tenant_id' => $tenant->id,
        ]);

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/v1/schedules', [
                'title' => 'Friday campaign',
                'content' => 'Launch post for the weekend campaign.',
                'platform' => 'facebook',
                'scheduled_at' => now()->addHour()->toISOString(),
                'timezone' => 'UTC',
                'media_urls' => [],
            ]);

        $response->assertCreated()
            ->assertJsonPath('message', 'Schedule queued successfully.')
            ->assertJsonPath('data.schedule.status', Schedule::STATUS_QUEUED)
            ->assertJsonPath('data.schedule.platform', 'facebook');

        $this->assertDatabaseHas('schedules', [
            'tenant_id' => $tenant->id,
            'platform' => 'facebook',
            'status' => Schedule::STATUS_QUEUED,
        ]);

        Queue::assertPushed(PublishScheduledPostJob::class);
    }

    public function test_user_can_list_only_their_tenant_schedules(): void
    {
        $tenant = Tenant::factory()->create();
        $otherTenant = Tenant::factory()->create();
        $user = User::factory()->create([
            'tenant_id' => $tenant->id,
        ]);

        $ownSchedule = Schedule::factory()->create([
            'tenant_id' => $tenant->id,
            'created_by_user_id' => $user->id,
        ]);
        Schedule::factory()->create([
            'tenant_id' => $otherTenant->id,
        ]);

        $response = $this->actingAs($user, 'sanctum')
            ->getJson('/api/v1/schedules');

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $ownSchedule->id);
    }

    public function test_user_cannot_access_another_tenant_schedule(): void
    {
        $tenant = Tenant::factory()->create();
        $otherTenant = Tenant::factory()->create();
        $user = User::factory()->create([
            'tenant_id' => $tenant->id,
        ]);
        $schedule = Schedule::factory()->create([
            'tenant_id' => $otherTenant->id,
        ]);

        $this->actingAs($user, 'sanctum')
            ->getJson("/api/v1/schedules/{$schedule->id}")
            ->assertForbidden();
    }

    public function test_failed_schedule_can_be_retried(): void
    {
        Queue::fake();

        $tenant = Tenant::factory()->create();
        $user = User::factory()->create([
            'tenant_id' => $tenant->id,
        ]);
        $schedule = Schedule::factory()->failed()->create([
            'tenant_id' => $tenant->id,
            'created_by_user_id' => $user->id,
        ]);

        $response = $this->actingAs($user, 'sanctum')
            ->postJson("/api/v1/schedules/{$schedule->id}/retry");

        $response->assertOk()
            ->assertJsonPath('message', 'Failed schedule queued for retry.')
            ->assertJsonPath('data.schedule.status', Schedule::STATUS_QUEUED)
            ->assertJsonPath('data.schedule.failure_reason', null);

        Queue::assertPushed(PublishScheduledPostJob::class);
    }
}
