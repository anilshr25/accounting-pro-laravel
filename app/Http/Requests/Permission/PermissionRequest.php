<?php

namespace App\Http\Requests\Permission;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PermissionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:255',
            ],

            'type' => [
                'required',
                'string',
                Rule::in([
                    'view',
                    'create',
                    'update',
                    'delete',
                    'clear',
                    'cancel',
                    'search',
                    'export'
                ]),
            ],

            'guard_name' => [
                'nullable',
                'string',
                'max:255',
            ],
        ];
    }
}
