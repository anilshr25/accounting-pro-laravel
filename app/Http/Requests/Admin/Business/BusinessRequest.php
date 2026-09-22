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
            'email' => ['nullable', 'email', 'max:255', Rule::unique('businesses', 'email')->ignore($businessId),],
            'phone' => ['nullable', 'string', 'max:10'],
            'address' => ['nullable', 'string', 'max:500'],
            'status' => [
                'nullable',
                Rule::in(['active', 'inactive']),
            ],

            'tenant_id' => ['nullable', 'string', 'exists:tenants,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Business name is required.',
            'email.email' => 'Please provide a valid email address.',
            'email.unique' => 'This email is already exists for another business.',
            'tenant_id.exists' => 'The selected tenant does not exist.',
        ];
    }
}
