<?php

namespace App\Repositories;

use App\Models\Schedule;
use App\Models\Tenant;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ScheduleRepository
{
    public function paginateForTenant(Tenant $tenant, array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return Schedule::query()
            ->where('tenant_id', $tenant->id)
            ->when($filters['status'] ?? null, fn ($query, $status) => $query->where('status', $status))
            ->when($filters['platform'] ?? null, fn ($query, $platform) => $query->where('platform', $platform))
            ->when($filters['date'] ?? null, fn ($query, $date) => $query->whereDate('scheduled_at', $date))
            ->orderBy('scheduled_at')
            ->paginate($perPage);
    }

    public function create(array $data): Schedule
    {
        return Schedule::query()->create($data);
    }

    public function update(Schedule $schedule, array $data): Schedule
    {
        $schedule->fill($data);
        $schedule->save();

        return $schedule->refresh();
    }

    public function delete(Schedule $schedule): void
    {
        $schedule->delete();
    }
}
