<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ProductRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        if ($this->filled('name') && ! $this->filled('slug')) {
            $this->merge(['slug' => Str::slug($this->string('name')->toString())]);
        }

        if ($this->filled('name') && ! $this->filled('url')) {
            $this->merge(['url' => Str::slug($this->string('name')->toString())]);
        }

        if ($this->filled('url')) {
            $this->merge(['url' => Str::slug($this->string('url')->toString())]);
        }
    }

    public function authorize(): bool
    {
        return $this->user()?->role === 'super_admin';
    }

    public function rules(): array
    {
        $productId = $this->route('product')?->id ?? $this->route('product');

        return [
            'name' => ['required', 'string', 'max:150'],
            'slug' => ['required', 'string', 'max:150', 'alpha_dash:ascii', Rule::unique('products', 'slug')->ignore($productId)],
            'url' => ['required', 'string', 'max:150', 'alpha_dash:ascii', Rule::unique('products', 'url')->ignore($productId)],
            'description' => ['nullable', 'string', 'max:5000'],
            'is_active' => ['boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ];
    }
}
