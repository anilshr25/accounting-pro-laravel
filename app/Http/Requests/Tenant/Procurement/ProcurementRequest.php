<?php

namespace App\Http\Requests\Tenant\Procurement;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProcurementRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'order_date' => 'nullable|date',
            'order_time' => 'nullable',
            'order_miti' => 'required|date',
            'order_created_by' => 'required|string',
            'total_amount' => 'nullable|string',
            'status' => 'nullable|string',
            'remarks' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|numeric|min:0.001',
            'items.*.amount' => 'required|numeric|min:0',
        ];
    }
}
