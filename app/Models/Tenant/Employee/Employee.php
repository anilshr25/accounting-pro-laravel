<?php

namespace App\Models\Tenant\Employee;

use App\Services\Traits\Auditable;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Employee extends Model
{
    use HasFactory, SoftDeletes, Auditable;
    protected $table = 'employees';
    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'phone',
        'address',
        'designation',
        'joining_date',
        'image',
        'salary',
        'pan_no',
        'license_no',
        'bank_name',
        'bank_account_number',
        'status'
    ];
}
