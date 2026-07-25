@extends('admin.layouts.app')

@section('title', 'API Keys & Integrations')

@section('content')
@php
    $activeTab = $activeTab ?? 'api-keys';
    $apiKeys = $apiKeys ?? collect();
    $webhooks = $webhooks ?? collect();
    $types = $types ?? [];
    $events = $events ?? [];
@endphp

<div class="row">
    <div class="col-12">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-0 py-3">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <h4 class="mb-1 fw-bold">
                            <i class="bi bi-key text-primary me-2"></i> API Keys & Integrations
                        </h4>
                        <p class="text-muted mb-0 small">Manage API keys and webhook endpoints for third-party integrations</p>
                    </div>
                </div>
            </div>
            <div class="card-body p-0">
                <!-- Tabs -->
                <ul class="nav nav-tabs border-bottom" id="apiKeysTabs" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link {{ $activeTab === 'api-keys' ? 'active' : '' }}" 
                           href="{{ route('admin.api-keys.index', ['tab' => 'api-keys']) }}">
                            <i class="bi bi-key me-1"></i> API Keys
                            <span class="badge bg-secondary ms-1">{{ \App\Models\ApiKey::count() }}</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ $activeTab === 'webhooks' ? 'active' : '' }}" 
                           href="{{ route('admin.api-keys.index', ['tab' => 'webhooks']) }}">
                            <i class="bi bi-plug me-1"></i> Webhooks
                            <span class="badge bg-secondary ms-1">{{ \App\Models\Webhook::count() }}</span>
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>

