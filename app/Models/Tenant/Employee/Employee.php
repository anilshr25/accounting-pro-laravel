<?php

namespace App\Models\Tenant\Employee;

use App\Services\Traits\Auditable;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Tenant\Kye\Kye;
use App\Models\Tenant\Kye\Address\KyeAddress;
use App\Models\Tenant\Kye\Education\KyeEducation;
use App\Models\Tenant\Kye\Experience\KyeExperience;
use App\Models\Tenant\Kye\EmergencyContact\KyeEmergencyContact;
use App\Models\Tenant\Kye\Service\KyeService;
use App\Models\Tenant\Salary\Salary;

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
        'department',
        'joining_date',
        'image',
        'salary',
        'pan_no',
        'license_no',
        'bank_name',
        'bank_account_number',
        'status'
    ];

    public function kye()
    {
        return $this->hasOne(Kye::class, 'employee_id');
    }

    public function addresses()
    {
        return $this->hasMany(KyeAddress::class);
    }

    public function educations()
    {
        return $this->hasOne(KyeEducation::class);
    }

    public function experiences()
    {
        return $this->hasMany(KyeExperience::class);
    }

    public function emergencyContact()
    {
        return $this->hasOne(KyeEmergencyContact::class);
    }

    public function services()
    {
        return $this->hasMany(KyeService::class);
    }

    public function salaries()
    {
        return $this->hasMany(Salary::class, 'employee_id');
    }
}
