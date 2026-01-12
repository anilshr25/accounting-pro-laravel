<?php

declare(strict_types=1);

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $user = auth()->user();
        return [
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'image' => ['nullable', 'file', 'image', 'max:2048'],
            'company_name' => ['required', 'string', 'max:255'],
            'company_address' => ['required', 'string', 'max:500'],
            'workplace' => [
                'required',
                'string',
                'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
                'max:255',
                Rule::unique('owner_users', 'workplace')->ignore($user?->id),
            ],
            'company_email' => ['required', 'email', 'max:255'],
            'company_pan_no' => [
                'required',
                'string',
                'max:100',
                Rule::unique('owner_users', 'company_pan_no')->ignore($user?->id),
            ],
            'company_registration_no' => [
                'required',
                'string',
                'max:100',
                Rule::unique('owner_users', 'company_registration_no')->ignore($user?->id),
            ],
            'company_industry' => ['nullable', 'string', 'max:255'],
            'company_country' => ['nullable', 'string', 'max:255'],
            'company_website' => ['nullable', 'url', 'max:255'],
            'file' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
            'doc_one_file' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
            'doc_two_file' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
            'doc_three_file' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
        ];
    }
}
