@extends('admin.layouts.app')

@section('title', 'Edit Email Template')

@section('content')
<!-- Header with Back Button -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">Edit Email Template</h4>
    <a href="{{ route('admin.settings.email-templates.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i> Back to Templates
    </a>
</div>

        <div class="row">
            <div class="col-lg-8">
                <!-- Main Form -->
                <form id="itemForm" method="POST" action="{{ route('admin.settings.email-templates.update', $emailTemplate->id) }}">
                    @csrf
                    @method('PUT')

                    <!-- Template Info Card -->
                    <div class="card border-0 shadow-sm mb-3">
                        <div class="card-header bg-white">
                            <h6 class="mb-0"><i class="bi bi-info-circle me-2"></i>Template Information</h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Slug</label>
                                    <input type="text" class="form-control" value="{{ $emailTemplate->slug }}" readonly>
                                    <div class="form-text">Unique identifier for this template</div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Event</label>
                                    <input type="text" class="form-control" value="{{ $emailTemplate->event_label }}" readonly>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="subject" class="form-label">Email Subject <span class="text-danger">*</span></label>
                                <input type="text" id="subject" name="subject" 
                                       class="form-control @error('subject') is-invalid @enderror" 
                                       value="{{ old('subject', $emailTemplate->subject) }}" required>
                                @error('subject')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">Use @{{variable}} to insert dynamic content</div>
                            </div>
                        </div>
                    </div>

                    <!-- Email Body Card -->
                    <div class="card border-0 shadow-sm mb-3">
                        <div class="card-header bg-white">
                            <h6 class="mb-0"><i class="bi bi-envelope me-2"></i>Email Body</h6>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label for="body" class="form-label">Body Content <span class="text-danger">*</span></label>
                                <textarea id="body" name="body" 
                                          class="form-control @error('body') is-invalid @enderror" 
                                          rows="15" required>{{ old('body', $emailTemplate->body) }}</textarea>
                                @error('body')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">
                                    HTML supported. Use @{{variable}} to insert dynamic content.
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>

            <div class="col-lg-4">
                <!-- Sidebar: Status -->
                <div class="card border-0 shadow-sm mb-3">
                    <div class="card-header bg-white">
                        <h6 class="mb-0">Status</h6>
                    </div>
                    <div class="card-body">
                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" id="is_active" name="is_active" 
                                   form="itemForm" {{ $emailTemplate->is_active ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_active">
                                <i class="bi bi-check-circle text-success me-1"></i> Active
                            </label>
                        </div>
                        <div class="form-text">Disable to prevent this email from being sent</div>
                    </div>
                </div>

                <!-- Sidebar: Variables -->
                <div class="card border-0 shadow-sm mb-3">
                    <div class="card-header bg-white">
                        <h6 class="mb-0"><i class="bi bi-code me-2"></i>Available Variables</h6>
                    </div>
                    <div class="card-body">
                        <p class="small text-muted mb-2">Click to copy variable name</p>
                        <div class="d-flex flex-wrap gap-2">
                            @forelse($emailTemplate->variables_list ?? [] as $variable)
                            <button type="button" class="btn btn-sm btn-outline-secondary" 
                                    onclick="copyVariable('{{ $variable }}')">
                                {{ '{{' . $variable . '}}' }}
                            </button>
                            @empty
                            <span class="text-muted small">No variables available</span>
                            @endforelse
                        </div>
                    </div>
                </div>

                <!-- Sidebar: Preview -->
                <div class="card border-0 shadow-sm mb-3">
                    <div class="card-header bg-white">
                        <h6 class="mb-0"><i class="bi bi-eye me-2"></i>Preview</h6>
                    </div>
                    <div class="card-body">
                        <button type="button" class="btn btn-outline-primary btn-sm w-100" 
                                onclick="previewTemplate()">
                            <i class="bi bi-eye me-1"></i> Preview with Sample Data
                        </button>
                    </div>
                </div>

                <!-- Sidebar: Help -->
                <div class="card border-0 shadow-sm mb-3">
                    <div class="card-header bg-white">
                        <h6 class="mb-0"><i class="bi bi-question-circle me-2"></i>Help</h6>
                    </div>
                    <div class="card-body">
                        <p class="small text-muted mb-2">HTML Tags Supported:</p>
                        <ul class="list-unstyled small text-muted">
                            <li><h1>, <h2>, <h3> - Headings</li>
                            <li><p> - Paragraphs</li>
                            <li><strong> - Bold</li>
                            <li><em> - Italic</li>
                            <li><a href="..."> - Links</li>
                            <li><ul>, <li> - Lists</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <!-- Floating Save Buttons -->
        <div class="floating-save-container">
            <a href="{{ route('admin.settings.email-templates.index') }}" class="btn btn-secondary floating-reset-btn">
                <i class="bi bi-x-lg me-1"></i> Cancel
            </a>
            <button type="submit" form="itemForm" class="btn btn-primary floating-save-btn">
                <i class="bi bi-check-lg me-1"></i> Update Template
            </button>
        </div>

<!-- Preview Modal -->
<div class="modal fade" id="previewModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Email Preview</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <strong>Subject:</strong>
                    <span id="previewSubject"></span>
                </div>
                <hr>
                <div id="previewBody" style="background: #f8f9fa; padding: 20px; border-radius: 5px; min-height: 200px;">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    /* Add padding at bottom to prevent floating button overlap */
    .content-area {
        padding-bottom: 100px !important;
    }
</style>
@endpush

@push('scripts')
<script>
    // Copy variable to clipboard
    function copyVariable(variable) {
        const text = '{{' + variable + '}}';
        navigator.clipboard.writeText(text).then(() => {
            // Show temporary success feedback
            event.target.classList.add('btn-success');
            event.target.classList.remove('btn-outline-secondary');
            setTimeout(() => {
                event.target.classList.remove('btn-success');
                event.target.classList.add('btn-outline-secondary');
            }, 1000);
        });
    }

    // Preview template
    function previewTemplate() {
        const subject = document.getElementById('subject').value;
        const body = document.getElementById('body').value;
        const templateId = {{ $emailTemplate->id }};

        // Get sample preview data
        fetch(`/admin/settings/email-templates/${templateId}/preview`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ variables: {} })
        })
        .then(res => res.json())
        .then(data => {
            document.getElementById('previewSubject').textContent = data.subject;
            document.getElementById('previewBody').innerHTML = data.body;
            
            // Show modal
            const modal = new bootstrap.Modal(document.getElementById('previewModal'));
            modal.show();
        })
        .catch(error => {
            console.error('Preview error:', error);
            alert('Failed to generate preview');
        });
    }
</script>
@endpush
