<?php

namespace App\Http\Requests\Tenant\Scheme;

use Illuminate\Foundation\Http\FormRequest;

class SchemeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $isUpdate = $this->method() === 'PUT' || $this->method() === 'PATCH';

        return [
            'supplier_id' => $isUpdate ? 'sometimes|exists:suppliers,id' : 'required|exists:suppliers,id',
            'scheme_name' => $isUpdate ? 'sometimes|string|max:255' : 'required|string|max:255',
            'start_date' => $isUpdate ? 'sometimes|date' : 'required|date',
            'end_date' => $isUpdate ? 'sometimes|date|after_or_equal:start_date' : 'required|date|after_or_equal:start_date',
            'start_miti' => $isUpdate ? 'sometimes|string' : 'required|string',
            'end_miti' => $isUpdate ? 'sometimes|string' : 'required|string',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'issued_amount' => 'nullable|numeric',
            'percentage' => 'nullable|numeric',
            'scheme_type' => $isUpdate ? 'sometimes|in:monthly,quarterly,6_monthly,annually' : 'required|in:monthly,quarterly,6_monthly,annually',
            'status' => 'nullable|in:pending,completed',
        ];
    }
}
