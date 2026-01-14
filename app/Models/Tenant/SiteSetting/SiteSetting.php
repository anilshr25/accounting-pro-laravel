<?php

namespace App\Models\Tenant\SiteSetting;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SiteSetting extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'site_settings';

    protected $fillable = [
        'company_name',
        'description',
        'mobile',
        'phone',
        'email',
        'address',
        'map_url',
        'zoom_link',
        'facebook',
        'twitter',
        'youtube',
        'instagram',
        'linkedin',
        'tiktok',
        'pinterest',
        'viber',
        'whatsapp',
        'facebook_chat_widgets',
        'google_analytics',
        'pixels',
        'slogan',
        'tagline',
        'website',
        'date_format',
        'enable_cookies',
        'cookie_content_text',
        'copy_right_text',
        'terms_condition',
        'fav_icon',
        'logo',
        'login_bg_image',
        'app_logo',
        'email_logo_image',
        'footer_logo',
        'recaptcha_site_key',
        'recaptcha_secret_key',
        'mail_driver',
        'mail_host',
        'mail_port',
        'mail_user_name',
        'mail_password',
        'mail_encryption',
        'mail_sender_name',
        'mail_sender_address',
        'storage_url',
        'storage_type',
        'storage_access_key',
        'storage_secret_key',
        'storage_region',
        'storage_endpoint',
        'storage_bucket_name',
        'tax_percentage',
        'pan_no',
        'vat_no',
    ];

    protected $casts = [
        'enable_cookies' => 'boolean',
    ];
}
