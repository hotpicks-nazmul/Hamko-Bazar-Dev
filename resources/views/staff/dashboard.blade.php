@extends('admin.layouts.app')

@section('title', 'Staff Dashboard')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">Staff Dashboard</h4>
    <span class="badge bg-primary">{{ auth()->user()->designation ?? 'Staff' }}</span>
</div>

<!-- Welcome Message -->
<div class="alert alert-info border-0 mb-4">
    <i class="bi bi-info-circle me-2"></i>
    Welcome, <strong>{{ auth()->user()->name }}</strong>! You have access to: 
    {{ implode(', ', array_map('ucfirst', $permissions)) }}
</div>

<!-- Stats Cards based on permissions -->
<div class="row g-4 mb-4">
    @if(in_array('orders', $permissions) || empty($permissions))
    <div class="col-md-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="text-muted mb-1">Pending Orders</p>
                        <h4 class="mb-0">{{ $stats['pending_orders'] ?? 0 }}</h4>
                    </div>
                    <div class="bg-warning bg-opacity-10 p-3 rounded">
                        <i class="bi bi-cart text-warning fs-4"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="text-muted mb-1">Total Orders</p>
                        <h4 class="mb-0">{{ $stats['total_orders'] ?? 0 }}</h4>
                    </div>
                    <div class="bg-success bg-opacity-10 p-3 rounded">
                        <i class="bi bi-bag text-success fs-4"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif
    
    @if(in_array('products', $permissions) || empty($permissions))
    <div class="col-md-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="text-muted mb-1">Total Products</p>
                        <h4 class="mb-0">{{ $stats['total_products'] ?? 0 }}</h4>
                    </div>
                    <div class="bg-info bg-opacity-10 p-3 rounded">
                        <i class="bi bi-box text-info fs-4"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="text-muted mb-1">Low Stock Items</p>
                        <h4 class="mb-0">{{ $stats['low_stock'] ?? 0 }}</h4>
                    </div>
                    <div class="bg-danger bg-opacity-10 p-3 rounded">
                        <i class="bi bi-exclamation-triangle text-danger fs-4"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>

<!-- Your Permissions -->
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white">
        <h6 class="mb-0"><i class="bi bi-key me-2"></i>Your Permissions</h6>
    </div>
    <div class="card-body">
        @if(count($permissions) > 0)
            <div class="d-flex flex-wrap gap-2">
                @foreach($permissions as $permission)
                    <span class="badge bg-success">{{ ucfirst($permission) }}</span>
                @endforeach
            </div>
        @else
            <p class="text-muted mb-0">No specific permissions set. Contact administrator for access.</p>
        @endif
    </div>
</div>
@endsection
