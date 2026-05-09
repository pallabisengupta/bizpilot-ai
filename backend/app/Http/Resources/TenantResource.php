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
            'status' => $this->status,
            'onboarding' => $this->onboarding ?? [],
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
