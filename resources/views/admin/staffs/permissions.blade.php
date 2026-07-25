@extends('admin.layouts.app')

@section('title', 'Staff Permissions')

@section('content')
<!-- Header -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">Staff Permissions</h4>
    <a href="{{ route('admin.staffs.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i> Back to Staffs
    </a>
</div>

<!-- Info Card -->
<div class="card border-0 shadow-sm mb-3">
    <div class="card-body">
        <div class="alert alert-info mb-0">
            <i class="bi bi-info-circle me-2"></i>
            <strong>Enhanced Permission System:</strong> Assign <strong>role templates</strong> for quick setup or customize individual <strong>CRUD permissions</strong> per module. 
            Super admins and admins automatically have full access.
        </div>
    </div>
</div>

<!-- Staff List with Permissions -->
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
                    @forelse($staff as $member)
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
                                    <span class="badge bg-warning text-dark">
                                        <i class="bi bi-shield-check me-1"></i> Super Admin
                                    </span>
                                @elseif($member->role === 'admin')
                                    <span class="badge bg-primary">
                                        <i class="bi bi-shield me-1"></i> Admin
                                    </span>
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
                                    <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#permissionsModal{{ $member->id }}">
                                        <i class="bi bi-gear"></i> Manage
                                    </button>
                                @else
                                    <span class="text-muted small">No action needed</span>
                                @endif
                            </td>
                        </tr>

                        <!-- Permissions Modal -->
                        @if(!$member->is_super_admin)
                        <div class="modal fade" id="permissionsModal{{ $member->id }}" tabindex="-1" aria-labelledby="permissionsModalLabel{{ $member->id }}" aria-hidden="true">
                            <div class="modal-dialog modal-xl">
                                <form action="{{ route('admin.staffs.permissions.update') }}" method="POST" id="permissionsForm{{ $member->id }}">
                                    @csrf
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="permissionsModalLabel{{ $member->id }}">
                                                <i class="bi bi-shield-lock me-2"></i>Manage Permissions - {{ $member->name }}
                                            </h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            <input type="hidden" name="staff_id" value="{{ $member->id }}">

                                            <!-- Role Template Selection -->
                                            <div class="card border mb-4">
                                                <div class="card-body">
                                                    <label class="form-label fw-bold">
                                                        <i class="bi bi-person-badge me-1"></i> Role Template (Quick Setup)
                                                    </label>
                                                    <div class="row align-items-end">
                                                        <div class="col-md-6">
                                                            <select class="form-select role-template-select" data-staff-id="{{ $member->id }}" name="role_template">
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
                                                        <div class="col-md-6">
                                                            <span class="text-muted small">
                                                                Selecting a template will auto-fill permissions below. You can then customize further.
                                                            </span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Granular CRUD Permissions by Module -->
                                            <div class="mb-2 d-flex justify-content-between align-items-center">
                                                <h6 class="mb-0"><i class="bi bi-shield-check me-1"></i>Individual Permissions</h6>
                                                <div>
                                                    <button type="button" class="btn btn-sm btn-outline-success select-all-modules" data-staff-id="{{ $member->id }}">
                                                        <i class="bi bi-check-all me-1"></i> Select All
                                                    </button>
                                                    <button type="button" class="btn btn-sm btn-outline-danger deselect-all-modules ms-1" data-staff-id="{{ $member->id }}">
                                                        <i class="bi bi-x-circle me-1"></i> Clear All
                                                    </button>
                                                </div>
                                            </div>

                                            <div class="accordion" id="permAccordion{{ $member->id }}">
                                                @foreach($permissionModules as $moduleKey => $module)
                                                    @php
                                                        $modulePrefix = $module['key'];
                                                        $hasView = $member->hasPermission($modulePrefix . '.view') || $member->hasPermission($modulePrefix);
                                                        $hasCreate = $member->hasPermission($modulePrefix . '.create');
                                                        $hasEdit = $member->hasPermission($modulePrefix . '.edit');
                                                        $hasDelete = $member->hasPermission($modulePrefix . '.delete');
                                                        $hasExport = $member->hasPermission($modulePrefix . '.export');
                                                        $hasImport = $member->hasPermission($modulePrefix . '.import');
                                                        // Check if legacy permission exists (full module access)
                                                        $hasModule = $member->hasPermission($modulePrefix) && !$member->hasPermission($modulePrefix . '.view');
                                                        $moduleGranted = $hasModule || $hasView;
                                                    @endphp
                                                    <div class="accordion-item">
                                                        <h2 class="accordion-header">
                                                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                                                data-bs-target="#permCollapse{{ $member->id }}_{{ $moduleKey }}">
                                                                <span class="me-2"><i class="{{ $module['icon'] }}"></i></span>
                                                                <strong>{{ $module['label'] }}</strong>
                                                                @if($moduleGranted)
                                                                    <span class="badge bg-success ms-2 module-status-badge" id="badgeStatus{{ $member->id }}_{{ $moduleKey }}">
                                                                        {{ $hasModule ? 'Full' : 'Partial' }}
                                                                    </span>
                                                                @else
                                                                    <span class="badge bg-secondary ms-2 module-status-badge" id="badgeStatus{{ $member->id }}_{{ $moduleKey }}">None</span>
                                                                @endif
                                                            </button>
                                                        </h2>
                                                        <div id="permCollapse{{ $member->id }}_{{ $moduleKey }}" class="accordion-collapse collapse" data-bs-parent="#permAccordion{{ $member->id }}">
                                                            <div class="accordion-body bg-light">
                                                                <!-- Select all / none for this module -->
                                                                <div class="mb-2">
                                                                    <button type="button" class="btn btn-sm btn-outline-primary select-module-btn"
                                                                        data-staff-id="{{ $member->id }}" data-module="{{ $modulePrefix }}">
                                                                        <i class="bi bi-check-square me-1"></i> All {{ count($module['actions']) }} actions
                                                                    </button>
                                                                    <button type="button" class="btn btn-sm btn-outline-secondary deselect-module-btn ms-1"
                                                                        data-staff-id="{{ $member->id }}" data-module="{{ $modulePrefix }}">
                                                                        <i class="bi bi-square me-1"></i> None
                                                                    </button>
                                                                </div>

                                                                <div class="row">
                                                                    @foreach($module['actions'] as $action)
                                                                        @php
                                                                            $permName = $modulePrefix . '.' . $action;
                                                                            $checked = $member->hasPermission($permName) ? 'checked' : '';
                                                                            $actionLabel = ucfirst($action);
                                                                            $actionIcons = [
                                                                                'view'   => 'bi-eye',
                                                                                'create' => 'bi-plus-circle',
                                                                                'edit'   => 'bi-pencil',
                                                                                'delete' => 'bi-trash',
                                                                                'export' => 'bi-download',
                                                                                'import' => 'bi-upload',
                                                                            ];
                                                                            $icon = $actionIcons[$action] ?? 'bi-check';
                                                                        @endphp
                                                                        <div class="col-md-4 col-lg-3 mb-2">
                                                                            <div class="form-check">
                                                                                <input class="form-check-input perm-checkbox perm-{{ $member->id }}-{{ $modulePrefix }}"
                                                                                    type="checkbox" name="permissions[]" value="{{ $permName }}"
                                                                                    id="perm_{{ $modulePrefix }}_{{ $action }}_{{ $member->id }}"
                                                                                    {{ $checked }}>
                                                                                <label class="form-check-label" for="perm_{{ $modulePrefix }}_{{ $action }}_{{ $member->id }}">
                                                                                    <i class="{{ $icon }} me-1"></i> {{ $actionLabel }}
                                                                                </label>
                                                                            </div>
                                                                        </div>
                                                                    @endforeach
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                                <i class="bi bi-x-lg me-1"></i> Cancel
                                            </button>
                                            <button type="submit" class="btn btn-primary">
                                                <i class="bi bi-check-lg me-1"></i> Save Changes
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                        @endif
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-5">
                                <i class="bi bi-people text-muted" style="font-size: 3rem;"></i>
                                <p class="text-muted mb-2 mt-2">No staff members found</p>
                                <a href="{{ route('admin.staffs.create') }}" class="btn btn-sm btn-primary">
                                    <i class="bi bi-plus-lg me-1"></i> Add First Staff
                                </a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($staff->hasPages())
            <div class="px-3 py-2 border-top">
                {{ $staff->links() }}
            </div>
        @endif
    </div>
