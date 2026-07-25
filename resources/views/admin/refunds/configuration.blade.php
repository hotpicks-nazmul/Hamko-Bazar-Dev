@extends('admin.layouts.app')

@section('title', 'Refund Configuration')

@section('content')
@php
    $settings = $settings ?? collect();
    $stats = $stats ?? ['total' => 0, 'pending' => 0, 'approved' => 0, 'rejected' => 0, 'processed' => 0];
@endphp

@if(session('success'))
<div class="alert alert-success alert-dismissible fade show" role="alert">
    <i class="bi bi-check-circle me-2"></i> {{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif

@if(session('error'))
<div class="alert alert-danger alert-dismissible fade show" role="alert">
    <i class="bi bi-exclamation-circle me-2"></i> {{ session('error') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">Refund Configuration</h4>
    <a href="{{ route('admin.refunds.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i> All Refunds
    </a>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white">
                <h6 class="mb-0"><i class="bi bi-gear me-2"></i>Refund Settings</h6>
            </div>
            <div class="card-body">
                <form id="configForm" method="POST" action="{{ route('admin.refunds.configuration.update') }}">
                    @csrf
                    
                    <div class="mb-4">
                        <label class="form-label">Refund Policy</label>
                        <div class="form-text mb-2">Configure your store's refund policy settings</div>
                        
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="form-check form-switch mb-3">
                                    <input class="form-check-input" type="checkbox" id="enable_refunds" name="enable_refunds" value="1" 
                                           {{ old('enable_refunds', $settings['enable_refunds'] ?? '1') == '1' ? 'checked' : '' }}>
                                    <label class="form-check-label" for="enable_refunds">
                                        <i class="bi bi-check-circle text-success me-1"></i> Enable Refund Requests
                                    </label>
                                    <div class="form-text">Allow customers to request refunds</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-check form-switch mb-3">
                                    <input class="form-check-input" type="checkbox" id="auto_approve" name="auto_approve" value="1" 
                                           {{ old('auto_approve', $settings['auto_approve_refunds'] ?? '0') == '1' ? 'checked' : '' }}>
                                    <label class="form-check-label" for="auto_approve">
                                        Auto-Approve Refunds
                                    </label>
                                    <div class="form-text">Automatically approve refund requests</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label">Refund Reasons</label>
                        <div class="form-text mb-2">Select which refund reasons are available to customers</div>
                        
                        <div class="row g-3">
                            @php
                                $savedReasons = $settings['refund_reasons'] ?? [];
                                if (is_string($savedReasons)) {
                                    $savedReasons = json_decode($savedReasons, true) ?? [];
                                }
                            @endphp
                            @foreach(App\Models\Refund::getReasonOptions() as $value => $label)
                            <div class="col-md-6">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="reason_{{ $value }}" 
                                           name="refund_reasons[]" value="{{ $value }}" 
                                           {{ in_array($value, old('refund_reasons', $savedReasons)) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="reason_{{ $value }}">
                                        {{ $label }}
                                    </label>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>

                     <div class="mb-4">
                         <label class="form-label">Refund Window (Days) <span class="text-danger">*</span></label>
                         <div class="form-text mb-2">Number of days after delivery during which refunds can be requested</div>
                         
                         <div class="row g-3">
                             <div class="col-md-4">
                                 <div class="input-group">
                                 <input type="number" class="form-control @error('refund_within_days') is-invalid @enderror" id="refund_within_days" name="refund_within_days" 
                                        value="{{ old('refund_within_days', $settings['refund_within_days'] ?? 30) }}" min="1" max="365" required>
                                     <span class="input-group-text">days</span>
                                 </div>
                                 @error('refund_within_days')
                                     <div class="invalid-feedback">{{ $message }}</div>
                                 @enderror
                             </div>
                         </div>
                     </div>

                     <div class="mb-4">
                         <label class="form-label">Refund Method <span class="text-danger">*</span></label>
                         <div class="form-text mb-2">How refunds should be processed</div>
                         
                         <select class="form-select @error('refund_method') is-invalid @enderror" id="refund_method" name="refund_method" required>
                             <option value="original_payment" {{ old('refund_method', $settings['refund_method'] ?? 'original_payment') == 'original_payment' ? 'selected' : '' }}>Original Payment Method</option>
                             <option value="store_credit" {{ old('refund_method', $settings['refund_method'] ?? '') == 'store_credit' ? 'selected' : '' }}>Store Credit</option>
                             <option value="bank_transfer" {{ old('refund_method', $settings['refund_method'] ?? '') == 'bank_transfer' ? 'selected' : '' }}>Bank Transfer</option>
                         </select>
                         @error('refund_method')
                             <div class="invalid-feedback">{{ $message }}</div>
                         @enderror
                     </div>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white">
                <h6 class="mb-0"><i class="bi bi-bar-chart me-2"></i>Quick Stats</h6>
            </div>
            <div class="card-body">
                <div class="d-flex justify-content-between mb-3">
                    <span class="text-muted">Total Refunds</span>
                    <span class="fw-medium">{{ number_format($stats['total']) }}</span>
                </div>
                <div class="d-flex justify-content-between mb-3">
                    <span class="text-muted">Pending</span>
                    <span class="fw-medium text-warning">{{ number_format($stats['pending']) }}</span>
                </div>
                <div class="d-flex justify-content-between mb-3">
                    <span class="text-muted">Approved</span>
                    <span class="fw-medium text-info">{{ number_format($stats['approved']) }}</span>
                </div>
                <div class="d-flex justify-content-between mb-3">
                    <span class="text-muted">Rejected</span>
                    <span class="fw-medium text-danger">{{ number_format($stats['rejected']) }}</span>
                </div>
                <div class="d-flex justify-content-between">
                    <span class="text-muted">Processed</span>
                    <span class="fw-medium text-success">{{ number_format($stats['processed']) }}</span>
                </div>
            </div>
        </div>
        
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white">
                <h6 class="mb-0"><i class="bi bi-question-circle me-2"></i>Help</h6>
            </div>
            <div class="card-body">
                <p class="small text-muted mb-2">Refund Configuration Tips:</p>
                <ul class="small text-muted mb-0">
                    <li>Enable refund requests to allow customers to request refunds</li>
                    <li>Set an appropriate refund window based on your return policy</li>
                    <li>Choose a refund method that works best for your business</li>
                    <li>Review pending refund requests regularly</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<!-- Floating Save Button -->
<div class="floating-save-container">
    <a href="{{ route('admin.refunds.index') }}" class="btn btn-secondary floating-reset-btn">
        <i class="bi bi-x-lg me-1"></i> Cancel
    </a>
    <button type="submit" form="configForm" class="btn btn-primary floating-save-btn">
        <i class="bi bi-check-lg me-1"></i> Save Configuration
    </button>
</div>

@push('styles')
<style>
    .content-area {
        padding-bottom: 100px !important;
    }
</style>
@endpush

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Auto-scroll to first error field
        @if($errors->any())
            var firstErrorField = document.querySelector('.is-invalid');
            if (firstErrorField) {
                setTimeout(function() {
                    firstErrorField.scrollIntoView({ 
                        behavior: 'smooth', 
                        block: 'center' 
                    });
                    firstErrorField.focus();
                }, 100);
            }
        @endif
    });
</script>
@endpush
@endsection
