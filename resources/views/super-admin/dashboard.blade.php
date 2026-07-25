@extends('admin.layouts.app')

@section('title', 'Super Admin Dashboard')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">Super Admin Dashboard</h4>
</div>

<!-- Stats Cards -->
<div class="row g-4 mb-4">
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="text-muted mb-1">Total Admins</p>
                        <h4 class="mb-0">{{ $stats['total_admins'] }}</h4>
                    </div>
                    <div class="bg-primary bg-opacity-10 p-3 rounded">
                        <i class="bi bi-people text-primary fs-4"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="text-muted mb-1">Active Admins</p>
                        <h4 class="mb-0">{{ $stats['active_admins'] }}</h4>
                    </div>
                    <div class="bg-success bg-opacity-10 p-3 rounded">
                        <i class="bi bi-person-check text-success fs-4"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="text-muted mb-1">Total Staffs</p>
                        <h4 class="mb-0">{{ $stats['total_staffs'] }}</h4>
                    </div>
                    <div class="bg-info bg-opacity-10 p-3 rounded">
                        <i class="bi bi-person-badge text-info fs-4"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="text-muted mb-1">Pending Refunds</p>
                        <h4 class="mb-0">{{ $stats['pending_refunds'] }}</h4>
                    </div>
                    <div class="bg-warning bg-opacity-10 p-3 rounded">
                        <i class="bi bi-credit-card text-warning fs-4"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Quick Actions -->
<div class="row g-4">
    <div class="col-md-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white">
                <h6 class="mb-0"><i class="bi bi-lightning me-2"></i>Quick Actions</h6>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-3">
                        <a href="{{ route('admin.staffs.create') }}?role=admin" class="btn btn-outline-primary w-100">
                            <i class="bi bi-person-plus me-2"></i>Create Admin
                        </a>
                    </div>
                    <div class="col-md-3">
                        <a href="{{ route('admin.staffs.create') }}" class="btn btn-outline-success w-100">
                            <i class="bi bi-person-plus me-2"></i>Create Staff
                        </a>
                    </div>
                    <div class="col-md-3">
                        <a href="{{ route('admin.staffs.index') }}" class="btn btn-outline-info w-100">
                            <i class="bi bi-people me-2"></i>Manage All Users
                        </a>
                    </div>
                    <div class="col-md-3">
                        <a href="{{ route('admin.settings.general') }}" class="btn btn-outline-warning w-100">
                            <i class="bi bi-gear me-2"></i>System Settings
                        </a>
                    </div>
                    <div class="col-md-3">
                        <a href="{{ route('super-admin.profile') }}" class="btn btn-outline-success w-100">
                            <i class="bi bi-person-circle me-2"></i>My Profile
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
