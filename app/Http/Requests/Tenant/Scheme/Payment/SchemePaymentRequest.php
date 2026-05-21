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
        $isUpdate = in_array($this->method(), ['PUT', 'PATCH']);

        return [
            'scheme_id' => $isUpdate ? 'sometimes|exists:schemes,id' : 'required|exists:schemes,id',
            'date'      => $isUpdate ? 'sometimes|date' : 'required|date',
            'miti'      => $isUpdate ? 'sometimes|string' : 'required|string',
            'amount'    => $isUpdate ? 'sometimes|numeric|min:0' : 'required|numeric|min:0',
            'remarks'   => 'sometimes|nullable|string',
        ];
    }
}
