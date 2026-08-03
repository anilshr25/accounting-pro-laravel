<?php

use App\Models\EmailTemplate\EmailTemplate;
use App\Models\Tenant\SiteSetting\SiteSetting;
use App\Services\Infrastructure\ScopedStorageService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Nilambar\NepaliDate\NepaliDate;

function formatDate($date, $format = 'd M Y', $default = true)
{
    return date($format, strtotime($date));
}

function formatMonthYear($date, $format = 'M Y', $default = true)
{
    return date($format, strtotime($date));
}

function formatTime($time, $format = 'g:i A')
{
    if (! empty($time)) {
        return Carbon::createFromFormat('H:i:s', $time)->format($format);
    }

    return null;
}
function getRandomString($len)
{
    $string = '';
    $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
    for ($i = 1; $i <= $len; $i++) {
        $string .= substr($chars, rand(0, strlen($chars)), 1);
    }

    return strtoupper($string);
}

function getDateString($date)
{
    return date('Y M d', strtotime($date));
}

function getDateTimeString($date)
{
    return date('Y M d h:m a', strtotime($date));
}

function encryptData($value)
{
    try {
        $hash = new Hashids\Hashids('', 6);

        return $hash->encode($value, 6);
    } catch (Exception $exception) {
        return $value;
    }
}

function decryptData($value)
{
    try {
        $hash = new Hashids\Hashids('', 6);
        if (env('APP_ENV') == 'production') {
            return $hash->decode($value);
        }

        return $value;
    } catch (Exception $exception) {
        return $value;
    }
}

function appendZeroInNumber($value, $noOfDigit = 5)
{
    $zeros = null;
    $length = strlen($value);
    for ($i = 0; $i < ($noOfDigit - $length); $i++) {
        $zeros .= '0';
    }

    return $zeros.$value;
}

function getBase64ImageSize($base64Image)
{
    // return memory size in B, KB, MB
    try {
        $size_in_bytes = (int) (strlen(rtrim($base64Image, '=')) * 3 / 4);
        $size_in_kb = $size_in_bytes / 1024;
        $size_in_mb = $size_in_kb / 1024;

        return $size_in_mb;
    } catch (Exception $e) {
        return $e;
    }
}

function formatPhone($phone)
{
    $format_phone = trim($phone);
    try {
        // dd('here'.substr($format_phone, 1, 1).'phone'.$format_phone);
        if (substr($format_phone, 1, 1) === '4') {
            $first_three = preg_replace('/^\w{4}/', '$0 ', $format_phone);
            $explode = explode(' ', $first_three);
            $last_digit = preg_replace('/^\w{3}/', '$0 ', $explode[1]);
            $formatted_phone = $explode[0].' '.$last_digit;
        } elseif (substr($format_phone, 1, 1) === '0') {
            $first_three = preg_replace('/^\w{2}/', '$0 ', $format_phone);
            $explode = explode(' ', $first_three);
            $last_digit = preg_replace('/^\w{4}/', '$0 ', $explode[1]);
            $formatted_phone = $explode[0].' '.$last_digit;
        } else {
            $first_three = preg_replace('/^\w{2}/', '$0 ', $format_phone);
            $explode = explode(' ', $first_three);
            $last_digit = preg_replace('/^\w{3}/', '$0 ', $explode[1]);
            $formatted_phone = $explode[0].' '.$last_digit;
        }
    } catch (Exception $e) {
        return $format_phone;
    }

    return $formatted_phone;
}

function getYears()
{
    $date = Carbon::now()->format('Y');
    $years = [];
    for ($i = 0; $i <= 10; $i++) {
        $years[] = $date + $i;
    }

    return $years;
}

function generateRandomPassword()
{
    return Str::random(10);
}

function getEmailRawTag($tag)
{
    return $tagHTML = config('email-template.tags.'.$tag);
}

function renderEmailHTML($description, $acceptedTags)
{
    if (! empty($acceptedTags)) {
        foreach ($acceptedTags as $tag) {
            $pattern = '/{!-'.$tag.'-!}/i';
            $description = preg_replace($pattern, getEmailRawTag($tag), $description);
        }
    }

    return $description;
}

function renderEmailData($description, $accepted_inputs, $data)
{
    if (! empty($accepted_inputs)) {
        foreach ($accepted_inputs as $input) {
            $pattern = '/{{'.$input.'}}/i';
            $description = preg_replace($pattern, $data[$input], $description);
        }
    }

    return $description;
}

