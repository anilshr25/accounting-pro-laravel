<?php

namespace App\Http\Requests\Tenant\Kye\Experience;

use Illuminate\Foundation\Http\FormRequest;

class KyeExperienceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'kye_id' => 'required|exists:kyes,id',
            'company' => 'required|string|max:255',
            'position' => 'required|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ];
    }
}
