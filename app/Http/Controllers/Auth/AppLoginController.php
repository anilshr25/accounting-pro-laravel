<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\OwnerUser\OwnerUser;
use App\Models\User\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AppLoginController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $email = $request->email;
        $password = $request->password;

        $ownerUser = OwnerUser::where('email', $email)->first();

        if ($ownerUser && Hash::check($password, $ownerUser->password)) {

            if (! $ownerUser->is_active) {
                return response()->json([
                    'status' => 'INACTIVE',
                    'message' => [
                        'Your owner account is inactive.',
                    ],
                ], 403);
            }

            $ownerUser->tokens()
                ->where('name', 'mobile')
                ->delete();

            $token = $ownerUser->createToken('mobile')->plainTextToken;

            $ownerUser->update([
                'last_logged_in' => now(),
            ]);

            $businesses = $ownerUser->businesses()
                ->where('businesses.status', 'active')
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

            return response()->json([
                'status' => 'OK',
                'message' => 'Login successful.',
                'data' => [
                    'id' => $ownerUser->id,
                    'name' => trim(
                        $ownerUser->first_name . ' ' . $ownerUser->last_name
                    ),
                    'email' => $ownerUser->email,
                    'user_type' => 'owner',
                    'token' => $token,
                    'businesses' => $businesses,
                ],
            ], 200);
        }

        $user = User::with('tenants.business')
            ->where('email', $email)
            ->first();

        if ($user && Hash::check($password, $user->password)) {

            if (isset($user->is_active) && ! $user->is_active) {
                return response()->json([
                    'status' => 'INACTIVE',
                    'message' => [
                        'Your account is inactive.',
                    ],
                ], 403);
            }

            $user->tokens()
                ->where('name', 'mobile')
                ->delete();

            $token = $user->createToken('mobile')->plainTextToken;

            $user->update([
                'last_logged_in' => now(),
            ]);

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

            return response()->json([
                'status' => 'OK',
                'message' => 'Login successful.',
                'data' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'user_type' => 'user',
                    'businesses' => $businesses,
                    'token' => $token,
                ],
            ], 200);
        }

        return response()->json([
            'status' => 'NOT_FOUND',
            'message' => [
                'The provided credentials are incorrect.',
            ],
        ], 401);
    }

    public function doVerify(Request $request)
    {
        $user = $request->user();

        if (! $user) {
            return response()->json([
                'status' => 'Unauthorized',
                'message' => 'Unauthenticated.',
            ], 401);
        }

        if ($user instanceof OwnerUser) {

            $businesses = $user->businesses()
                ->where('businesses.status', 'active')
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

            return response()->json([
                'status' => 'OK',
                'data' => [
                    'id' => $user->id,
                    'name' => trim(
                        $user->first_name . ' ' . $user->last_name
                    ),
                    'email' => $user->email,
                    'user_type' => 'owner',
                    'businesses' => $businesses,
                ],
            ], 200);
        }

        if ($user instanceof User) {

            $user->load('tenants.business');

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

            return response()->json([
                'status' => 'OK',
                'data' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'user_type' => 'user',
                    'businesses' => $businesses,
                ],
            ], 200);
        }

        return response()->json([
            'status' => 'Unauthorized',
            'message' => 'Invalid authenticated user.',
        ], 401);
    }

    public function logout(Request $request)
    {
        $user = $request->user();

        if ($user && $user->currentAccessToken()) {
            $user->currentAccessToken()->delete();
        }

        return response()->json([
            'status' => 'OK',
            'message' => 'Logout successfully.',
        ], 200);
    }

    public function selectBusiness(Request $request)
    {
        $request->validate([
            'tenant_id' => ['required', 'string'],
        ]);

        $user = $request->user();

        if (! $user) {
            return response()->json([
                'status' => 'Unauthorized',
                'message' => 'Unauthenticated.',
            ], 401);
        }

        if ($user instanceof OwnerUser) {

            $business = $user->businesses()
                ->where('businesses.tenant_id', $request->tenant_id)
                ->where('businesses.status', 'active')
                ->first();

            if (! $business) {
                return response()->json([
                    'status' => 'NO_BUSINESS',
                    'message' => 'This business is not assigned to this owner.',
                ], 403);
            }

            return response()->json([
                'status' => 'OK',
                'message' => 'Business selected successfully.',
                'data' => [
                    'tenant_id' => $business->tenant_id,
                    'business_id' => $business->id,
                    'business_name' => $business->name,
                    'role_id' => null,
                    'user_type' => 'owner',
                ],
            ], 200);
        }

        if ($user instanceof User) {

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
            ], 200);
        }

        return response()->json([
            'status' => 'Unauthorized',
        ], 401);
    }
}
