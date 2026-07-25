<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class PermissionRoleController extends Controller
{
    private function authorizeAccess(): void
    {
        $user = auth()->user();
        if (!$user || $user->role !== 'super_admin') {
            abort(403, 'Only super admins can manage role templates.');
        }
    }

    /**
     * List all role templates.
     */
    public function index()
    {
        $this->authorizeAccess();
        $roles = Role::where('guard_name', 'web')
            ->with('permissions')
            ->orderBy('name')
            ->get();

        return view('admin.permissions.roles', compact('roles'));
    }

    /**
     * Show create form.
     */
    public function create()
    {
        $this->authorizeAccess();

        $permissions = Permission::where('guard_name', 'web')
            ->orderBy('name')
            ->get()
            ->groupBy(function ($perm) {
                return explode('.', $perm->name)[0];
            });

        $permissionModules = \App\Helpers\PermissionHelper::modules();
        $moduleActions = \App\Helpers\PermissionHelper::moduleActions();

        return view('admin.permissions.role-create', compact('permissions', 'permissionModules', 'moduleActions'));
    }

    /**
     * Store a new role.
     */
    public function store(Request $request)
    {
        $this->authorizeAccess();

        $request->validate([
            'name' => 'required|string|max:50|regex:/^[a-z-]+$/|unique:roles,name',
            'permissions' => 'nullable|array',
            'permissions.*' => 'string',
            'submenu_visibility' => 'nullable|array',
            'submenu_visibility.*' => 'string',
        ]);

        $role = Role::create([
            'name' => $request->name,
            'guard_name' => 'web',
        ]);

        if ($request->permissions) {
            $permIds = Permission::whereIn('name', $request->permissions)
                ->where('guard_name', 'web')
                ->pluck('id')
                ->toArray();
            $role->syncPermissions($permIds);
        }

        // Save submenu visibility to the role itself
        if ($request->has('submenu_visibility')) {
            $role->submenu_visibility = $request->submenu_visibility;
            $role->save();
        }

        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        return redirect()->route('admin.roles.index')
            ->with('success', "Role '{$request->name}' created successfully.");
    }

    /**
     * Show edit form.
     */
    public function edit($id)
    {
        $this->authorizeAccess();

        $role = Role::with('permissions')->findOrFail($id);

        // Decode JSON submenu_visibility (Spatie's Role model doesn't auto-cast)
        $rawVisibility = $role->submenu_visibility;
        $role->submenu_visibility = is_array($rawVisibility)
            ? $rawVisibility
            : (json_decode($rawVisibility, true) ?? []);

        $permissions = Permission::where('guard_name', 'web')
            ->orderBy('name')
            ->get()
            ->groupBy(function ($perm) {
                return explode('.', $perm->name)[0];
            });

        $permissionModules = \App\Helpers\PermissionHelper::modules();
        $moduleActions = \App\Helpers\PermissionHelper::moduleActions();

        return view('admin.permissions.role-edit', compact('role', 'permissions', 'permissionModules', 'moduleActions'));
    }

    /**
     * Update role.
     */
    public function update(Request $request, $id)
    {
        $this->authorizeAccess();

        $role = Role::findOrFail($id);

        $request->validate([
            'name' => ['required', 'string', 'max:50', 'regex:/^[a-z-]+$/', Rule::unique('roles')->ignore($role->id)],
            'permissions' => 'nullable|array',
            'permissions.*' => 'string',
            'submenu_visibility' => 'nullable|array',
            'submenu_visibility.*' => 'string',
        ]);

        $role->name = $request->name;
        $role->save();

        if ($request->permissions) {
            $permIds = Permission::whereIn('name', $request->permissions)
                ->where('guard_name', 'web')
                ->pluck('id')
                ->toArray();
            $role->syncPermissions($permIds);
        } else {
            $role->syncPermissions([]);
        }

        // Cascade updated permissions to all staff members assigned this role
        $rolePermissionNames = $role->permissions->pluck('name')->toArray();
        $affectedUsers = User::role($role->name)->get();

        // Parse and save submenu visibility to the role itself
        $roleSubmenuEnabled = [];
        $roleSubmenuDisabled = [];
        if ($request->has('submenu_visibility')) {
            foreach ($request->submenu_visibility as $v) {
                if (str_starts_with($v, 'submenu:')) {
                    $roleSubmenuEnabled[] = $v;
                } elseif (str_starts_with($v, 'submenu_disabled:')) {
                    $roleSubmenuDisabled[] = $v;
                }
            }
        }
        $roleSubmenuSettings = array_merge($roleSubmenuEnabled, $roleSubmenuDisabled);
        $role->submenu_visibility = $roleSubmenuSettings;
        $role->save();

        foreach ($affectedUsers as $user) {
            // Don't sync role permissions to user level — Spatie's hasPermission()
            // checks both direct and role-inherited permissions automatically.
            // Only update legacy_permissions for the old permission system.
            $moduleKeys = [];
            foreach ($rolePermissionNames as $perm) {
                if (str_contains($perm, '.')) {
                    $module = explode('.', $perm)[0];
                    if (!in_array($module, $moduleKeys)) {
                        $moduleKeys[] = $module;
                    }
                }
            }
            // Apply role's submenu visibility settings (overrides any existing user-level settings)
            $user->legacy_permissions = array_merge($moduleKeys, $roleSubmenuSettings);
            $user->save();
        }

        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        return redirect()->route('admin.roles.index')
            ->with('success', "Role '{$request->name}' updated successfully.");
    }

    /**
     * Delete a role.
     */
    public function destroy($id)
    {
        $this->authorizeAccess();

        $role = Role::findOrFail($id);
        $name = $role->name;
        $role->delete();

        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        return redirect()->route('admin.roles.index')
            ->with('success', "Role '$name' deleted successfully.");
    }
}
