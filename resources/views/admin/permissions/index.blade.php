@extends('admin.layouts.app')

@section('title', 'Permission Settings')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0"><i class="bi bi-key me-2"></i>Permission Settings</h4>
    <div>
        <a href="{{ route('admin.roles.index') }}" class="btn btn-outline-primary me-2">
            <i class="bi bi-person-badge me-1"></i> Manage Roles
        </a>
        <a href="{{ route('admin.staffs.permissions') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> Staff Permissions
        </a>
    </div>
</div>

<!-- Info -->
<div class="alert alert-info mb-3 d-flex justify-content-between align-items-center">
    <div>
        <i class="bi bi-info-circle me-2"></i>
        <strong>Dynamic Permission System:</strong> Create, manage, and delete permission keys here. 
        These keys automatically appear in the sidebar and permission assignment modal. 
        Format: <code>module.action</code> (e.g., <code>products.view</code>, <code>inventory.export</code>)
    </div>
    <button type="button" class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#permissionKeysModal">
        <i class="bi bi-question-circle me-1"></i> View Available Keys
    </button>
</div>

<!-- Create New Permission -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
        <h6 class="mb-0"><i class="bi bi-plus-circle me-2"></i>Create New Permission</h6>
    </div>
    <div class="card-body">
        <form action="{{ route('admin.permissions.store') }}" method="POST">
            @csrf
            @php
                $existingModules = array_keys($permissions->toArray());
                $existingActions = $permissions->flatten()->map(fn($p) => explode('.', $p->name)[1] ?? 'view')->unique()->sort()->values()->toArray();
                $commonActions = ['view', 'create', 'edit', 'delete', 'export', 'import', 'manage', 'upload', 'install', 'uninstall'];
                $allActions = array_unique(array_merge($commonActions, $existingActions));
                sort($allActions);
            @endphp
            <div class="row g-3">
                <div class="col-md-5">
                    <label class="form-label fw-semibold">
                        <i class="bi bi-box me-1"></i>Module
                    </label>
                    <div class="position-relative">
                        <input type="text" name="module" id="moduleInput" class="form-control form-control-lg" placeholder="Select module..." required pattern="[a-z-]+" list="moduleList" autocomplete="off">
                        <datalist id="moduleList">
                            @foreach($existingModules as $module)
                                <option value="{{ $module }}">{{ $module }}</option>
                            @endforeach
                        </datalist>
                        <i class="bi bi-chevron-down position-absolute top-50 end-0 translate-middle-y me-3 text-muted"></i>
                    </div>
                </div>
                <div class="col-md-5">
                    <label class="form-label fw-semibold">
                        <i class="bi bi-lightning me-1"></i>Action
                    </label>
                    <div class="position-relative">
                        <input type="text" name="action" id="actionInput" class="form-control form-control-lg" placeholder="Select action..." required pattern="[a-z-]+" list="actionList" autocomplete="off">
                        <datalist id="actionList">
                            @foreach($allActions as $action)
                                <option value="{{ $action }}">{{ $action }}</option>
                            @endforeach
                        </datalist>
                        <i class="bi bi-chevron-down position-absolute top-50 end-0 translate-middle-y me-3 text-muted"></i>
                    </div>
                    <div class="mt-2 d-flex flex-wrap gap-1" id="quickActions">
                        @foreach($allActions as $action)
                            <span class="badge rounded-pill px-3 py-1 fw-normal" style="cursor:pointer; background: #667eea; opacity: 0.85; transition: opacity 0.2s;" 
                                  data-action="{{ $action }}"
                                  onmouseover="this.style.opacity='1'" 
                                  onmouseout="this.style.opacity='0.85'">{{ $action }}</span>
                        @endforeach
                    </div>
                </div>
                <div class="col-md-2 d-flex flex-column justify-content-end">
                    <label class="form-label fw-semibold text-nowrap">
                        <i class="bi bi-key me-1"></i>Result
                    </label>
                    <div class="d-flex align-items-center gap-2">
                        <code id="permPreview" class="fs-6 fw-bold px-3 py-2 rounded-3 d-inline-block" style="background: #f0f0ff; color: #667eea; border: 1px dashed #667eea; flex: 1;">module.action</code>
                        <button type="submit" class="btn btn-lg px-4" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; white-space: nowrap;">
                            <i class="bi bi-check-lg me-1"></i> Create
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Permission List -->
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <h6 class="mb-0"><i class="bi bi-shield-check me-2"></i>All Permissions ({{ $permissions->flatten()->count() }})</h6>
        <button class="btn btn-sm btn-outline-danger" id="bulkDeleteBtn" disabled>
            <i class="bi bi-trash me-1"></i> Delete Selected
        </button>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width: 40px;">
                            <input type="checkbox" id="selectAll" class="form-check-input">
                        </th>
                        <th>Module</th>
                        <th>Actions</th>
                        <th style="width: 100px;">Sidebar</th>
                        <th style="width: 100px;">Delete</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($permissions as $module => $perms)
                        <tr class="table-secondary">
                            <td colspan="5">
                                <div class="d-flex align-items-center justify-content-between">
                                    <div>
                                        <strong>{{ $module }}</strong>
                                        <span class="badge bg-primary ms-2">{{ $perms->count() }} actions</span>
                                    </div>
                                    <div class="form-check form-switch mb-0">
                                        <input class="form-check-input module-visibility-toggle" type="checkbox" role="switch"
                                               data-module="{{ $module }}"
                                               {{ \App\Helpers\PermissionHelper::isModuleVisible($module) ? 'checked' : '' }}>
                                        <label class="form-check-label small">
                                            {{ \App\Helpers\PermissionHelper::isModuleVisible($module) ? 'Visible' : 'Hidden' }}
                                        </label>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        @foreach($perms as $perm)
                        <tr>
                            <td>
                                <input type="checkbox" class="form-check-input perm-select" value="{{ $perm->id }}">
                            </td>
                            <td><code>{{ $perm->name }}</code></td>
                            <td>
                                @php $parts = explode('.', $perm->name); @endphp
                                <span class="badge bg-info me-1">{{ $parts[0] ?? '' }}</span>
                                <span class="badge bg-secondary">{{ $parts[1] ?? '' }}</span>
                            </td>
                            <td></td>
                            <td>
                                <form action="{{ route('admin.permissions.destroy', $perm->id) }}" method="POST" onsubmit="return confirm('Delete {{ $perm->name }}? This will remove it from all roles and staff.');">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-4 text-muted">
                                No permissions found. Create your first permission above.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Permission Keys Modal -->