<!-- Alert Messages -->
@if(session('success'))
<div class="alert alert-success alert-dismissible fade show" role="alert">
    {{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

@if(session('error'))
<div class="alert alert-danger alert-dismissible fade show" role="alert">
    {{ session('error') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

@if(session('api_key_secret'))
<div class="alert alert-warning alert-dismissible fade show" role="alert">
    <div class="d-flex align-items-start">
        <i class="bi bi-exclamation-triangle-fill me-2 mt-1"></i>
        <div class="flex-grow-1">
            <strong>API Key Created!</strong>
            <p class="mb-2">Copy and save your API key and secret now. They will not be shown again.</p>
            <div class="input-group input-group-sm">
                <input type="text" class="form-control" value="{{ session('api_key_secret') }}" id="apiSecret" readonly>
                <button class="btn btn-outline-secondary" type="button" onclick="copyToClipboard('apiSecret')">
                    <i class="bi bi-clipboard"></i>
                </button>
            </div>
        </div>
    </div>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

<!-- Tab Content -->
<div class="tab-content" id="apiKeysTabsContent">
    @if($activeTab === 'api-keys')
        <!-- API Keys Tab -->
        <div class="tab-pane fade {{ $activeTab === 'api-keys' ? 'show active' : '' }}" id="api-keys" role="tabpanel">
            <div class="row">
                <div class="col-lg-8">
                    <!-- API Keys List -->
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                            <h5 class="mb-0"><i class="bi bi-list-ul me-2"></i>API Keys</h5>
                        </div>
                        <div class="card-body p-0">
                            @if($apiKeys->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Name</th>
                                            <th>Type</th>
                                            <th>Key</th>
                                            <th>Status</th>
                                            <th>Last Used</th>
                                            <th style="width: 150px;">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($apiKeys as $key)
                                        <tr>
                                            <td>
                                                <div class="fw-medium">{{ $key->name }}</div>
                                                @if($key->description)
                                                <small class="text-muted">{{ Str::limit($key->description, 50) }}</small>
                                                @endif
                                            </td>
                                            <td>
                                                <span class="badge bg-light text-dark">
                                                    {{ ucfirst($key->type) }}
                                                </span>
                                            </td>
                                            <td>
                                                <code class="small">{{ $key->masked_key }}</code>
                                            </td>
                                            <td>
                                                @if($key->is_active)
                                                    <span class="badge bg-success">Active</span>
                                                @else
                                                    <span class="badge bg-secondary">Inactive</span>
                                                @endif
                                                @if($key->isExpired())
                                                    <span class="badge bg-danger">Expired</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($key->last_used_at)
                                                    <small>{{ $key->last_used_at->diffForHumans() }}</small>
                                                @else
                                                    <small class="text-muted">Never</small>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="d-flex gap-1">
                                                    <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editKeyModal{{ $key->id }}">
                                                        <i class="bi bi-pencil"></i>
                                                    </button>
                                                    <form action="{{ route('admin.api-keys.regenerate', $key->id) }}" method="POST" class="d-inline">
                                                        @csrf
                                                        <button type="submit" class="btn btn-sm btn-outline-warning" title="Regenerate Key" onclick="return confirm('Are you sure? This will invalidate the current key.')">
                                                            <i class="bi bi-arrow-repeat"></i>
                                                        </button>
                                                    </form>
                                                    <form action="{{ route('admin.api-keys.toggle', $key->id) }}" method="POST" class="d-inline">
                                                        @csrf
                                                        <button type="submit" class="btn btn-sm btn-outline-{{ $key->is_active ? 'danger' : 'success' }}" title="{{ $key->is_active ? 'Disable' : 'Enable' }}">
                                                            <i class="bi bi-{{ $key->is_active ? 'x-circle' : 'check-circle' }}"></i>
                                                        </button>
                                                    </form>
                                                    <form action="{{ route('admin.api-keys.destroy', $key->id) }}" method="POST" class="d-inline">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete" onclick="return confirm('Are you sure you want to delete this API key?')">
                                                            <i class="bi bi-trash"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            @else
                            <div class="text-center py-5">
                                <i class="bi bi-key text-muted" style="font-size: 3rem;"></i>
                                <p class="text-muted mb-2 mt-2">No API keys found</p>
                                <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#createKeyModal">
                                    <i class="bi bi-plus-lg me-1"></i> Create First API Key
                                </button>
                            </div>
                            @endif
                        </div>
                        @if($apiKeys->hasPages())
                        <div class="card-footer bg-white">
                            {{ $apiKeys->appends(request()->query())->links() }}
                        </div>
                        @endif
                    </div>
                </div>
                
                <div class="col-lg-4">
                    <!-- Create New Button -->
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-body text-center py-4">
                            <i class="bi bi-plus-circle text-primary" style="font-size: 2.5rem;"></i>
                            <h5 class="mt-3">Add New API Key</h5>
                            <p class="text-muted small mb-3">Create API keys to allow third-party applications to access your store.</p>
                            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createKeyModal">
                                <i class="bi bi-plus-lg me-1"></i> Create API Key
                            </button>
                        </div>
                    </div>
                    
                    <!-- Quick Info -->
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-header bg-white py-3">
                            <h6 class="mb-0"><i class="bi bi-info-circle me-2"></i>Quick Info</h6>
                        </div>
                        <div class="card-body">
                            <ul class="list-unstyled mb-0 small">
                                <li class="mb-2">
                                    <i class="bi bi-check-circle text-success me-2"></i>
                                    API keys are used for authentication
                                </li>
                                <li class="mb-2">
                                    <i class="bi bi-check-circle text-success me-2"></i>
                                    You can set rate limits per key
                                </li>
                                <li class="mb-2">
                                    <i class="bi bi-check-circle text-success me-2"></i>
                                    Keys can be regenerated anytime
                                </li>
                                <li class="mb-0">
                                    <i class="bi bi-check-circle text-success me-2"></i>
                                    Set expiration for added security
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @else
        <!-- Webhooks Tab -->
        <div class="tab-pane fade {{ $activeTab === 'webhooks' ? 'show active' : '' }}" id="webhooks" role="tabpanel">
            @include('admin.settings.api-keys.webhooks')
        </div>
    @endif
</div>

<!-- Create API Key Modal -->
<div class="modal fade" id="createKeyModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-plus-lg me-2"></i>Create New API Key</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.api-keys.store') }}" method="POST" id="createKeyForm">
                @csrf
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Name <span class="text-danger">*</span></label>
                                <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" placeholder="My API Key" value="{{ old('name') }}" required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Type <span class="text-danger">*</span></label>
                                <select name="type" class="form-select @error('type') is-invalid @enderror" required>
                                    @foreach($types as $value => $label)
                                        <option value="{{ $value }}" {{ old('type') == $value ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                                @error('type')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control @error('description') is-invalid @enderror" rows="2" placeholder="Optional description...">{{ old('description') }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Rate Limit (requests/min)</label>
                                <input type="number" name="rate_limit" class="form-control @error('rate_limit') is-invalid @enderror" value="{{ old('rate_limit', 100) }}" min="1" max="1000">
                                @error('rate_limit')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Expires At</label>
                                <input type="datetime-local" name="expires_at" class="form-control @error('expires_at') is-invalid @enderror" value="{{ old('expires_at') }}">
                                @error('expires_at')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">Leave empty for no expiration</div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-lg me-1"></i> Create API Key
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit API Key Modals -->
@foreach($apiKeys as $key)
<div class="modal fade" id="editKeyModal{{ $key->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-pencil me-2"></i>Edit API Key</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.api-keys.update', $key->id) }}" method="POST" id="editKeyForm{{ $key->id }}">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    @php
                        $isEditing = old('_editing_key_id') == $key->id;
                    @endphp
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Name <span class="text-danger">*</span></label>
                                <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ $isEditing ? old('name') : $key->name }}" required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Type <span class="text-danger">*</span></label>
                                <select name="type" class="form-select @error('type') is-invalid @enderror" required>
                                    @foreach($types as $value => $label)
                                        <option value="{{ $value }}" {{ ($isEditing ? old('type') : $key->type) === $value ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                                @error('type')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control @error('description') is-invalid @enderror" rows="2">{{ $isEditing ? old('description') : $key->description }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Rate Limit (requests/min)</label>
                                <input type="number" name="rate_limit" class="form-control @error('rate_limit') is-invalid @enderror" value="{{ $isEditing ? old('rate_limit') : $key->rate_limit }}" min="1" max="1000">
                                @error('rate_limit')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Expires At</label>
                                <input type="datetime-local" name="expires_at" class="form-control @error('expires_at') is-invalid @enderror" value="{{ $isEditing ? old('expires_at') : ($key->expires_at ? $key->expires_at->format('Y-m-d\TH:i') : '') }}">
                                @error('expires_at')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="is_active" id="editActive{{ $key->id }}" value="1" {{ ($isEditing ? old('is_active') : $key->is_active) ? 'checked' : '' }}>
                            <label class="form-check-label" for="editActive{{ $key->id }}">Active</label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-lg me-1"></i> Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endforeach

@endsection

@push('styles')
<style>
    /* Force Bootstrap Icons to display - SAME AS REFERENCE PAGE */
    .stat-card-icon i,
    .stat-card-icon i::before,
    .bi::before,
    [class*="bi bi-"]::before {
        display: inline-block !important;
        visibility: visible !important;
        opacity: 1 !important;
        font-family: 'bootstrap-icons' !important;
    }
</style>
@endpush

@push('scripts')
<script>
function copyToClipboard(elementId) {
    const copyText = document.getElementById(elementId);
    copyText.select();
    copyText.setSelectionRange(0, 99999);
    navigator.clipboard.writeText(copyText.value).then(() => {
        alert('Copied to clipboard!');
    });
}

// Reopen modals on validation errors
document.addEventListener('DOMContentLoaded', function() {
    @if($errors->any())
        @if(old('name') || old('type') || old('url') || old('event'))
            // Determine which modal to reopen based on active tab and form fields
            @if($activeTab === 'webhooks' && (old('url') || old('event')))
                var createWebhookModal = new bootstrap.Modal(document.getElementById('createWebhookModal'));
                createWebhookModal.show();
            @elseif($activeTab === 'api-keys')
                var createKeyModal = new bootstrap.Modal(document.getElementById('createKeyModal'));
                createKeyModal.show();
            @endif
        @endif
    @endif
});
</script>
@endpush
