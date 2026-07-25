@extends('admin.layouts.app')

@section('title', 'Create Newsletter')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">Create Newsletter</h4>
    <a href="{{ route('admin.marketing.newsletters.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i> Back to Newsletters
    </a>
</div>

<div class="row">
    <div class="col-lg-8">
        <form action="{{ route('admin.marketing.newsletters.store') }}" method="POST" id="newsletterForm">
            @csrf
            
            <!-- Subject Card -->
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-white">
                    <h6 class="mb-0"><i class="bi bi-card-text me-2"></i>Newsletter Content</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="subject" class="form-label">Subject <span class="text-danger">*</span></label>
                        <input type="text" id="subject" name="subject" class="form-control @error('subject') is-invalid @enderror" 
                               value="{{ old('subject') }}" placeholder="Enter newsletter subject" required>
                        @error('subject')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @else
                            <div class="form-text">The subject line that recipients will see in their inbox</div>
                        @enderror
                    </div>
                    
                    <div class="mb-3">
                        <label for="content" class="form-label">Content <span class="text-danger">*</span></label>
                        <textarea id="content" name="content" class="form-control @error('content') is-invalid @enderror" 
                                  rows="15" placeholder="Enter newsletter content (HTML supported)" required>{{ old('content') }}</textarea>
                        @error('content')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @else
                            <div class="form-text">HTML is supported. You can use standard HTML tags for formatting.</div>
                        @enderror
                    </div>
                </div>
            </div>
        </form>
    </div>
    
    <div class="col-lg-4">
        <!-- Recipients Card -->
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white">
                <h6 class="mb-0"><i class="bi bi-people me-2"></i>Recipients</h6>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label for="recipients_type" class="form-label">Send To</label>
                    <select name="recipients_type" id="recipients_type" class="form-select" form="newsletterForm" onchange="updateRecipientCount()">
                        <option value="all">All (Subscribers + Customers)</option>
                        <option value="subscribers">Newsletter Subscribers Only</option>
                        <option value="users">Registered Customers Only</option>
                    </select>
                    <div class="form-text">Recipients will be selected when sending the newsletter</div>
                </div>
                <div class="d-flex align-items-center">
                    <i class="bi bi-info-circle me-2 text-muted"></i>
                    <span class="text-muted small">You can select recipients when sending</span>
                </div>
            </div>
        </div>
        
        <!-- Tips Card -->
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white">
                <h6 class="mb-0"><i class="bi bi-lightbulb me-2"></i>Tips</h6>
            </div>
            <div class="card-body">
                <ul class="list-unstyled mb-0 small text-muted">
                    <li class="mb-2"><i class="bi bi-check-circle text-success me-2"></i>Keep the subject line engaging</li>
                    <li class="mb-2"><i class="bi bi-check-circle text-success me-2"></i>Use a clear Call to Action</li>
                    <li class="mb-2"><i class="bi bi-check-circle text-success me-2"></i>Make it mobile-friendly</li>
                    <li class="mb-0"><i class="bi bi-check-circle text-success me-2"></i>Include your company branding</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<!-- Floating Buttons -->
<div class="floating-save-container">
    <a href="{{ route('admin.marketing.newsletters.index') }}" class="btn btn-secondary floating-reset-btn">
        <i class="bi bi-x-lg me-1"></i> Cancel
    </a>
    <button type="submit" form="newsletterForm" class="btn btn-primary floating-save-btn">
        <i class="bi bi-check-lg me-1"></i> Create Newsletter
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
