<?php

namespace App\Models\Tenant\Kye\Experience;

use App\Services\Traits\Auditable;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Tenant\Kye\Kye;

class KyeExperience extends Model
{
    use HasFactory, SoftDeletes, Auditable;
    protected $table = 'kye_experiences';
    protected $fillable = [
        'kye_id',
        'company_name',
        'position',
        'start_date',
        'end_date',
    ];
    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    public function kye()
    {
        return $this->belongsTo(Kye::class);
    }
}
