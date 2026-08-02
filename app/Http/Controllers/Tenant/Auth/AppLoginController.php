<?php

namespace App\Http\Controllers\Tenant\Auth;

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

        if (! $this->attemptLogin($request)) {
            return response()->json([
                'status' => 'NOT_FOUND',
                'message' => ['The provided credentials are incorrect.'],
            ], 200);
        }

        $authUser = auth()->guard('web')->user();

        if (method_exists($authUser, 'tokens')) {
            $authUser->tokens()->where('name', 'mobile')->delete();
        }

        if (! method_exists($authUser, 'createToken')) {
            return response()->json([
                'status' => 'NOT_FOUND',
                'message' => ['Unable to create authentication token.'],
            ], 200);
        }

        $token = $authUser->createToken('mobile')->plainTextToken;

        $this->user->update($authUser->id, [
            'last_logged_in' => now(),
        ]);

        return response()->json([
            'data' => [
                'uuid' => $authUser->uuid,
                'full_name' => $authUser->full_name,
                'user_type_text' => $authUser->user_type_text,
                'image_path' => $authUser->image_path,
                'token' => $token,
            ],
        ], 200);
    }

    public function doVerify(Request $request)
    {
        return response()->json([
            'data' => new AuthUserResource($request->user()),
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'status' => 'OK',
            'message' => 'Logout successfully.',
        ], 200);
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