function getToken($type)
{
    switch ($type) {
        case 'email':
            return hash_hmac('sha256', Str::random(40), config('app.key'));
        default:
            return rand(123456, 987654);
    }
}

function getFavIcon()
{
    $site_settings = SiteSetting::first();

    return ! empty($site_settings) && ! empty($site_settings->fav_icon) ? $site_settings->fav_icon_path['real'] : asset('assets/images/logo/Futech_Logo.png');
}

function getEmailTemplate($role, $type)
{
    $template = EmailTemplate::whereRole($role)->whereType($type)->whereIsActive(1)->first();

    return $template;
}

function buildTableNameToLogInfoTitle($tableName)
{
    $nameArr = explode('_', $tableName);
    foreach ($nameArr as $key => $name) {
        $nameArr[$key] = Str::singular($name);
    }
    $name = implode(' ', $nameArr);

    return ucwords($name);
}

function getSetting()
{
    return SiteSetting::first();
}

function getImagePath($uploadPath, $imageName, $signed = false)
{
    $imagePath = null;
    if (empty($imageName)) {
        return $imagePath;
    }
    $realPath = buildUploadPathUrl("$uploadPath/$imageName");
    $thumbPath = buildUploadPathUrl("$uploadPath/thumb/$imageName");
    $fileType = checkFileType($imageName);

    if ($fileType == 'image') {
        $imagePath = [
            'original' => s3_image_url($realPath, true),
            'thumb' => s3_image_url($thumbPath, true),
        ];
    } else {
        $imagePath = ($fileType != 'other') ? [
            'original' => s3_image_url($realPath, true),
        ] : null;
    }

    return $imagePath;
}

function checkFileType($imageName)
{
    $imageType = ['jpeg', 'jpg', 'png', 'JPEG', 'JPG', 'ico'];

    $docType = ['docx', 'doc'];

    $spreadType = ['xls', 'xlsx'];

    $image = explode('.', $imageName);

    if (isset($image[1])) {
        if (in_array($image[1], $imageType)) {
            return 'image';
        }

        if (in_array($image[1], $docType)) {
            return 'doc';
        }

        if ($image[1] == 'cvs' || $image[1] == 'txt') {
            return 'cvs';
        }

        if (in_array($image[1], $spreadType)) {
            return 'xls';
        }

        return 'pdf';
    } else {
        return 'other';
    }
}

function s3_image_url($path, $signed = false)
{
    $storage = app(ScopedStorageService::class);

    return $signed ? $storage->temporaryUrl($path) : $storage->url($path);
}

function getStorageType()
{
    $setting = SiteSetting::first();

    return $setting->storage_type ?? 'local';
}

function buildUploadPathUrl($path)
{
    $env = Config::get('app.env');
    if ($env == 'production') {
        return $path;
    } else {
        return 'local/'.$path;
    }
}

function getHashedPassword($value)
{
    try {
        return Hash::make($value);
    } catch (Exception $exception) {
        return $value;
    }
}

function convertDate($date, $option = 'ad')
{
    if (! empty($date)) {
        $date = explode('-', $date);
        $obj = new NepaliDate;
        if (isset($date[0]) && isset($date[1]) && isset($date[2])) {
            if ($option == 'ad') {
                $newDate = $obj->convertBsToAd($date[0], $date[1], $date[2]);
            } else {
                $newDate = $obj->convertAdToBs($date[0], $date[1], $date[2]);
            }

            $month = $newDate['month'];
            $day = $newDate['day'];
            if (strlen((string) $month) == 1) {
                $month = '0'.$month;
            }
            if (strlen((string) $day) == 1) {
                $day = '0'.$day;
            }
            $adDate = $newDate['year'].'-'.$month.'-'.$day;

            return $adDate;
        }

        return null;
    }

    return null;
}

function encryptId(int|string $id): string
{
    $enc = Crypt::encryptString((string) $id);

    // make URL-safe: +/ -> -_ and trim padding =
    return rtrim(strtr($enc, '+/', '-_'), '=');
}

function decryptId(string $value): string
{
    // restore padding and original chars
    $b64 = strtr($value, '-_', '+/').str_repeat('=', (4 - strlen($value) % 4) % 4);

    return Crypt::decryptString($b64);
}
