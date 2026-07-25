@extends('admin.layouts.app')

@section('title', 'Profile')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">Profile</h4>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white">
                <h6 class="mb-0"><i class="bi bi-person me-2"></i>Personal Information</h6>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('admin.profile.update') }}">
                    @csrf
                    
                    <div class="mb-3">
                        <label for="name" class="form-label">Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="name" name="name" value="{{ Auth::user()->name }}" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                        <input type="email" class="form-control" id="email" name="email" value="{{ Auth::user()->email }}" required>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-lg me-1"></i> Update Profile
                    </button>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white">
                <h6 class="mb-0"><i class="bi bi-person-circle me-2"></i>Avatar</h6>
            </div>
            <div class="card-body text-center">
                <img src="{{ Auth::user()->avatar ?? 'https://ui-avatars.com/api/?name=' . urlencode(Auth::user()->name) }}" 
                     alt="{{ Auth::user()->name }}" 
                     class="rounded-circle mb-3" 
                     width="120" 
                     height="120">
                <p class="text-muted small">Your profile picture is managed through Gravatar or can be set by updating your email.</p>
            </div>
        </div>
        
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white">
                <h6 class="mb-0"><i class="bi bi-info-circle me-2"></i>Account Info</h6>
            </div>
            <div class="card-body">
                <div class="mb-2">
                    <small class="text-muted">Role</small>
                    <div class="fw-medium">{{ ucfirst(Auth::user()->role) }}</div>
                </div>
                @if(Auth::user()->designation)
                <div class="mb-2">
                    <small class="text-muted">Designation</small>
                    <div class="fw-medium">{{ Auth::user()->designation }}</div>
                </div>
                @endif
                @if(Auth::user()->warehouse_id && Auth::user()->warehouse)
                <div class="mb-2">
                    <small class="text-muted">Warehouse</small>
                    <div class="fw-medium">{{ Auth::user()->warehouse->name }}</div>
                </div>
                @endif
                <div class="mb-2">
                    <small class="text-muted">Member Since</small>
                    <div class="fw-medium">{{ Auth::user()->created_at->format('M d, Y') }}</div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Password Change Section -->
<div class="row">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white">
                <h6 class="mb-0"><i class="bi bi-key me-2"></i>Change Password</h6>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('admin.password.update') }}">
                    @csrf
                    
                    <div class="mb-3">
                        <label for="current_password" class="form-label">Current Password <span class="text-danger">*</span></label>
                        <input type="password" class="form-control" id="current_password" name="current_password" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="password" class="form-label">New Password <span class="text-danger">*</span></label>
                        <input type="password" class="form-control" id="password" name="password" required minlength="8">
                        <small class="text-muted">Minimum 8 characters</small>
                    </div>
                    
                    <div class="mb-3">
                        <label for="password_confirmation" class="form-label">Confirm New Password <span class="text-danger">*</span></label>
                        <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" required>
                    </div>
                    
                    <button type="submit" class="btn btn-danger">
                        <i class="bi bi-key me-1"></i> Change Password
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
