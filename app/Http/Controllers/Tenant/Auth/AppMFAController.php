<?php

namespace App\Http\Controllers\Tenant\Auth;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use App\Services\Tenant\User\UserService;
use App\Services\Authenticator\Authenticator;

class AppMFAController extends Controller
{
    protected UserService $user;
    protected Authenticator $authenticator;

    public function __construct(
        UserService $user,
        Authenticator $authenticator,
    ) {
        $this->user = $user;
        $this->authenticator = $authenticator;
    }

    public function checkVerificationEnabled(Request $request)
    {
        $user = $this->user->getUserForLogin(
            $request->only(['email', 'password'])
        );

        if (!$user) {
            return response([
                'status' => 'NOT_FOUND',
                'message' => 'The provided credentials are incorrect.',
            ], 200);
        }

        if (!$user->is_login_verified) {
            return response([
                'status' => 'NOT_VERIFIED',
                'message' => 'Email not verified. Please verify your email.',
            ], 200);
        }

        return response([
            'status' => 'OK',
            'data' => $user,
        ], 200);
    }

    public function activateEmailAuthenticator(Request $request)
    {
        $authUser = auth()->guard('web')->user();

        if ($this->user->update($authUser->id, $request->all())) {
            return response([
                'status' => 'OK',
            ]);
        }

        return response([
            'status' => 'ERROR',
        ], 500);
    }

    public function deactivateEmailAuthenticator()
    {
        $authUser = auth()->guard('web')->user();

        $this->user->update($authUser->id, [
            'is_email_authentication_enabled' => false,
        ]);

        return response([
            'status' => 'OK',
        ], 200);
    }

    public function getMfaAuthenticatorCode()
    {
        $authUser = auth()->guard('web')->user();

        $secret = $this->authenticator->createSecret();

        $qrCodeUrl = $this->authenticator->getQRCodeUrl(
            $authUser->email,
            $secret,
            $authUser->organisation->display_name ?? env('APP_NAME')
        );

        return response([
            'data' => [
                'secret_key' => $secret,
                'qrCodeUrl' => $qrCodeUrl,
            ],
        ]);
    }

    public function activateMfaAuthenticator(Request $request)
    {
        $authUser = auth()->guard('web')->user();

        if (!$request->filled('secret_key')) {
            return response([
                'msg' => 'Secret key not found',
            ], 200);
        }

        $secret = $request->secret_key;
        $verificationCode = $request->auth_code;
        $qrCodeUrl = $request->qrCodeUrl;

        $checkResult = $this->authenticator->verifyCode(
            $secret,
            $verificationCode,
            2
        );

        if (!$checkResult) {
            return response([
                'status' => 'ERROR',
            ], 500);
        }

        $this->user->update($authUser->id, [
            'is_mfa_enabled' => true,
            'mfa_secret_code' => $secret,
            'mfa_authentication_image' => $qrCodeUrl,
        ]);

        return response([
            'status' => 'OK',
        ], 200);
    }

    public function deactivateMfaAuthenticator(Request $request)
    {
        $authUser = auth()->guard('web')->user();

        $checkResult = $this->authenticator->verifyCode(
            $authUser->mfa_secret_code,
            $request->auth_code,
            2
        );

        if (!$checkResult) {
            return response([
                'message' => 'Authentication code is not valid',
            ], 200);
        }

        $this->user->update($authUser->id, [
            'is_mfa_enabled' => false,
            'mfa_secret_code' => null,
            'mfa_authentication_image' => null,
        ]);

        return response([
            'status' => 'OK',
        ], 200);
    }

    public function csrfToken()
    {
        return new Response(status: 204);
    }
}
