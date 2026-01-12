<?php

namespace App\Models\OwnerUser;

use App\Services\Traits\UploadPathTrait;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class OwnerUser extends Authenticatable
{
    use HasFactory, UploadPathTrait, SoftDeletes;

    protected $uploadPath = "owner-user";

    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'password',
        'phone',
        'image',
        'is_active',
        'company_name',
        'company_address',
        'workplace',
        'company_email',
        'company_pan_no',
        'company_registration_no',
        'company_industry',
        'company_country',
        'company_website',
        'doc_one',
        'doc_two',
        'doc_three',
        'status',
        'approved_by',
        'remarks',
    ];

    protected $hidden = ['password', 'company_pan_no', 'company_registration_no'];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    protected $appends = [
        'image_path',
        'doc_one_path',
        'doc_two_path',
        'doc_three_path',
    ];

    public function getImagePathAttribute()
    {
        $imagePath = null;
        if (!empty($this->image)) {
            $uploadPath = $this->getUploadPath($this->uploadPath, 'image');
            $imagePath = getImagePath($uploadPath, $this->image, true);
        }
        return $imagePath;
    }

    public function getDocOnePathAttribute()
    {
        $imagePath = null;
        if (!empty($this->doc_one)) {
            $uploadPath = $this->getUploadPath($this->uploadPath, 'doc/one');
            $imagePath = getImagePath($uploadPath, $this->doc_one, true);
        }
        return $imagePath;
    }

    public function getDocTwoPathAttribute()
    {
        $imagePath = null;
        if (!empty($this->doc_two)) {
            $uploadPath = $this->getUploadPath($this->uploadPath, 'doc/two');
            $imagePath = getImagePath($uploadPath, $this->doc_two, true);
        }
        return $imagePath;
    }

    public function getDocThreePathAttribute()
    {
        $imagePath = null;
        if (!empty($this->doc_three)) {
            $uploadPath = $this->getUploadPath($this->uploadPath, 'doc/three');
            $imagePath = getImagePath($uploadPath, $this->doc_three, true);
        }
        return $imagePath;
    }

    /**
     * Tenants owned by this OwnerUser (central tenants table)
     */
    public function tenants()
    {
        return $this->hasMany(\App\Models\Tenant\Tenant::class, 'owner_user_id', 'id');
    }

    /**
     * Domains owned by this OwnerUser
     */
    public function domains()
    {
        return $this->hasMany(\App\Models\Domain\Domain::class, 'owner_user_id', 'id');
    }
}
