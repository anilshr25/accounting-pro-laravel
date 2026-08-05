<?php

namespace App\Http\Requests\Tenant\Dashboard;

use Illuminate\Foundation\Http\FormRequest;

class DashboardRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'type' => 'nullable|in:daily,monthly,yearly,custom',

            'date' => 'required_if:type,daily|date',

            'month' => 'required_if:type,monthly|integer|min:1|max:12',
            'year'  => 'nullable|integer',

            'fiscal_year' => 'nullable|string',
            
            'start_date' => 'required_if:type,custom|date',
            'end_date'   => 'required_if:type,custom|date|after_or_equal:start_date',
        ];
    }
}
