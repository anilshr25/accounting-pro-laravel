<?php

namespace App\Models\Tenant\Salary;

use App\Services\Traits\Auditable;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Tenant\Employee\Employee;

class Salary extends Model
{
    use HasFactory, SoftDeletes, Auditable;
    protected $table = 'salaries';
    protected $fillable = [
        'employee_id',
        'month',
        'payment_date',
        'payment_method',
        'amount',
        'status'
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }
}
