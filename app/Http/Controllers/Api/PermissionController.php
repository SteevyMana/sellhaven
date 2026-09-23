<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use Illuminate\Http\Request;

class PermissionController extends Controller
{
    public function index()
    {
        return Permission::all();
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255|unique:permissions,name',
            'module' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        return Permission::create($data);
    }

    public function show(Permission $permission)
    {
        return $permission;
    }

    public function update(Request $request, Permission $permission)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255|unique:permissions,name,' . $permission->id,
            'module' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $permission->update($data);

        return $permission;
    }

    public function destroy(Permission $permission)
    {
        if ($permission->roles()->exists()) {
            abort(422, 'you cannot delete a permission that is assigned to roles.');
        }

        $permission->delete();

        return response()->json(null, 204);
    }
}