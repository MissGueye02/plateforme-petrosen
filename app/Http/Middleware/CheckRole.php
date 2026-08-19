<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
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

        $rolesAutorises = array_filter(array_map('trim', explode(',', $roles)));
        $roleActuel = $user->roleCode();

        // Compatibilité avec les anciennes valeurs présentes dans la base.
        $aliases = [
            'administrateur' => ['administrateur'],
            'ingenieur_terrain' => ['ingenieur_terrain', 'ingenieur terrain', 'ingénieur terrain'],
            'chef_projet' => ['chef_projet', 'chef de projet'],
            'partenaire' => ['partenaire'],
            'auditeur_itie' => ['auditeur_itie', 'auditeur ITIE'],
        ];

        foreach ($rolesAutorises as $roleAutorise) {
            if ($roleActuel === $roleAutorise) {
                return $next($request);
            }

            foreach ($aliases[$roleAutorise] ?? [] as $alias) {
                if ($user->role?->libelle === $alias) {
                    return $next($request);
                }
            }
        }

        return response()->json([
            'success' => false,
            'message' => 'Accès refusé pour ce rôle.',
        ], 403);
    }
}
