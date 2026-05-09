<?php

namespace Tests\Unit;

use App\Jobs\PublishScheduledPostJob;
use App\Models\Schedule;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublishScheduledPostJobTest extends TestCase
{
    use RefreshDatabase;

    public function test_publish_job_marks_schedule_as_published(): void
    {
        $schedule = Schedule::factory()->create([
            'status' => Schedule::STATUS_QUEUED,
            'attempts' => 0,
        ]);

        (new PublishScheduledPostJob($schedule->id))->handle();

        $schedule->refresh();

        $this->assertSame(Schedule::STATUS_PUBLISHED, $schedule->status);
        $this->assertSame(1, $schedule->attempts);
        $this->assertNotNull($schedule->provider_post_id);
        $this->assertNotNull($schedule->published_at);
    }
}
