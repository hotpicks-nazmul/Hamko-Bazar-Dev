@extends('admin.layouts.app')

@section('title', 'View Bundle')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">Bundle Details</h4>
    <div class="d-flex gap-2">
        <a href="{{ route('admin.product-bundles.edit', $productBundle->id) }}" class="btn btn-primary">
            <i class="bi bi-pencil me-1"></i> Edit Bundle
        </a>
        <a href="{{ route('admin.product-bundles.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> Back to Bundles
        </a>
    </div>
</div>

<div class="row">
    <div class="col-lg-8">
        <!-- Basic Information -->
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white">
                <h6 class="mb-0"><i class="bi bi-info-circle me-2"></i>Basic Information</h6>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-3 text-muted">Bundle Name:</div>
                    <div class="col-md-9 fw-semibold">{{ $productBundle->name }}</div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-3 text-muted">Slug:</div>
                    <div class="col-md-9"><code>{{ $productBundle->slug }}</code></div>
                </div>
                @if($productBundle->description)
                <div class="row mb-3">
                    <div class="col-md-3 text-muted">Description:</div>
                    <div class="col-md-9">{{ $productBundle->description }}</div>
                </div>
                @endif
            </div>
        </div>
        
        <!-- Bundle Products -->
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white">
                <h6 class="mb-0"><i class="bi bi-box-seam me-2"></i>Bundle Products ({{ $productBundle->products_count }} items)</h6>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 60px;">Image</th>
                                <th>Product</th>
                                <th style="width: 100px;">Quantity</th>
                                <th style="width: 120px;">Unit Price</th>
                                <th style="width: 120px;">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($productBundle->items as $item)
                            <tr>
                                <td>
                                    @if($item->product->featured_image)
                                        <img src="{{ asset('storage/' . $item->product->featured_image) }}" 
                                             alt="{{ $item->product->name }}" 
                                             class="rounded" 
                                             style="width: 50px; height: 50px; object-fit: cover;">
                                    @else
                                        <div class="bg-light rounded d-flex align-items-center justify-content-center" 
                                             style="width: 50px; height: 50px;">
                                            <i class="bi bi-box text-muted"></i>
                                        </div>
                                    @endif
                                </td>
                                <td>
                                    <div class="fw-semibold">{{ $item->product->name }}</div>
                                    <small class="text-muted">SKU: {{ $item->product->sku ?? 'N/A' }}</small>
                                </td>
                                <td>{{ $item->quantity }}</td>
                                <td>
                                    @if($item->custom_price)
                                        <span class="text-success">${{ number_format($item->custom_price, 2) }}</span>
                                        <br><small class="text-decoration-line-through text-muted">${{ number_format($item->product->final_price, 2) }}</small>
                                    @else
                                        ${{ number_format($item->product->final_price, 2) }}
                                    @endif
                                </td>
                                <td class="fw-semibold">${{ number_format($item->total_price, 2) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="table-light">
                            <tr>
                                <td colspan="4" class="text-end"><strong>Original Total:</strong></td>
                                <td><strong>${{ number_format($productBundle->original_price, 2) }}</strong></td>
                            </tr>
                            <tr>
                                <td colspan="4" class="text-end"><strong>Bundle Price:</strong></td>
                                <td><strong class="text-success">${{ number_format($productBundle->final_price, 2) }}</strong></td>
                            </tr>
                            <tr>
                                <td colspan="4" class="text-end"><strong>Customer Savings:</strong></td>
                                <td><strong class="text-primary">${{ number_format($productBundle->savings, 2) }} ({{ $productBundle->discount_percentage }}%)</strong></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
        
        <!-- Pricing Details -->
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white">
                <h6 class="mb-0"><i class="bi bi-currency-dollar me-2"></i>Pricing Details</h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="text-muted small">Fixed Bundle Price</label>
                            <div class="fw-semibold">{{ $productBundle->bundle_price > 0 ? '$' . number_format($productBundle->bundle_price, 2) : 'Not Set (Using Discount)' }}</div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="text-muted small">Discount Type</label>
                            <div class="fw-semibold">{{ ucfirst($productBundle->discount_type) }}</div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="text-muted small">Discount Value</label>
                            <div class="fw-semibold">
                                @if($productBundle->discount_type === 'percentage')
                                    {{ $productBundle->discount_value }}%
                                @else
                                    ${{ number_format($productBundle->discount_value, 2) }}
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-lg-4">
        <!-- Featured Image -->
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white">
                <h6 class="mb-0"><i class="bi bi-image me-2"></i>Featured Image</h6>
            </div>
            <div class="card-body text-center">
                @if($productBundle->featured_image)
                    <img src="{{ asset('storage/' . $productBundle->featured_image) }}" 
                         alt="{{ $productBundle->name }}" 
                         class="img-fluid rounded" 
                         style="max-height: 300px;">
                @else
                    <div class="bg-light rounded d-flex align-items-center justify-content-center py-5">
                        <i class="bi bi-image text-muted" style="font-size: 3rem;"></i>
                    </div>
                    <p class="text-muted mt-2 mb-0">No image uploaded</p>
                @endif
            </div>
        </div>
        
        <!-- Status -->
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white">
                <h6 class="mb-0"><i class="bi bi-gear me-2"></i>Status</h6>
            </div>
            <div class="card-body">
                <div class="d-flex justify-content-between mb-3">
                    <span class="text-muted">Status:</span>
                    <span class="badge bg-{{ $productBundle->status_color }}">{{ $productBundle->status_label }}</span>
                </div>
                <div class="d-flex justify-content-between mb-3">
                    <span class="text-muted">Active:</span>
                    <span class="badge bg-{{ $productBundle->is_active ? 'success' : 'secondary' }}">
                        {{ $productBundle->is_active ? 'Yes' : 'No' }}
                    </span>
                </div>
                <div class="d-flex justify-content-between mb-3">
                    <span class="text-muted">Featured:</span>
                    <span class="badge bg-{{ $productBundle->is_featured ? 'info' : 'secondary' }}">
                        {{ $productBundle->is_featured ? 'Yes' : 'No' }}
                    </span>
                </div>
                <div class="d-flex justify-content-between">
                    <span class="text-muted">Sort Order:</span>
                    <span>{{ $productBundle->sort_order }}</span>
                </div>
            </div>
        </div>
        
        <!-- Availability -->
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white">
                <h6 class="mb-0"><i class="bi bi-calendar me-2"></i>Availability</h6>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="text-muted small">Start Date</label>
                    <div class="fw-semibold">{{ $productBundle->starts_at?->format('M d, Y H:i') ?? 'Immediately' }}</div>
                </div>
                <div class="mb-3">
                    <label class="text-muted small">End Date</label>
                    <div class="fw-semibold">{{ $productBundle->expires_at?->format('M d, Y H:i') ?? 'No Expiration' }}</div>
                </div>
                <div class="mb-3">
                    <label class="text-muted small">Max Purchases</label>
                    <div class="fw-semibold">{{ $productBundle->max_purchases ?? 'Unlimited' }}</div>
                </div>
                <div class="mb-3">
                    <label class="text-muted small">Max Per User</label>
                    <div class="fw-semibold">{{ $productBundle->max_purchases_per_user ?? 'Unlimited' }}</div>
                </div>
            </div>
        </div>
        
        <!-- Statistics -->
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white">
                <h6 class="mb-0"><i class="bi bi-bar-chart me-2"></i>Statistics</h6>
            </div>
            <div class="card-body">
                <div class="d-flex justify-content-between mb-3">
                    <span class="text-muted">Total Purchases:</span>
                    <strong>{{ $productBundle->total_purchases }}</strong>
                </div>
                @if($productBundle->max_purchases)
                <div class="d-flex justify-content-between mb-3">
                    <span class="text-muted">Remaining:</span>
                    <strong>{{ $productBundle->remaining_purchases }} / {{ $productBundle->max_purchases }}</strong>
                </div>
                <div class="progress mb-3" style="height: 10px;">
                    <div class="progress-bar bg-success" 
                         style="width: {{ ($productBundle->total_purchases / $productBundle->max_purchases) * 100 }}%"></div>
                </div>
                @endif
                <div class="d-flex justify-content-between">
                    <span class="text-muted">Created:</span>
                    <span>{{ $productBundle->created_at->format('M d, Y') }}</span>
                </div>
            </div>
        </div>
        
        <!-- SEO -->
        @if($productBundle->meta_title || $productBundle->meta_description)
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white">
                <h6 class="mb-0"><i class="bi bi-search me-2"></i>SEO Settings</h6>
            </div>
            <div class="card-body">
                @if($productBundle->meta_title)
                <div class="mb-3">
                    <label class="text-muted small">Meta Title</label>
                    <div>{{ $productBundle->meta_title }}</div>
                </div>
                @endif
                @if($productBundle->meta_description)
                <div>
                    <label class="text-muted small">Meta Description</label>
                    <div>{{ $productBundle->meta_description }}</div>
                </div>
                @endif
            </div>
        </div>
        @endif
    </div>
</div>
@endsection
