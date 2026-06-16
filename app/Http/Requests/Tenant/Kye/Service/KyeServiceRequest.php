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
            'kye_id' => 'required|exists:kyes,id',
            'department' => 'required|string|max:255',
            'position' => 'required|string|max:255',
            'shift' => 'required|in:morning,evening',
            'salary' => 'required|numeric',
        ];
    }
}
