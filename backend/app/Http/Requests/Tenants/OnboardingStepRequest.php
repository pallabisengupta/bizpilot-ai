<?php

namespace App\Http\Requests\Tenants;

use Illuminate\Foundation\Http\FormRequest;

class OnboardingStepRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'step' => ['required', 'string', 'max:80'],
            'payload' => ['nullable', 'array'],
            'company_name' => ['nullable', 'string', 'max:150'],
            'timezone' => ['nullable', 'timezone', 'max:80'],
            'language' => ['nullable', 'string', 'max:20'],
        ];
    }
}
