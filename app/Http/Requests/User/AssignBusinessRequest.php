<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;

class AssignBusinessRequest extends FormRequest
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
                'exists:tenants,id',
            ],

            'role_id' => [
                'required',
                'integer',
                'exists:roles,id',
            ],

            'is_active' => [
                'nullable',
                'boolean',
            ],
        ];
    }
}
