<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Http\Request;
use Str;

class RoleController extends Controller
{
    public function index()
    {
        $roles = Role::with('permissions')->get();
        return view('admin.roles.index', compact('roles'));
    }

    public function create()
    {
        $permissions = Permission::all();
        return view('admin.roles.create', compact('permissions'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'min:3', 'unique:roles,name']
        ]);
        Role::create([
            'name' => $validated['name'],
            'guard_name' => 'web'
        ]);

        return to_route('admin.roles.index')->with('message', 'Role Created successfully.');
    }

    public function edit(Role $role)
    {
        $permissions = Permission::all()->reject(function ($permission) {
            return Str::contains($permission->name, 'observationHistory');
        });

        $groupedPermissions = $permissions->groupBy(function ($permission) {
            $name = $permission->name;

            if (Str::contains($name, '-')) {
                return Str::before($name, '-');
            } elseif (Str::contains($name, ' ')) {
                return Str::afterLast($name, ' ');
            }

            return 'others';
        });

        return view('admin.roles.edit', compact('role', 'groupedPermissions'));
    }

    public function update(Request $request, Role $role)
    {
        $request->validate([
            'name' => 'required|unique:roles,name,' . $role->id,
            'permissions' => 'array',
            'permissions.*' => 'exists:permissions,id'
        ]);
        $role->update(['name' => $request->name]);

        $role->permissions()->sync($request->permissions);

        return redirect()->back()->with('success', 'Role updated successfully.');
    }

    public function destroy(Role $role)
    {
        $role->delete();
        return redirect()->route('admin.roles.index')->with('success', 'Role deleted successfully.');
    }

    public function assignRole()
    {
        $users = User::all();
        $roles = Role::all();
        return view('admin.roles.assign', compact('users', 'roles'));
    }

    // public function assignRoleToUser(Request $request)
    // {
    //     $request->validate([
    //         'user_id' => 'required|exists:users,id',
    //         'role' => 'required|exists:roles,name',
    //     ]);

    //     $user = User::findOrFail($request->user_id);
    //     $user->syncRoles([$request->role]);

    //     return redirect()->route('admin.roles.assign')->with('success', 'Role assigned successfully.');
    // }

    public function givePermission(Request $request, Role $role)
    {
        if ($role->hasPermissionTo($request->permission)) {
            return back()->with('message', 'Permission exists.');
        }
        $role->givePermissionTo($request->permission);
        return back()->with('message', 'Permission added.');
    }


    public function revokePermission(Role $role, Permission $permission)
    {
        if ($role->hasPermissionTo($permission)) {
            $role->revokePermissionTo($permission);
            return back()->with('message', 'Permission revoked.');
        }
        return back()->with('message', 'Permission not exists.');
    }
}
