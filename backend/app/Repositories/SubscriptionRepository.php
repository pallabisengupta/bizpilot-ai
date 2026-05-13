<?php

namespace App\Repositories;

use App\Models\Subscription;
use App\Models\Tenant;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class SubscriptionRepository
{
    public function paginate(array $filters = [], int $perPage = 25): LengthAwarePaginator
    {
        return Subscription::query()
            ->with(['tenant:id,company_name,email,status', 'plan:id,name,slug'])
            ->when($filters['status'] ?? null, fn ($query, $status) => $query->where('status', $status))
            ->when($filters['billing_status'] ?? null, fn ($query, $status) => $query->where('billing_status', $status))
            ->latest()
            ->paginate($perPage);
    }

    public function activeForTenant(Tenant $tenant): ?Subscription
    {
        return Subscription::query()
            ->with('plan')
            ->where('tenant_id', $tenant->id)
            ->where('status', 'active')
            ->latest()
            ->first();
    }

    public function create(array $data): Subscription
    {
        return Subscription::query()->create($data);
    }

    public function update(Subscription $subscription, array $data): Subscription
    {
        $subscription->fill($data);
        $subscription->save();

        return $subscription->refresh()->load(['tenant:id,company_name,email,status', 'plan:id,name,slug']);
    }
}
