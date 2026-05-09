<?php

namespace App\Http\Requests\Schedules;

use App\Models\Schedule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class IndexScheduleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'status' => ['nullable', 'string', Rule::in(Schedule::STATUSES)],
            'platform' => ['nullable', 'string', Rule::in(['facebook', 'instagram', 'whatsapp'])],
            'date' => ['nullable', 'date_format:Y-m-d'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ];
    }
}
