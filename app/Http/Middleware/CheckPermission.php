<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckPermission
{
    /**
     * Usage: ->middleware('permission:manage-users')
     * Or multiple: ->middleware('permission:manage-users,configure-alerts')
     */
    public function handle(Request $request, Closure $next, string $permissions): Response
    {
        if (! $request->user()) {
            return response()->json([
                'success' => false,
                'message' => 'Authentification requise.',
            ], 401);
        }

        $perms = array_map('trim', explode(',', $permissions));

        foreach ($perms as $perm) {
            if ($request->user()->hasPermission($perm)) {
                return $next($request);
            }
        }

        return response()->json([
            'success' => false,
            'message' => 'Permission requise: ' . implode(', ', $perms),
        ], 403);
    }
}
