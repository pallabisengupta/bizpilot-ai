<?php

namespace App\Http\Requests\Leads;

use App\Models\Lead;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateLeadRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'assigned_user_id' => ['sometimes', 'nullable', 'integer', Rule::exists('users', 'id')],
            'name' => ['sometimes', 'nullable', 'string', 'max:150'],
            'email' => ['sometimes', 'nullable', 'email', 'max:255'],
            'phone' => ['sometimes', 'nullable', 'string', 'max:40'],
            'company_name' => ['sometimes', 'nullable', 'string', 'max:150'],
            'source' => ['sometimes', 'nullable', 'string', 'max:80'],
            'status' => ['sometimes', 'required', 'string', Rule::in(Lead::STATUSES)],
            'priority' => ['sometimes', 'nullable', 'string', Rule::in(['low', 'normal', 'high'])],
            'score' => ['sometimes', 'nullable', 'integer', 'min:0', 'max:100'],
            'last_contacted_at' => ['sometimes', 'nullable', 'date'],
            'next_follow_up_at' => ['sometimes', 'nullable', 'date'],
            'notes' => ['sometimes', 'nullable', 'string', 'max:5000'],
            'custom_fields' => ['sometimes', 'nullable', 'array'],
        ];
    }
}
