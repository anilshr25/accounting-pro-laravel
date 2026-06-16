<?php

namespace App\Models\Tenant\Kye\Service;

use App\Services\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Tenant\Kye\Kye;

class KyeService extends Model
{
    use HasFactory, SoftDeletes, Auditable;
    protected $table = 'kye_services';
    protected $fillable = [
        'kye_id',
        'department',
        'position',
        'shift',
        'salary',
    ];
    public function kye()
    {
        return $this->belongsTo(Kye::class);
    }
}
