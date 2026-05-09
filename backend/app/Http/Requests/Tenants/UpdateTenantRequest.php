<?php

namespace App\Http\Requests\Tenants;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTenantRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        $tenantId = $this->route('tenant')?->id ?? $this->route('tenant');

        return [
            'company_name' => ['sometimes', 'required', 'string', 'max:150'],
            'slug' => ['sometimes', 'required', 'string', 'max:150', 'alpha_dash:ascii', Rule::unique('tenants', 'slug')->ignore($tenantId)],
            'industry' => ['sometimes', 'nullable', 'string', 'max:100'],
            'logo' => ['sometimes', 'nullable', 'string', 'max:500'],
            'phone' => ['sometimes', 'nullable', 'string', 'max:40'],
            'email' => ['sometimes', 'nullable', 'email', 'max:255'],
            'website' => ['sometimes', 'nullable', 'url', 'max:255'],
            'timezone' => ['sometimes', 'required', 'timezone', 'max:80'],
            'language' => ['sometimes', 'required', 'string', 'max:20'],
            'plan_id' => ['sometimes', 'nullable', 'integer', Rule::exists('plans', 'id')],
            'status' => ['sometimes', 'required', 'string', Rule::in(['onboarding', 'active', 'suspended', 'cancelled'])],
        ];
    }
}
