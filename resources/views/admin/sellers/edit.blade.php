@extends('admin.layouts.app')

@section('title', 'Edit Seller')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">Edit Seller</h4>
    <a href="{{ route('admin.sellers.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i> Back to Sellers
    </a>
</div>

<form id="sellerForm" method="POST" action="{{ route('admin.sellers.update', $seller->id) }}" enctype="multipart/form-data">
    @csrf
    @method('PUT')
    <div class="row">
        <div class="col-lg-8">
            <!-- Basic Information -->
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-white">
                    <h6 class="mb-0"><i class="bi bi-person me-2"></i>Basic Information</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="name" class="form-label">Full Name <span class="text-danger">*</span></label>
                            <input type="text" id="name" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $seller->name) }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="email" class="form-label">Email Address <span class="text-danger">*</span></label>
                            <input type="email" id="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $seller->email) }}" required>
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="phone" class="form-label">Phone Number</label>
                            <input type="text" id="phone" name="phone" class="form-control @error('phone') is-invalid @enderror" value="{{ old('phone', $seller->phone) }}">
                            @error('phone')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="password" class="form-label">New Password</label>
                            <input type="password" id="password" name="password" class="form-control @error('password') is-invalid @enderror" placeholder="Leave blank to keep current">
                            <div class="form-text">Leave blank if you don't want to change password</div>
                            @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="mb-3" form="sellerForm">
                        <label for="password_confirmation" class="form-label">Confirm New Password</label>
                        <input type="password" id="password_confirmation" name="password_confirmation" class="form-control" form="sellerForm">
                    </div>
                </div>
            </div>

            <!-- Shop Information -->
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-white">
                    <h6 class="mb-0"><i class="bi bi-shop me-2"></i>Shop Information</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="seller_type" class="form-label">Seller Type <span class="text-danger">*</span></label>
                            <select id="seller_type" name="seller_type" class="form-select @error('seller_type') is-invalid @enderror" required form="sellerForm">
                                <option value="">Select Type</option>
                                <option value="individual" {{ old('seller_type', $seller->seller_type) === 'individual' ? 'selected' : '' }}>Individual</option>
                                <option value="company" {{ old('seller_type', $seller->seller_type) === 'company' ? 'selected' : '' }}>Company</option>
                            </select>
                            @error('seller_type')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="shop_name" class="form-label">Shop Name</label>
                            <input type="text" id="shop_name" name="shop_name" class="form-control @error('shop_name') is-invalid @enderror" value="{{ old('shop_name', $seller->shop_name) }}" form="sellerForm">
                            <div class="form-text">This will be displayed as your shop name</div>
                            @error('shop_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="shop_description" class="form-label">Shop Description</label>
                        <textarea id="shop_description" name="shop_description" class="form-control @error('shop_description') is-invalid @enderror" rows="3" form="sellerForm">{{ old('shop_description', $seller->shop_description) }}</textarea>
                        @error('shop_description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="shop_logo" class="form-label">Shop Logo</label>
                            <input type="file" id="shop_logo" name="shop_logo" class="form-control @error('shop_logo') is-invalid @enderror" accept="image/*" form="sellerForm">
                            <div class="form-text">Recommended size: 200x200px</div>
                            @error('shop_logo')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            @if($seller->shop_logo && file_exists(public_path('uploads/shop_logos/' . $seller->shop_logo)))
                                <div class="mt-2">
                                    <p class="small text-muted mb-1">Current Logo:</p>
                                    <img src="{{ asset('uploads/shop_logos/' . $seller->shop_logo) }}" alt="Shop Logo" class="current-logo">
                                </div>
                            @endif
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="shop_banner" class="form-label">Shop Banner</label>
                            <input type="file" id="shop_banner" name="shop_banner" class="form-control @error('shop_banner') is-invalid @enderror" accept="image/*" form="sellerForm">
                            <div class="form-text">Recommended size: 1200x400px</div>
                            @error('shop_banner')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            @if($seller->shop_banner && file_exists(public_path('uploads/shop_banners/' . $seller->shop_banner)))
                                <div class="mt-2">
                                    <p class="small text-muted mb-1">Current Banner:</p>
                                    <img src="{{ asset('uploads/shop_banners/' . $seller->shop_banner) }}" alt="Shop Banner" class="current-banner">
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Business Information -->
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-white">
                    <h6 class="mb-0"><i class="bi bi-briefcase me-2"></i>Business Information</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="company_name" class="form-label">Company Name</label>
                            <input type="text" id="company_name" name="company_name" class="form-control @error('company_name') is-invalid @enderror" value="{{ old('company_name', $seller->company_name) }}" form="sellerForm">
                            @error('company_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="business_registration_number" class="form-label">Business Registration Number</label>
                            <input type="text" id="business_registration_number" name="business_registration_number" class="form-control @error('business_registration_number') is-invalid @enderror" value="{{ old('business_registration_number', $seller->business_registration_number) }}" form="sellerForm">
                            @error('business_registration_number')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="tax_id" class="form-label">Tax ID / TIN</label>
                        <input type="text" id="tax_id" name="tax_id" class="form-control @error('tax_id') is-invalid @enderror" value="{{ old('tax_id', $seller->tax_id) }}" form="sellerForm">
                        @error('tax_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Contact Person Information -->
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-white">
                    <h6 class="mb-0"><i class="bi bi-person-badge me-2"></i>Contact Person Information</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="contact_person_name" class="form-label">Contact Person Name</label>
                            <input type="text" id="contact_person_name" name="contact_person_name" class="form-control @error('contact_person_name') is-invalid @enderror" value="{{ old('contact_person_name', $seller->contact_person_name) }}" form="sellerForm">
                            @error('contact_person_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="contact_person_phone" class="form-label">Contact Person Phone</label>
                            <input type="text" id="contact_person_phone" name="contact_person_phone" class="form-control @error('contact_person_phone') is-invalid @enderror" value="{{ old('contact_person_phone', $seller->contact_person_phone) }}" form="sellerForm">
                            @error('contact_person_phone')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="contact_person_email" class="form-label">Contact Person Email</label>
                            <input type="email" id="contact_person_email" name="contact_person_email" class="form-control @error('contact_person_email') is-invalid @enderror" value="{{ old('contact_person_email', $seller->contact_person_email) }}" form="sellerForm">
                            @error('contact_person_email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="return_address" class="form-label">Return Address</label>
                        <textarea id="return_address" name="return_address" class="form-control @error('return_address') is-invalid @enderror" rows="2" form="sellerForm">{{ old('return_address', $seller->return_address) }}</textarea>
                        @error('return_address')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Bank Information -->
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-white">
                    <h6 class="mb-0"><i class="bi bi-bank me-2"></i>Bank Information (For Payouts)</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="bank_name" class="form-label">Bank Name</label>
                            <input type="text" id="bank_name" name="bank_name" class="form-control @error('bank_name') is-invalid @enderror" value="{{ old('bank_name', $seller->bank_name) }}" form="sellerForm">
                            @error('bank_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="bank_account_number" class="form-label">Account Number</label>
                            <input type="text" id="bank_account_number" name="bank_account_number" class="form-control @error('bank_account_number') is-invalid @enderror" value="{{ old('bank_account_number', $seller->bank_account_number) }}" form="sellerForm">
                            @error('bank_account_number')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="bank_account_name" class="form-label">Account Name</label>
                            <input type="text" id="bank_account_name" name="bank_account_name" class="form-control @error('bank_account_name') is-invalid @enderror" value="{{ old('bank_account_name', $seller->bank_account_name) }}" form="sellerForm">
                            @error('bank_account_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="bank_routing_code" class="form-label">Routing Code</label>
                            <input type="text" id="bank_routing_code" name="bank_routing_code" class="form-control @error('bank_routing_code') is-invalid @enderror" value="{{ old('bank_routing_code', $seller->bank_routing_code) }}" form="sellerForm">
                            @error('bank_routing_code')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <!-- Status -->
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-white">
                    <h6 class="mb-0">Status</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="status" class="form-label">Account Status <span class="text-danger">*</span></label>
                        <select id="status" name="status" class="form-select @error('status') is-invalid @enderror" required form="sellerForm">
                            <option value="active" {{ old('status', $seller->status) === 'active' ? 'selected' : '' }}>Active</option>
                            <option value="inactive" {{ old('status', $seller->status) === 'inactive' ? 'selected' : '' }}>Inactive</option>
                        </select>
                        @error('status')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label for="verification_status" class="form-label">Verification Status <span class="text-danger">*</span></label>
                        <select id="verification_status" name="verification_status" class="form-select @error('verification_status') is-invalid @enderror" required form="sellerForm">
                            <option value="pending" {{ old('verification_status', $seller->verification_status) === 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="verified" {{ old('verification_status', $seller->verification_status) === 'verified' ? 'selected' : '' }}>Verified</option>
                            <option value="rejected" {{ old('verification_status', $seller->verification_status) === 'rejected' ? 'selected' : '' }}>Rejected</option>
                        </select>
                        @error('verification_status')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label for="verification_notes" class="form-label">Verification Notes</label>
                        <textarea id="verification_notes" name="verification_notes" class="form-control @error('verification_notes') is-invalid @enderror" rows="3" form="sellerForm">{{ old('verification_notes', $seller->verification_notes) }}</textarea>
                        @error('verification_notes')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Commission & Wallet -->
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-white">
                    <h6 class="mb-0">Commission & Wallet</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="commission_rate" class="form-label">Commission Rate (%)</label>
                        <div class="input-group">
                            <input type="number" id="commission_rate" name="commission_rate" class="form-control @error('commission_rate') is-invalid @enderror" value="{{ old('commission_rate', $seller->commission_rate) }}" min="0" max="100" step="0.01" form="sellerForm">
                            <span class="input-group-text">%</span>
                        </div>
                        <div class="form-text">Percentage of sales that goes to platform</div>
                        @error('commission_rate')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label for="wallet_balance" class="form-label">Wallet Balance</label>
                        <div class="input-group">
                            <span class="input-group-text">৳</span>
                            <input type="number" id="wallet_balance" name="wallet_balance" class="form-control @error('wallet_balance') is-invalid @enderror" value="{{ old('wallet_balance', $seller->wallet_balance) }}" min="0" step="0.01" form="sellerForm">
                        </div>
                        <div class="form-text">Current available balance for payout</div>
                        @error('wallet_balance')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-white">
                    <h6 class="mb-0">Quick Actions</h6>
                </div>
                <div class="card-body">
                    @if($seller->verification_status === 'pending')
                        <form action="{{ route('admin.sellers.approve', $seller->id) }}" method="POST" class="mb-2">
                            @csrf
                            <button type="submit" class="btn btn-success w-100">
                                <i class="bi bi-check-circle me-1"></i> Approve Seller
                            </button>
                        </form>
                    @endif
                    @if($seller->status === 'active')
                        <form action="{{ route('admin.sellers.suspend', $seller->id) }}" method="POST" class="mb-2">
                            @csrf
                            <button type="submit" class="btn btn-warning w-100" onclick="return confirm('Are you sure you want to suspend this seller?')">
                                <i class="bi bi-pause-circle me-1"></i> Suspend Seller
                            </button>
                        </form>
                    @else
                        <form action="{{ route('admin.sellers.activate', $seller->id) }}" method="POST" class="mb-2">
                            @csrf
                            <button type="submit" class="btn btn-success w-100">
                                <i class="bi bi-play-circle me-1"></i> Activate Seller
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </div>
</form>

<!-- Floating Buttons -->
<div class="floating-save-container">
    <a href="{{ route('admin.sellers.index') }}" class="btn btn-secondary floating-reset-btn">
        <i class="bi bi-x-lg me-1"></i> Cancel
    </a>
    <form action="{{ route('admin.sellers.destroy', $seller->id) }}" method="POST" class="d-inline" id="deleteForm">
        @csrf
        @method('DELETE')
        <button type="button" class="btn btn-outline-danger floating-reset-btn" onclick="confirm('Are you sure you want to delete this seller? This will also remove their products.') && document.getElementById('deleteForm').submit()">
            <i class="bi bi-trash me-1"></i> Delete
        </button>
    </form>
    <button type="submit" form="sellerForm" class="btn btn-primary floating-save-btn">
        <i class="bi bi-check-lg me-1"></i> Update Seller
    </button>
</div>
@endsection

@push('styles')
<style>
    .content-area {
        padding-bottom: 100px !important;
    }
    .current-logo, .current-banner {
        max-width: 150px;
        max-height: 150px;
        object-fit: contain;
        border: 1px solid #dee2e6;
        border-radius: 4px;
    }
</style>
@endpush
