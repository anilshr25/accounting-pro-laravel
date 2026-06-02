<?php

namespace App\Http\Resources\Tenant\Employee;

use Illuminate\Http\Resources\Json\JsonResource;

class EmployeeResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'email' => $this->email,
            'phone' => $this->phone,
            'address' => $this->address,
            'designation' => $this->designation,
            'joining_date' => $this->joining_date,
            'pan-no' => $this->pan_no,
            'license_no' => $this->license_no,
            'salary' => $this->salary,
            'image' => $this->image
                ? url($this->image)
                : null,
            'bank_name' => $this->bank_name,
            'bank_account_number' => $this->bank_account_number,
            'status' => $this->status,
        ];
    }
}
