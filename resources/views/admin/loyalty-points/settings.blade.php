@extends('admin.layouts.app')

@section('content')
<!-- Header -->
<div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="mb-0">Loyalty Points Settings</h4>
            <a href="{{ route('admin.customers.loyalty.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i> Back to Loyalty Points
            </a>
        </div>

        <form id="settingsForm" method="POST" action="{{ route('admin.customers.loyalty.settings.update') }}">
            @csrf
            <div class="row">
                <!-- Main Settings -->
                <div class="col-lg-8">
                    <!-- General Settings -->
                    <div class="card border-0 shadow-sm mb-3">
                        <div class="card-header bg-white">
                            <h6 class="mb-0"><i class="bi bi-gear me-2"></i>General Settings</h6>
                        </div>
                        <div class="card-body">
                            <div class="form-check form-switch mb-4">
                                <input class="form-check-input" type="checkbox" id="is_enabled" name="is_enabled" form="settingsForm"
                                       {{ $settings['is_enabled'] ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_enabled">
                                    <i class="bi bi-check-circle text-success me-1"></i> Enable Loyalty Program
                                </label>
                                <div class="form-text">When disabled, customers won't earn or redeem loyalty points.</div>
                            </div>

                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Points Per Currency <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text">1 $ = </span>
                                        <input type="number" name="points_per_currency" form="settingsForm" class="form-control" 
                                               value="{{ $settings['points_per_currency'] }}" min="0" step="0.01" required>
                                        <span class="input-group-text">points</span>
                                    </div>
                                    <div class="form-text">How many points customers earn per currency unit spent</div>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Currency Value Per Points <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text">1 point = </span>
                                        <input type="number" name="currency_per_points" form="settingsForm" class="form-control" 
                                               value="{{ $settings['currency_per_points'] }}" min="0" step="0.01" required>
                                        <span class="input-group-text">$</span>
                                    </div>
                                    <div class="form-text">Currency value of each point when redeemed</div>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Minimum Spend to Earn <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text">$</span>
                                        <input type="number" name="minimum_spend" form="settingsForm" class="form-control" 
                                               value="{{ $settings['minimum_spend'] }}" min="0" step="0.01" required>
                                    </div>
                                    <div class="form-text">Minimum order amount to earn points</div>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Points Expiry (Days) <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <input type="number" name="points_expiry_days" form="settingsForm" class="form-control" 
                                               value="{{ $settings['points_expiry_days'] }}" min="0" required>
                                        <span class="input-group-text">days</span>
                                    </div>
                                    <div class="form-text">Points will expire after this many days (0 = never expire)</div>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">New Customer Bonus Points</label>
                                    <div class="input-group">
                                        <input type="number" name="new_customer_bonus" form="settingsForm" class="form-control" 
                                               value="{{ $settings['new_customer_bonus'] }}" min="0" required>
                                        <span class="input-group-text">points</span>
                                    </div>
                                    <div class="form-text">Bonus points for new customers (0 = no bonus)</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sidebar -->
                <div class="col-lg-4">
                    <!-- Quick Stats -->
                    <div class="card border-0 shadow-sm mb-3">
                        <div class="card-header bg-white">
                            <h6 class="mb-0">Quick Stats</h6>
                        </div>
                        <div class="card-body">
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted">Active Rewards:</span>
                                <span class="fw-medium">{{ $rewards->where('is_active', true)->count() }}</span>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted">Total Redemptions:</span>
                                <span class="fw-medium">{{ $rewards->sum('redemption_count') }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>

        <!-- Floating Save Container -->
        <div class="floating-save-container">
            <a href="{{ route('admin.customers.loyalty.index') }}" class="btn btn-secondary floating-reset-btn">
                <i class="bi bi-x-lg me-1"></i> Cancel
            </a>
            <button type="submit" form="settingsForm" class="btn btn-primary floating-save-btn">
                <i class="bi bi-check-lg me-1"></i> Save Settings
            </button>
        </div>

        <!-- Rewards Section -->
        <div class="card border-0 shadow-sm mt-4">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h6 class="mb-0"><i class="bi bi-gift me-2"></i>Rewards Management</h6>
                <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#createRewardModal">
                    <i class="bi bi-plus-lg me-1"></i> Add Reward
                </button>
            </div>
            <div class="card-body p-0">
                @if($rewards->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Reward Name</th>
                                    <th>Type</th>
                                    <th>Points Required</th>
                                    <th>Discount Value</th>
                                    <th>Redemptions</th>
                                    <th>Status</th>
                                    <th style="width: 120px;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($rewards as $reward)
                                    <tr>
                                        <td>
                                            <div class="fw-medium">{{ $reward->name }}</div>
                                            @if($reward->code)
                                                <div class="small text-muted">Code: {{ $reward->code }}</div>
                                            @endif
                                        </td>
                                        <td>
                                            @switch($reward->reward_type)
                                                @case('discount')
                                                    <span class="badge bg-primary">Discount</span>
                                                    @break
                                                @case('voucher')
                                                    <span class="badge bg-info">Voucher</span>
                                                    @break
                                                @case('product')
                                                    <span class="badge bg-warning text-dark">Product</span>
                                                    @break
                                                @case('coupon')
                                                    <span class="badge bg-success">Coupon</span>
                                                    @break
                                            @endswitch
                                        </td>
                                        <td>{{ number_format($reward->points_required) }}</td>
                                        <td>
                                            @if($reward->discount_value)
                                                ${{ number_format($reward->discount_value, 2) }}
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td>
                                            {{ $reward->redemption_count }}
                                            @if($reward->max_redemptions)
                                                <span class="text-muted">/ {{ $reward->max_redemptions }}</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($reward->is_active && $reward->isValid())
                                                <span class="badge bg-success">Active</span>
                                            @else
                                                <span class="badge bg-secondary">Inactive</span>
                                            @endif
                                        </td>
                                         <td style="width: 120px;">
                                             <div class="btn-group">
                                                 <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editRewardModal{{ $reward->id }}" title="Edit">
                                                     <i class="bi bi-pencil"></i>
                                                 </button>
                                                 <a href="{{ route('admin.customers.loyalty.toggleReward', $reward->id) }}" class="btn btn-sm {{ $reward->is_active ? 'btn-outline-warning' : 'btn-outline-success' }}" title="{{ $reward->is_active ? 'Deactivate' : 'Activate' }}">
                                                     <i class="bi bi-toggle-on"></i>
                                                 </a>
                                                 <button class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#deleteRewardModal{{ $reward->id }}" title="Delete">
                                                     <i class="bi bi-trash"></i>
                                                 </button>
                                             </div>
                                         </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-5">
                        <i class="bi bi-gift text-muted" style="font-size: 3rem;"></i>
                        <p class="text-muted mb-2 mt-2">No rewards created yet</p>
                        <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#createRewardModal">
                            <i class="bi bi-plus-lg me-1"></i> Create First Reward
                        </button>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Create Reward Modal -->
<div class="modal fade" id="createRewardModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Create New Reward</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.customers.loyalty.createReward') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Reward Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control" rows="2"></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Reward Type <span class="text-danger">*</span></label>
                            <select name="reward_type" class="form-select" required>
                                <option value="discount">Discount</option>
                                <option value="voucher">Voucher</option>
                                <option value="product">Free Product</option>
                                <option value="coupon">Coupon</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Points Required <span class="text-danger">*</span></label>
                            <input type="number" name="points_required" class="form-control" min="1" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Discount Value</label>
                        <div class="input-group">
                            <span class="input-group-text">$</span>
                            <input type="number" name="discount_value" class="form-control" min="0" step="0.01">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Voucher Code</label>
                        <input type="text" name="code" class="form-control" placeholder="Optional unique code">
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Valid From</label>
                            <input type="date" name="valid_from" class="form-control">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Valid Until</label>
                            <input type="date" name="valid_until" class="form-control">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Max Redemptions</label>
                        <input type="number" name="max_redemptions" class="form-control" min="1" placeholder="Unlimited if empty">
                    </div>
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="create_is_active" name="is_active" checked>
                        <label class="form-check-label" for="create_is_active">
                            <i class="bi bi-check-circle text-success me-1"></i> Active
                        </label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Create Reward</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Reward Modals -->
@foreach($rewards as $reward)
<div class="modal fade" id="editRewardModal{{ $reward->id }}" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Reward: {{ $reward->name }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.customers.loyalty.updateReward', $reward->id) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Reward Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" value="{{ $reward->name }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control" rows="2">{{ $reward->description }}</textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Reward Type <span class="text-danger">*</span></label>
                            <select name="reward_type" class="form-select" required>
                                <option value="discount" {{ $reward->reward_type == 'discount' ? 'selected' : '' }}>Discount</option>
                                <option value="voucher" {{ $reward->reward_type == 'voucher' ? 'selected' : '' }}>Voucher</option>
                                <option value="product" {{ $reward->reward_type == 'product' ? 'selected' : '' }}>Free Product</option>
                                <option value="coupon" {{ $reward->reward_type == 'coupon' ? 'selected' : '' }}>Coupon</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Points Required <span class="text-danger">*</span></label>
                            <input type="number" name="points_required" class="form-control" value="{{ $reward->points_required }}" min="1" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Discount Value</label>
                        <div class="input-group">
                            <span class="input-group-text">$</span>
                            <input type="number" name="discount_value" class="form-control" value="{{ $reward->discount_value }}" min="0" step="0.01">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Voucher Code</label>
                        <input type="text" name="code" class="form-control" value="{{ $reward->code }}" placeholder="Optional unique code">
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Valid From</label>
                            <input type="date" name="valid_from" class="form-control" value="{{ $reward->valid_from ? $reward->valid_from->format('Y-m-d') : '' }}">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Valid Until</label>
                            <input type="date" name="valid_until" class="form-control" value="{{ $reward->valid_until ? $reward->valid_until->format('Y-m-d') : '' }}">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Max Redemptions</label>
                        <input type="number" name="max_redemptions" class="form-control" value="{{ $reward->max_redemptions }}" min="1" placeholder="Unlimited if empty">
                    </div>
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="edit_is_active{{ $reward->id }}" name="is_active" {{ $reward->is_active ? 'checked' : '' }}>
                        <label class="form-check-label" for="edit_is_active{{ $reward->id }}">
                            <i class="bi bi-check-circle text-success me-1"></i> Active
                        </label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Reward</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Reward Modal -->
<div class="modal fade" id="deleteRewardModal{{ $reward->id }}" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Delete Reward</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete the reward "<strong>{{ $reward->name }}</strong>"?</p>
                <p class="text-danger">This action cannot be undone.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form action="{{ route('admin.customers.loyalty.deleteReward', $reward->id) }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-danger">Delete</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endforeach
@endsection

@push('styles')
<style>
/* Add padding at bottom to prevent floating button overlap */
.content-area {
    padding-bottom: 100px !important;
}
</style>
@endpush
