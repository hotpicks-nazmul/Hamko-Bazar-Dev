@extends('admin.layouts.app')

@section('title', 'Permissions')

@php
    $keyExists = [];
    $permEnabled = [];
    foreach ($permissions as $module => $perms) {
        foreach ($perms as $perm) {
            $keyExists[$perm->name] = true;
            $permEnabled[$perm->name] = !in_array($perm->name, $disabledPerms);
        }
    }
    $guardedSections = ['customers.view-financial', 'orders.view-customer', 'orders.view-pricing', 'products.view-cost', 'refund.view-customer', 'dashboard.view-revenue', 'dashboard.view-sales', 'analytics.view-revenue', 'analytics.view-sales'];
@endphp

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0"><i class="bi bi-shield-lock me-2"></i>Permissions</h4>
</div>

<ul class="nav nav-tabs mb-4" id="permissionsTabs" role="tablist">
    <li class="nav-item" role="presentation">
        <button class="nav-link active" id="keys-tab" data-bs-toggle="tab" data-bs-target="#keys" type="button" role="tab">
            <i class="bi bi-key me-1"></i> Permission Settings
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="roles-tab" data-bs-toggle="tab" data-bs-target="#roles" type="button" role="tab">
            <i class="bi bi-person-badge me-1"></i> Role Templates
            <span class="badge bg-secondary ms-1">{{ $roles->count() }}</span>
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="staff-tab" data-bs-toggle="tab" data-bs-target="#staff" type="button" role="tab">
            <i class="bi bi-shield-lock me-1"></i> Staff Permissions
            <span class="badge bg-secondary ms-1">{{ $staff->total() }}</span>
        </button>
    </li>
</ul>

<div class="tab-content" id="permissionsTabsContent">

    {{-- ============ TAB 1: PERMISSION KEYS (Toggle Matrix) ============ --}}
    <div class="tab-pane fade show active" id="keys" role="tabpanel">

        <div class="alert alert-info mb-3 d-flex justify-content-between align-items-center">
            <div>
                <i class="bi bi-info-circle me-2"></i>
                Toggle each action pill to <strong>create</strong> or <strong>delete</strong> permission keys.
                <span class="badge bg-success ms-2">Green = exists</span>
                <span class="badge bg-secondary ms-1">Gray = create</span>
            </div>
            <span class="text-muted small">{{ $permissions->flatten()->count() }} keys exist / {{ $moduleActions ? collect($moduleActions)->flatten()->count() : 0 }} possible</span>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h6 class="mb-0"><i class="bi bi-shield-check me-2"></i>Permission Settings</h6>
                <span class="text-muted small">{{ $permissions->flatten()->count() }} keys</span>
            </div>
            <div class="card-body p-3">
                <div class="d-none d-md-flex align-items-center px-2 py-2 mb-2 bg-light rounded text-muted small fw-semibold">
                    <div style="width: 32px; flex-shrink: 0;"></div>
                    <div style="width: 50px; flex-shrink: 0; text-align: center;">Vis</div>
                    <div style="flex: 0 0 140px; flex-shrink: 0;">Module</div>
                    <div style="flex: 1; min-width: 0;">Actions</div>
                    <div style="flex: 1; min-width: 0; text-align: right; padding-right: 8px;">Section</div>
                    <div style="width: 220px; flex-shrink: 0; text-align: right;">Page</div>
                </div>

                @forelse($permissionModules as $moduleKey => $module)
                    @php
                        $submenus = \App\Helpers\PermissionHelper::submenus()[$moduleKey] ?? [];
                        $pageSectionPerms = \App\Helpers\PermissionHelper::pageSectionPermissions();
                        $sectionActions = \App\Helpers\PermissionHelper::sectionActions();
                        $moduleSupportedActions = $moduleActions[$moduleKey] ?? [];
                        $nonSectionActions = array_values(array_filter($moduleSupportedActions, fn($a) => !in_array($a, $sectionActions)));
                        $moduleVisible = \App\Helpers\PermissionHelper::isModuleVisible($moduleKey);
                    @endphp
                    <div class="module-group card border-0 rounded-3 mb-2" data-module="{{ $moduleKey }}" style="background: #fafbfc;">
                        <div class="module-header-row d-flex align-items-center gap-2 px-3 py-2" style="cursor:pointer;">
                            <span class="module-collapse-icon text-muted" style="font-size: 0.8rem; width: 16px; flex-shrink: 0;">
                                <i class="bi bi-chevron-right"></i>
                            </span>
                            <span class="badge rounded-pill module-visibility-toggle d-inline-flex align-items-center gap-1 {{ $moduleVisible ? 'pill-blue' : 'pill-gray' }}"
                                  data-module="{{ $moduleKey }}"
                                  data-state="{{ $moduleVisible ? '1' : '0' }}"
                                  style="cursor:pointer; padding: 0.25em 0.55em; font-size: 0.75rem; flex-shrink: 0; width: 40px; text-align: center;">
                                <i class="bi bi-{{ $moduleVisible ? 'eye' : 'eye-slash' }}"></i>
                            </span>
                            <div style="flex: 0 0 130px; flex-shrink: 0; display: flex; align-items: center; gap: 6px;">
                                <i class="{{ $module['icon'] }}" style="font-size: 1rem;"></i>
                                <strong style="font-size: 0.85rem;">{{ $module['label'] }}</strong>
                            </div>
                            <div style="flex: 1; min-width: 0;">
                                @if(!empty($nonSectionActions))
                                <div class="d-flex flex-wrap gap-1">
                                    @foreach($nonSectionActions as $act)
                                        @php
                                            $permName = $moduleKey . '.' . $act;
                                            $exists = isset($permEnabled[$permName]) && $permEnabled[$permName] ?: false;
                                        @endphp
                                        <span class="badge rounded-pill perm-toggle {{ $exists ? 'pill-green' : 'pill-gray' }}"
                                              data-module="{{ $moduleKey }}"
                                              data-action="{{ $act }}"
                                              data-state="{{ $exists ? '1' : '0' }}"
                                              style="cursor:pointer; padding: 0.2em 0.65em; font-size: 0.8em;">
                                            {{ $act }}
                                        </span>
                                    @endforeach
                                </div>
                                @endif
                            </div>
                            <div style="flex: 1; min-width: 0; text-align: right; padding-right: 8px;"></div>
                            <span class="text-muted" style="font-size: 0.7rem; flex-shrink: 0; width: 50px; text-align: right;">{{ count($submenus) }}p</span>
                        </div>
                        @if(!empty($submenus))
                        <div class="module-submenu-content" style="display: none;">
                            <div style="border-top: 1px solid #e9ecef; margin: 0 12px;"></div>
                            @php
                                $allPageComponents = \App\Helpers\PermissionHelper::pageComponents();
                            @endphp
                            @foreach($submenus as $routeName => $label)
                                @php
