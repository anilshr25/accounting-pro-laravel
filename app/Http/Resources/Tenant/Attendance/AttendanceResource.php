<?php

namespace App\Http\Resources\Tenant\Attendance;

use Illuminate\Http\Resources\Json\JsonResource;

class AttendanceResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'employee_id' => $this->employee_id,
            'date' => $this->date,
            'check_in' => $this->check_in,
            'check_out' => $this->check_out,
            'working_hours' => $this->working_hours,
            'overtime_hours' => $this->overtime_hours,
            'status' => $this->status,
        ];
    }
}
