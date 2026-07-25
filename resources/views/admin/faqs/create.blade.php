@extends('admin.layouts.app')

@section('title', 'Create FAQ')

@section('content')
<!-- Page Header -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">Create FAQ</h4>
    <a href="{{ route('admin.faqs.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i> Back to FAQs
    </a>
</div>

<div class="row">
    <div class="col-lg-8">
        <!-- FAQ Details Card -->
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white">
                <h6 class="mb-0"><i class="bi bi-question-diamond me-2"></i>FAQ Details</h6>
            </div>
            <div class="card-body">
                <form id="faqForm" method="POST" action="{{ route('admin.faqs.store') }}">
                    @csrf
                    <!-- Question -->
                    <div class="mb-3">
                        <label for="question" class="form-label">Question <span class="text-danger">*</span></label>
                        <input type="text" id="question" name="question" 
                               class="form-control @error('question') is-invalid @enderror" 
                               value="{{ old('question') }}" required>
                        <div class="form-text">Enter the frequently asked question</div>
                        @error('question')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Answer -->
                    <div class="mb-3">
                        <label for="answer" class="form-label">Answer <span class="text-danger">*</span></label>
                        <textarea id="answer" name="answer" 
                                  class="form-control @error('answer') is-invalid @enderror" 
                                  rows="6" required>{{ old('answer') }}</textarea>
                        <div class="form-text">Enter the answer to the question</div>
                        @error('answer')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <!-- Status Card -->
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white">
                <h6 class="mb-0"><i class="bi bi-gear me-2"></i>Settings</h6>
            </div>
            <div class="card-body">
                <!-- Status -->
                <div class="mb-3">
                    <label for="status" class="form-label">Status</label>
                    <select id="status" name="status" form="faqForm" class="form-select">
                        <option value="active" {{ old('status') === 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ old('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                    <div class="form-text">Inactive FAQs won't be shown on the frontend</div>
                </div>

                <!-- Sort Order -->
                <div class="mb-3">
                    <label for="sort_order" class="form-label">Sort Order</label>
                    <input type="number" id="sort_order" name="sort_order" 
                           form="faqForm" class="form-control" 
                           value="{{ old('sort_order', 0) }}" min="0">
                    <div class="form-text">Display order (0 = first)</div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Floating Buttons -->
<div class="floating-save-container">
    <a href="{{ route('admin.faqs.index') }}" class="btn btn-secondary floating-reset-btn">
        <i class="bi bi-x-lg me-1"></i> Cancel
    </a>
    <button type="submit" form="faqForm" class="btn btn-primary floating-save-btn">
        <i class="bi bi-check-lg me-1"></i> Create FAQ
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
