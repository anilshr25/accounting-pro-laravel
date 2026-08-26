<?php

declare(strict_types=1);

namespace App\Http\Requests\OwnerUser;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class OwnerUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $ownerUserId = $this->route('id');

        return [
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100',],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('owner_users', 'email')
                    ->ignore($ownerUserId),
            ],
            'phone' => ['nullable', 'string', 'max:20',],
            'password' => [$this->isMethod('POST') ? 'required' : 'nullable', 'string', 'min:8', 'confirmed',],

            'business_ids' => [
                'required',
                'array',
                'min:1',
            ],

            'business_ids.*' => [
                'required',
                'integer',
                'distinct',
                'exists:businesses,id',
            ],

            'status' => [
                'nullable',
                'string',
                Rule::in([
                    'pending',
                    'approved',
                    'rejected',
                    'suspended',
                ]),
            ],
            'approved_by' => ['nullable', 'integer', 'exists:admin_users,id'],
            'remarks' => ['nullable', 'string', 'max:1000',],
            'is_active' => ['nullable', 'boolean',],
            'is_mfa_enabled' => ['nullable', 'boolean'],
            'is_email_authentication_enabled' => ['nullable', 'boolean'],
            'is_login_verified' => ['nullable', 'boolean'],
            'force_password_change' => ['nullable', 'boolean'],

            'file' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:2048',
            ],

            'doc_one_file' => [
                'nullable',
                'file',
                'max:5120',
            ],

            'doc_two_file' => [
                'nullable',
                'file',
                'max:5120',
            ],

            'doc_three_file' => [
                'nullable',
                'file',
                'max:5120',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'first_name.required' => 'First name is required.',
            'last_name.required' => 'Last name is required.',

            'email.required' => 'Email is required.',
            'email.email' => 'Please provide a valid email address.',
            'email.unique' => 'This email is already registered.',

            'password.required' => 'Password is required when creating an owner.',
            'password.min' => 'Password must be at least 8 characters.',
            'password.confirmed' => 'Password confirmation does not match.',

            'business_ids.required' => 'At least one business is required.',
            'business_ids.array' => 'Business IDs must be an array.',
            'business_ids.min' => 'At least one business must be selected.',
            'business_ids.*.required' => 'Business ID is required.',
            'business_ids.*.integer' => 'Business ID must be an integer.',
            'business_ids.*.distinct' => 'Duplicate business IDs are not allowed.',
            'business_ids.*.exists' => 'The selected business does not exist.',

            'approved_by.exists' => 'The selected admin user does not exist.',

            'file.image' => 'The profile image must be an image.',
            'file.mimes' => 'The profile image must be a JPG, JPEG, PNG, or WEBP file.',
            'file.max' => 'The profile image may not be larger than 2MB.',

            'doc_one_file.max' => 'Document one may not be larger than 5MB.',
            'doc_two_file.max' => 'Document two may not be larger than 5MB.',
            'doc_three_file.max' => 'Document three may not be larger than 5MB.',
        ];
    }
}
