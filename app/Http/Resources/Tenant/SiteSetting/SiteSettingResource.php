<?php

namespace App\Http\Resources\Tenant\SiteSetting;

use Illuminate\Http\Resources\Json\JsonResource;

class SiteSettingResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'company_name' => $this->company_name,
            'description' => $this->description,
            'mobile' => $this->mobile,
            'phone' => $this->phone,
            'email' => $this->email,
            'address' => $this->address,
            'map_url' => $this->map_url,
            'zoom_link' => $this->zoom_link,
            'facebook' => $this->facebook,
            'twitter' => $this->twitter,
            'youtube' => $this->youtube,
            'instagram' => $this->instagram,
            'linkedin' => $this->linkedin,
            'tiktok' => $this->tiktok,
            'pinterest' => $this->pinterest,
            'viber' => $this->viber,
            'whatsapp' => $this->whatsapp,
            'facebook_chat_widgets' => $this->facebook_chat_widgets,
            'google_analytics' => $this->google_analytics,
            'pixels' => $this->pixels,
            'slogan' => $this->slogan,
            'tagline' => $this->tagline,
            'website' => $this->website,
            'date_format' => $this->date_format,
            'enable_cookies' => $this->enable_cookies,
            'cookie_content_text' => $this->cookie_content_text,
            'copy_right_text' => $this->copy_right_text,
            'terms_condition' => $this->terms_condition,
            'fav_icon' => $this->fav_icon,
            'logo' => $this->logo,
            'login_bg_image' => $this->login_bg_image,
            'app_logo' => $this->app_logo,
            'email_logo_image' => $this->email_logo_image,
            'footer_logo' => $this->footer_logo,
            'recaptcha_site_key' => $this->recaptcha_site_key,
            'recaptcha_secret_key' => $this->recaptcha_secret_key,
            'mail_driver' => $this->mail_driver,
            'mail_host' => $this->mail_host,
            'mail_port' => $this->mail_port,
            'mail_user_name' => $this->mail_user_name,
            'mail_password' => $this->mail_password,
            'mail_encryption' => $this->mail_encryption,
            'mail_sender_name' => $this->mail_sender_name,
            'mail_sender_address' => $this->mail_sender_address,
            'storage_url' => $this->storage_url,
            'storage_type' => $this->storage_type,
            'storage_access_key' => $this->storage_access_key,
            'storage_secret_key' => $this->storage_secret_key,
            'storage_region' => $this->storage_region,
            'storage_endpoint' => $this->storage_endpoint,
            'storage_bucket_name' => $this->storage_bucket_name,
            'tax_percentage' => $this->tax_percentage,
            'pan_no' => $this->pan_no,
            'vat_no' => $this->vat_no,
        ];
    }
}
