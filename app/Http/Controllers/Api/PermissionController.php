<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PermissionController extends Controller
{
    // GET /api/permissions
    public function index(): JsonResponse
    {
        return response()->json(Permission::all());
    }

    // POST /api/permissions
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => 'required|string|unique:permissions,name',
            'label' => 'nullable|string',
        ]);

        $permission = Permission::create($data);
        return response()->json($permission, 201);
    }

    // DELETE /api/permissions/{permission}
    public function destroy(Permission $permission): JsonResponse
    {
        $permission->delete();
        return response()->json(['message' => 'Permission supprimee.']);
    }
}
