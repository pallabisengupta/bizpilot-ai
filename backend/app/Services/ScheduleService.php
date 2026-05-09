<?php

namespace App\Services;

use App\Jobs\PublishScheduledPostJob;
use App\Models\Schedule;
use App\Models\Tenant;
use App\Models\User;
use App\Repositories\ScheduleRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;

class ScheduleService
{
    public function __construct(
        private readonly ScheduleRepository $schedules,
    ) {
    }

    public function list(Tenant $tenant, array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->schedules->paginateForTenant($tenant, $filters, $perPage);
    }

    public function create(Tenant $tenant, User $user, array $data): Schedule
    {
        $schedule = $this->schedules->create(array_merge($data, [
            'tenant_id' => $tenant->id,
            'created_by_user_id' => $user->id,
            'status' => Schedule::STATUS_QUEUED,
            'attempts' => 0,
            'payload' => [
                'platform' => $data['platform'],
                'content' => $data['content'],
                'media_urls' => $data['media_urls'] ?? [],
            ],
        ]));

        $this->dispatchPublishJob($schedule);

        return $schedule;
    }

    public function update(Schedule $schedule, array $data): Schedule
    {
        if (! in_array($schedule->status, [Schedule::STATUS_QUEUED, Schedule::STATUS_FAILED], true)) {
            throw ValidationException::withMessages([
                'status' => ['Only queued or failed schedules can be edited.'],
            ]);
        }

        $data['status'] = Schedule::STATUS_QUEUED;
        $data['failure_reason'] = null;
        $data['published_at'] = null;
        $data['payload'] = [
            'platform' => $data['platform'] ?? $schedule->platform,
            'content' => $data['content'] ?? $schedule->content,
            'media_urls' => $data['media_urls'] ?? $schedule->media_urls ?? [],
        ];

        $schedule = $this->schedules->update($schedule, $data);
        $this->dispatchPublishJob($schedule);

        return $schedule;
    }

    public function retry(Schedule $schedule): Schedule
    {
        if ($schedule->status !== Schedule::STATUS_FAILED) {
            throw ValidationException::withMessages([
                'status' => ['Only failed schedules can be retried.'],
            ]);
        }

        $schedule = $this->schedules->update($schedule, [
            'status' => Schedule::STATUS_QUEUED,
            'failure_reason' => null,
            'published_at' => null,
        ]);

        $this->dispatchPublishJob($schedule);

        return $schedule;
    }

    public function delete(Schedule $schedule): void
    {
        if ($schedule->status === Schedule::STATUS_PROCESSING) {
            throw ValidationException::withMessages([
                'status' => ['Processing schedules cannot be deleted.'],
            ]);
        }

        $this->schedules->delete($schedule);
    }

    private function dispatchPublishJob(Schedule $schedule): void
    {
        $delay = Carbon::parse($schedule->scheduled_at)->isFuture()
            ? Carbon::parse($schedule->scheduled_at)
            : now();

        PublishScheduledPostJob::dispatch($schedule->id)->delay($delay);
    }
}
