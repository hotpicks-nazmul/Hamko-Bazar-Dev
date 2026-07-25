@extends('admin.layouts.app')

@section('title', 'Create Form')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0"><i class="bi bi-ui-checks me-2"></i>Create New Form</h4>
    <a href="{{ route('admin.form-builder.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i> Back to Forms
    </a>
</div>

<form id="formForm" method="POST" action="{{ route('admin.form-builder.store') }}">
    @csrf
    
    <div class="row">
        <div class="col-lg-8">
            <!-- Basic Info -->
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-white">
                    <h6 class="mb-0"><i class="bi bi-info-circle me-2"></i>Basic Information</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="name" class="form-label">Form Name <span class="text-danger">*</span></label>
                        <input type="text" id="name" name="name" class="form-control @error('name') is-invalid @enderror" 
                               placeholder="e.g., Contact Us, Feedback Form" value="{{ old('name') }}" required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @else
                            <div class="form-text">Internal name for identifying the form</div>
                        @enderror
                    </div>
                    
                    <div class="mb-3">
                        <label for="title" class="form-label">Form Title</label>
                        <input type="text" id="title" name="title" class="form-control @error('title') is-invalid @enderror" 
                               placeholder="e.g., Contact Us" value="{{ old('title') }}">
                        <div class="form-text">Title displayed on the form (optional)</div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea id="description" name="description" class="form-control @error('description') is-invalid @enderror" 
                                  rows="3" placeholder="Brief description of the form">{{ old('description') }}</textarea>
                        <div class="form-text">Description shown to users (optional)</div>
                    </div>
                </div>
            </div>
            
            <!-- Settings -->
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-white">
                    <h6 class="mb-0"><i class="bi bi-gear me-2"></i>Form Settings</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="success_message" class="form-label">Success Message</label>
                        <textarea id="success_message" name="success_message" class="form-control" 
                                  rows="2" placeholder="Thank you for your submission!">{{ old('success_message', 'Thank you for your submission!') }}</textarea>
                        <div class="form-text">Message shown after form is submitted successfully</div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="submit_button_text" class="form-label">Submit Button Text</label>
                        <input type="text" id="submit_button_text" name="submit_button_text" class="form-control" 
                               value="{{ old('submit_button_text', 'Submit') }}">
                    </div>
                    
                    <div class="mb-3">
                        <label for="redirect_url" class="form-label">Redirect URL</label>
                        <input type="url" id="redirect_url" name="redirect_url" class="form-control @error('redirect_url') is-invalid @enderror" 
                               placeholder="https://example.com/thank-you" value="{{ old('redirect_url') }}">
                        <div class="form-text">Redirect users to this URL after submission (optional)</div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-4">
            <!-- Status Card -->
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-white">
                    <h6 class="mb-0"><i class="bi bi-power me-2"></i>Status</h6>
                </div>
                <div class="card-body">
                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" id="is_active" name="is_active" checked>
                        <label class="form-check-label" for="is_active">
                            <i class="bi bi-check-circle text-success me-1"></i> Active
                        </label>
                        <div class="form-text">Enable to make form functional</div>
                    </div>
                    
                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" id="show_on_frontend" name="show_on_frontend" checked>
                        <label class="form-check-label" for="show_on_frontend">
                            <i class="bi bi-eye text-info me-1"></i> Show on Frontend
                        </label>
                        <div class="form-text">Display form on the website</div>
                    </div>
                </div>
            </div>
            
            <!-- Help Card -->
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-white">
                    <h6 class="mb-0"><i class="bi bi-question-circle me-2"></i>Quick Tips</h6>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled mb-0 small text-muted">
                        <li class="mb-2"><i class="bi bi-check2 me-2 text-success"></i>Create a form with a name and title</li>
                        <li class="mb-2"><i class="bi bi-check2 me-2 text-success"></i>Click "Create Form" to save basic info</li>
                        <li class="mb-2"><i class="bi bi-check2 me-2 text-success"></i>Then add fields like text, email, etc.</li>
                        <li class="mb-0"><i class="bi bi-check2 me-2 text-success"></i>Drag and drop to reorder fields</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- Floating Buttons -->
    <div class="floating-save-container">
        <a href="{{ route('admin.form-builder.index') }}" class="btn btn-secondary floating-reset-btn">
            <i class="bi bi-x-lg me-1"></i> Cancel
        </a>
        <button type="submit" form="formForm" class="btn btn-primary floating-save-btn">
            <i class="bi bi-check-lg me-1"></i> Create Form
        </button>
    </div>
</form>
@endsection

@push('styles')
<style>
/* Add padding at bottom to prevent floating button overlap */
.content-area {
    padding-bottom: 100px !important;
}
</style>
@endpush
