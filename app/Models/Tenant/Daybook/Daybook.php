<?php

namespace App\Models\Tenant\Daybook;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Daybook extends Model
{
    use HasFactory;
    protected $table = 'daybooks';
    protected $fillable = [
        'date',
        'name',
        'amount',
        'type',
        'total_amount',
        'remarks',
    ];
    protected $casts = [
        'date' => 'datetime',
    ];
    public $timestamps = false;
}
