<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRoleNotIn
{
    public function handle(Request $request, Closure $next, string $roles): Response
    {
        $user = $request->user();

        if (! $user) {
            return response()->json([
                'success' => false,
                'message' => 'Authentification requise.',
            ], 401);
        }

        $forbidden = array_filter(array_map('trim', explode(',', $roles)));
        $roleActuel = $user->roleCode();

        if ($roleActuel && in_array($roleActuel, $forbidden, true)) {
            return response()->json([
                'success' => false,
                'message' => 'Accès interdit pour ce rôle.',
            ], 403);
        }

        return $next($request);
    }
}
