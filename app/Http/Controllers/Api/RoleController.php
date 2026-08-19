<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Role;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(Role::with('permissions')->orderBy('libelle')->get());
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'libelle' => 'required|string|unique:roles,libelle',
            'code' => 'required|string|alpha_dash|unique:roles,code',
        ]);

        return response()->json(Role::create($data), 201);
    }

    public function show(Role $role): JsonResponse
    {
        return response()->json($role->load('permissions'));
    }

    public function update(Request $request, Role $role): JsonResponse
    {
        $data = $request->validate([
            'libelle' => 'sometimes|string|unique:roles,libelle,' . $role->id,
        ]);

        $role->update($data);
        return response()->json($role->fresh()->load('permissions'));
    }

    public function destroy(Role $role): JsonResponse
    {
        if ($role->users()->count() > 0) {
            return response()->json([
                'message' => 'Impossible de supprimer un rôle assigné à des utilisateurs.',
            ], 400);
        }

        $role->permissions()->detach();
        $role->delete();

        return response()->json(['message' => 'Rôle supprimé.']);
    }
}
