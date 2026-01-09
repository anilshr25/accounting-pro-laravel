<?php

namespace App\Models\EmailTemplate;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EmailTemplate extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'title',
        'identifier',
        'subject',
        'role',
        'type',
        'category',
        'description',
        'message_content',
        'accepted_inputs',
        'is_active',
    ];
}
