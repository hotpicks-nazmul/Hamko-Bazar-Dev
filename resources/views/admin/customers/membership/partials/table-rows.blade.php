@forelse($plans as $index => $plan)
    @php
        $search = request('search');
        $isMatch = $search && (
            stripos($plan->name, $search) !== false || 
            stripos($plan->slug, $search) !== false
        );
    @endphp
    <tr class="{{ $isMatch ? 'table-warning' : '' }}">
        <td>{{ $plans->firstItem() + $index }}</td>
        <td>
            <div class="d-flex align-items-center">
                @if($plan->icon)
                    <div class="me-2" style="width: 40px; height: 40px; background: {{ $plan->color }}20; border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                        <i class="{{ $plan->icon }}" style="color: {{ $plan->color }};"></i>
                    </div>
                @else
                    <div class="avatar-circle me-2" style="background: {{ $plan->color }};">
                        {{ strtoupper(substr($plan->name, 0, 1)) }}
                    </div>
                @endif
                <div>
                    <div class="fw-medium">{{ $plan->name }}</div>
                    <div class="small text-muted">{{ $plan->slug }}</div>
                </div>
            </div>
        </td>
        <td class="text-center">
            <span class="fw-medium">{{ $plan->formatted_price }}</span>
        </td>
        <td class="text-center">
            <span class="text-muted">{{ $plan->formatted_duration }}</span>
        </td>
        <td class="text-center">
            @if($plan->discount_percentage > 0)
                <span class="badge bg-success">{{ number_format($plan->discount_percentage, 0) }}%</span>
            @else
                <span class="text-muted">-</span>
            @endif
        </td>
        <td class="text-center">
            <span class="text-muted">{{ number_format($plan->members_count) }}</span>
            @if($plan->max_members)
                <span class="text-muted">/ {{ number_format($plan->max_members) }}</span>
            @endif
        </td>
        <td class="text-center">
            @if($plan->is_active)
                <span class="badge bg-success">Active</span>
            @else
                <span class="badge bg-secondary">Inactive</span>
            @endif
        </td>
        <td class="text-center">
            @if($plan->is_featured)
                <span class="badge bg-warning text-dark"><i class="bi bi-star-fill me-1"></i>Featured</span>
            @else
                <span class="text-muted">-</span>
            @endif
        </td>
        <td>
            <div class="d-flex gap-1">
                <a href="{{ route('admin.customers.membership.edit', $plan->id) }}" class="btn btn-sm btn-outline-primary" title="Edit">
                    <i class="bi bi-pencil"></i>
                </a>
                <button type="button" class="btn btn-sm btn-outline-{{ $plan->is_active ? 'warning' : 'success' }} status-toggle" data-id="{{ $plan->id }}" title="{{ $plan->is_active ? 'Deactivate' : 'Activate' }}">
                    <i class="bi bi-{{ $plan->is_active ? 'pause' : 'play' }}-fill"></i>
                </button>
                <button type="button" class="btn btn-sm btn-outline-{{ $plan->is_featured ? 'warning' : 'secondary' }} featured-toggle" data-id="{{ $plan->id }}" title="{{ $plan->is_featured ? 'Unfeature' : 'Feature' }}">
                    <i class="bi bi-star{{ $plan->is_featured ? '-fill' : '' }}"></i>
                </button>
                <button type="button" class="btn btn-sm btn-outline-danger" title="Delete" data-bs-toggle="modal" data-bs-target="#deleteModal{{ $plan->id }}">
                    <i class="bi bi-trash"></i>
                </button>
            </div>
        </td>
    </tr>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteModal{{ $plan->id }}" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Delete Membership Plan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete the membership plan <strong>{{ $plan->name }}</strong>?</p>
                    @if($plan->members_count > 0)
                        <div class="alert alert-warning">
                            <i class="bi bi-exclamation-triangle me-2"></i>
                            This plan has <strong>{{ number_format($plan->members_count) }}</strong> active members. Please reassign them before deleting.
                        </div>
                    @endif
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <form action="{{ route('admin.customers.membership.destroy', $plan->id) }}" method="POST">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">Delete Plan</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@empty
    <tr>
        <td colspan="9" class="text-center py-5">
            <i class="bi bi-card-checklist text-muted" style="font-size: 3rem;"></i>
            <p class="text-muted mb-2 mt-2">No membership plans found</p>
            <a href="{{ route('admin.customers.membership.create') }}" class="btn btn-sm btn-primary mt-1">
                <i class="bi bi-plus-lg me-1"></i> Add First Plan
            </a>
        </td>
    </tr>
@endforelse
