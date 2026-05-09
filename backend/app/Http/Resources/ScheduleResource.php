<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ScheduleResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'tenant_id' => $this->tenant_id,
            'created_by_user_id' => $this->created_by_user_id,
            'title' => $this->title,
            'content' => $this->content,
            'media_urls' => $this->media_urls ?? [],
            'platform' => $this->platform,
            'scheduled_at' => $this->scheduled_at,
            'timezone' => $this->timezone,
            'status' => $this->status,
            'provider_post_id' => $this->provider_post_id,
            'failure_reason' => $this->failure_reason,
            'attempts' => $this->attempts,
            'published_at' => $this->published_at,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
