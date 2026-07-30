<?php

namespace App\Http\Controllers\Tenant\Auth;

use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Services\Tenant\User\UserService;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use App\Http\Resources\Tenant\Auth\AuthUserResource;

class AppLoginController extends Controller
{
    use AuthenticatesUsers;

    protected string $redirectTo = '';

    protected UserService $user;

    public function __construct(UserService $user)
    {
        $this->user = $user;
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|string',
            'password' => 'required|string',
        ]);

        if (!$this->attemptLogin($request)) {
            return response([
                'status' => 'NOT_FOUND',
                'message' => ['The provided credentials are incorrect.'],
            ], 200);
        }

        $authUser = auth()->guard('web')->user();

        $user = new \stdClass();
        $user->uuid = $authUser->uuid;
        $user->full_name = $authUser->full_name;
        $user->user_type_text = $authUser->user_type_text;
        $user->image_path = $authUser->image_path;
        $user->token = Str::random(75);

        $this->user->update($authUser->id, [
            'last_logged_in' => now(),
        ]);

        return response([
            'data' => $user,
        ], 200);
    }

    public function doVerify()
    {
        $user = auth()->guard('web')->user();

        if ($user) {
            return response([
                'data' => new AuthUserResource($user),
            ], 200);
        }

        return response([
            'status' => 'Unauthorized',
        ], 401);
    }

    public function username()
    {
        $field = filter_var(request()->email, FILTER_VALIDATE_EMAIL)
            ? 'email'
            : 'username';

        request()->merge([
            $field => request()->email,
        ]);

        return $field;
    }

    public function logout(Request $request)
    {
        $user = auth()->guard('web')->user();

        if (!$user || $user->uuid !== $request->uuid) {
            return response([
                'status' => 'Unauthorized',
            ], 401);
        }

        auth()->guard('web')->logout();

        return response([
            'status' => 'OK',
            'message' => 'Logout successfully.',
        ], 200);
    }

    protected function passwordResetUrl($token, $email)
    {
        return env('APP_URL') . '?' . http_build_query([
            'email' => $email,
            'token' => $token,
        ]);
    }

    protected function guard()
    {
        return Auth::guard('web');
    }
}
