<?php

namespace App\Models\Tenant\Kye;

use App\Services\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Tenant\Kye\Address\KyeAddress;
use App\Models\Tenant\Kye\Education\KyeEducation;
use App\Models\Tenant\Kye\Experience\KyeExperience;
use App\Models\Tenant\Kye\EmergencyContact\KyeEmergencyContact;
use App\Models\Tenant\Kye\Service\KyeService;
use App\Models\Tenant\Employee\Employee;

class Kye extends Model
{
    use HasFactory, SoftDeletes, Auditable;
    protected $table = 'kyes';
    protected $fillable = [
        'employee_id',
        'full_name',
        'date_of_birth_ad',
        'date_of_birth_bs',
        'marital_status',
        'gender',
        'blood_group',
        'citizenship_number',
        'issue_date',
        'issue_district',
        'front_image',
        'back_image',
    ];
    protected $casts = [
        'date_of_birth_ad' => 'date',
        'date_of_birth_bs' => 'date',
        'issue_date' => 'date',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    public function addresses()
    {
        return $this->hasMany(
            KyeAddress::class,
            'employee_id',
            'employee_id'
        );
    }

    public function educations()
    {
        return $this->hasOne(
            KyeEducation::class,
            'employee_id',
            'employee_id'
        );
    }

    public function experiences()
    {
        return $this->hasMany(
            KyeExperience::class,
            'employee_id',
            'employee_id'
        );
    }

    public function emergencyContacts()
    {
        return $this->hasMany(
            KyeEmergencyContact::class,
            'employee_id',
            'employee_id'
        );
    }

    public function services()
    {
        return $this->hasMany(
            KyeService::class,
            'employee_id',
            'employee_id'
        );
    }
}
