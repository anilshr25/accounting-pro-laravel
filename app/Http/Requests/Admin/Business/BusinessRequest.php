<?php

namespace App\Http\Requests\Admin\Business;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BusinessRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $businessId = $this->route('id');

        return [
            'name' => ['required', 'string', 'max:255'],
            'slug' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('businesses', 'slug')->ignore($businessId)
            ],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20'],
            'address' => ['nullable', 'string', 'max:500'],
            'status' => [
                'nullable',
                Rule::in(['active', 'inactive']),
            ],

            'tenant_id' => ['nullable', 'string', 'exists:tenants,id'],
        ];
    }
}
