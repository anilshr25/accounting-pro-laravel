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

class Kye extends Model
{
    use HasFactory, SoftDeletes, Auditable;
    protected $table = 'kyes';
    protected $fillable = [
        'full_name',
        'date_of_birth_ad',
        'date_of_birth_bs',
        'marital_status',
        'gender',
        'blood_group',
        'citizenship_number',
        'issue_date',
        'issue_district',
    ];
    protected $casts = [
        'date_of_birth_ad' => 'date',
        'date_of_birth_bs' => 'date',
        'issue_date' => 'date',
    ];

    public function addresses()
    {
        return $this->hasMany(KyeAddress::class);
    }

    public function educations()
    {
        return $this->hasMany(KyeEducation::class);
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
}
