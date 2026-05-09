<?php

namespace App\Http\Requests\Tenants;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class OnboardingTenantRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'company_name' => ['required', 'string', 'max:150'],
            'slug' => ['nullable', 'string', 'max:150', 'alpha_dash:ascii', Rule::unique('tenants', 'slug')->ignore($this->user()?->tenant_id)],
            'industry' => ['nullable', 'string', 'max:100'],
            'logo' => ['nullable', 'string', 'max:500'],
            'phone' => ['nullable', 'string', 'max:40'],
            'email' => ['nullable', 'email', 'max:255'],
            'website' => ['nullable', 'url', 'max:255'],
            'timezone' => ['required', 'timezone', 'max:80'],
            'language' => ['required', 'string', 'max:20'],
            'plan_id' => ['nullable', 'integer', Rule::exists('plans', 'id')],
            'status' => ['nullable', 'string', Rule::in(['active', 'onboarding'])],
        ];
    }
}
