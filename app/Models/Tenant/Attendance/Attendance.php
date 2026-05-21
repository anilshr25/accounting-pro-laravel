<?php

namespace App\Models\Tenant\Attendance;

use App\Services\Traits\Auditable;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Tenant\Employee\Employee;

class Attendance extends Model
{
    use HasFactory, SoftDeletes, Auditable;
    protected $table = 'attendances';
    protected $fillable = [
        'employee_id',
        'date',
        'check_in',
        'check_out',
        'working_hours',
        'overtime_hours',
        'status'
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }
}
