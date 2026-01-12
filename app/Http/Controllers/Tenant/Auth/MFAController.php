<?php

namespace App\Http\Controllers\Tenant\Auth;

use ReCaptcha\ReCaptcha;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Mail;
use App\Services\Traits\MailTemplate;
use App\Mail\Tenant\VerificationCodeMail;
use App\Services\Tenant\User\UserService;
use App\Services\Authenticator\Authenticator;
use App\Http\Requests\Tenant\Auth\UserVerificationRequest;

class MFAController extends Controller
{
    use MailTemplate;

    protected $authenticator;
    protected $user;

    public function __construct(
        UserService $user,
        Authenticator $authenticator,
    ) {
        $this->authenticator = $authenticator;
        $this->user = $user;
    }

    public function checkVerificationEnabled(Request $request)
    {
        $recaptcha = new ReCaptcha(config('recaptcha.secret_key'));
        $response = $recaptcha->verify($request->token);
        if ($response->isSuccess()) {
            $authUser = new \stdClass();
            $user = $this->user->getUserForLogin($request->only(['email', 'password']));
            if ($user?->is_login_verified) {
                $authUser->uuid = $user->uuid;
                $authUser->full_name = $user->full_name;
                $authUser->email = $user->email;
                $authUser->is_login_verified = $user->is_login_verified;
                $authUser->is_mfa_enabled = $user->is_mfa_enabled;
                $authUser->is_email_authentication_enabled = $user->is_email_authentication_enabled;
                $authUser->is_active = $user->is_active;

                if ($user->is_email_authentication_enabled && !$user->is_mfa_enabled)
                    $this->sendEmailVerificationCode($user);

                return response(["status" => "OK", 'data' => $user], 200);
            } else if ($user && !$user->is_login_verified) {
                return response([
                    'message' => 'Email not verified. Please verify your email',
                    'status' => 'NOT_VERIFIED'
                ], 200);
            } else {
                return response([
                    'message' => 'The provided Credentials are incorrect.',
                    'status' => 'NOT_FOUND'
                ], 200);
            }
        } else {
            return response([
                'errors' => ['Something went wrong in recaptcha !!'],
            ], 500);
        }
    }

    public function activateEmailAuthenticator(Request $request)
    {
        $authUser = auth()->guard('web')->user();
        if ($this->user->update($authUser->id, $request->all())) {
            return response(['status' => 'OK']);
        }
    }

    public function deactivateEmailAuthenticator()
    {
        $authUser = auth()->guard('web')->user();
        if ($this->user->update($authUser->id, ['is_email_authentication_enabled' => false])) {
            return response(['status' => 'OK'], 200);
        }
        return response(['status' => 'OK'], 200);
    }

    public function getMfaAuthenticatorCode()
    {
        $authUser = auth()->guard('web')->user();
        $secret = $this->authenticator->createSecret();
        $qrCodeUrl = $this->authenticator->getQRCodeUrl($authUser->email, $secret, $authUser->organisation->display_name ?? env('APP_NAME'));
        return response(['data' => ['secret_key' => $secret, 'qrCodeUrl' => $qrCodeUrl]]);
    }

    public function activateMfaAuthenticator(Request $request)
    {
        $authUser = auth()->guard('web')->user();
        if ($request->filled('secret_key')) {
            $secret = $request->get('secret_key');
            $verificationCode = $request->get('auth_code');
            $qrCodeUrl = $request->get('qrCodeUrl');
            $checkResult = $this->authenticator->verifyCode($secret, $verificationCode, 2);
            $data = [
                'is_mfa_enabled' => true,
                'mfa_secret_code' => $secret,
                'mfa_authentication_image' => $qrCodeUrl
            ];
            if ($checkResult && $this->user->update($authUser->id, $data)) {
                return response(["status" => "OK"], 200);
            }
            return response(["status" => "ERROR"], 500);
        }
        return response(["msg" => 'Secret key not found'], 200);
    }

    public function deactivateMfaAuthenticator(Request $request)
    {
        $authUser = auth()->guard('web')->user();
        $data = $request->all();
        $data['is_mfa_enabled'] = false;
        $data['mfa_secret_code'] = null;
        $data['mfa_authentication_image'] = null;
        $verificationCode = $request->get('auth_code');

        $checkResult = $this->authenticator->verifyCode($authUser->mfa_secret_code, $verificationCode, 2);
        if (!$checkResult) {
            return response(["message" => "Authentication code is not valid"], 200);
        }
        if ($checkResult && $this->user->update($authUser->id, $data)) {
            return response(["status" => "OK"], 200);
        }
        return response(["status" => 'ERROR'], 200);
    }

    public function verifyMfaVerificationCode(UserVerificationRequest $request)
    {
        $data = $request->validated();
        $recaptcha = new ReCaptcha(config('recaptcha.secret_key'));
        $response = $recaptcha->verify($data['token']);
        if ($response->isSuccess()) {
            $user = $this->user->getUserForLogin($data['email'], $data['password']);
            if ($user) {
                if ($this->authenticator->verifyCode($user->mfa_secret_code, $data['verification_code'], 2)) {
                    return response(["status" => "OK"], 200);
                } else {
                    return response([
                        "status" => "ERROR",
                        'message' => 'The verification code is not valid.',
                    ], 200);
                }
            } else {
                return response([
                    "status" => "ERROR",
                    'message' => ['The provided Credentials are Incorrect.'],
                ], 200);
            }
        }
        return response([
            "status" => "ERROR",
            'message' => 'Invalid Captcha',
        ], 200);
    }

    public function requestEmailVerificationCode(UserVerificationRequest $request)
    {
        $data = $request->validated();

        $user = $this->user->getUserForLogin($data['email'], $data['password']);
        if ($user) {
            $this->sendEmailVerificationCode($user);
            return response(["status" => "OK"], 200);
        } else {
            return response([
                "status" => "ERROR",
                'message' => 'The provided Credentials are Incorrect.',
            ], 200);
        }
    }

    public function verifyEmailVerificationCode(UserVerificationRequest $request)
    {
        $data = $request->validated();

        $recaptcha = new ReCaptcha(config('recaptcha.secret_key'));
        $response = $recaptcha->verify($data['token']);
        if ($response->isSuccess()) {

            $user = $this->user->getUserForLogin($data['email'], $data['password']);
            if ($user) {
                $secret = !empty($user->mfa_secret_code) ? $user->mfa_secret_code : $user->email;
                if ($this->authenticator->verifyCode($secret, $data['verification_code'], 2)) {
                    return response(["status" => "OK"], 200);
                } else {
                    return response([
                        'status' => 'ERROR',
                        'message' => 'The verification code is not valid.',
                    ], 200);
                }
            } else {
                return response([
                    'status' => 'ERROR',
                    'message' => 'The provided Credentials are Incorrect.',
                ], 200);
            }
        }
        return response([
            "status" => "ERROR",
            'message' => 'Invalid Captcha',
        ], 200);
    }

    private function sendEmailVerificationCode($user)
    {
        setSMTP();
        $secret = !empty($user->mfa_secret_code) ? $user->mfa_secret_code : $user->email;
        $code = $this->authenticator->getCode($secret);
        $template = $this->getTemplate( 'user', 'verification_code_email');
        $acceptData = [
            'first_name' => $user->first_name,
            'verification_code' => $code
        ];
        $template->description = $this->sanitize($acceptData, $template);
        Mail::to($user->email)->send(new VerificationCodeMail($user, $template, $code));
    }

    public function csrfToken()
    {
        return new Response(status: 204);
    }
}
