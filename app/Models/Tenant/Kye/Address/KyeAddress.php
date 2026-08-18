<?php

namespace App\Models\Tenant\Kye\Address;

use App\Services\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Tenant\Employee\Employee;

class KyeAddress extends Model
{
    use HasFactory, SoftDeletes, Auditable;
    protected $table = 'kye_addresses';
    protected $fillable = [
        'employee_id',
        'type',
        'zone',
        'district',
        'municipality',
        'ward_number',
        'plus_code',
        'locality',
    ];
    
    public function employee()
    {
        return $this->belongsTo(
            Employee::class,
            'employee_id',
            'id'
        );
    }
}
