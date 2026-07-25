@forelse($customers as $index => $customer)
<tr>
    <td>{{ $customers->firstItem() + $index }}</td>
    <td>
        <div class="d-flex align-items-center">
            <div class="avatar-circle bg-primary text-white me-2">
                {{ strtoupper(substr($customer->name, 0, 1)) }}
            </div>
            <div>
                <div class="fw-medium">{{ $customer->name }}</div>
                <div class="small text-muted">{{ $customer->email }}</div>
                @if($customer->phone)
                    <div class="small text-muted">{{ $customer->phone }}</div>
                @endif
            </div>
        </div>
    </td>
    <td class="text-center">
        <span class="badge bg-warning text-dark fs-6">
            <i class="bi bi-star-fill me-1"></i>{{ number_format($customer->loyalty_points) }}
        </span>
    </td>
    <td class="text-center">
        <span class="text-muted">{{ number_format($customer->loyalty_points_spent) }}</span>
    </td>
    <td class="text-center">
        <span class="fw-medium">${{ number_format($customer->total_spent, 2) }}</span>
    </td>
    <td class="text-center">
        <div class="small text-muted">{{ $customer->created_at->format('M d, Y') }}</div>
    </td>
     <td class="text-center">
         <div class="btn-group">
             <a href="{{ route('admin.customers.loyalty.show', $customer->id) }}" class="btn btn-sm btn-outline-primary" title="View Details">
                 <i class="bi bi-eye"></i>
             </a>
             <button class="btn btn-sm btn-outline-success" title="Add Points" data-bs-toggle="modal" data-bs-target="#addPointsModal{{ $customer->id }}">
                 <i class="bi bi-plus-circle"></i>
             </button>
             <button class="btn btn-sm btn-outline-warning" title="Deduct Points" data-bs-toggle="modal" data-bs-target="#deductPointsModal{{ $customer->id }}">
                 <i class="bi bi-dash-circle"></i>
             </button>
             <a href="{{ route('admin.customers.show', $customer->id) }}" class="btn btn-sm btn-outline-secondary" title="View Profile">
                 <i class="bi bi-person"></i>
             </a>
         </div>
     </td>
</tr>

<!-- Add Points Modal -->
<div class="modal fade" id="addPointsModal{{ $customer->id }}" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Points to {{ $customer->name }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.customers.loyalty.addPoints') }}" method="POST">
                @csrf
                <input type="hidden" name="user_id" value="{{ $customer->id }}">
                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle me-2"></i>
                        Current Balance: <strong>{{ number_format($customer->loyalty_points) }} points</strong>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Points <span class="text-danger">*</span></label>
                        <input type="number" name="points" class="form-control" min="1" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Type <span class="text-danger">*</span></label>
                        <select name="type" class="form-select" required>
                            <option value="bonus">Bonus</option>
                            <option value="earned">Earned</option>
                            <option value="adjusted">Adjusted</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <input type="text" name="description" class="form-control" placeholder="Reason for adding points">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Points</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Deduct Points Modal -->
<div class="modal fade" id="deductPointsModal{{ $customer->id }}" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Deduct Points from {{ $customer->name }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.customers.loyalty.deductPoints') }}" method="POST">
                @csrf
                <input type="hidden" name="user_id" value="{{ $customer->id }}">
                <div class="modal-body">
                    <div class="alert alert-warning">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        Current Balance: <strong>{{ number_format($customer->loyalty_points) }} points</strong>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Points to Deduct <span class="text-danger">*</span></label>
                        <input type="number" name="points" class="form-control" min="1" max="{{ $customer->loyalty_points }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Reason</label>
                        <input type="text" name="description" class="form-control" placeholder="Reason for deduction">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Deduct Points</button>
                </div>
            </form>
        </div>
    </div>
</div>
@empty
<tr>
    <td colspan="7" class="text-center py-5">
        <i class="bi bi-star text-muted" style="font-size: 3rem;"></i>
        <p class="text-muted mb-2 mt-2">No customers with loyalty points found</p>
    </td>
</tr>
@endforelse