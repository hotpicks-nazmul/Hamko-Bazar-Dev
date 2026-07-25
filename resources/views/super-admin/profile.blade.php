@extends('admin.layouts.app')

@section('title', 'Super Admin Profile')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0"><i class="bi bi-shield-lock me-2"></i>Super Admin Profile</h4>
</div>

@if(session('success'))
<div class="alert alert-success alert-dismissible fade show" role="alert">
    <i class="bi bi-check-circle me-1"></i> {{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

<div class="row">
    <!-- Personal Information -->
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white d-flex align-items-center gap-2">
                <i class="bi bi-person-circle text-primary fs-5"></i>
                <h6 class="mb-0">Personal Information</h6>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('super-admin.profile.update') }}">
                    @csrf

                    <div class="mb-3">
                        <label for="name" class="form-label">Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', Auth::user()->name) }}" required>
                        @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-3">
                        <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                        <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email', Auth::user()->email) }}" required>
                        @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        <div class="form-text text-warning">
                            <i class="bi bi-exclamation-triangle me-1"></i>
                            Changing your email will require you to login again (2FA code will be sent to the new email).
                        </div>
                    </div>

                    <!-- Current password shown only when email field is modified (JS) -->
                    <div id="passwordConfirmSection" class="mb-3 d-none">
                        <label for="current_password" class="form-label">Current Password <span class="text-danger">*</span></label>
                        <input type="password" class="form-control @error('current_password') is-invalid @enderror" id="current_password" name="current_password" placeholder="Enter your current password to change email">
                        @error('current_password') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        <div class="form-text text-danger">
                            <i class="bi bi-shield me-1"></i>
                            Required to confirm email change.
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-lg me-1"></i> Update Profile
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Change Password -->
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white d-flex align-items-center gap-2">
                <i class="bi bi-key text-danger fs-5"></i>
                <h6 class="mb-0">Change Password</h6>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('super-admin.profile.password') }}">
                    @csrf

                    <div class="mb-3">
                        <label for="pw_current" class="form-label">Current Password <span class="text-danger">*</span></label>
                        <input type="password" class="form-control @error('current_password') is-invalid @enderror" id="pw_current" name="current_password" required>
                        @error('current_password') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label">New Password <span class="text-danger">*</span></label>
                        <input type="password" class="form-control @error('password') is-invalid @enderror" id="password" name="password" required minlength="8">
                        @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        <div class="form-text">Minimum 8 characters.</div>
                    </div>

                    <div class="mb-3">
                        <label for="password_confirmation" class="form-label">Confirm New Password <span class="text-danger">*</span></label>
                        <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" required>
                    </div>

                    <button type="submit" class="btn btn-danger">
                        <i class="bi bi-key me-1"></i> Change Password
                    </button>

                    <div class="form-text text-warning mt-2">
                        <i class="bi bi-exclamation-triangle me-1"></i>
                        Changing your password will log you out. Login again with your new password.
                    </div>
                </form>
            </div>
        </div>

        <!-- Security Info Card -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white d-flex align-items-center gap-2">
                <i class="bi bi-shield-check text-success fs-5"></i>
                <h6 class="mb-0">Security Status</h6>
            </div>
            <div class="card-body">
                <div class="d-flex align-items-center gap-2 mb-2">
                    <i class="bi bi-check-circle-fill text-success"></i>
                    <span>Two-Factor Authentication (Email) — <strong>Active</strong></span>
                </div>
                <div class="d-flex align-items-center gap-2 mb-2">
                    <i class="bi bi-check-circle-fill text-success"></i>
                    <span>Rate Limited Login — <strong>Active</strong></span>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-info-circle-fill text-info"></i>
                    <span class="text-muted small">Login attempts and profile changes are logged.</span>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    // Show password confirmation field when email is changed
    const emailInput = document.getElementById('email');
    const originalEmail = emailInput.value;
    const passwordSection = document.getElementById('passwordConfirmSection');
    const passwordInput = document.getElementById('current_password');

    emailInput.addEventListener('input', function() {
        if (this.value !== originalEmail) {
            passwordSection.classList.remove('d-none');
            passwordInput.setAttribute('required', 'required');
        } else {
            passwordSection.classList.add('d-none');
            passwordInput.removeAttribute('required');
        }
    });
</script>
@endsection
