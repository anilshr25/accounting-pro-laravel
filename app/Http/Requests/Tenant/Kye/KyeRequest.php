<?php

namespace App\Http\Requests\Tenant\Kye;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class KyeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $kyeId = $this->route('id');

        return [
            'full_name' => 'required|string|max:255',
            'date_of_birth_ad' => 'required|date',
            'date_of_birth_bs' => 'required|date',
            'marital_status' => 'required|in:single,married,divorced',
            'gender' => 'required|in:male,female,other',
            'blood_group' => 'nullable|string|max:10',
            'citizenship_number' => [
                'required',
                'string',
                'max:255',
                Rule::unique('kyes', 'citizenship_number')->ignore($kyeId),
            ],
            'issue_date' => 'required|date',
            'issue_district' => 'required|string|max:255',

            'addresses' => 'nullable|array',
            'addresses.*.type' => 'required|string|max:50',
            'addresses.*.zone' => 'nullable|string|max:255',
            'addresses.*.district' => 'required|string|max:255',
            'addresses.*.municipality' => 'required|string|max:255',
            'addresses.*.ward_number' => 'required|string|max:50',
            'addresses.*.plus_code' => 'nullable|string|max:255',
            'addresses.*.locality' => 'nullable|string|max:255',

            'educations' => 'nullable|array',
            'educations.*.degree' => 'required|string|max:255',
            'educations.*.university' => 'required|string|max:255',
            'educations.*.from_date' => 'required|date',
            'educations.*.to_date' => 'nullable|date|after_or_equal:educations.*.from_date',

            'experiences' => 'nullable|array',
            'experiences.*.company_name' => 'required|string|max:255',
            'experiences.*.position' => 'required|string|max:255',
            'experiences.*.start_date' => 'required|date',
            'experiences.*.end_date' => 'nullable|date',

            'services' => 'nullable|array',
            'services.*.department' => 'required|string|max:255',
            'services.*.position' => 'required|string|max:255',
            'services.*.shift' => 'nullable|string|max:100',
            'services.*.salary' => 'nullable|numeric|min:0',

            'emergency_contact' => 'nullable|array',
            'emergency_contact.name' => 'required_with:emergency_contact|string|max:255',
            'emergency_contact.relationship' => 'required_with:emergency_contact|string|max:255',
            'emergency_contact.phone' => 'required_with:emergency_contact|string|max:50',
            'emergency_contact.email' => 'nullable|email|max:255',
        ];
    }
}
