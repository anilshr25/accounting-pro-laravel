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
        return [
            'supplier_id' => 'required|exists:suppliers,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'start_miti' => 'required|string',
            'end_miti' => 'required|string',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'issued_amount' => 'nullable|numeric',
            'percentage' => 'nullable|numeric',
            'scheme_type' => 'required|in:monthly,quarterly,6_monthly,annually',
            'status' => 'nullable|in:pending,completed',
        ];
    }
}
