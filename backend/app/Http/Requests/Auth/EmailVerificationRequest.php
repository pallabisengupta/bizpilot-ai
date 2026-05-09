<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class EmailVerificationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null
            && (string) $this->route('id') === (string) $this->user()->getKey();
    }

    public function rules(): array
    {
        return [
            'id' => ['required'],
            'hash' => ['required', 'string'],
        ];
    }

    protected function validationData(): array
    {
        return array_merge($this->all(), [
            'id' => $this->route('id'),
            'hash' => $this->route('hash'),
        ]);
    }
}
