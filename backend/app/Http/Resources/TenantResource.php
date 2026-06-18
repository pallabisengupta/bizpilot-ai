<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TenantResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'company_name' => $this->company_name,
            'slug' => $this->slug,
            'industry' => $this->industry,
            'logo' => $this->logo,
            'phone' => $this->phone,
            'email' => $this->email,
            'website' => $this->website,
            'timezone' => $this->timezone,
            'language' => $this->language,
            'plan_id' => $this->plan_id,
            'plan' => $this->whenLoaded('plan', fn () => [
                'id' => $this->plan?->id,
                'name' => $this->plan?->name,
                'slug' => $this->plan?->slug,
                'billing_interval' => $this->plan?->billing_interval,
                'price_amount' => $this->plan?->price_amount,
                'currency' => $this->plan?->currency,
            ]),
            'owner' => $this->whenLoaded('users', fn () => optional(
                $this->users->firstWhere('role', 'owner') ?? $this->users->first()
            )->only(['id', 'name', 'email', 'role'])),
            'latest_subscription' => $this->whenLoaded('latestSubscription', fn () => $this->latestSubscription ? [
                'id' => $this->latestSubscription->id,
                'status' => $this->latestSubscription->status,
                'billing_status' => $this->latestSubscription->billing_status,
                'billing_interval' => $this->latestSubscription->billing_interval,
                'amount' => $this->latestSubscription->amount,
                'currency' => $this->latestSubscription->currency,
                'current_period_starts_at' => $this->latestSubscription->current_period_starts_at,
                'current_period_ends_at' => $this->latestSubscription->current_period_ends_at,
                'cancelled_at' => $this->latestSubscription->cancelled_at,
                'plan' => $this->latestSubscription->relationLoaded('plan') && $this->latestSubscription->plan ? [
                    'id' => $this->latestSubscription->plan->id,
                    'name' => $this->latestSubscription->plan->name,
                    'slug' => $this->latestSubscription->plan->slug,
                ] : null,
            ] : null),
            'users_count' => $this->whenCounted('users'),
            'leads_count' => $this->whenCounted('leads'),
            'schedules_count' => $this->whenCounted('schedules'),
            'status' => $this->status,
            'onboarding' => $this->onboarding ?? [],
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
