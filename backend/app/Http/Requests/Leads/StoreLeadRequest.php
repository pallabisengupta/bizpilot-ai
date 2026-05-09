<?php

namespace App\Http\Requests\Leads;

use App\Models\Lead;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreLeadRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'assigned_user_id' => ['nullable', 'integer', Rule::exists('users', 'id')],
            'name' => ['nullable', 'string', 'max:150'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:40'],
            'company_name' => ['nullable', 'string', 'max:150'],
            'source' => ['nullable', 'string', 'max:80'],
            'status' => ['nullable', 'string', Rule::in(Lead::STATUSES)],
            'priority' => ['nullable', 'string', Rule::in(['low', 'normal', 'high'])],
            'score' => ['nullable', 'integer', 'min:0', 'max:100'],
            'next_follow_up_at' => ['nullable', 'date'],
            'notes' => ['nullable', 'string', 'max:5000'],
            'custom_fields' => ['nullable', 'array'],
        ];
    }
}
