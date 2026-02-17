<?php

namespace App\Http\Requests\Tenant\Procurement;

use Illuminate\Foundation\Http\FormRequest;

class ProcurementRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'     => 'required|string|max:255',
            'unit'     => 'required|string',
            'price'    => 'nullable|numeric',
            'category' => 'required|string',
            'remarks'  => 'nullable|string|max:255',
            'status'   => 'nullable|string',
        ];
    }
}
