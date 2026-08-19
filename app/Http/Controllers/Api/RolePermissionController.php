<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use App\Models\Role;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RolePermissionController extends Controller
{
    // GET /api/roles/{role}/permissions
    public function index(Role $role): JsonResponse
    {
        return response()->json($role->permissions()->get());
    }

    // POST /api/roles/{role}/permissions  (body: ["perm_name1","perm_name2"] or [ids])
    public function attach(Request $request, Role $role): JsonResponse
    {
        $data = $request->validate([
            'permissions' => 'required|array|min:1',
        ]);

        // Accept either permission ids or names
        $permIds = Permission::whereIn('id', $data['permissions'])->orWhereIn('name', $data['permissions'])->pluck('id')->toArray();
        $role->permissions()->syncWithoutDetaching($permIds);

        return response()->json($role->permissions()->get());
    }

    // DELETE /api/roles/{role}/permissions  (body: ["perm_name1","perm_name2"] or [ids])
    public function detach(Request $request, Role $role): JsonResponse
    {
        $data = $request->validate([
            'permissions' => 'required|array|min:1',
        ]);

        $permIds = Permission::whereIn('id', $data['permissions'])->orWhereIn('name', $data['permissions'])->pluck('id')->toArray();
        $role->permissions()->detach($permIds);

        return response()->json($role->permissions()->get());
    }
}
