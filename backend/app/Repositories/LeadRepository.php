<?php

namespace App\Repositories;

use App\Models\Lead;
use App\Models\Tenant;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class LeadRepository
{
    public function paginateForTenant(Tenant $tenant, array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return Lead::query()
            ->with('assignedUser:id,name,email')
            ->where('tenant_id', $tenant->id)
            ->when($filters['status'] ?? null, fn ($query, $status) => $query->where('status', $status))
            ->when($filters['source'] ?? null, fn ($query, $source) => $query->where('source', $source))
            ->when($filters['assigned_user_id'] ?? null, fn ($query, $userId) => $query->where('assigned_user_id', $userId))
            ->when($filters['search'] ?? null, function ($query, $search): void {
                $query->where(function ($inner) use ($search): void {
                    $inner->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%")
                        ->orWhere('company_name', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate($perPage);
    }

    public function create(array $data): Lead
    {
        return Lead::query()->create($data);
    }

    public function update(Lead $lead, array $data): Lead
    {
        $lead->fill($data);
        $lead->save();

        return $lead->refresh()->load('assignedUser:id,name,email', 'activities.user:id,name,email');
    }

    public function delete(Lead $lead): void
    {
        $lead->delete();
    }

    public function stats(Tenant $tenant): array
    {
        $counts = Lead::query()
            ->where('tenant_id', $tenant->id)
            ->select('status', DB::raw('count(*) as aggregate'))
            ->groupBy('status')
            ->pluck('aggregate', 'status');

        $total = (int) $counts->sum();
        $won = (int) ($counts[Lead::STATUS_WON] ?? 0);

        return [
            'total_leads' => $total,
            'new_leads' => (int) ($counts[Lead::STATUS_NEW] ?? 0),
            'conversion_rate' => $total > 0 ? round(($won / $total) * 100, 2) : 0,
        ];
    }
}
