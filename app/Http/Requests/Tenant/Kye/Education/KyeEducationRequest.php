<?php

namespace App\Http\Requests\Tenant\Kye\Education;

use Illuminate\Foundation\Http\FormRequest;

class KyeEducationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'employee_id' => 'required|exists:employees,id',
            'degree' => 'required|string|max:255',
            'university' => 'required|string|max:255',
            'from_date' => 'required|date',
            'to_date' => 'required|date|after_or_equal:from_date',
        ];
    }
}
