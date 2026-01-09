<?php

namespace App\Http\Controllers\Tenant\Auth;

use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Services\Tenant\User\UserService;
use App\Http\Resources\Tenant\User\UserResource;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use App\Http\Resources\Tenant\Auth\AuthUserResource;

class LoginController extends Controller
{
    use AuthenticatesUsers;

    protected $redirectTo = '';

    protected $user;

    public function __construct(UserService $user)
    {
        $this->user = $user;
    }

    public function login(Request $request)
    {
        if ($request->token) {
            if ($this->attemptLogin($request)) {
                $authUser = auth()->guard('user')->user();
                $user = new \stdClass();
                $user->uuid = $authUser->uuid;
                $user->full_name = $authUser->full_name;
                $user->user_type_text = $authUser->user_type_text;
                $user->image_path = $authUser->image_path;
                $user->token = Str::random('75');
                $this->user->update($authUser->id, ['last_logged_in' => now()]);
                return response(["data" => $user], 200);
            } else {
                return response([
                    'status' => 'NOT_FOUND',
                    'message' => ['The provided credentials are incorrect.'],
                ], 200);
            }
        } else {
            return response([
                'status' => 'ERROR',
                'message' => ['Something went wrong in recaptcha !!'],
            ], 500);
        }
    }

    public function doVerify()
    {
        $user = auth()->guard('user')->user();
        if (!empty($user)) {
            return response(['data' => new AuthUserResource($user)], 200);
        }
        return response(['status' => "Unauthorized"], 401);
    }

    public function username()
    {
        $field = (filter_var(request()->email, FILTER_VALIDATE_EMAIL) || !request()->email) ? 'email' : 'username';
        request()->merge([$field => request()->email]);
        return $field;
    }

    public function logout(Request $request)
    {
        $user = auth()->guard('user')->user();
        if ($user->uuid == $request->uuid) {

            if (auth()->guard('user')->user()) {
                if ($response = $this->loggedOut($request)) {
                    return $response;
                }
                auth()->guard('user')->logout();

                return response(['status' => 'OK', 'message' => 'Logout successfully.'], 200);
            }
            return response(['status' => 'OK', 'message' => 'Logout successfully.'], 200);
        }
        return response(['status' => "Unauthorized"], 401);
    }

    protected function passwordResetUrl($token, $email)
    {
        $url = env('APP_URL');
        $params = [
            'email' => $email,
            'token' => $token,
        ];
        return $url . '?' . http_build_query($params);
    }

    protected function guard()
    {
        return Auth::guard('user');
    }
}
