<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LeadResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'tenant_id' => $this->tenant_id,
            'assigned_user_id' => $this->assigned_user_id,
            'assigned_user' => $this->whenLoaded('assignedUser', fn () => [
                'id' => $this->assignedUser?->id,
                'name' => $this->assignedUser?->name,
                'email' => $this->assignedUser?->email,
            ]),
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'company_name' => $this->company_name,
            'source' => $this->source,
            'status' => $this->status,
            'priority' => $this->priority,
            'score' => $this->score,
            'last_contacted_at' => $this->last_contacted_at,
            'next_follow_up_at' => $this->next_follow_up_at,
            'notes' => $this->notes,
            'custom_fields' => $this->custom_fields ?? [],
            'activities' => LeadActivityResource::collection($this->whenLoaded('activities')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
