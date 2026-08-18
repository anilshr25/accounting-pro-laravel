<?php

namespace App\Http\Requests\Tenant\Kye\Service;

use Illuminate\Foundation\Http\FormRequest;

class KyeServiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'employee_id' => 'required|exists:employees,id',
            'department' => 'required|string|max:255',
            'position' => 'required|string|max:255',
            'shift' => 'required|in:morning,evening',
            'salary' => 'required|numeric',
        ];
    }
}
