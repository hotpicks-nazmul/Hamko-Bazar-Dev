@extends('admin.layouts.app')

@section('title', 'Role Templates')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0"><i class="bi bi-person-badge me-2"></i>Role Templates</h4>
    <div>
        <a href="{{ route('admin.roles.create') }}" class="btn btn-primary me-2">
            <i class="bi bi-plus-circle me-1"></i> Create Role
        </a>
        <a href="{{ route('admin.permissions.index') }}" class="btn btn-outline-secondary me-2">
            <i class="bi bi-key me-1"></i> Manage Permissions
        </a>
        <a href="{{ route('admin.staffs.permissions') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> Staff Permissions
        </a>
    </div>
</div>

<div class="alert alert-info mb-3">
    <i class="bi bi-info-circle me-2"></i>
    <strong>Role Templates:</strong> Pre-configured permission sets that can be assigned to staff members.
    When a staff member is assigned a role, they automatically get all of the role's permissions.
    You can also override individual permissions per staff member.
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
@endsection
