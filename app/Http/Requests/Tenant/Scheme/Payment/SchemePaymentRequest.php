<?php

namespace App\Http\Requests\Tenant\Scheme\Payment;

use Illuminate\Foundation\Http\FormRequest;

class SchemePaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'scheme_id' => 'required|exists:schemes,id',
            'date' => 'required|date',
            'miti' => 'required|string',
            'amount' => 'required|numeric|min:0',
            'remarks' => 'nullable|string',
        ];
    }
}
