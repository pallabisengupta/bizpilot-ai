<?php

namespace App\Http\Requests\Schedules;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateScheduleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'title' => ['sometimes', 'nullable', 'string', 'max:180'],
            'content' => ['sometimes', 'required', 'string', 'max:5000'],
            'media_urls' => ['sometimes', 'nullable', 'array'],
            'media_urls.*' => ['url', 'max:500'],
            'platform' => ['sometimes', 'required', 'string', Rule::in(['facebook', 'instagram', 'whatsapp'])],
            'scheduled_at' => ['sometimes', 'required', 'date'],
            'timezone' => ['sometimes', 'required', 'timezone', 'max:80'],
        ];
    }
}
