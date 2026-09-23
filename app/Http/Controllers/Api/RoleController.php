<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class RoleController extends Controller
{
    public function index()
    {
        return Role::with('permissions')->withCount('users')->get();
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'permission_ids' => 'array',
            'permission_ids.*' => 'exists:permissions,id',
        ]);

        $role = Role::create([
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
        ]);

        $role->permissions()->sync($data['permission_ids'] ?? []);

        return $role->load('permissions');
    }

    public function show(Role $role)
    {
        return $role->load('permissions')->loadCount('users');
    }

    public function update(Request $request, Role $role)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('roles', 'name')->ignore($role->id)],
            'description' => 'nullable|string',
            'permission_ids' => 'array',
            'permission_ids.*' => 'exists:permissions,id',
        ]);

        // Si este rol es crítico (is_admin_role), no puede quedarse sin la
        // capacidad de administrar seguridad — o nadie podría corregirlo después.
        if ($role->is_admin_role) {
            $criticalPermissionIds = Permission::whereIn('name', ['users.manage', 'roles.manage', 'settings.manage'])->pluck('id');
            $submittedIds = collect($data['permission_ids'] ?? []);

            if ($criticalPermissionIds->diff($submittedIds)->isNotEmpty()) {
                abort(422, 'No se le pueden quitar los permisos de seguridad (users.manage, roles.manage, settings.manage) a un rol administrador — el sistema quedaría sin nadie que pueda corregirlo.');
            }
        }

        $role->update([
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
        ]);

        $role->permissions()->sync($data['permission_ids'] ?? []);

        return $role->load('permissions');
    }

    public function destroy(Role $role)
    {
        if ($role->users()->exists()) {
            return response()->json([
                'message' => 'Cannot delete a role that has users assigned.',
            ], 422);
        }

        $role->delete();

        return response()->json(null, 204);
    }
}
