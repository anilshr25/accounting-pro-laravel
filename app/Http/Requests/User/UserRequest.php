<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $userId = $this->route('id');

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                $this->isMethod('post') ? 'required' : 'sometimes',
                'required',
                'email',
                'max:255',
                Rule::unique('central.users', 'email')
                    ->ignore($userId),
            ],
            'password' => [
                $this->isMethod('post') ? 'required' : 'nullable',
                'string',
                'min:8',
            ],
            'tenant_id' => ['required', 'string', 'max:255', 'exists:central.tenants,id',],
            'role' => ['required', 'string', 'max:255',],
            'permission_ids.*' => ['integer', 'exists:central.permissions,id'],
            'is_active' => ['sometimes', 'boolean',],
        ];
    }

    public function messages(): array
    {
        return [
            'email.unique' => 'A user with this email already exists.',
            'tenant_id.exists' => 'The selected business does not exist.',
        ];
    }
}
