<?php

namespace App\Http\Requests\Tenant\Daybook;

use Illuminate\Foundation\Http\FormRequest;

class DaybookRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'date' => 'nullable|date',
            'name' => 'nullable|string|max:255',
            'amount' => 'nullable|numeric',
            'type' => 'nullable|string|max:255',
            'total_amount' => 'nullable|numeric',
            'remarks' => 'nullable|string|max:255',
        ];
    }
}
