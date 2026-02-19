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
        $id = $this->route('id');
        return [
            'order_number' => [
                'sometimes',
                'string',
                Rule::unique('procurements', 'order_number')->ignore($id),
            ],
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

    protected function prepareForValidation()
    {
        if ($this->has('order_number')) {
            $this->merge([
                'order_number' => trim($this->order_number),
            ]);
        }
    }
}
