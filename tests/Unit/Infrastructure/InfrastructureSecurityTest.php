<?php

declare(strict_types=1);

namespace Tests\Unit\Infrastructure;

use App\Http\Resources\Tenant\SiteSetting\SiteSettingResource;
use App\Jobs\Tenant\SendVerificationCodeEmail;
use App\Models\Tenant\SiteSetting\SiteSetting;
use Tests\TestCase;

final class InfrastructureSecurityTest extends TestCase
{
    public function test_credentials_are_encrypted_and_never_exposed_by_the_resource(): void
    {
        $setting = new SiteSetting;
        $setting->forceFill([
            'mail_password' => 'mail-secret',
            'storage_access_key' => 'access-secret',
            'storage_secret_key' => 'storage-secret',
            'recaptcha_secret_key' => 'captcha-secret',
        ]);

        $this->assertNotSame('mail-secret', $setting->getRawOriginal('mail_password'));
        $this->assertSame('encrypted', $setting->getCasts()['mail_password']);

        $resource = (new SiteSettingResource($setting))->toArray(request());

        $this->assertArrayNotHasKey('mail_password', $resource);
        $this->assertArrayNotHasKey('storage_access_key', $resource);
        $this->assertArrayNotHasKey('storage_secret_key', $resource);
        $this->assertTrue($resource['mail_password_configured']);
        $this->assertTrue($resource['storage_access_key_configured']);
    }

    public function test_verification_email_job_has_bounded_retries_and_backoff(): void
    {
        $job = new SendVerificationCodeEmail('tenant-alpha', 42);

        $this->assertSame(3, $job->tries);
        $this->assertSame([10, 30, 120], $job->backoff());
        $this->assertSame('tenant-alpha', $job->tenantId);
        $this->assertSame(42, $job->userId);
    }
}
