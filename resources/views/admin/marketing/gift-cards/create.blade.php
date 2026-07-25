@extends('admin.layouts.app')

@section('title', 'Add Gift Card')

@section('content')
<!-- Header -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">Add Gift Card</h4>
    <a href="{{ route('admin.marketing.gift-cards.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i> Back to Gift Cards
    </a>
</div>

<form method="POST" action="{{ route('admin.marketing.gift-cards.store') }}" id="itemForm">
    @csrf
    
    <div class="row">
        <div class="col-lg-8">
            <!-- Basic Info Card -->
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-white">
                    <h6 class="mb-0"><i class="bi bi-info-circle me-2"></i>Basic Information</h6>
                </div>
                <div class="card-body">
                    <!-- Title -->
                    <div class="mb-3">
                        <label for="title" class="form-label">Title <span class="text-danger">*</span></label>
                        <input type="text" id="title" name="title" class="form-control @error('title') is-invalid @enderror" value="{{ old('title') }}" required>
                        @error('title')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @else
                            <div class="form-text">Enter a name for your gift card</div>
                        @enderror
                    </div>

                    <!-- Description -->
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea id="description" name="description" class="form-control @error('description') is-invalid @enderror" rows="3">{{ old('description') }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @else
                            <div class="form-text">Optional description for the gift card</div>
                        @enderror
                    </div>

                    <!-- Code -->
                    <div class="mb-3">
                        <label for="code" class="form-label">Gift Card Code</label>
                        <div class="input-group">
                            <input type="text" id="code" name="code" class="form-control @error('code') is-invalid @enderror" value="{{ old('code') }}" placeholder="Auto-generated if left empty">
                            <button type="button" class="btn btn-outline-secondary" onclick="generateCode()">
                                <i class="bi bi-arrow-repeat"></i> Generate
                            </button>
                        </div>
                        @error('code')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @else
                            <div class="form-text">Leave empty to auto-generate a unique code</div>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Value Card -->
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-white">
                    <h6 class="mb-0"><i class="bi bi-credit-card me-2"></i>Value & Discount</h6>
                </div>
                <div class="card-body">
                    <!-- Balance -->
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label for="balance" class="form-label">Gift Card Balance <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">{{ config('app.currency_symbol', '$') }}</span>
                                <input type="number" id="balance" name="balance" class="form-control @error('balance') is-invalid @enderror" value="{{ old('balance', 0) }}" min="0" step="0.01" required>
                            </div>
                            @error('balance')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @else
                                <div class="form-text">Initial value of the gift card</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Discount -->
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label for="discount_type" class="form-label">Discount Type <span class="text-danger">*</span></label>
                            <select id="discount_type" name="discount_type" class="form-select @error('discount_type') is-invalid @enderror" required>
                                <option value="percent" {{ old('discount_type') === 'percent' ? 'selected' : '' }}>Percentage (%)</option>
                                <option value="fixed" {{ old('discount_type') === 'fixed' ? 'selected' : '' }}>Fixed Amount</option>
                            </select>
                            @error('discount_type')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-4">
                            <label for="discount_value" class="form-label">Discount Value <span class="text-danger">*</span></label>
                            <input type="number" id="discount_value" name="discount_value" class="form-control @error('discount_value') is-invalid @enderror" value="{{ old('discount_value', 0) }}" min="0" step="0.01" required>
                            @error('discount_value')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-4">
                            <label for="min_order_amount" class="form-label">Min Order Amount</label>
                            <div class="input-group">
                                <span class="input-group-text">{{ config('app.currency_symbol', '$') }}</span>
                                <input type="number" id="min_order_amount" name="min_order_amount" class="form-control @error('min_order_amount') is-invalid @enderror" value="{{ old('min_order_amount', 0) }}" min="0" step="0.01">
                            </div>
                            @error('min_order_amount')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @else
                                <div class="form-text">Minimum order to apply discount</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Max Discount -->
                    <div class="row g-3 mt-2">
                        <div class="col-md-6">
                            <label for="max_discount_amount" class="form-label">Max Discount Amount</label>
                            <div class="input-group">
                                <span class="input-group-text">{{ config('app.currency_symbol', '$') }}</span>
                                <input type="number" id="max_discount_amount" name="max_discount_amount" class="form-control @error('max_discount_amount') is-invalid @enderror" value="{{ old('max_discount_amount') }}" min="0" step="0.01">
                            </div>
                            @error('max_discount_amount')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @else
                                <div class="form-text">Maximum discount cap (leave empty for no limit)</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="usage_limit" class="form-label">Usage Limit</label>
                            <input type="number" id="usage_limit" name="usage_limit" class="form-control @error('usage_limit') is-invalid @enderror" value="{{ old('usage_limit') }}" min="1">
                            @error('usage_limit')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @else
                                <div class="form-text">Maximum times this card can be used (leave empty for unlimited)</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recipient Info Card -->
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-white">
                    <h6 class="mb-0"><i class="bi bi-person me-2"></i>Recipient Information (Optional)</h6>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="recipient_name" class="form-label">Recipient Name</label>
                            <input type="text" id="recipient_name" name="recipient_name" class="form-control @error('recipient_name') is-invalid @enderror" value="{{ old('recipient_name') }}">
                            @error('recipient_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="recipient_email" class="form-label">Recipient Email</label>
                            <input type="email" id="recipient_email" name="recipient_email" class="form-control @error('recipient_email') is-invalid @enderror" value="{{ old('recipient_email') }}">
                            @error('recipient_email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="row g-3 mt-2">
                        <div class="col-md-6">
                            <label for="sender_name" class="form-label">Sender Name</label>
                            <input type="text" id="sender_name" name="sender_name" class="form-control @error('sender_name') is-invalid @enderror" value="{{ old('sender_name') }}">
                            @error('sender_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="user_id" class="form-label">Assign to User</label>
                            <select id="user_id" name="user_id" class="form-select @error('user_id') is-invalid @enderror">
                                <option value="">Select User (Optional)</option>
                                @foreach($users as $user)
                                    <option value="{{ $user->id }}" {{ old('user_id') == $user->id ? 'selected' : '' }}>
                                        {{ $user->name }} ({{ $user->email }})
                                    </option>
                                @endforeach
                            </select>
                            @error('user_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @else
                                <div class="form-text">Assign this gift card to a specific user</div>
                            @enderror
                        </div>
                    </div>
                    <div class="mt-3">
                        <label for="message" class="form-label">Gift Message</label>
                        <textarea id="message" name="message" class="form-control @error('message') is-invalid @enderror" rows="3" placeholder="Enter a message to include with the gift card">{{ old('message') }}</textarea>
                        @error('message')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <!-- Status Card -->
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-white">
                    <h6 class="mb-0">Status</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                        <select id="status" name="status" class="form-select @error('status') is-invalid @enderror" required>
                            <option value="active" {{ old('status') === 'active' ? 'selected' : '' }}>Active</option>
                            <option value="inactive" {{ old('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                        </select>
                        @error('status')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @else
                            <div class="form-text">Set to active to make the gift card usable</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="expiry_date" class="form-label">Expiry Date</label>
                        <input type="date" id="expiry_date" name="expiry_date" class="form-control @error('expiry_date') is-invalid @enderror" value="{{ old('expiry_date') }}">
                        @error('expiry_date')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @else
                            <div class="form-text">Leave empty for no expiration</div>
                        @enderror
                    </div>

                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" id="is_featured" name="is_featured" value="1" {{ old('is_featured') ? 'checked' : '' }}>
                        <label class="form-check-label" for="is_featured">
                            <i class="bi bi-star text-warning me-1"></i> Featured
                        </label>
                        <div class="form-text">Featured gift cards appear on the shop</div>
                    </div>

                    <div class="mb-0">
                        <label for="background_color" class="form-label">Background Color</label>
                        <div class="input-group">
                            <input type="color" id="background_color" name="background_color" class="form-control form-control-color" value="{{ old('background_color', '#6366f1') }}">
                            <input type="text" id="background_color_text" class="form-control" value="{{ old('background_color', '#6366f1') }}">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Terms Card -->
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-white">
                    <h6 class="mb-0"><i class="bi bi-file-text me-2"></i>Terms & Conditions</h6>
                </div>
                <div class="card-body">
                    <textarea id="terms_conditions" name="terms_conditions" class="form-control @error('terms_conditions') is-invalid @enderror" rows="4" placeholder="Enter terms and conditions">{{ old('terms_conditions') }}</textarea>
                    @error('terms_conditions')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @else
                        <div class="form-text">Optional terms and conditions for this gift card</div>
                    @enderror
                </div>
            </div>

            <!-- Help Card -->
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-white">
                    <h6 class="mb-0"><i class="bi bi-lightbulb me-2"></i>Tips</h6>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled mb-0 small text-muted">
                        <li class="mb-2"><i class="bi bi-check2 me-1"></i> Gift card balance is the value customer can use</li>
                        <li class="mb-2"><i class="bi bi-check2 me-1"></i> Discount value determines how much off they get</li>
                        <li class="mb-2"><i class="bi bi-check2 me-1"></i> Set minimum order amount to prevent misuse</li>
                        <li><i class="bi bi-check2 me-1"></i> Add recipient info to personalize the gift</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</form>

<!-- Floating Buttons -->
<div class="floating-save-container">
    <a href="{{ route('admin.marketing.gift-cards.index') }}" class="btn btn-secondary floating-reset-btn">
        <i class="bi bi-x-lg me-1"></i> Cancel
    </a>
    <button type="submit" form="itemForm" class="btn btn-primary floating-save-btn">
        <i class="bi bi-check-lg me-1"></i> Create Gift Card
    </button>
</div>
@endsection

@push('styles')
<style>
    .content-area {
        padding-bottom: 100px !important;
    }
</style>
@endpush

@push('scripts')
<script>
    // Color sync
    const bgColorInput = document.getElementById('background_color');
    const bgColorText = document.getElementById('background_color_text');

    if (bgColorInput && bgColorText) {
        bgColorInput.addEventListener('input', function() {
            bgColorText.value = this.value;
        });
        bgColorText.addEventListener('input', function() {
            bgColorInput.value = this.value;
        });
    }

    // Generate code function
    function generateCode() {
        fetch('{{ route("admin.marketing.gift-cards.generate-code") }}')
            .then(response => response.json())
            .then(data => {
                document.getElementById('code').value = data.code;
            })
            .catch(error => {
                console.error('Error generating code:', error);
                // Fallback: generate locally
                const chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
                let code = '';
                for (let i = 0; i < 16; i++) {
                    if (i > 0 && i % 4 === 0) code += '-';
                    code += chars[Math.floor(Math.random() * chars.length)];
                }
                document.getElementById('code').value = code;
            });
    }
</script>
@endpush
