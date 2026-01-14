<?php

namespace App\Http\Requests\Tenant\SiteSetting;

use Illuminate\Foundation\Http\FormRequest;

class SiteSettingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'company_name' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'mobile' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string|max:255',
            'map_url' => 'nullable|string|max:255',
            'zoom_link' => 'nullable|string|max:255',
            'facebook' => 'nullable|string|max:255',
            'twitter' => 'nullable|string|max:255',
            'youtube' => 'nullable|string|max:255',
            'instagram' => 'nullable|string|max:255',
            'linkedin' => 'nullable|string|max:255',
            'tiktok' => 'nullable|string|max:255',
            'pinterest' => 'nullable|string|max:255',
            'viber' => 'nullable|string|max:255',
            'whatsapp' => 'nullable|string|max:255',
            'facebook_chat_widgets' => 'nullable|string',
            'google_analytics' => 'nullable|string',
            'pixels' => 'nullable|string',
            'slogan' => 'nullable|string|max:255',
            'tagline' => 'nullable|string|max:255',
            'website' => 'nullable|string|max:255',
            'date_format' => 'nullable|string|max:255',
            'enable_cookies' => 'nullable|boolean',
            'cookie_content_text' => 'nullable|string',
            'copy_right_text' => 'nullable|string|max:255',
            'terms_condition' => 'nullable|string|max:255',
            'fav_icon' => 'nullable|string|max:255',
            'logo' => 'nullable|string|max:255',
            'login_bg_image' => 'nullable|string|max:255',
            'app_logo' => 'nullable|string|max:255',
            'email_logo_image' => 'nullable|string|max:255',
            'footer_logo' => 'nullable|string|max:255',
            'recaptcha_site_key' => 'nullable|string|max:255',
            'recaptcha_secret_key' => 'nullable|string|max:255',
            'mail_driver' => 'nullable|string|max:255',
            'mail_host' => 'nullable|string|max:255',
            'mail_port' => 'nullable|string|max:255',
            'mail_user_name' => 'nullable|string|max:255',
            'mail_password' => 'nullable|string|max:255',
            'mail_encryption' => 'nullable|string|max:255',
            'mail_sender_name' => 'nullable|string|max:255',
            'mail_sender_address' => 'nullable|string|max:255',
            'storage_url' => 'nullable|string|max:255',
            'storage_type' => 'nullable|string|max:255',
            'storage_access_key' => 'nullable|string|max:255',
            'storage_secret_key' => 'nullable|string|max:255',
            'storage_region' => 'nullable|string|max:255',
            'storage_endpoint' => 'nullable|string|max:255',
            'storage_bucket_name' => 'nullable|string|max:255',
            'tax_percentage' => 'nullable|string|max:255',
            'pan_no' => 'nullable|string|max:255',
            'vat_no' => 'nullable|string|max:255',
        ];
    }
}
