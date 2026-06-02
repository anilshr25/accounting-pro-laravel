<?php

namespace App\Http\Requests\Tenant\Kye\EmergencyContact;

use Illuminate\Foundation\Http\FormRequest;

class KyeEmergencyContactRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'kye_id' => 'required|exists:kyes,id',
            'name' => 'required|string|max:255',
            'relationship' => 'required|string|max:255',
            'phone' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
        ];
    }
}
