<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Admin\Concerns\SortsLists;
use App\Helpers\ImageHelper;
use App\Helpers\PermissionHelper;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class StaffController extends Controller
{
    use SortsLists;

    /**
     * Display a listing of staff members.
     */
    public function index(Request $request)
    {
        $stats = $this->getStats();

        $currentUser = auth()->user();
        if ($currentUser->role === 'super_admin') {
            $query = User::adminPanel()->with('warehouse');
        } else {
            $query = User::staff()->with('warehouse');
        }

        if ($request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('designation', 'like', "%{$search}%");
            });
        }

        if ($request->status) {
            $query->where('status', $request->status);
        }

        if ($request->warehouse_id) {
            $query->where('warehouse_id', $request->warehouse_id);
        }

        // `?sort_by=nope` was SQLSTATE 42S22, `?sort_order=sideways` a SQL syntax error and
        // `?per_page=abc` a type error on both staff lists; the page sends sort_by/sort_order, so the
        // trait reads those names rather than the default sort/direction.
        $query->orderBy(
            $this->sortColumn($request, ['created_at', 'name', 'email', 'phone', 'designation', 'status', 'warehouse_id'], 'created_at', 'sort_by'),
            $this->sortDirection($request, 'desc', 'sort_order')
        );

        $staffs = $query->paginate($this->perPage($request, 10, 100));

        $warehouses = Warehouse::where('is_active', 1)->orderBy('name')->get();

        if ($request->ajax()) {
            $showingText = '';
            if ($staffs->hasPages()) {
                $showingText = "Showing {$staffs->firstItem()} - {$staffs->lastItem()} of {$staffs->total()} staffs";
            }
            return response()->json([
                'html' => view('admin.staffs.partials.table-rows', compact('staffs'))->render(),
                'stats' => $stats,
                'pagination' => $staffs->links()->toHtml(),
                'showing_text' => $showingText,
            ]);
        }

        return view('admin.staffs.index', compact('staffs', 'stats', 'warehouses'));
    }

    /**
     * Get statistics for staff
     */
    protected function getStats()
    {
        $currentUser = auth()->user();

        if ($currentUser->role === 'super_admin') {
            $users = User::adminPanel();
        } else {
            $users = User::staff();
        }

        return [
            'total' => (clone $users)->count(),
            'active' => (clone $users)->where('status', 'active')->count(),
            'inactive' => (clone $users)->where('status', 'inactive')->count(),
            'banned' => (clone $users)->where('status', 'banned')->count(),
        ];
    }

    /**
     * Show the form for creating a new staff member.
     */
    public function create()
    {
        $user = auth()->user();

        $allowedRoles = [];
        if ($user->role === 'super_admin') {
            $allowedRoles = ['admin', 'staff'];
        } elseif ($user->role === 'admin') {
            $allowedRoles = ['staff'];
        } else {
            $allowedRoles = ['staff'];
        }

        $warehouses = Warehouse::where('is_active', 1)->orderBy('name')->get();
        $roleTemplates = Role::where('guard_name', 'web')->with('permissions')->orderBy('name')->get();
        $permissionModules = PermissionHelper::modules();

        return view('admin.staffs.create', compact('warehouses', 'allowedRoles', 'roleTemplates', 'permissionModules'));
    }

    /**
     * Store a newly created staff member.
     */
    public function store(Request $request)
    {
        $user = auth()->user();

        if ($user->role === 'staff') {
            abort(403, 'Unauthorized access. Staff members cannot create other staff.');
        }

        $allowedRoles = [];
        if ($user->role === 'super_admin') {
            $allowedRoles = ['admin', 'staff'];
        } elseif ($user->role === 'admin') {
            $allowedRoles = ['staff'];
        } else {
            $allowedRoles = ['staff'];
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email',
            'phone' => 'nullable|string|max:20',
            'password' => 'required|string|min:8|confirmed',
            'designation' => 'nullable|string|max:255',
            'warehouse_id' => 'nullable|exists:warehouses,id',
            'role' => 'required|in:' . implode(',', $allowedRoles),
            'is_super_admin' => 'boolean',
            'status' => 'required|in:active,inactive,banned',
            'role_template' => 'nullable|string',
            'custom_permissions' => 'nullable|array',
            'custom_permissions.*' => 'exists:permissions,name',
            // The avatar had no rule at all: a file that failed ImageHelper's check was dropped silently,
            // so the operator saw "created successfully" with no picture.
            'avatar' => 'nullable|image|mimes:jpeg,jpg,png,webp,gif|max:2048',
        ], [
            'avatar.image' => 'The photo must be an image file.',
            'avatar.mimes' => 'The photo must be a JPEG, PNG, WebP or GIF.',
        ]);

        if ($request->is_super_admin && $user->role !== 'super_admin') {
            abort(403, 'Only super administrators can grant super admin privileges.');
        }

        $staff = new User();
        $staff->name = $request->name;
        $staff->email = $request->email;
        $staff->phone = $request->phone;
        $staff->password = Hash::make($request->password);
        $staff->role = $request->role;
        $staff->designation = $request->designation;
        $staff->warehouse_id = $request->warehouse_id;
        $staff->is_super_admin = $request->is_super_admin ?? false;
        $staff->status = $request->status;

        if ($request->hasFile('avatar')) {
            if (ImageHelper::isValidImage($request->file('avatar'))) {
                $imageResult = ImageHelper::processImage(
                    $request->file('avatar'),
                    'staffs',
                    512,
                    150,
                    85
                );
                $staff->avatar = ltrim($imageResult['path'], '/');
            }
        }

        $staff->save();

        // Assign Spatie role template if selected
        if ($request->role_template && $staff->role === 'staff') {
            $roleTemplate = Role::findById($request->role_template);
            if ($roleTemplate) {
                $staff->assignRole($roleTemplate);
            }
        }

        // Assign custom granular permissions if provided
        if ($request->custom_permissions && $staff->role === 'staff') {
            $permIds = \Spatie\Permission\Models\Permission::whereIn('name', $request->custom_permissions)
                ->where('guard_name', 'web')
                ->pluck('id')->toArray();
            $staff->permissions()->sync($permIds);

            // Also save legacy module-level permissions for backward compat
            $moduleKeys = $this->extractModuleKeys($request->custom_permissions);
            $staff->legacy_permissions = $moduleKeys;
            $staff->save();
        }

        $roleLabel = ucfirst($request->role);
        return redirect()->route('admin.staffs.index')
            ->with('success', $roleLabel . ' created successfully.');
    }

    /**
     * Show the form for editing the specified staff member.
     */
    public function edit($id)
    {
        $currentUser = auth()->user();

        if ($currentUser->role === 'super_admin') {
            $staff = User::adminPanel()->findOrFail($id);
            $allowedRoles = ['admin', 'staff'];
        } else {
            $staff = User::staff()->findOrFail($id);
            $allowedRoles = ['staff'];
        }

        $staff->load('roles', 'permissions');

        $warehouses = Warehouse::where('is_active', 1)->orderBy('name')->get();
        $roleTemplates = Role::where('guard_name', 'web')->with('permissions')->orderBy('name')->get();
        $permissionModules = PermissionHelper::modules();

        return view('admin.staffs.edit', compact('staff', 'warehouses', 'allowedRoles', 'roleTemplates', 'permissionModules'));
    }

    /**
     * Update the specified staff member.
     */
    public function update(Request $request, $id)
    {
        $currentUser = auth()->user();

        if ($currentUser->role === 'super_admin') {
            $staff = User::adminPanel()->findOrFail($id);
            $allowedRoles = ['admin', 'staff'];
        } else {
            $staff = User::staff()->findOrFail($id);
            $allowedRoles = ['staff'];
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users')->ignore($staff->id),
            ],
            'phone' => 'nullable|string|max:20',
            'password' => 'nullable|string|min:8|confirmed',
            'designation' => 'nullable|string|max:255',
            'warehouse_id' => 'nullable|exists:warehouses,id',
            'role' => 'sometimes|in:' . implode(',', $allowedRoles),
            'is_super_admin' => 'boolean',
            'status' => 'required|in:active,inactive,banned',
            'role_template' => 'nullable|string',
            'custom_permissions' => 'nullable|array',
            'custom_permissions.*' => 'exists:permissions,name',
            'avatar' => 'nullable|image|mimes:jpeg,jpg,png,webp,gif|max:2048',
        ], [
            'avatar.image' => 'The photo must be an image file.',
            'avatar.mimes' => 'The photo must be a JPEG, PNG, WebP or GIF.',
        ]);

        if ($request->is_super_admin && $currentUser->role !== 'super_admin') {
            abort(403, 'Only super administrators can grant super admin privileges.');
        }

        $staff->name = $request->name;
        $staff->email = $request->email;
        $staff->phone = $request->phone;
        $staff->designation = $request->designation;
        $staff->warehouse_id = $request->warehouse_id;
        if ($request->has('is_super_admin')) {
            $staff->is_super_admin = $request->is_super_admin ?? false;
        }
        $staff->status = $request->status;

        if ($request->has('role') && in_array($request->role, $allowedRoles)) {
            $staff->role = $request->role;
        }

        if ($request->password) {
            $staff->password = Hash::make($request->password);
        }

        if ($request->hasFile('avatar')) {
            if ($staff->avatar) {
                ImageHelper::deleteImage($staff->avatar);
            }
            if (ImageHelper::isValidImage($request->file('avatar'))) {
                $imageResult = ImageHelper::processImage(
                    $request->file('avatar'),
                    'staffs',
                    512,
                    150,
                    85
                );
                $staff->avatar = ltrim($imageResult['path'], '/');
            }
        }

        $staff->save();

        // Manage Spatie role assignment
        if ($staff->role === 'staff') {
            if ($request->has('role_template')) {
                if ($request->role_template) {
                    $roleTemplate = Role::findById($request->role_template);
                    if ($roleTemplate) {
                        $staff->syncRoles([$roleTemplate]);
                    }
                } else {
                    $staff->syncRoles([]);
                }
            }
            if ($request->has('custom_permissions')) {
                $permIds = \Spatie\Permission\Models\Permission::whereIn('name', $request->custom_permissions)
                    ->where('guard_name', 'web')
                    ->pluck('id')->toArray();
                $staff->permissions()->sync($permIds);

                $moduleKeys = $this->extractModuleKeys($request->custom_permissions);
                $staff->legacy_permissions = $moduleKeys;
                $staff->save();
            }
        }

        return redirect()->route('admin.staffs.index')
            ->with('success', 'Staff member updated successfully.');
    }

    /**
     * Remove the specified staff member.
     */
    public function destroy($id)
    {
        $currentUser = auth()->user();

        if ($currentUser->role === 'super_admin') {
            $staff = User::adminPanel()->findOrFail($id);
        } else {
            $staff = User::staff()->findOrFail($id);
        }

        if ($staff->id === $currentUser->id) {
            abort(403, 'You cannot delete your own account.');
        }

        if ($staff->role === 'super_admin') {
            $superAdminCount = User::where('role', 'super_admin')->count();
            if ($superAdminCount <= 1) {
                abort(403, 'Cannot delete the last super administrator.');
            }
        }

        if ($staff->avatar) {
            ImageHelper::deleteImage($staff->avatar);
        }

        $staff->permissions()->detach();
        $staff->roles()->detach();
        $staff->delete();

        return redirect()->route('admin.staffs.index')
            ->with('success', 'Staff member deleted successfully.');
    }

    /**
     * Display warehouse staff page.
     */
    public function warehouse(Request $request)
    {
        $currentUser = auth()->user();

        if ($currentUser->role === 'staff') {
            abort(403, 'Unauthorized access. Staff members cannot access warehouse staff page.');
        }

        if ($currentUser->role === 'super_admin' || $currentUser->role === 'admin') {
            $query = User::adminPanel()->whereNotNull('warehouse_id')->with('warehouse');
        } else {
            $query = User::staff()->whereNotNull('warehouse_id')->with('warehouse');
        }

        if ($request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        if ($request->warehouse_id) {
            $query->where('warehouse_id', $request->warehouse_id);
        }

        if ($request->status) {
            $query->where('status', $request->status);
        }

        // `?sort_by=nope` was SQLSTATE 42S22, `?sort_order=sideways` a SQL syntax error and
        // `?per_page=abc` a type error on both staff lists; the page sends sort_by/sort_order, so the
        // trait reads those names rather than the default sort/direction.
        $query->orderBy(
            $this->sortColumn($request, ['created_at', 'name', 'email', 'phone', 'designation', 'status', 'warehouse_id'], 'created_at', 'sort_by'),
            $this->sortDirection($request, 'desc', 'sort_order')
        );

        $staffs = $query->paginate($this->perPage($request, 10, 100));

        $warehouses = Warehouse::where('is_active', 1)->orderBy('name')->get();

        if ($request->ajax()) {
            $showingText = '';
            if ($staffs->hasPages()) {
                $showingText = "Showing {$staffs->firstItem()} - {$staffs->lastItem()} of {$staffs->total()} staffs";
            }
            return response()->json([
                'html' => view('admin.staffs.partials.warehouse-table-rows', compact('staffs'))->render(),
                'pagination' => $staffs->links()->toHtml(),
                'showing_text' => $showingText,
                'stats' => [
                    'total' => $staffs->total(),
                    'active' => (clone $query)->where('status', 'active')->count(),
                    'inactive' => (clone $query)->where('status', 'inactive')->count(),
                ],
            ]);
        }

        $stats = [
            'total' => $staffs->total(),
            'active' => (clone $query)->where('status', 'active')->count(),
            'inactive' => (clone $query)->where('status', 'inactive')->count(),
        ];

        return view('admin.staffs.warehouse', compact('staffs', 'warehouses', 'stats'));
    }

    /**
     * Display staff permissions page.
     */
    public function permissions(Request $request)
    {
        $currentUser = auth()->user();

        if ($currentUser->role === 'staff') {
            abort(403, 'Unauthorized access. Staff members cannot manage permissions.');
        }

        if ($currentUser->role === 'super_admin' || $currentUser->role === 'admin') {
            $query = User::adminPanel()->with('warehouse');
        } else {
            $query = User::staff()->with('warehouse');
        }

        if ($request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($request->warehouse_id) {
            $query->where('warehouse_id', $request->warehouse_id);
        }

        $perPage = $request->per_page ?? 25;
        $staff = $query->orderBy('created_at', 'desc')->paginate($perPage);

        $warehouses = Warehouse::where('is_active', 1)->orderBy('name')->get();
        $permissionModules = PermissionHelper::modules();
        $roleTemplates = Role::where('guard_name', 'web')->with('permissions')->orderBy('name')->get();

        return view('admin.staffs.permissions', compact('staff', 'warehouses', 'permissionModules', 'roleTemplates'));
    }

    /**
     * Update staff permissions.
     */
    public function updatePermissions(Request $request)
    {
        $currentUser = auth()->user();

        if (!$currentUser || !in_array($currentUser->role, ['super_admin', 'admin'])) {
            if ($request->ajax()) {
                return response()->json(['message' => 'Only super admins and admins can manage permissions.'], 403);
            }
            abort(403, 'Only super admins and admins can manage permissions.');
        }

        $request->validate([
            'staff_id' => 'required|exists:users,id',
            'permissions' => 'nullable|array',
            // A permission name is `module.action`. Without a shape rule any string was accepted and
            // silently minted as a new permission row ('invented.action.that.did.not.exist' became one).
            // The Staff Permissions modal also posts the sidebar markers `submenu:route.name` and
            // `submenu_disabled:route.name` into this same array — the method below splits them out.
            // The old rule rejected them, so every save from that modal answered 422 and no permission
            // was ever stored.
            'permissions.*' => [
                'string',
                'max:150',
                'regex:/^(submenu:|submenu_disabled:)?[a-z0-9-]+(\.[a-z0-9-]+)*$/',
            ],
            'role_template' => 'nullable|string',
        ]);

        // Target scoping, same shape as the per-record routes: a plain admin manages staff only. It used
        // to resolve any admin-panel account, so an admin could rewrite the super admin's permissions.
        $staff = $currentUser->role === 'super_admin'
            ? User::adminPanel()->findOrFail($request->staff_id)
            : User::staff()->findOrFail($request->staff_id);

        // Separate submenu permissions from regular permissions
        $allPerms = $request->permissions ?? [];
        $submenuEnabled = array_filter($allPerms, fn($p) => str_starts_with($p, 'submenu:'));
        $submenuDisabled = array_filter($allPerms, fn($p) => str_starts_with($p, 'submenu_disabled:'));
        $regularPerms = array_filter($allPerms, fn($p) => !str_starts_with($p, 'submenu:') && !str_starts_with($p, 'submenu_disabled:'));

        // Handle Spatie granular permissions (regular permissions only)
        if (!empty($regularPerms)) {
            $permNames = array_values($regularPerms);

            // Auto-create any permissions that don't exist yet
            $existingPerms = \Spatie\Permission\Models\Permission::whereIn('name', $permNames)
                ->where('guard_name', 'web')
                ->pluck('name')
                ->toArray();
            $missingPerms = array_diff($permNames, $existingPerms);
            foreach ($missingPerms as $missingName) {
                \Spatie\Permission\Models\Permission::create([
                    'name' => $missingName,
                    'guard_name' => 'web',
                ]);
            }

            $permIds = \Spatie\Permission\Models\Permission::whereIn('name', $permNames)
                ->where('guard_name', 'web')
                ->pluck('id')
                ->toArray();
            $staff->permissions()->sync($permIds);

            // Also save module-level keys in legacy column for backward compat
            $moduleKeys = $this->extractModuleKeys($regularPerms);
            $staff->legacy_permissions = $moduleKeys;
        } else {
            $staff->permissions()->detach();
            $staff->legacy_permissions = null;
        }

        // Handle submenu permissions (stored in legacy_permissions)
        // ON by default unless explicitly disabled
        $submenuPerms = array_values($submenuEnabled);
        $submenuDisabledPerms = array_values($submenuDisabled);
        $existingLegacy = $staff->legacy_permissions ?? [];
        if (!is_array($existingLegacy)) {
            $existingLegacy = json_decode($existingLegacy ?? '[]', true) ?? [];
        }
        // Keep non-submenu legacy permissions
        $nonSubmenuLegacy = array_filter($existingLegacy, fn($p) => !str_starts_with($p, 'submenu:') && !str_starts_with($p, 'submenu_disabled:'));
        $staff->legacy_permissions = array_merge($nonSubmenuLegacy, $submenuPerms, $submenuDisabledPerms);

        // Handle role template assignment
        if ($request->has('role_template')) {
            if ($request->role_template) {
                $roleTemplate = Role::findById($request->role_template);
                if ($roleTemplate) {
                    $staff->syncRoles([$roleTemplate]);
                }
            } else {
                $staff->syncRoles([]);
            }
        }

        $staff->save();

        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Permissions updated successfully.',
            ]);
        }

        return redirect()->route('admin.permissions.index')
            ->with('success', 'Permissions updated successfully.');
    }

    /**
     * Bulk actions on staff members.
     */
    public function bulkAction(Request $request)
    {
        $currentUser = auth()->user();

        if ($currentUser->role === 'staff') {
            abort(403, 'Unauthorized access. Staff members cannot perform bulk actions.');
        }

        // The page posts `JSON.stringify([...])` — a string — while validation demanded an array, so every
        // bulk button answered 422 ("The staff ids field must be an array"). Accept either shape.
        $request->validate([
            'action' => 'required|in:activate,deactivate,delete',
            'staff_ids' => 'required',
        ]);

        $raw = $request->input('staff_ids');

        if (is_string($raw)) {
            $decoded = json_decode($raw, true);
            $raw = is_array($decoded) ? $decoded : array_filter(explode(',', $raw));
        }

        $staffIds = is_array($raw) ? array_values(array_unique(array_map('intval', $raw))) : [];
        $staffIds = array_values(array_filter($staffIds));

        if (empty($staffIds)) {
            return back()->with('error', 'No staff members selected.');
        }

        // Scope the target set exactly like the per-record routes do. Without this the bulk path was a
        // privilege hole: a plain admin could deactivate (and delete) the super admin account by posting
        // its id, while /admin/staffs/{id} correctly answered 404 for the same row.
        $query = $currentUser->role === 'super_admin' ? User::adminPanel() : User::staff();
        $staffs = $query->whereIn('id', $staffIds)->get();

        if ($staffs->isEmpty()) {
            $message = $currentUser->role === 'super_admin'
                ? 'No matching staff members were found.'
                : 'You can only act on staff members from this screen.';

            return back()->with('error', $message);
        }

        // Acting on your own row through a bulk button is an easy way to lock yourself out.
        $selfSkipped = $staffs->contains(fn ($s) => (int) $s->id === (int) $currentUser->id);
        $staffs = $staffs->reject(fn ($s) => (int) $s->id === (int) $currentUser->id);
        $targetIds = $staffs->pluck('id')->all();

        if (empty($targetIds)) {
            return back()->with('error', 'You cannot change your own account from a bulk action.');
        }


        $action = $request->action;

        switch ($action) {
            case 'activate':
                User::whereIn('id', $targetIds)->update(['status' => 'active']);
                $message = $staffs->count() . ' staff member(s) have been activated.';
                break;

            case 'deactivate':
                // Refuse to empty the panel of its last working super admin.
                $deactivatingSuperAdmins = $staffs->where('role', 'super_admin')->pluck('id')->all();
                if ($deactivatingSuperAdmins) {
                    $remaining = User::where('role', 'super_admin')
                        ->where('status', 'active')
                        ->whereNotIn('id', $deactivatingSuperAdmins)
                        ->count();

                    if ($remaining === 0) {
                        return back()->with('error', 'Cannot deactivate the last active super administrator.');
                    }
                }

                User::whereIn('id', $targetIds)->update(['status' => 'inactive']);
                $message = $staffs->count() . ' staff member(s) have been deactivated.';
                break;

            case 'delete':
                // Protect the last super admin (this stays even with the scoping above, because a super
                // admin deleting the only other super admin leaves the panel with none).
                if ($staffs->where('role', 'super_admin')->isNotEmpty()) {
                    $remaining = User::where('role', 'super_admin')->whereNotIn('id', $targetIds)->count();
                    if ($remaining === 0) {
                        return back()->with('error', 'Cannot delete the last super administrator.');
                    }
                }

                foreach ($staffs as $staff) {
                    if ($staff->avatar) {
                        ImageHelper::deleteImage($staff->avatar);
                    }
                }

                foreach ($staffs as $staff) {
                    $staff->permissions()->detach();
                    $staff->roles()->detach();
                }

                User::whereIn('id', $targetIds)->delete();
                $message = $staffs->count() . ' staff member(s) have been deleted.';
                break;

            default:
                return back()->with('error', 'Unknown action.');
        }

        if ($selfSkipped) {
            $message .= ' Your own account was skipped.';
        }

        return redirect()->route('admin.staffs.index')->with('success', $message);
    }

    /**
     * Extract module-level permission keys from granular permissions.
     * E.g., ['products.view', 'products.edit'] -> ['products']
     */
    protected function extractModuleKeys(array $granularPermissions): array
    {
        $moduleKeys = [];
        foreach ($granularPermissions as $perm) {
            if (str_contains($perm, '.')) {
                $module = explode('.', $perm)[0];
                if (!in_array($module, $moduleKeys)) {
                    $moduleKeys[] = $module;
                }
            } else {
                if (!in_array($perm, $moduleKeys)) {
                    $moduleKeys[] = $perm;
                }
            }
        }
        return $moduleKeys;
    }
}
