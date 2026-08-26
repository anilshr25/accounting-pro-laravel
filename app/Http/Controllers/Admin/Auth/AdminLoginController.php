<?php

namespace App\Http\Controllers\Admin\Auth;

use App\Http\Controllers\Controller;
use App\Models\AdminUser\AdminUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminLoginController extends Controller
{
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (! Auth::guard('admin')->attempt($credentials)) {
            return response()->json([
                'status' => 'NOT_FOUND',
                'message' => [
                    'The provided credentials are incorrect.',
                ],
            ], 401);
        }

        $request->session()->regenerate();

        $admin = AdminUser::find(Auth::guard('admin')->id());

        if (! $admin) {
            Auth::guard('admin')->logout();

            return response()->json([
                'status' => 'ERROR',
                'message' => 'Unable to retrieve authenticated admin.',
            ], 401);
        }

        if (! $admin->is_active) {
            Auth::guard('admin')->logout();

            return response()->json([
                'status' => 'ERROR',
                'message' => 'Admin account is inactive.',
            ], 403);
        }

        $admin->update([
            'last_logged_in' => now(),
        ]);

        return response()->json([
            'status' => 'OK',
            'data' => [
                'id' => $admin->id,
                'first_name' => $admin->first_name,
                'last_name' => $admin->last_name,
                'email' => $admin->email,
                'user_type' => $admin->user_type,
            ],
        ], 200);
    }

    public function profile()
    {
        $admin = AdminUser::find(Auth::guard('admin')->id());

        if (! $admin) {
            return response()->json([
                'status' => 'UNAUTHORIZED',
                'message' => 'Admin is not authenticated.',
            ], 401);
        }

        return response()->json([
            'status' => 'OK',
            'data' => [
                'id' => $admin->id,
                'first_name' => $admin->first_name,
                'last_name' => $admin->last_name,
                'email' => $admin->email,
                'user_type' => $admin->user_type,
            ],
        ], 200);
    }

    public function logout(Request $request)
    {
        Auth::guard('admin')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json([
            'status' => 'OK',
            'message' => 'Logout successfully.',
        ], 200);
    }
}
