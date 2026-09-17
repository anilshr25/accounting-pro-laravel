<?php

namespace App\Http\Middleware;

use App\Models\Business\Business;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class EnsureTenantOwner
{
    public function handle(Request $request, Closure $next): Response
    {
        $owner = $request->user('owner');

        $tenant = tenancy()->initialized
            ? tenant()
            : null;

        if ($owner === null || $tenant === null) {
            abort(403, 'Only the tenant owner may manage infrastructure settings.');
        }

        $hasAccess = Business::query()
            ->where('tenant_id', $tenant->id)
            ->whereHas('owners', function ($query) use ($owner) {
                $query->where('owner_users.id', $owner->id);
            })
            ->exists();

        if (! $hasAccess) {
            abort(403, 'Only the tenant owner may manage infrastructure settings.');
        }

        return $next($request);
    }
}
