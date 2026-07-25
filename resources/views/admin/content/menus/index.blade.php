@extends('admin.layouts.app')

@section('title', 'Menu Builder')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">Menu Builder</h4>
    <a href="{{ route('admin.menus.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-lg me-1"></i> Add New Menu
    </a>
</div>

<!-- Menus Table -->
<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Menu Name</th>
                        <th>Slug</th>
                        <th>Location</th>
                        <th>Items</th>
                        <th>Status</th>
                        <th style="width: 150px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($menus as $menu)
                    <tr>
                        <td>
                            <strong>{{ $menu->name }}</strong>
                            @if($menu->description)
                            <br><small class="text-muted">{{ $menu->description }}</small>
                            @endif
                        </td>
                        <td>
                            <code class="small">{{ $menu->slug }}</code>
                        </td>
                        <td>
                            @if($menu->location)
                            <span class="badge bg-secondary">{{ $menu->location }}</span>
                            @else
                            <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>
                            <span class="badge bg-primary">{{ $menu->items->count() }}</span>
                        </td>
                        <td>
                            @if($menu->is_active)
                            <span class="badge bg-success">Active</span>
                            @else
                            <span class="badge bg-secondary">Inactive</span>
                            @endif
                        </td>
                        <td>
                            <div class="btn-group">
                                <form action="{{ route('admin.menus.toggle', $menu->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-sm {{ $menu->is_active ? 'btn-outline-warning' : 'btn-outline-success' }}" title="{{ $menu->is_active ? 'Deactivate' : 'Activate' }}">
                                        <i class="bi bi-{{ $menu->is_active ? 'pause' : 'play' }}-circle"></i>
                                    </button>
                                </form>
                                <a href="{{ route('admin.menus.items', $menu->id) }}" class="btn btn-sm btn-outline-info" title="Manage Items">
                                    <i class="bi bi-list-nested"></i>
                                </a>
                                <a href="{{ route('admin.menus.edit', $menu->id) }}" class="btn btn-sm btn-outline-primary" title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <form action="{{ route('admin.menus.destroy', $menu->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this menu?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-5">
                            <i class="bi bi-list-nested text-muted" style="font-size: 3rem;"></i>
                            <p class="text-muted mb-2 mt-2">No menus found</p>
                            <a href="{{ route('admin.menus.create') }}" class="btn btn-sm btn-primary mt-1">
                                <i class="bi bi-plus-lg me-1"></i> Add First Menu
                            </a>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($menus->hasPages())
    <div class="card-footer bg-white d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div class="text-muted small">
            Showing {{ $menus->firstItem() }} - {{ $menus->lastItem() }} of {{ $menus->total() }} menus
        </div>
        <div>
            {{ $menus->appends(request()->query())->links() }}
        </div>
    </div>
    @endif
</div>
@endsection
