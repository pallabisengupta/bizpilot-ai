<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SubscriptionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'tenant_id' => $this->tenant_id,
            'plan_id' => $this->plan_id,
            'plan' => $this->whenLoaded('plan', fn () => [
                'id' => $this->plan?->id,
                'name' => $this->plan?->name,
                'slug' => $this->plan?->slug,
            ]),
            'tenant' => $this->whenLoaded('tenant', fn () => [
                'id' => $this->tenant?->id,
                'company_name' => $this->tenant?->company_name,
                'email' => $this->tenant?->email,
                'status' => $this->tenant?->status,
            ]),
            'status' => $this->status,
            'billing_status' => $this->billing_status,
            'billing_interval' => $this->billing_interval,
            'amount' => $this->amount,
            'currency' => $this->currency,
            'current_period_starts_at' => $this->current_period_starts_at,
            'current_period_ends_at' => $this->current_period_ends_at,
            'cancelled_at' => $this->cancelled_at,
            'created_at' => $this->created_at,
        ];
    }
}
