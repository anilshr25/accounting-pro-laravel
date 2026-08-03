<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\OwnerUser\OwnerUser;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class EnsureTenantOwner
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user('web');
        $tenant = tenancy()->initialized ? tenant() : null;
        $owner = $tenant?->owner_user_id
            ? OwnerUser::query()->find($tenant->owner_user_id)
            : null;

        if ($user === null || $owner === null || ! hash_equals(strtolower($owner->email), strtolower($user->email))) {
            abort(403, 'Only the tenant owner may manage infrastructure settings.');
        }

        return $next($request);
    }
}
