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
        $isUpdate = $this->method() === 'PUT' || $this->method() === 'PATCH';
        $kyeId = $this->route('id');

        return [
            'employee_id' => $isUpdate ? 'sometimes|exists:employees,id' : 'required|exists:employees,id',
            'full_name' => $isUpdate ? 'sometimes|string|max:255' : 'required|string|max:255',
            'date_of_birth_ad' => $isUpdate ? 'sometimes|date' : 'required|date',
            'date_of_birth_bs' => $isUpdate ? 'sometimes|date' : 'required|date',
            'marital_status' => $isUpdate ? 'sometimes|in:single,married,divorced' : 'required|in:single,married,divorced',
            'gender' => $isUpdate ? 'sometimes|in:male,female,other' : 'required|in:male,female,other',
            'blood_group' => 'nullable|string|max:10',
            'citizenship_number' => [
                $isUpdate ? 'sometimes' : 'required',
                'string',
                'max:255',
                Rule::unique('kyes', 'citizenship_number')->ignore($kyeId),
            ],
            'issue_date' => $isUpdate ? 'sometimes|date' : 'required|date',
            'issue_district' => $isUpdate ? 'sometimes|string|max:255' : 'required|string|max:255',
            'front_image' => $isUpdate
                ? 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
                : 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
            'back_image' => $isUpdate
                ? 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
                : 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ];
    }
}
