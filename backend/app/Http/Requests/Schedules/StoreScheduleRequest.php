<?php

namespace App\Http\Requests\Schedules;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreScheduleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'title' => ['nullable', 'string', 'max:180'],
            'content' => ['required', 'string', 'max:5000'],
            'media_urls' => ['nullable', 'array'],
            'media_urls.*' => ['url', 'max:500'],
            'platform' => ['required', 'string', Rule::in(['facebook', 'instagram', 'whatsapp'])],
            'scheduled_at' => ['required', 'date'],
            'timezone' => ['required', 'timezone', 'max:80'],
        ];
    }
}