$visible = \App\Helpers\PermissionHelper::isSubmenuVisible($routeName);
                                    $routeSections = $pageSectionPerms[$routeName] ?? [];
                                    $page = $allPageComponents[$routeName] ?? [];
                                    $pageItems = $page['items'] ?? [];
                                    $pageGroups = $page['groups'] ?? [];
                                @endphp
                                {{-- L0: Submenu row (page name + visibility toggle) --}}
                                <div style="padding: 5px 12px 5px 48px;">
                                    <span class="badge rounded-pill submenu-visibility-toggle d-inline-flex align-items-center gap-1 {{ $visible ? 'pill-blue' : 'pill-gray' }}"
                                          data-submenu="{{ $routeName }}"
                                          data-state="{{ $visible ? '1' : '0' }}"
                                          style="cursor:pointer; padding: 0.25em 0.6em; font-size: 0.78rem; white-space: nowrap;">
                                        <i class="bi bi-{{ $visible ? 'eye' : 'eye-slash' }}"></i> {{ $label }}
                                    </span>

                                    {{-- Section pills (purple) inline --}}
                                    @if(!empty($routeSections))
                                        @foreach($routeSections as $act)
                                            @php
                                                $permName = $moduleKey . '.' . $act;
                                                $exists = isset($permEnabled[$permName]) && $permEnabled[$permName] ?: false;
                                            @endphp
                                            <span class="badge rounded-pill perm-toggle {{ $exists ? 'pill-purple' : 'pill-gray' }}"
                                                  data-module="{{ $moduleKey }}"
                                                  data-action="{{ $act }}"
                                                  data-state="{{ $exists ? '1' : '0' }}"
                                                  style="cursor:pointer; padding: 0.2em 0.65em; font-size: 0.84em; vertical-align:middle;">
                                                {{ str_replace('view-', '', $act) }}
                                            </span>
                                        @endforeach
                                    @endif

                                    {{-- Direct page items (orange) inline --}}
                                    @if(!empty($pageItems))
                                        @foreach($pageItems as $itemLabel => $permName)
                                            @php $exists = isset($permEnabled[$permName]) && $permEnabled[$permName] ?: false; @endphp
                                            <span class="badge rounded-pill page-action-pill {{ $exists ? 'pill-purple' : 'pill-gray' }}"
                                                  data-full-name="{{ $permName }}"
                                                  data-state="{{ $exists ? '1' : '0' }}"
                                                  style="cursor:pointer; padding: 0.2em 0.65em; font-size: 0.78rem; vertical-align:middle;">
                                                {{ $itemLabel }}
                                            </span>
                                        @endforeach
                                    @endif
                                </div>

                                {{-- L1: Groups --}}
                                @foreach($pageGroups as $groupLabel => $group)
                                    @php
                                        $gItems = $group['items'] ?? [];
                                        $gChildren = $group['children'] ?? [];
                                    @endphp
                                    {{-- L1: Group header --}}
                                    <div style="padding: 4px 12px 2px 64px;">
                                        <small class="text-muted" style="font-size: 0.76rem;"><i class="bi bi-layout-sidebar me-1"></i>{{ $groupLabel }}</small>
                                    </div>
                                    {{-- L2: Group items --}}
                                    @if(!empty($gItems))
                                    <div style="padding: 2px 12px 4px 80px;">
                                        @foreach($gItems as $itemLabel => $permName)
                                            @php
                                                $exists = isset($permEnabled[$permName]) && $permEnabled[$permName] ?: false;
                                                $hasChild = isset($gChildren[$itemLabel]);
                                            @endphp
                                            <span class="badge rounded-pill page-action-pill {{ $hasChild ? 'has-child-page' : '' }} {{ $exists ? 'pill-purple' : 'pill-gray' }}"
                                                  data-full-name="{{ $permName }}"
                                                  data-state="{{ $exists ? '1' : '0' }}"
                                                  style="cursor:pointer; padding: 0.2em 0.65em; font-size: 0.78rem;">
                                                {{ $itemLabel }}
                                            </span>
                                        @endforeach

                                        {{-- L3: Child pages attached to group items --}}
                                        @foreach($gChildren as $childItemLabel => $childInfo)
                                            @php
                                                $childRoute = $childInfo['route'] ?? '';
                                                $childLabel = $childInfo['label'] ?? '';
                                                $childPage = $childRoute ? ($allPageComponents[$childRoute] ?? []) : [];
                                                $childItems = $childPage['items'] ?? [];
                                            @endphp
                                            @if(!empty($childItems))
                                            <div style="margin-top: 4px; padding-left: 16px;">
                                                {{-- Child page header --}}
                                                <div style="padding: 2px 0;">
                                                    <span class="badge rounded-pill d-inline-flex align-items-center gap-1"
                                                          style="padding: 0.2em 0.6em; font-size: 0.74rem; white-space: nowrap; background: #e8f4fd; color: #0d6efd; border: 1px solid #b8daff;">
                                                        <i class="bi bi-file-text"></i> {{ $childLabel }}
                                                    </span>
                                                </div>
                                                {{-- Child page items --}}
                                                <div style="padding: 2px 0 2px 16px;">
                                                    @foreach($childItems as $childItemLabel => $childPermName)
                                                        @php $exists = isset($permEnabled[$childPermName]) && $permEnabled[$childPermName] ?: false; @endphp
                                                        <span class="badge rounded-pill page-action-pill {{ $exists ? 'pill-purple' : 'pill-gray' }}"
                                                              data-full-name="{{ $childPermName }}"
                                                              data-state="{{ $exists ? '1' : '0' }}"
                                                              style="cursor:pointer; padding: 0.2em 0.65em; font-size: 0.76rem;">
                                                            {{ $childItemLabel }}
                                                        </span>
                                                    @endforeach
                                                </div>
                                            </div>
                                            @endif
                                        @endforeach
                                    </div>
                                    @endif
                                @endforeach
                            @endforeach
                        </div>
                        @endif
                    </div>
                @empty
                    <div class="text-center py-5 text-muted">
                        No permissions yet. Click <strong>"Add Module"</strong> to get started.
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    {{-- ============ TAB 2: ROLE TEMPLATES ============ --}}
    <div class="tab-pane fade" id="roles" role="tabpanel">

        <div class="alert alert-info mb-3">
            <i class="bi bi-info-circle me-2"></i>
            <strong>Role Templates:</strong> Pre-configured permission sets assignable to staff.
        </div>

        <div class="d-flex justify-content-end mb-3">
            <a href="{{ route('admin.roles.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-circle me-1"></i> Create Role
            </a>
        </div>

        <div class="row">
            @forelse($roles as $role)
            <div class="col-md-6 col-lg-4 mb-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <h6 class="mb-0">
                                <i class="bi bi-shield-check text-success me-2"></i>
                                {{ ucwords(str_replace('-', ' ', $role->name)) }}
                            </h6>
                            <div class="dropdown">
                                <button class="btn btn-sm btn-outline-secondary" data-bs-toggle="dropdown">
                                    <i class="bi bi-three-dots-vertical"></i>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    <li><a class="dropdown-item" href="{{ route('admin.roles.edit', $role->id) }}">
                                        <i class="bi bi-pencil me-2"></i> Edit
                                    </a></li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li>
                                        <form action="{{ route('admin.roles.destroy', $role->id) }}" method="POST" onsubmit="return confirm('Delete this role? Staff assigned to it will lose those permissions.');">
                                            @csrf @method('DELETE')
                                            <button class="dropdown-item text-danger"><i class="bi bi-trash me-2"></i> Delete</button>
                                        </form>
                                    </li>
                                </ul>
                            </div>
                        </div>
                        <p class="text-muted small mb-2">
                            <code>{{ $role->name }}</code> &middot; {{ $role->permissions->count() }} permissions
                        </p>
                        <div class="d-flex flex-wrap gap-1">
                            @foreach($role->permissions->take(8) as $perm)
                                <span class="badge bg-light text-dark border">{{ $perm->name }}</span>
                            @endforeach
                            @if($role->permissions->count() > 8)
                                <span class="badge bg-secondary">+{{ $role->permissions->count() - 8 }} more</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            @empty
            <div class="col-12">
                <div class="text-center py-5 text-muted">
                    <i class="bi bi-person-badge" style="font-size: 3rem;"></i>
                    <p class="mt-2">No role templates yet.</p>
                    <a href="{{ route('admin.roles.create') }}" class="btn btn-primary">
                        <i class="bi bi-plus-circle me-1"></i> Create First Role
                    </a>
                </div>
            </div>
            @endforelse
        </div>
    </div>

    {{-- ============ TAB 3: STAFF PERMISSIONS ============ --}}
    <div class="tab-pane fade" id="staff" role="tabpanel">

        <div class="card border-0 shadow-sm mb-3">
            <div class="card-body">
                <div class="alert alert-info mb-0">
                    <i class="bi bi-info-circle me-2"></i>
                    Assign <strong>role templates</strong> for quick setup or toggle individual <strong>CRUD permissions</strong> per module.
                    Super admins and admins automatically have full access.
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h6 class="mb-0"><i class="bi bi-shield-lock me-2"></i>Manage Staff Permissions</h6>
                <span class="text-muted small">{{ $staff->total() }} staff member(s)</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Staff Member</th>
                                <th>Designation</th>
                                <th>Warehouse</th>
                                <th>Role</th>
                                <th>Assigned Roles</th>
                                <th style="width: 120px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if($staff->count() > 0)
                                @foreach($staff as $member)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            @php
                                                $avatarUrl = $member->avatar;
                                                if ($avatarUrl && !str_starts_with($avatarUrl, '/storage/') && !str_starts_with($avatarUrl, 'http')) {
                                                    $avatarUrl = '/storage/' . $avatarUrl;
                                                }
                                            @endphp
                                            @if($avatarUrl)
                                                <img src="{{ $avatarUrl }}" alt="{{ $member->name }}" class="rounded-circle me-2" style="width: 40px; height: 40px; object-fit: cover;">
                                            @else
                                                <div class="bg-secondary rounded-circle d-flex align-items-center justify-content-center me-2" style="width: 40px; height: 40px;">
                                                    <i class="bi bi-person text-white"></i>
                                                </div>
                                            @endif
                                            <div>
                                                <div class="fw-medium">{{ $member->name }}</div>
                                                <div class="small text-muted">{{ $member->email }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>{{ $member->designation ?? 'N/A' }}</td>
                                    <td>
                                        @if($member->warehouse)
                                            <span class="badge bg-info">{{ $member->warehouse->name }}</span>
                                        @else
                                            <span class="text-muted">Not Assigned</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($member->role === 'super_admin')
                                            <span class="badge bg-warning text-dark"><i class="bi bi-shield-check me-1"></i> Super Admin</span>
                                        @elseif($member->role === 'admin')
                                            <span class="badge bg-primary"><i class="bi bi-shield me-1"></i> Admin</span>
                                        @else
                                            <span class="badge bg-secondary">Staff</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($member->role === 'staff')
                                            @php $spatieRoles = $member->roles ?? collect(); @endphp
                                            @if($spatieRoles->isNotEmpty())
                                                @foreach($spatieRoles as $r)
                                                    <span class="badge bg-success me-1">{{ $r->name }}</span>
                                                @endforeach
                                            @else
                                                <span class="text-muted small">Custom</span>
                                            @endif
                                        @else
                                            <span class="text-muted small">Full Access</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if(!$member->is_super_admin)
                                            <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#staffPermModal{{ $member->id }}">
                                                <i class="bi bi-gear"></i> Manage
                                            </button>
                                        @else
                                            <span class="text-muted small">No action needed</span>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            @else
                                <tr>
                                    <td colspan="6" class="text-center py-5">
                                        <i class="bi bi-people text-muted" style="font-size: 3rem;"></i>
                                        <p class="text-muted mb-2 mt-2">No staff members found</p>
                                        <a href="{{ route('admin.staffs.create') }}" class="btn btn-sm btn-primary">
                                            <i class="bi bi-plus-lg me-1"></i> Add First Staff
                                        </a>
                                    </td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
                @if($staff->hasPages())
                    <div class="px-3 py-2 border-top">{{ $staff->links() }}</div>
                @endif
            </div>
        </div>

        @if($staff->count() > 0)
            @foreach($staff as $member)
                @if(!$member->is_super_admin)
                <div class="modal fade" id="staffPermModal{{ $member->id }}" tabindex="-1">
                    <div class="modal-dialog modal-xl">
                        <form action="{{ route('admin.staffs.permissions.update') }}" method="POST" id="staffPermForm{{ $member->id }}">
                            @csrf
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title"><i class="bi bi-shield-lock me-2"></i>Permissions - {{ $member->name }}</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body">
                                    <input type="hidden" name="staff_id" value="{{ $member->id }}">

                                    <div class="card border mb-4">
                                        <div class="card-body">
                                            <label class="form-label fw-bold"><i class="bi bi-person-badge me-1"></i> Role Template</label>
                                            <select class="form-select staff-role-template" data-staff-id="{{ $member->id }}" name="role_template">
                                                <option value="">-- Custom Permissions --</option>
                                                @foreach($roleTemplates as $template)
                                                    <option value="{{ $template->id }}"
                                                        data-permissions="{{ json_encode($template->permissions->pluck('name')) }}"
                                                        {{ $member->roles->contains($template->id) ? 'selected' : '' }}>
                                                        {{ ucwords(str_replace('-', ' ', $template->name)) }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>

                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <h6 class="mb-0"><i class="bi bi-shield-check me-1"></i>Individual Permissions</h6>
                                        <div>
                                            <button type="button" class="btn btn-sm btn-outline-success staff-select-all" data-staff-id="{{ $member->id }}">
                                                <i class="bi bi-check-all me-1"></i> Select All
                                            </button>
                                            <button type="button" class="btn btn-sm btn-outline-danger staff-clear-all ms-1" data-staff-id="{{ $member->id }}">
                                                <i class="bi bi-x-circle me-1"></i> Clear All
                                            </button>
                                        </div>
                                    </div>

                                    <div class="staff-perm-modules">
                                        @foreach($permissionModules as $moduleKey => $module)
                                        @php
                                            $modulePrefix = $module['key'];
                                            $submenus = \App\Helpers\PermissionHelper::submenus()[$moduleKey] ?? [];
                                            $pageSectionPerms = \App\Helpers\PermissionHelper::pageSectionPermissions();
                                            $sectionActions = \App\Helpers\PermissionHelper::sectionActions();
                                            $moduleSupportedActions = $moduleActions[$moduleKey] ?? [];
                                            $nonSectionActions = array_values(array_filter($moduleSupportedActions, fn($a) => !in_array($a, $sectionActions)));
                                            $globalVisible = \App\Helpers\PermissionHelper::isModuleVisible($moduleKey);
                                        @endphp
                                        <div class="card border-0 rounded-3 mb-2 staff-module-group" style="background: #fafbfc;">
                                            <div class="staff-module-header d-flex align-items-center gap-2 px-3 py-2" style="cursor:pointer;">
                                                <span class="staff-collapse-icon text-muted" style="font-size: 0.8rem; width: 16px; flex-shrink: 0;">
                                                    <i class="bi bi-chevron-right"></i>
                                                </span>
                                                <div style="flex: 0 0 130px; flex-shrink: 0; display: flex; align-items: center; gap: 6px;">
                                                    <i class="{{ $module['icon'] }}" style="font-size: 1rem;"></i>
                                                    <strong style="font-size: 0.85rem;">{{ $module['label'] }}</strong>
                                                </div>
                                                <div style="flex: 1; min-width: 0;">
                                                    @if(!empty($nonSectionActions))
                                                    <div class="d-flex flex-wrap gap-1">
                                                        @foreach($nonSectionActions as $action)
                                                            @php
                                                                $aPermName = $modulePrefix . '.' . $action;
                                                                $aChecked = $member->hasPermission($aPermName) ? '1' : '0';
                                                                $aGloballyDisabled = in_array($aPermName, $disabledPerms);
                                                            @endphp
                                                            <span class="badge rounded-pill staff-perm-pill {{ $aChecked == '1' ? 'pill-green' : ($aGloballyDisabled ? 'pill-disabled-global' : 'pill-gray') }}"
                                                                  data-staff-id="{{ $member->id }}"
                                                                  data-perm="{{ $aPermName }}"
                                                                  data-state="{{ $aChecked }}"
                                                                  title="{{ $aGloballyDisabled ? 'Globally disabled in Permission Keys (Tab 1). Toggle ON there first.' : '' }}"
                                                                  style="cursor:pointer; padding: 0.2em 0.65em; font-size: 0.8em;">
                                                                {{ $action }}@if($aGloballyDisabled) <i class="bi bi-exclamation-triangle-fill" style="font-size:0.75em;"></i>@endif
                                                            </span>
                                                        @endforeach
                                                    </div>
                                                    @endif
                                                </div>
                                                <span class="text-muted" style="font-size: 0.7rem; flex-shrink: 0;">{{ count($submenus) }}p</span>
                                            </div>
                                            <div class="staff-submenu-content" style="display: none;">
                                                <div style="border-top: 1px solid #e9ecef; margin: 0 12px;"></div>
                                                @php
                                                    $allStaffPageComponents = \App\Helpers\PermissionHelper::pageComponents();
                                                @endphp
                                                @foreach($submenus as $routeName => $label)
                                                @php
$visGlobal = \App\Helpers\PermissionHelper::isSubmenuVisible($routeName);
                                                    $permName = 'submenu:' . $routeName;
                                                    $disabledPerm = 'submenu_disabled:' . $routeName;
                                                    $legacyPerms = is_array($member->legacy_permissions) ? $member->legacy_permissions : json_decode($member->legacy_permissions ?? '[]', true);
                                                    $hasPerm = empty($legacyPerms) ? true : !in_array($disabledPerm, $legacyPerms);
                                                    $routeSections = $pageSectionPerms[$routeName] ?? [];
                                                    $staffPageData = $allStaffPageComponents[$routeName] ?? [];
                                                    $staffPageItems = $staffPageData['items'] ?? [];
                                                    $staffPageGroups = $staffPageData['groups'] ?? [];
                                                @endphp
                                                @if($visGlobal)
                                                <div style="padding: 5px 12px 5px 44px;">
                                                    <span class="badge rounded-pill staff-submenu-pill d-inline-flex align-items-center gap-1 {{ $hasPerm ? 'pill-blue' : 'pill-gray' }}"
                                                          data-staff-id="{{ $member->id }}"
                                                          data-submenu="{{ $routeName }}"
                                                          data-state="{{ $hasPerm ? '1' : '0' }}"
                                                          style="cursor:pointer; padding: 0.25em 0.6em; font-size: 0.78rem; white-space: nowrap;">
                                                        <i class="bi bi-{{ $hasPerm ? 'eye' : 'eye-slash' }}"></i> {{ $label }}
                                                    </span>
                                                    {{-- Section pills (purple) inline per-submenu --}}
                                                    @if(!empty($routeSections))
                                                        @foreach($routeSections as $action)
                                                            @php
                                                                $sPermName = $modulePrefix . '.' . $action;
                                                                if (!in_array($sPermName, $guardedSections)) continue;
                                                                $sChecked = $member->hasPermission($sPermName) ? '1' : '0';
                                                                $sGloballyDisabled = in_array($sPermName, $disabledPerms);
                                                            @endphp
                                                            <span class="badge rounded-pill staff-perm-pill {{ $sChecked == '1' ? 'pill-purple' : ($sGloballyDisabled ? 'pill-disabled-global' : 'pill-gray') }}"
                                                                  data-staff-id="{{ $member->id }}"
                                                                  data-perm="{{ $sPermName }}"
                                                                  data-state="{{ $sChecked }}"
                                                                  title="{{ $sGloballyDisabled ? 'Globally disabled in Permission Keys (Tab 1). Toggle ON there first.' : '' }}"
                                                                  style="cursor:pointer; padding: 0.2em 0.65em; font-size: 0.84em; vertical-align:middle;">
                                                                {{ str_replace('view-', '', $action) }}@if($sGloballyDisabled) <i class="bi bi-exclamation-triangle-fill" style="font-size:0.75em;"></i>@endif
                                                            </span>
                                                        @endforeach
                                                    @endif
                                                    {{-- Staff page action items (orange) inline --}}
                                                    @if(!empty($staffPageItems))
                                                        @foreach($staffPageItems as $itemLabel => $itemPermName)
                                                            @php $pChecked = $member->hasPermission($itemPermName) ? '1' : '0'; @endphp
                                                            <span class="badge rounded-pill staff-perm-pill {{ $pChecked == '1' ? 'pill-purple' : 'pill-gray' }}"
                                                                  data-staff-id="{{ $member->id }}"
                                                                  data-perm="{{ $itemPermName }}"
                                                                  data-state="{{ $pChecked }}"
                                                                  data-orange="1"
                                                                  style="cursor:pointer; padding: 0.2em 0.65em; font-size: 0.78rem; vertical-align:middle;">
                                                                {{ $itemLabel }}
                                                            </span>
                                                        @endforeach
                                                    @endif
                                                </div>

                                                {{-- L1: Groups --}}
                                                @foreach($staffPageGroups as $gLabel => $group)
                                                    @php
                                                        $gItems = $group['items'] ?? [];
                                                        $gChildren = $group['children'] ?? [];
                                                        // Skip groups where all items are section permissions (already covered by purple pills)
                                                        $allSection = !empty($gItems) && collect($gItems)->every(function($perm, $label) use ($sectionActions) {
                                                            return collect($sectionActions)->contains(fn($a) => str_ends_with($perm, '.' . $a));
                                                        });
                                                    @endphp
                                                    @if(!$allSection)
                                                    {{-- L1: Group header --}}
                                                    <div style="padding: 4px 12px 2px 64px;">
                                                        <small class="text-muted" style="font-size: 0.76rem;"><i class="bi bi-layout-sidebar me-1"></i>{{ $gLabel }}</small>
                                                    </div>
                                                    {{-- L2: Group items --}}
                                                    @if(!empty($gItems))
                                                    <div style="padding: 2px 12px 4px 80px;">
                                                        @foreach($gItems as $giLabel => $giPermName)
                                                            @php
                                                                $giChecked = $member->hasPermission($giPermName) ? '1' : '0';
                                                                $hasChild = isset($gChildren[$giLabel]);
                                                            @endphp
                                                            <span class="badge rounded-pill staff-perm-pill {{ $hasChild ? 'has-child-page' : '' }} {{ $giChecked == '1' ? 'pill-purple' : 'pill-gray' }}"
                                                                  data-staff-id="{{ $member->id }}"
                                                                  data-perm="{{ $giPermName }}"
                                                                  data-state="{{ $giChecked }}"
                                                                  data-orange="1"
                                                                  style="cursor:pointer; padding: 0.2em 0.65em; font-size: 0.78rem;">
                                                                {{ $giLabel }}
                                                            </span>
                                                        @endforeach

                                                        {{-- L3: Child pages attached to group items --}}
                                                        @foreach($gChildren as $childItemLabel => $childInfo)
                                                            @php
                                                                $childRoute = $childInfo['route'] ?? '';
                                                                $childLabel = $childInfo['label'] ?? '';
                                                                $childPage = $childRoute ? ($allStaffPageComponents[$childRoute] ?? []) : [];
                                                                $childItems = $childPage['items'] ?? [];
                                                            @endphp
                                                            @if(!empty($childItems))
                                                            <div style="margin-top: 4px; padding-left: 16px;">
                                                                {{-- Child page header --}}
                                                                <div style="padding: 2px 0;">
                                                                    <span class="badge rounded-pill d-inline-flex align-items-center gap-1"
                                                                          style="padding: 0.2em 0.6em; font-size: 0.74rem; white-space: nowrap; background: #e8f4fd; color: #0d6efd; border: 1px solid #b8daff;">
                                                                        <i class="bi bi-file-text"></i> {{ $childLabel }}
                                                                    </span>
                                                                </div>
                                                                {{-- Child page items --}}
                                                                <div style="padding: 2px 0 2px 16px;">
                                                                    @foreach($childItems as $ciLabel => $ciPermName)
                                                                        @php $ciChecked = $member->hasPermission($ciPermName) ? '1' : '0'; @endphp
                                                                        <span class="badge rounded-pill staff-perm-pill {{ $ciChecked == '1' ? 'pill-purple' : 'pill-gray' }}"
                                                                              data-staff-id="{{ $member->id }}"
                                                                              data-perm="{{ $ciPermName }}"
                                                                              data-state="{{ $ciChecked }}"
                                                                              data-orange="1"
                                                                              style="cursor:pointer; padding: 0.2em 0.65em; font-size: 0.76rem;">
                                                                            {{ $ciLabel }}
                                                                        </span>
                                                                    @endforeach
                                                                </div>
                                                            </div>
                                                            @endif
                                                        @endforeach
                                                    </div>
                                                    @endif
                                                @endif
                                                @endforeach
                                                @endif
                                                @endforeach
                                            </div>
                                        </div>
                                        @endforeach
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><i class="bi bi-x-lg me-1"></i> Cancel</button>
                                    <button type="button" class="btn btn-primary staff-save-perms" data-form-id="staffPermForm{{ $member->id }}">
                                        <i class="bi bi-check-lg me-1"></i> Save Changes
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
                @endif
            @endforeach
        @endif
    </div>

</div>
@endsection

@push('styles')
<style>
    .bi::before, [class*="bi bi-"]::before {
        display: inline-block !important;
        visibility: visible !important;
        opacity: 1 !important;
        font-family: 'bootstrap-icons' !important;
    }
    .perm-toggle:hover, .staff-perm-pill:hover {
        transform: scale(1.08);
        box-shadow: 0 2px 6px rgba(0,0,0,0.15);
    }
    /* Permission pill colors - edit here to change globally */
    .pill-green { background: #198754; color: #fff; }
    .pill-purple { background: #6f42c1; color: #fff; }
    .pill-blue { background: #0d6efd; color: #fff; }
    .pill-orange { background: #e86c00; color: #fff; }
    .pill-gray { background: #e9ecef; color: #6c757d; }
    .pill-disabled-global { background: #fff3cd; color: #856404; border: 1px solid #ffc107; }
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {

    // ============ MODULE COLLAPSE/EXPAND ============
    document.querySelectorAll('.module-header-row').forEach(function(header) {
        header.addEventListener('click', function(e) {
            if (e.target.closest('.perm-toggle, .page-action-pill, .module-visibility-toggle')) return;
            const group = this.closest('.module-group');
            const content = group.querySelector('.module-submenu-content');
            const icon = this.querySelector('.module-collapse-icon i');
            const expanded = content.style.display !== 'none';
            content.style.display = expanded ? 'none' : 'block';
            icon.className = expanded ? 'bi bi-chevron-right' : 'bi bi-chevron-down';
        });
    });

    // ============ RESTORE & PERSIST ACTIVE TAB ============
    var savedTab = localStorage.getItem('permissionsActiveTab');
    if (savedTab) {
        var tabEl = document.querySelector('#permissionsTabs button[data-bs-target="' + savedTab + '"]');
        if (tabEl) {
            bootstrap.Tab.getOrCreateInstance(tabEl).show();
        }
    }
    document.querySelectorAll('#permissionsTabs button[data-bs-toggle="tab"]').forEach(function(tab) {
        tab.addEventListener('shown.bs.tab', function() {
            localStorage.setItem('permissionsActiveTab', this.dataset.bsTarget);
        });
    });

    // ============ TAB 1: Permission Key Toggles (AJAX create/delete) ============
    document.querySelectorAll('.perm-toggle').forEach(function(pill) {
        pill.addEventListener('click', function() {
            const module = this.dataset.module;
            const action = this.dataset.action;
            const wasOn = this.dataset.state === '1';
            const originalHtml = this.innerHTML;
            this.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';

            fetch('{{ route('admin.permissions.toggle-key') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ module: module, action: action })
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    this.dataset.state = data.state ? '1' : '0';
                    const sectionActions = ['view-customer', 'view-pricing', 'view-cost', 'view-financial', 'view-revenue', 'view-sales'];
                    const isSection = sectionActions.includes(this.dataset.action);
                    if (data.state) {
                        this.classList.remove('pill-gray');
                        this.classList.add(isSection ? 'pill-purple' : 'pill-green');
                        if (typeof adminToast === 'function') {
                            adminToast('success', 'Created', "'" + module + '.' + action + "' created.");
                        }
                    } else {
                        this.classList.remove('pill-green', 'pill-purple');
                        this.classList.add('pill-gray');
                        if (typeof adminToast === 'function') {
                            adminToast('warning', 'Removed', "'" + module + '.' + action + "' removed.");
                        }
                    }
                }
                this.innerHTML = originalHtml;
            })
            .catch(function() {
                this.innerHTML = originalHtml;
                if (typeof adminToast === 'function') {
                    adminToast('error', 'Error', 'Failed to toggle permission.');
                }
            }.bind(this));
        });
    });

    // Module visibility toggle
    document.querySelectorAll('.module-visibility-toggle').forEach(function(toggle) {
        toggle.addEventListener('click', function() {
            const module = this.dataset.module;
            const icon = this.querySelector('i');
            fetch('/admin/permissions/toggle-visibility/' + module, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                }
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    this.dataset.state = data.visible ? '1' : '0';
                    if (data.visible) {
                        icon.className = 'bi bi-eye';
                        this.classList.remove('pill-gray');
                        this.classList.add('pill-blue');
                    } else {
                        icon.className = 'bi bi-eye-slash';
                        this.classList.remove('pill-blue');
                        this.classList.add('pill-gray');
                    }
                    if (typeof adminToast === 'function') {
                        adminToast(data.visible ? 'success' : 'warning', 'Sidebar Visibility', data.message);
                    }
                }
            });
        });
    });

    // Submenu visibility toggle (clickable badge)
    document.querySelectorAll('.submenu-visibility-toggle').forEach(function(badge) {
        badge.addEventListener('click', function() {
            const submenuKey = this.dataset.submenu;
            const icon = this.querySelector('i');

            fetch('/admin/permissions/toggle-submenu/' + submenuKey, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                }
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    this.dataset.state = data.visible ? '1' : '0';
                    if (data.visible) {
                        icon.className = 'bi bi-eye';
                        this.classList.remove('pill-gray');
                        this.classList.add('pill-blue');
                    } else {
                        icon.className = 'bi bi-eye-slash';
                        this.classList.remove('pill-blue');
                        this.classList.add('pill-gray');
                    }
                    if (typeof adminToast === 'function') {
                        adminToast(data.visible ? 'success' : 'warning', 'Submenu Visibility', data.message);
                    }
                }
            });
        });
    });

    // ============ TAB 3: Staff Permission Pills (visual toggle, saved on submit) ============
    const sectionActions = ['view-customer', 'view-pricing', 'view-cost', 'view-financial', 'view-revenue', 'view-sales'];
    document.querySelectorAll('.staff-perm-pill').forEach(function(pill) {
        pill.addEventListener('click', function() {
            const newState = this.dataset.state === '1' ? '0' : '1';
            this.dataset.state = newState;
            if (newState === '1') {
                const perm = this.dataset.perm;
                const isSection = sectionActions.some(function(a) {
                    return perm.endsWith('.' + a);
                });
                const isOrange = this.dataset.orange === '1';
                this.style.background = isOrange ? '#6f42c1' : (isSection ? '#6f42c1' : '#198754');
                this.style.color = '#fff';
            } else {
                this.style.background = '#e9ecef';
                this.style.color = '#6c757d';
            }
        });
    });

    // Submenu pill toggle (clickable, stays blue when enabled)
    document.querySelectorAll('.staff-submenu-pill').forEach(function(pill) {
        pill.addEventListener('click', function() {
            const newState = this.dataset.state === '1' ? '0' : '1';
            this.dataset.state = newState;
            if (newState === '1') {
                this.style.background = '#0d6efd';
                this.style.color = '#fff';
            } else {
                this.style.background = '#e9ecef';
                this.style.color = '#6c757d';
            }
        });
    });

    // ============ STAFF MODULE COLLAPSE/EXPAND ============
    document.querySelectorAll('.staff-module-header').forEach(function(header) {
        header.addEventListener('click', function(e) {
            if (e.target.closest('.staff-perm-pill, .staff-submenu-pill')) return;
            const group = this.closest('.staff-module-group');
            const content = group.querySelector('.staff-submenu-content');
            const icon = this.querySelector('.staff-collapse-icon i');
            const expanded = content.style.display !== 'none';
            content.style.display = expanded ? 'none' : 'block';
            icon.className = expanded ? 'bi bi-chevron-right' : 'bi bi-chevron-down';
        });
    });

    // Role template selection fills pills
    document.querySelectorAll('.staff-role-template').forEach(function(select) {
        select.addEventListener('change', function() {
            const staffId = this.dataset.staffId;
            const selected = this.options[this.selectedIndex];
            const form = document.getElementById('staffPermForm' + staffId);
            if (!form) return;

            // Reset all pills in this form to OFF
            form.querySelectorAll('.staff-perm-pill, .staff-submenu-pill').forEach(function(p) {
                p.dataset.state = '0';
                p.style.background = '#e9ecef';
                p.style.color = '#6c757d';
            });

            if (this.value) {
                try {
                    const perms = JSON.parse(selected.dataset.permissions || '[]');
                    perms.forEach(function(permName) {
                        // Regular permission pill
                        const pill = form.querySelector('.staff-perm-pill[data-perm="' + permName + '"]');
                        if (pill) {
                            pill.dataset.state = '1';
                            const isSection = sectionActions.some(function(a) {
                                return permName.endsWith('.' + a);
                            });
                            const isOrange = pill.dataset.orange === '1';
                            pill.style.background = isOrange ? '#6f42c1' : (isSection ? '#6f42c1' : '#198754');
                            pill.style.color = '#fff';
                        }
                        // Submenu permission pill (submenu:routeName format)
                        if (permName.startsWith('submenu:')) {
                            const subpill = form.querySelector('.staff-submenu-pill[data-submenu="' + permName.replace('submenu:', '') + '"]');
                            if (subpill) {
                                subpill.dataset.state = '1';
                                subpill.style.background = '#0d6efd';
                                subpill.style.color = '#fff';
                            }
                        }
                    });
                } catch(e) {}
            }
        });
    });

    // Select All within a staff form
    document.querySelectorAll('.staff-select-all').forEach(function(btn) {
        btn.addEventListener('click', function() {
            const staffId = this.dataset.staffId;
            const form = document.getElementById('staffPermForm' + staffId);
            if (!form) return;
            form.querySelectorAll('.staff-perm-pill').forEach(function(p) {
                p.dataset.state = '1';
                const perm = p.dataset.perm;
                const isSection = sectionActions.some(function(a) {
                    return perm.endsWith('.' + a);
                });
                const isOrange = p.dataset.orange === '1';
                p.style.background = isOrange ? '#6f42c1' : (isSection ? '#6f42c1' : '#198754');
                p.style.color = '#fff';
            });
            form.querySelectorAll('.staff-submenu-pill').forEach(function(p) {
                p.dataset.state = '1';
                p.style.background = '#0d6efd';
                p.style.color = '#fff';
            });
        });
    });

    // Clear All within a staff form
    document.querySelectorAll('.staff-clear-all').forEach(function(btn) {
        btn.addEventListener('click', function() {
            const staffId = this.dataset.staffId;
            const form = document.getElementById('staffPermForm' + staffId);
            if (!form) return;
            form.querySelectorAll('.staff-perm-pill, .staff-submenu-pill').forEach(function(p) {
                p.dataset.state = '0';
                p.style.background = '#e9ecef';
                p.style.color = '#6c757d';
            });
            const select = form.querySelector('.staff-role-template');
            if (select) select.value = '';
        });
    });

    // Save permissions via AJAX
    document.querySelectorAll('.staff-save-perms').forEach(function(btn) {
        btn.addEventListener('click', function() {
            const formId = this.dataset.formId;
            const form = document.getElementById(formId);
            if (!form) return;

            // Build permissions array from pills that are ON
            const checkedPerms = [];
            form.querySelectorAll('.staff-perm-pill[data-state="1"]').forEach(function(p) {
                checkedPerms.push(p.dataset.perm);
            });

            // Build submenu permissions array
            // ON: submenu:routeName, OFF: submenu_disabled:routeName
            const checkedSubmenus = [];
            const uncheckedSubmenus = [];
            form.querySelectorAll('.staff-submenu-pill').forEach(function(p) {
                if (p.dataset.state === '1') {
                    checkedSubmenus.push('submenu:' + p.dataset.submenu);
                } else {
                    uncheckedSubmenus.push('submenu_disabled:' + p.dataset.submenu);
                }
            });

            const formData = new FormData(form);
            // Remove any existing permissions[] entries and add our built list
            checkedPerms.forEach(function(p) {
                formData.append('permissions[]', p);
            });
            // Add enabled submenus
            checkedSubmenus.forEach(function(p) {
                formData.append('permissions[]', p);
            });
            // Add disabled submenus
            uncheckedSubmenus.forEach(function(p) {
                formData.append('permissions[]', p);
            });

            const submitBtn = this;
            const originalText = submitBtn.innerHTML;
            const modalEl = form.closest('.modal');

            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Saving...';

            fetch(form.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            })
            .then(function(response) {
                if (!response.ok) throw new Error('Request failed');
                return response.json();
            })
            .then(function(data) {
                const modal = bootstrap.Modal.getInstance(modalEl);
                if (modal) modal.hide();
                if (typeof adminToast === 'function') {
                    adminToast('success', 'Success!', 'Permissions updated successfully.');
                } else {
                    alert('Permissions saved successfully!');
                }
                setTimeout(function() { location.reload(); }, 1500);
            })
            .catch(function(err) {
                const modal = bootstrap.Modal.getInstance(modalEl);
                if (modal) modal.hide();
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
                if (typeof adminToast === 'function') {
                    adminToast('error', 'Error!', 'Failed to save permissions.');
                } else {
                    alert('Failed to save permissions.');
                }
            });
        });
    });

    // ============ Page Action Toggles (orange pills, use full_name) ============
    document.querySelectorAll('.page-action-pill').forEach(function(pill) {
        pill.addEventListener('click', function() {
            const fullName = this.dataset.fullName;
            const wasOn = this.dataset.state === '1';
            const originalHtml = this.innerHTML;
            this.innerHTML = '<span class=\"spinner-border spinner-border-sm\"></span>';

            fetch('{{ route('admin.permissions.toggle-key') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ full_name: fullName })
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    this.dataset.state = data.state ? '1' : '0';
                    if (data.state) {
                        this.style.background = '#6f42c1';
                        this.style.color = '#fff';
                        if (typeof adminToast === 'function') {
                            adminToast('success', 'Created', "'" + fullName + "' created.");
                        }
                    } else {
                        this.style.background = '#e9ecef';
                        this.style.color = '#6c757d';
                        if (typeof adminToast === 'function') {
                            adminToast('warning', 'Removed', "'" + fullName + "' removed.");
                        }
                    }
                }
                this.innerHTML = originalHtml;
            })
            .catch(function() {
                this.innerHTML = originalHtml;
                if (typeof adminToast === 'function') {
                    adminToast('error', 'Error', 'Failed to toggle permission.');
                }
            }.bind(this));
        });
    });
});
</script>
@endpush
