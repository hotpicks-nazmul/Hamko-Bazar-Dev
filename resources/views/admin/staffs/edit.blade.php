@extends('admin.layouts.app')

@section('title', 'Edit Staff Member')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">Edit Staff Member</h4>
    <a href="{{ route('admin.staffs.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i> Back to Staffs
    </a>
</div>

<form id="staffForm" method="POST" action="{{ route('admin.staffs.update', $staff->id) }}" enctype="multipart/form-data">
    @csrf
    @method('PUT')
    <div class="row">
        <div class="col-lg-8">
            <!-- Basic Information -->
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-white">
                    <h6 class="mb-0"><i class="bi bi-person me-2"></i>Basic Information</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="name" class="form-label">Full Name <span class="text-danger">*</span></label>
                            <input type="text" id="name" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $staff->name) }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="email" class="form-label">Email Address <span class="text-danger">*</span></label>
                            <input type="email" id="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $staff->email) }}" required>
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="phone" class="form-label">Phone Number</label>
                            <input type="tel" id="phone" name="phone" class="form-control @error('phone') is-invalid @enderror" value="{{ old('phone', $staff->phone) }}" oninput="this.value = this.value.replace(/[^0-9+\-\s]/g, '')">
                            @error('phone')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="designation" class="form-label">Designation</label>
                            <input type="text" id="designation" name="designation" class="form-control @error('designation') is-invalid @enderror" value="{{ old('designation', $staff->designation) }}" placeholder="e.g. Sales Manager, Support Agent">
                            @error('designation')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="password" class="form-label">New Password</label>
                            <input type="password" id="password" name="password" class="form-control @error('password') is-invalid @enderror" placeholder="Leave blank to keep current">
                            @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="password_confirmation" class="form-label">Confirm New Password</label>
                            <input type="password" id="password_confirmation" name="password_confirmation" class="form-control" placeholder="Leave blank to keep current">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Avatar Upload -->
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-white">
                    <h6 class="mb-0"><i class="bi bi-image me-2"></i>Profile Photo</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            @php
                                $avatarUrl = $staff->avatar;
                                if ($avatarUrl && !str_starts_with($avatarUrl, '/storage/') && !str_starts_with($avatarUrl, 'http')) {
                                    $avatarUrl = '/storage/' . $avatarUrl;
                                }
                            @endphp
                            @if($avatarUrl)
                                <img src="{{ $avatarUrl }}" alt="{{ $staff->name }}" class="img-thumbnail" style="max-width: 150px;">
                            @else
                                <div class="bg-secondary rounded d-flex align-items-center justify-content-center text-white" style="width: 150px; height: 150px;">
                                    <i class="bi bi-person" style="font-size: 3rem;"></i>
                                </div>
                            @endif
                        </div>
                        <div class="col-md-8 mb-3">
                            <label for="avatar" class="form-label">Change Photo</label>
                            <input type="file" id="avatar" name="avatar" class="form-control @error('avatar') is-invalid @enderror" accept="image/*">
                            <div class="form-text">Recommended size: 200x200px. Max size: 2MB. Leave blank to keep current photo.</div>
                            @error('avatar')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <!-- Status Settings -->
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-white">
                    <h6 class="mb-0"><i class="bi bi-gear me-2"></i>Status</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="status" class="form-label">Account Status <span class="text-danger">*</span></label>
                        <select id="status" name="status" class="form-select @error('status') is-invalid @enderror" required>
                            <option value="active" {{ old('status', $staff->status) === 'active' ? 'selected' : '' }}>Active</option>
                            <option value="inactive" {{ old('status', $staff->status) === 'inactive' ? 'selected' : '' }}>Inactive</option>
                            <option value="banned" {{ old('status', $staff->status) === 'banned' ? 'selected' : '' }}>Banned</option>
                        </select>
                        @error('status')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    @if(isset($allowedRoles) && count($allowedRoles) > 1)
                    <div class="mb-3">
                        <label for="role" class="form-label">Role <span class="text-danger">*</span></label>
                        <select id="role" name="role" class="form-select @error('role') is-invalid @enderror" required>
                            @foreach($allowedRoles as $role)
                                <option value="{{ $role }}" {{ old('role', $staff->role) === $role ? 'selected' : '' }}>
                                    {{ ucfirst($role) }}
                                </option>
                            @endforeach
                        </select>
                        @error('role')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    @else
                        <input type="hidden" name="role" value="{{ old('role', $staff->role) }}">
                    @endif

                    @if(auth()->user()->role === 'super_admin')
                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" id="is_super_admin" name="is_super_admin" value="1" {{ old('is_super_admin', $staff->is_super_admin) ? 'checked' : '' }}>
                        <label class="form-check-label" for="is_super_admin">
                            <i class="bi bi-shield-check text-warning me-1"></i> Super Admin
                        </label>
                        <div class="form-text">Super admins have access to all features</div>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Warehouse Assignment -->
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-white">
                    <h6 class="mb-0"><i class="bi bi-building me-2"></i>Warehouse Assignment</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="warehouse_id" class="form-label">Assign to Warehouse</label>
                        <select id="warehouse_id" name="warehouse_id" class="form-select @error('warehouse_id') is-invalid @enderror">
                            <option value="">No Warehouse Assigned</option>
                            @foreach($warehouses as $warehouse)
                                <option value="{{ $warehouse->id }}" {{ old('warehouse_id', $staff->warehouse_id) == $warehouse->id ? 'selected' : '' }}>
                                    {{ $warehouse->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('warehouse_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text">Leave empty if this staff doesn't belong to any warehouse</div>
                    </div>
                </div>
            </div>

            <!-- Permissions -->
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-white">
                    <h6 class="mb-0"><i class="bi bi-shield-lock me-2"></i>Permissions</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold"><i class="bi bi-person-badge me-1"></i> Role Template</label>
                        <select class="form-select edit-role-template" name="role_template">
                            <option value="">-- Custom Permissions --</option>
                            @foreach($roleTemplates as $template)
                                <option value="{{ $template->id }}"
                                    data-permissions="{{ json_encode($template->permissions->pluck('name')) }}"
                                    {{ $staff->roles->contains($template->id) ? 'selected' : '' }}>
                                    {{ ucwords(str_replace('-', ' ', $template->name)) }} ({{ $template->permissions->count() }} permissions)
                                </option>
                            @endforeach
                        </select>
                        <div class="form-text">Assign a pre-configured role template. Staff may also have individual permissions.</div>
                    </div>

                    <hr>

                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <label class="form-label mb-0 fw-bold"><i class="bi bi-shield-check me-1"></i> Individual Permissions</label>
                        <div>
                            <button type="button" class="btn btn-sm btn-outline-success edit-select-all">
                                <i class="bi bi-check-all me-1"></i> Select All
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-danger edit-clear-all ms-1">
                                <i class="bi bi-x-circle me-1"></i> Clear All
                            </button>
                        </div>
                    </div>

                    <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                        <table class="table table-bordered table-sm mb-0">
                            <thead class="table-light sticky-top">
                                <tr>
                                    <th style="width: 120px;">Module</th>
                                    <th>Permissions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php $staffPerms = $staff->getAllPermissions()->pluck('name')->toArray(); @endphp
                                @foreach($permissionModules as $moduleKey => $module)
                                    <tr>
                                        <td class="align-middle">
                                            <div class="d-flex align-items-center">
                                                <i class="{{ $module['icon'] }} me-1 small"></i>
                                                <small class="fw-medium">{{ $module['label'] }}</small>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="d-flex flex-wrap gap-1">
                                                @foreach($module['actions'] as $action)
                                                    @php
                                                        $permName = $moduleKey . '.' . $action;
                                                        $checked = in_array($permName, $staffPerms);
                                                    @endphp
                                                    <label class="badge rounded-pill edit-perm-pill" style="cursor:pointer; padding: 0.35em 0.65em; font-size: 0.78em; {{ $checked ? 'background: #198754; color: #fff;' : 'background: #e9ecef; color: #6c757d;' }} user-select: none; transition: all 0.15s;">
                                                        <input type="checkbox" name="custom_permissions[]" value="{{ $permName }}" class="d-none edit-perm-checkbox" {{ $checked ? 'checked' : '' }}>
                                                        {{ $action }}
                                                    </label>
                                                @endforeach
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="form-text mt-2">Toggle action pills above. Uncheck a pill to remove that permission.</div>
                </div>
            </div>

            <!-- Account Info -->
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-white">
                    <h6 class="mb-0"><i class="bi bi-info-circle me-2"></i>Account Info</h6>
                </div>
                <div class="card-body">
                    <div class="small text-muted">
                        <p class="mb-1"><strong>Created:</strong> {{ $staff->created_at->format('M d, Y h:i A') }}</p>
                        <p class="mb-0"><strong>Last Updated:</strong> {{ $staff->updated_at->format('M d, Y h:i A') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>

<!-- Floating Buttons -->
<div class="floating-save-container">
    <a href="{{ route('admin.staffs.index') }}" class="btn btn-secondary floating-reset-btn">
        <i class="bi bi-x-lg me-1"></i> Cancel
    </a>
    <a href="#" class="btn btn-outline-danger floating-reset-btn" onclick="event.preventDefault(); if(confirm('Are you sure you want to delete this staff member?')) { document.getElementById('delete-form').submit(); }">
        <i class="bi bi-trash me-1"></i> Delete
    </a>
    <button type="submit" form="staffForm" class="btn btn-primary floating-save-btn">
        <i class="bi bi-check-lg me-1"></i> Update Staff
    </button>
</div>

<form id="delete-form" action="{{ route('admin.staffs.destroy', $staff->id) }}" method="POST" style="display: none;">
    @csrf
    @method('DELETE')
</form>
@endsection

@push('styles')
<style>
    .content-area {
        padding-bottom: 100px !important;
    }
    .edit-perm-pill:hover {
        transform: scale(1.06);
        box-shadow: 0 2px 6px rgba(0,0,0,0.12);
    }
    .sticky-top {
        position: sticky;
        top: 0;
    }
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    var pillStyle = function(label) {
        var cb = label.querySelector('.edit-perm-checkbox');
        if (cb && cb.checked) {
            label.style.background = '#198754';
            label.style.color = '#fff';
        } else {
            label.style.background = '#e9ecef';
            label.style.color = '#6c757d';
        }
    };

    document.querySelectorAll('.edit-perm-pill').forEach(function(label) {
        label.addEventListener('click', function(e) {
            var cb = this.querySelector('.edit-perm-checkbox');
            if (cb) {
                cb.checked = !cb.checked;
                pillStyle(this);
            }
        });
    });

    document.querySelector('.edit-role-template')?.addEventListener('change', function() {
        var selected = this.options[this.selectedIndex];
        document.querySelectorAll('.edit-perm-checkbox').forEach(function(cb) {
            cb.checked = false;
        });
        if (this.value && selected.dataset.permissions) {
            try {
                JSON.parse(selected.dataset.permissions).forEach(function(name) {
                    var cb = document.querySelector('.edit-perm-checkbox[value="' + name + '"]');
                    if (cb) cb.checked = true;
                });
            } catch(e) {}
        }
        document.querySelectorAll('.edit-perm-pill').forEach(function(label) {
            pillStyle(label);
        });
    });

    document.querySelector('.edit-select-all')?.addEventListener('click', function() {
        document.querySelectorAll('.edit-perm-checkbox').forEach(function(cb) { cb.checked = true; });
        document.querySelectorAll('.edit-perm-pill').forEach(function(label) { pillStyle(label); });
        var select = document.querySelector('.edit-role-template');
        if (select) select.value = '';
    });

    document.querySelector('.edit-clear-all')?.addEventListener('click', function() {
        document.querySelectorAll('.edit-perm-checkbox').forEach(function(cb) { cb.checked = false; });
        document.querySelectorAll('.edit-perm-pill').forEach(function(label) { pillStyle(label); });
        var select = document.querySelector('.edit-role-template');
        if (select) select.value = '';
    });
});
</script>
@endpush
