<?php

namespace App\Models\Tenant\Daybook;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Daybook extends Model
{
    use HasFactory;
    protected $table = 'daybook';
    protected $fillable = [
        'date',
        'name',
        'amount',
        'type',
        'total_amount',
        'remarks',
    ];
    public $timestamps = false;
}
