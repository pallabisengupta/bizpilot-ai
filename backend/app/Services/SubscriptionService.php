<?php

namespace App\Services;

use App\Models\Plan;
use App\Models\Subscription;
use App\Models\Tenant;
use App\Repositories\SubscriptionRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SubscriptionService
{
    public function __construct(
        private readonly SubscriptionRepository $subscriptions,
    ) {
    }

    public function list(array $filters = [], int $perPage = 25): LengthAwarePaginator
    {
        return $this->subscriptions->paginate($filters, $perPage);
    }

    public function current(Tenant $tenant): ?Subscription
    {
        return $this->subscriptions->activeForTenant($tenant);
    }

    public function subscribe(Tenant $tenant, Plan $plan, array $metadata = []): Subscription
    {
        if (! $plan->is_active) {
            throw ValidationException::withMessages([
                'plan_id' => ['Selected plan is not active.'],
            ]);
        }

        return DB::transaction(function () use ($tenant, $plan): Subscription {
            Subscription::query()
                ->where('tenant_id', $tenant->id)
                ->where('status', 'active')
                ->update([
                    'status' => 'expired',
                    'billing_status' => 'replaced',
                ]);

            $periodStart = now();
            $periodEnd = $plan->billing_interval === 'yearly'
                ? now()->addYear()
                : now()->addMonth();

            $subscription = $this->subscriptions->create([
                'tenant_id' => $tenant->id,
                'plan_id' => $plan->id,
                'status' => 'active',
                'billing_status' => 'paid',
                'billing_interval' => $plan->billing_interval,
                'amount' => $plan->price_amount,
                'currency' => $plan->currency,
                'current_period_starts_at' => $periodStart,
                'current_period_ends_at' => $periodEnd,
                'metadata' => [
                    'provider' => 'manual',
                    'source' => 'admin_platform_mvp',
                ] + $metadata,
            ]);

            $tenant->forceFill([
                'plan_id' => $plan->id,
                'status' => 'active',
            ])->save();

            return $subscription->load(['tenant:id,company_name,email,status', 'plan:id,name,slug']);
        });
    }

    public function markStatus(Subscription $subscription, string $status, string $billingStatus): Subscription
    {
        return $this->subscriptions->update($subscription, [
            'status' => $status,
            'billing_status' => $billingStatus,
            'cancelled_at' => $status === 'cancelled' ? now() : $subscription->cancelled_at,
        ]);
    }
}
