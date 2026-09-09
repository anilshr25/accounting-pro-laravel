<?php

namespace App\Http\Requests\Role;

use Illuminate\Foundation\Http\FormRequest;

class RoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'tenant_id' => [
                'required',
                'string',
                'max:255',
                'exists:tenants,id',
            ],

            'name' => [
                'required',
                'string',
                'max:255',
            ],

            'guard_name' => [
                'nullable',
                'string',
                'max:255',
            ],
        ];
    }
}
