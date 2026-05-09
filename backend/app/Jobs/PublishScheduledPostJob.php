<?php

namespace App\Jobs;

use App\Models\Schedule;
use Throwable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class PublishScheduledPostJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries = 3;

    public function __construct(
        public readonly int $scheduleId,
    ) {
        $this->onQueue('publishing');
    }

    public function handle(): void
    {
        $schedule = Schedule::query()->find($this->scheduleId);

        if (! $schedule || $schedule->status === Schedule::STATUS_PUBLISHED) {
            return;
        }

        $schedule->forceFill([
            'status' => Schedule::STATUS_PROCESSING,
            'attempts' => $schedule->attempts + 1,
            'failure_reason' => null,
        ])->save();

        try {
            // Provider integrations will replace this simulated publish result.
            $schedule->forceFill([
                'status' => Schedule::STATUS_PUBLISHED,
                'provider_post_id' => 'mock_'.$schedule->platform.'_'.$schedule->id.'_'.now()->timestamp,
                'published_at' => now(),
                'payload' => array_merge($schedule->payload ?? [], [
                    'published_by' => self::class,
                ]),
            ])->save();
        } catch (Throwable $exception) {
            $schedule->forceFill([
                'status' => Schedule::STATUS_FAILED,
                'failure_reason' => $exception->getMessage(),
            ])->save();

            throw $exception;
        }
    }

    public function failed(Throwable $exception): void
    {
        Schedule::query()
            ->whereKey($this->scheduleId)
            ->update([
                'status' => Schedule::STATUS_FAILED,
                'failure_reason' => $exception->getMessage(),
            ]);
    }
}