<div class="modal fade" id="permissionKeysModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-key me-2"></i>Available Permission Keys</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-0">
                <ul class="nav nav-tabs mb-0 px-3 pt-3 bg-white border-bottom" role="tablist">
                    <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#moduleLevel">Module-Level</button></li>
                    <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#granular">Granular</button></li>
                </ul>
                <div class="tab-content" style="max-height: 60vh; overflow-y: auto;">
                    <div class="tab-pane fade show active p-3" id="moduleLevel">
                        <table class="table table-sm table-bordered">
                            <thead class="table-light"><tr><th>Permission Key</th><th>Controls</th></tr></thead>
                            <tbody>
                                <tr><td><code>dashboard</code></td><td>Admin dashboard</td></tr>
                                <tr><td><code>analytics</code></td><td>Sales analytics & export</td></tr>
                                <tr><td><code>products</code></td><td>Products, categories, attributes, brands, colors</td></tr>
                                <tr><td><code>orders</code></td><td>In-house, seller, pickup-point orders</td></tr>
                                <tr><td><code>customers</code></td><td>Customer management</td></tr>
                                <tr><td><code>marketing</code></td><td>Flash deals, coupons, newsletters, SMS</td></tr>
                                <tr><td><code>content</code></td><td>Blogs, pages</td></tr>
                                <tr><td><code>appearance</code></td><td>Themes, menus, widgets, media</td></tr>
                                <tr><td><code>settings</code></td><td>General, payment, shipping, otp, locations</td></tr>
                                <tr><td><code>support</code></td><td>Support tickets, FAQs</td></tr>
                                <tr><td><code>reports</code></td><td>Reports</td></tr>
                                <tr><td><code>inventory</code></td><td>Stock management</td></tr>
                                <tr><td><code>delivery</code></td><td>Delivery settings</td></tr>
                                <tr><td><code>refund</code></td><td>Refund requests</td></tr>
                                <tr><td><code>sellers</code></td><td>Seller management</td></tr>
                                <tr><td><code>warehouse</code></td><td>Warehouse</td></tr>
                                <tr><td><code>staffs</code></td><td>Staff & permissions</td></tr>
                                <tr><td><code>system</code></td><td>System, updates, logs</td></tr>
                                <tr><td><code>pos</code></td><td>POS</td></tr>
                                <tr><td><code>multistore</code></td><td>Multi-store</td></tr>
                                <tr><td><code>addon</code></td><td>Addons</td></tr>
                                <tr><td><code>affiliate</code></td><td>Affiliate</td></tr>
                                <tr><td><code>locations</code></td><td>Countries, states, cities, areas</td></tr>
                                <tr><td><code>otp</code></td><td>OTP config</td></tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="tab-pane fade" id="granular">
                        <table class="table table-sm table-bordered">
                            <thead class="table-light"><tr><th>Module</th><th>Available Actions</th></tr></thead>
                            <tbody>
                                <tr><td><code>products</code></td><td><span class="badge bg-secondary">view</span> <span class="badge bg-secondary">create</span> <span class="badge bg-secondary">edit</span> <span class="badge bg-secondary">delete</span> <span class="badge bg-secondary">import</span> <span class="badge bg-secondary">export</span></td></tr>
                                <tr><td><code>orders</code></td><td><span class="badge bg-secondary">view</span> <span class="badge bg-secondary">create</span> <span class="badge bg-secondary">edit</span> <span class="badge bg-secondary">delete</span> <span class="badge bg-secondary">export</span></td></tr>
                                <tr><td><code>customers</code></td><td><span class="badge bg-secondary">view</span> <span class="badge bg-secondary">create</span> <span class="badge bg-secondary">edit</span> <span class="badge bg-secondary">delete</span> <span class="badge bg-secondary">export</span></td></tr>
                                <tr><td><code>marketing</code></td><td><span class="badge bg-secondary">view</span> <span class="badge bg-secondary">create</span> <span class="badge bg-secondary">edit</span> <span class="badge bg-secondary">delete</span></td></tr>
                                <tr><td><code>content</code></td><td><span class="badge bg-secondary">view</span> <span class="badge bg-secondary">create</span> <span class="badge bg-secondary">edit</span> <span class="badge bg-secondary">delete</span></td></tr>
                                <tr><td><code>appearance</code></td><td><span class="badge bg-secondary">view</span> <span class="badge bg-secondary">create</span> <span class="badge bg-secondary">edit</span> <span class="badge bg-secondary">delete</span></td></tr>
                                <tr><td><code>settings</code></td><td><span class="badge bg-secondary">view</span> <span class="badge bg-secondary">create</span> <span class="badge bg-secondary">edit</span> <span class="badge bg-secondary">delete</span></td></tr>
                                <tr><td><code>support</code></td><td><span class="badge bg-secondary">view</span> <span class="badge bg-secondary">create</span> <span class="badge bg-secondary">edit</span> <span class="badge bg-secondary">delete</span></td></tr>
                                <tr><td><code>reports</code></td><td><span class="badge bg-secondary">view</span> <span class="badge bg-secondary">export</span></td></tr>
                                <tr><td><code>inventory</code></td><td><span class="badge bg-secondary">view</span> <span class="badge bg-secondary">create</span> <span class="badge bg-secondary">edit</span> <span class="badge bg-secondary">delete</span> <span class="badge bg-secondary">export</span></td></tr>
                                <tr><td><code>delivery</code></td><td><span class="badge bg-secondary">view</span> <span class="badge bg-secondary">create</span> <span class="badge bg-secondary">edit</span> <span class="badge bg-secondary">delete</span></td></tr>
                                <tr><td><code>refund</code></td><td><span class="badge bg-secondary">view</span> <span class="badge bg-secondary">manage</span></td></tr>
                                <tr><td><code>sellers</code></td><td><span class="badge bg-secondary">view</span> <span class="badge bg-secondary">create</span> <span class="badge bg-secondary">edit</span> <span class="badge bg-secondary">delete</span></td></tr>
                                <tr><td><code>warehouse</code></td><td><span class="badge bg-secondary">view</span> <span class="badge bg-secondary">create</span> <span class="badge bg-secondary">edit</span> <span class="badge bg-secondary">delete</span></td></tr>
                                <tr><td><code>locations</code></td><td><span class="badge bg-secondary">states</span> <span class="badge bg-secondary">cities</span> <span class="badge bg-secondary">areas</span> <span class="badge bg-secondary">settings</span></td></tr>
                                <tr><td><code>system</code></td><td><span class="badge bg-secondary">view</span> <span class="badge bg-secondary">update</span> <span class="badge bg-secondary">logs</span></td></tr>
                                <tr><td><code>otp</code></td><td><span class="badge bg-secondary">view</span> <span class="badge bg-secondary">configure</span> <span class="badge bg-secondary">credentials</span> <span class="badge bg-secondary">templates</span></td></tr>
                                <tr><td><code>pos</code></td><td><span class="badge bg-secondary">view</span> <span class="badge bg-secondary">create</span> <span class="badge bg-secondary">edit</span> <span class="badge bg-secondary">delete</span></td></tr>
                                <tr><td><code>affiliate</code></td><td><span class="badge bg-secondary">view</span> <span class="badge bg-secondary">create</span> <span class="badge bg-secondary">edit</span> <span class="badge bg-secondary">delete</span></td></tr>
                                <tr><td><code>media</code></td><td><span class="badge bg-secondary">view</span> <span class="badge bg-secondary">upload</span> <span class="badge bg-secondary">delete</span></td></tr>
                                <tr><td><code>multistore</code></td><td><span class="badge bg-secondary">view</span> <span class="badge bg-secondary">create</span> <span class="badge bg-secondary">edit</span> <span class="badge bg-secondary">delete</span></td></tr>
                                <tr><td><code>addon</code></td><td><span class="badge bg-secondary">view</span> <span class="badge bg-secondary">install</span> <span class="badge bg-secondary">uninstall</span></td></tr>
                                <tr><td><code>staffs</code></td><td><span class="badge bg-secondary">view</span> <span class="badge bg-secondary">all</span> <span class="badge bg-secondary">permissions</span> <span class="badge bg-secondary">roles</span></td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Live preview of permission name
    const moduleInput = document.querySelector('input[name="module"]');
    const actionInput = document.querySelector('input[name="action"]');
    const preview = document.getElementById('permPreview');
    function updatePreview() {
        const mod = moduleInput.value || 'module';
        const act = actionInput.value || 'action';
        preview.textContent = mod + '.' + act;
    }
    if (moduleInput) moduleInput.addEventListener('input', updatePreview);
    if (actionInput) actionInput.addEventListener('input', updatePreview);

    // Clickable quick action badges
    document.querySelectorAll('#quickActions .badge').forEach(badge => {
        badge.addEventListener('click', function() {
            actionInput.value = this.dataset.action;
            updatePreview();
            actionInput.focus();
        });
    });

    // Select all
    const selectAll = document.getElementById('selectAll');
    const bulkDeleteBtn = document.getElementById('bulkDeleteBtn');
    const checkboxes = document.querySelectorAll('.perm-select');

    if (selectAll) {
        selectAll.addEventListener('change', function() {
            checkboxes.forEach(cb => cb.checked = this.checked);
            updateBulkBtn();
        });
    }

    checkboxes.forEach(cb => cb.addEventListener('change', updateBulkBtn));

    function updateBulkBtn() {
        const checked = document.querySelectorAll('.perm-select:checked');
        bulkDeleteBtn.disabled = checked.length === 0;
        bulkDeleteBtn.textContent = checked.length > 0 
            ? 'Delete ' + checked.length + ' Selected'
            : 'Delete Selected';
    }

    // Bulk delete
    bulkDeleteBtn.addEventListener('click', function() {
        const ids = Array.from(document.querySelectorAll('.perm-select:checked')).map(cb => cb.value);
        if (!ids.length || !confirm('Delete ' + ids.length + ' permissions? This cannot be undone.')) return;

        fetch('{{ route('admin.permissions.bulk-delete') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ ids: ids })
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) location.reload();
        });
    // Module visibility toggle
    document.querySelectorAll('.module-visibility-toggle').forEach(toggle => {
        toggle.addEventListener('change', function() {
            const module = this.dataset.module;
            const toggleEl = this;
            const label = this.closest('tr').querySelector('.form-check-label');

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
                    toggleEl.checked = data.visible;
                    label.textContent = data.visible ? 'Visible' : 'Hidden';
                    if (typeof adminToast === 'function') {
                        adminToast(data.visible ? 'success' : 'warning', 'Sidebar Visibility', data.message);
                    }
                }
            })
            .catch(() => {
                toggleEl.checked = !toggleEl.checked;
                label.textContent = toggleEl.checked ? 'Visible' : 'Hidden';
            });
        });
    });
});
</script>
@endpush
