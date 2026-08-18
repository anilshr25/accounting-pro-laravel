<?php

namespace App\Models\Tenant\Kye\Service;

use App\Services\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Tenant\Employee\Employee;

class KyeService extends Model
{
    use HasFactory, SoftDeletes, Auditable;
    protected $table = 'kye_services';
    protected $fillable = [
        'employee_id',
        'department',
        'position',
        'shift',
        'salary',
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