</div>
@endsection

@push('styles')
<style>
    .bi::before,
    [class*="bi bi-"]::before {
        display: inline-block !important;
        visibility: visible !important;
        opacity: 1 !important;
        font-family: 'bootstrap-icons' !important;
    }
</style>
@endpush

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {

        function updateModuleBadges(staffId) {
            const form = document.getElementById('permissionsForm' + staffId);
            if (!form) return;

            const checkboxes = form.querySelectorAll('.perm-checkbox');
            const moduleMap = {};

            checkboxes.forEach(function(cb) {
                const val = cb.value;
                if (val.includes('.')) {
                    const module = val.split('.')[0];
                    if (!moduleMap[module]) moduleMap[module] = { total: 0, checked: 0 };
                    moduleMap[module].total++;
                    if (cb.checked) moduleMap[module].checked++;
                }
            });

            Object.keys(moduleMap).forEach(function(module) {
                const badge = document.getElementById('badgeStatus' + staffId + '_' + module);
                if (badge) {
                    const m = moduleMap[module];
                    badge.className = 'badge ms-2 module-status-badge';
                    if (m.checked === 0) {
                        badge.classList.add('bg-secondary');
                        badge.textContent = 'None';
                    } else if (m.checked === m.total) {
                        badge.classList.add('bg-success');
                        badge.textContent = 'Full';
                    } else {
                        badge.classList.add('bg-info');
                        badge.textContent = m.checked + '/' + m.total;
                    }
                }
            });
        }

        // Handle role template selection
        document.querySelectorAll('.role-template-select').forEach(function(select) {
            select.addEventListener('change', function() {
                const staffId = this.getAttribute('data-staff-id');
                const selectedOption = this.options[this.selectedIndex];

                if (this.value) {
                    try {
                        const permissions = JSON.parse(selectedOption.getAttribute('data-permissions') || '[]');
                        document.querySelectorAll('#permissionsForm' + staffId + ' .perm-checkbox').forEach(cb => cb.checked = false);
                        permissions.forEach(function(permName) {
                            const cb = document.querySelector('#permissionsForm' + staffId + ' input[value="' + permName + '"]');
                            if (cb) cb.checked = true;
                        });
                    } catch(e) {}
                }
                updateModuleBadges(staffId);
            });
        });

        // Select all modules
        document.querySelectorAll('.select-all-modules').forEach(function(btn) {
            btn.addEventListener('click', function() {
                const staffId = this.getAttribute('data-staff-id');
                document.querySelectorAll('#permissionsForm' + staffId + ' .perm-checkbox').forEach(cb => cb.checked = true);
                updateModuleBadges(staffId);
            });
        });

        // Deselect all modules
        document.querySelectorAll('.deselect-all-modules').forEach(function(btn) {
            btn.addEventListener('click', function() {
                const staffId = this.getAttribute('data-staff-id');
                document.querySelectorAll('#permissionsForm' + staffId + ' .perm-checkbox').forEach(cb => cb.checked = false);
                const select = document.querySelector('#permissionsForm' + staffId + ' .role-template-select');
                if (select) select.value = '';
                updateModuleBadges(staffId);
            });
        });

        // Select all actions within a module
        document.querySelectorAll('.select-module-btn').forEach(function(btn) {
            btn.addEventListener('click', function() {
                const staffId = this.getAttribute('data-staff-id');
                const modulePrefix = this.getAttribute('data-module');
                document.querySelectorAll('.perm-' + staffId + '-' + modulePrefix).forEach(cb => cb.checked = true);
                updateModuleBadges(staffId);
            });
        });

        // Deselect all actions within a module
        document.querySelectorAll('.deselect-module-btn').forEach(function(btn) {
            btn.addEventListener('click', function() {
                const staffId = this.getAttribute('data-staff-id');
                const modulePrefix = this.getAttribute('data-module');
                document.querySelectorAll('.perm-' + staffId + '-' + modulePrefix).forEach(cb => cb.checked = false);
                updateModuleBadges(staffId);
            });
        });

        // Update badges when individual checkboxes change
        document.querySelectorAll('.perm-checkbox').forEach(function(cb) {
            cb.addEventListener('change', function() {
                const form = this.closest('form');
                if (form) {
                    const match = form.id.match(/permissionsForm(\d+)/);
                    if (match) updateModuleBadges(match[1]);
                }
            });
        });
    });
</script>
@endpush
