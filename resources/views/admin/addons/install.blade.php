@extends('admin.layouts.app')

@section('title', 'Install Addon')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">Install Addon</h4>
    <a href="{{ route('admin.addons.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i> Back to Addons
    </a>
</div>

<div class="row">
    <div class="col-lg-8">
        <!-- Manual Install Form -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white">
                <h6 class="mb-0"><i class="bi bi-plus-circle me-2"></i>Manual Installation</h6>
            </div>
            <div class="card-body">
                <form id="addonForm" method="POST" action="{{ route('admin.addons.install.process') }}">
                    @csrf
                    
                    <div class="mb-3">
                        <label for="name" class="form-label">Addon Name <span class="text-danger">*</span></label>
                        <input type="text" id="name" name="name" class="form-control @error('name') is-invalid @enderror" required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text">Enter the name of the addon you want to install.</div>
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea id="description" name="description" rows="3" class="form-control @error('description') is-invalid @enderror"></textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="version" class="form-label">Version</label>
                            <input type="text" id="version" name="version" class="form-control" value="1.0.0" placeholder="1.0.0">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="icon" class="form-label">Icon</label>
                            <input type="text" id="icon" name="icon" class="form-control" placeholder="bi bi-puzzle">
                            <div class="form-text">Bootstrap Icons class (e.g., bi bi-star)</div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="author" class="form-label">Author</label>
                            <input type="text" id="author" name="author" class="form-control" placeholder="Author Name">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="author_website" class="form-label">Author Website</label>
                            <input type="url" id="author_website" name="author_website" class="form-control" placeholder="https://">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="website" class="form-label">Addon Website</label>
                        <input type="url" id="website" name="website" class="form-control" placeholder="https://">
                    </div>

                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" id="is_core" name="is_core" value="1">
                        <label class="form-check-label" for="is_core">
                            <i class="bi bi-info-circle text-info me-1"></i> Core Addon
                        </label>
                        <div class="form-text">Core addons cannot be uninstalled or deactivated.</div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <!-- Install from Template -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white">
                <h6 class="mb-0"><i class="bi bi-collection me-2"></i>Available Addons</h6>
            </div>
            <div class="card-body p-0">
                <div class="list-group list-group-flush" id="addonTemplates">
                    <div class="text-center py-4">
                        <div class="spinner-border spinner-border-sm text-primary"></div>
                        <p class="text-muted small mt-2">Loading available addons...</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Help Card -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white">
                <h6 class="mb-0"><i class="bi bi-question-circle me-2"></i>Installation Help</h6>
            </div>
            <div class="card-body">
                <p class="small text-muted mb-2">You can install addons in two ways:</p>
                <ol class="small text-muted">
                    <li class="mb-2"><strong>Available Addons:</strong> Click the install button next to any addon from the list to install it.</li>
                    <li><strong>Manual Installation:</strong> Fill out the form on the left to manually add an addon.</li>
                </ol>
                <hr>
                <p class="small text-muted mb-0">
                    <i class="bi bi-info-circle me-1"></i>
                    After installation, you can activate the addon from the Addon Manager.
                </p>
            </div>
        </div>
    </div>
</div>

<!-- Floating Buttons -->
<div class="floating-save-container">
    <a href="{{ route('admin.addons.index') }}" class="btn btn-secondary floating-reset-btn">
        <i class="bi bi-x-lg me-1"></i> Cancel
    </a>
    <button type="submit" form="addonForm" class="btn btn-primary floating-save-btn">
        <i class="bi bi-check-lg me-1"></i> Install Addon
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
    // Load available addon templates
    document.addEventListener('DOMContentLoaded', function() {
        fetch('{{ route("admin.addons.templates") }}', {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            const templatesContainer = document.getElementById('addonTemplates');
            
            if (data.templates && data.templates.length > 0) {
                let html = '';
                data.templates.forEach(template => {
                    html += `
                        <div class="list-group-item">
                            <div class="d-flex justify-content-between align-items-center">
                                <div class="d-flex align-items-center">
                                    <i class="${template.icon} fs-4 me-3 text-primary"></i>
                                    <div>
                                        <strong>${template.name}</strong>
                                        <br><small class="text-muted">${template.description}</small>
                                        <br><small class="text-muted">v${template.version} by ${template.author}</small>
                                    </div>
                                </div>
                                <form method="POST" action="{{ route('admin.addons.templates.install') }}">
                                    @csrf
                                    <input type="hidden" name="slug" value="${template.slug}">
                                    <button type="submit" class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-download me-1"></i> Install
                                    </button>
                                </form>
                            </div>
                        </div>
                    `;
                });
                templatesContainer.innerHTML = html;
            } else {
                templatesContainer.innerHTML = `
                    <div class="list-group-item text-center py-4">
                        <i class="bi bi-inbox text-muted fs-4"></i>
                        <p class="text-muted small mb-0 mt-2">No templates available</p>
                    </div>
                `;
            }
        })
        .catch(error => {
            console.error('Error loading templates:', error);
            document.getElementById('addonTemplates').innerHTML = `
                <div class="list-group-item text-center py-4">
                    <i class="bi bi-exclamation-triangle text-warning fs-4"></i>
                    <p class="text-muted small mb-0 mt-2">Failed to load templates</p>
                </div>
            `;
        });
    });

    // Auto-scroll to first error field
    document.addEventListener('DOMContentLoaded', function() {
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
