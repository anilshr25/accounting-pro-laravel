<?php

namespace App\Models\Tenant\Kye\Education;

use App\Services\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Tenant\Kye\Kye;

class KyeEducation extends Model
{
    use HasFactory, SoftDeletes, Auditable;
    protected $table = 'kye_educations';
    protected $fillable = [
        'kye_id',
        'degree',
        'university',
        'from_date',
        'to_date',
    ];
    protected $casts = [
        'from_date' => 'date',
        'to_date' => 'date',
    ];

    public function kye()
    {
        return $this->belongsTo(Kye::class);
    }
}
