<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\OwnerUser\OwnerUser;
use App\Models\User\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function login(Request $request)
    {
        if (!$request->token) {
            return response([
                'status' => 'ERROR',
                'message' => ['Something went wrong in recaptcha !!'],
            ], 500);
        }

        $request->validate([
            'email' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        $email = $request->email;
        $password = $request->password;

        $ownerUser = OwnerUser::where('email', $email)->first();

        if ($ownerUser && Hash::check($password, $ownerUser->password)) {

            if (!$ownerUser->is_active) {
                return response([
                    'status' => 'INACTIVE',
                    'message' => [
                        'Your owner account is inactive.'
                    ],
                ], 403);
            }

            Auth::guard('owner')->login($ownerUser);

            $request->session()->regenerate();

            $businesses = $ownerUser->businesses()
                ->where('status', 'active')
                ->get()
                ->map(function ($business) {
                    return [
                        'tenant_id' => $business->tenant_id,
                        'business_id' => $business->id,
                        'business_name' => $business->name,
                        'role_id' => null,
                        'is_active' => true,
                    ];
                })
                ->values();

            return response([
                'status' => 'OK',
                'message' => 'Login successful.',
                'data' => [
                    'id' => $ownerUser->id,
                    'name' => trim(
                        $ownerUser->first_name . ' ' . $ownerUser->last_name
                    ),
                    'email' => $ownerUser->email,
                    'user_type' => 'owner',
                    'businesses' => $businesses,
                ],
            ], 200);
        }

        $user = User::with('tenants.business')
            ->where('email', $email)
            ->first();

        if ($user && Hash::check($password, $user->password)) {

            Auth::guard('user')->login($user);

            $request->session()->regenerate();

            $businesses = $user->tenants
                ->where('pivot.is_active', true)
                ->map(function ($tenant) {

                    return [
                        'tenant_id' => $tenant->id,
                        'business_id' => $tenant->business?->id,
                        'business_name' => $tenant->business?->name,
                        'role_id' => $tenant->pivot->role_id,
                        'is_active' => (bool) $tenant->pivot->is_active,
                    ];
                })
                ->values();

            return response([
                'status' => 'OK',
                'message' => 'Login successful.',
                'data' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'user_type' => 'user',
                    'businesses' => $businesses,
                ],
            ], 200);
        }

        return response([
            'status' => 'NOT_FOUND',
            'message' => [
                'The provided credentials are incorrect.'
            ],
        ], 401);
    }

    public function verify()
    {
        if (Auth::guard('owner')->check()) {

            $owner = Auth::guard('owner')->user();

            return response([
                'status' => 'OK',
                'data' => [
                    'id' => $owner->id,
                    'name' => $owner->name,
                    'email' => $owner->email,
                    'user_type' => 'owner',
                ],
            ], 200);
        }

        if (Auth::guard('user')->check()) {

            $user = Auth::guard('user')->user();

            return response([
                'status' => 'OK',
                'data' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'user_type' => 'user',
                ],
            ], 200);
        }

        return response([
            'status' => 'Unauthorized'
        ], 401);
    }

    public function logout(Request $request)
    {
        Auth::guard('owner')->logout();
        Auth::guard('user')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response([
            'status' => 'OK',
            'message' => 'Logout successfully.'
        ], 200);
    }

    public function selectBusiness(Request $request)
    {
        $request->validate([
            'tenant_id' => ['required', 'string'],
        ]);

        if (Auth::guard('owner')->check()) {

            $owner = Auth::guard('owner')->user();

            if (! $owner instanceof \App\Models\OwnerUser\OwnerUser || ! method_exists($owner, 'businesses')) {
                $owner = OwnerUser::find(Auth::guard('owner')->id());
            }

            if (! $owner) {
                return response()->json([
                    'status' => 'Unauthorized',
                ], 401);
            }

            $business = $owner->businesses()
                ->where('businesses.tenant_id', $request->tenant_id)
                ->where('businesses.status', 'active')
                ->first();

            if (! $business) {
                return response()->json([
                    'status' => 'NO_BUSINESS',
                    'message' => 'This business is not assigned to this owner.',
                ], 403);
            }

            session([
                'tenant_id'  => $business->tenant_id,
                'business_id' => $business->id,
                'user_type'  => 'owner',
                'user_id'    => $owner->id,
            ]);

            return response()->json([
                'status' => 'OK',
                'message' => 'Business selected successfully.',
                'data' => [
                    'tenant_id' => $business->tenant_id,
                    'business_id' => $business->id,
                    'business_name' => $business->name,
                    'user_type' => 'owner',
                ],
            ]);
        }

        if (Auth::guard('user')->check()) {

            $user = Auth::guard('user')->user();

            if (! $user instanceof \App\Models\User\User || ! method_exists($user, 'tenants')) {
                $user = User::find(Auth::guard('user')->id());
            }

            if (! $user) {
                return response()->json([
                    'status' => 'Unauthorized',
                ], 401);
            }

            $tenant = $user->tenants()
                ->where('tenants.id', $request->tenant_id)
                ->wherePivot('is_active', true)
                ->with('business')
                ->first();

            if (! $tenant) {
                return response()->json([
                    'status' => 'NO_BUSINESS',
                    'message' => 'This business is not assigned to this user.',
                ], 403);
            }

            session([
                'tenant_id'  => $tenant->id,
                'business_id' => $tenant->business?->id,
                'user_type'  => 'user',
                'user_id'    => $user->id,
                'role_id'    => $tenant->pivot->role_id,
            ]);

            return response()->json([
                'status' => 'OK',
                'message' => 'Business selected successfully.',
                'data' => [
                    'tenant_id' => $tenant->id,
                    'business_id' => $tenant->business?->id,
                    'business_name' => $tenant->business?->name,
                    'role_id' => $tenant->pivot->role_id,
                    'user_type' => 'user',
                ],
            ]);
        }

        return response()->json([
            'status' => 'Unauthorized',
        ], 401);
    }
}
