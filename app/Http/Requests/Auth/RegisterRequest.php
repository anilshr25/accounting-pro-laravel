<?php

declare(strict_types=1);

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:owner_users,email'],
            'password' => ['required', 'confirmed', Password::defaults()],
            'phone' => ['nullable', 'string', 'max:30'],
            'company_name' => ['required', 'string', 'max:255'],
            'company_address' => ['required', 'string', 'max:500'],
            'workplace' => [
                'required',
                'string',
                'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
                'max:255',
                Rule::unique('owner_users', 'workplace'),
            ],
            'company_email' => ['required', 'email', 'max:255'],
            'company_pan_no' => ['required', 'string', 'max:100', Rule::unique('owner_users', 'company_pan_no')],
            'company_registration_no' => ['required', 'string', 'max:100', Rule::unique('owner_users', 'company_registration_no')],
            'company_industry' => ['nullable', 'string', 'max:255'],
            'company_country' => ['nullable', 'string', 'max:255'],
            'company_website' => ['nullable', 'url', 'max:255'],
            'doc_one_file' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
            'doc_two_file' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
            'doc_three_file' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
        ];
    }
}
