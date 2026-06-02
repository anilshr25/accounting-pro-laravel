<?php

namespace App\Http\Requests\Tenant\Kye\Address;

use Illuminate\Foundation\Http\FormRequest;

class KyeAddressRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'kye_id' => 'required|exists:kyes,id',
            'type' => 'required|string|max:255',
            'zone' => 'required|string|max:255',
            'district' => 'required|string|max:255',
            'municipality' => 'required|string|max:255',
            'ward_number' => 'required|string|max:255',
            'plus_code' => 'required|string|max:255',
            'locality' => 'required|string|max:255',
        ];
    }
}
