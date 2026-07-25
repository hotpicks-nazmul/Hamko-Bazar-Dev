@extends('admin.layouts.app')

@section('title', 'Edit Addon')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">Edit Addon</h4>
    <a href="{{ route('admin.addons.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i> Back to Addons
    </a>
</div>

<div class="row">
    <div class="col-lg-8">
        <form id="addonForm" method="POST" action="{{ route('admin.addons.update', $addon->id) }}">
            @csrf
            @method('PUT')
            
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h6 class="mb-0"><i class="bi bi-info-circle me-2"></i>Basic Information</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="name" class="form-label">Addon Name <span class="text-danger">*</span></label>
                        <input type="text" id="name" name="name" class="form-control @error('name') is-invalid @enderror" 
                               value="{{ old('name', $addon->name) }}" required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea id="description" name="description" rows="3" class="form-control @error('description') is-invalid @enderror">{{ old('description', $addon->description) }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="version" class="form-label">Version</label>
                            <input type="text" id="version" name="version" class="form-control" 
                                   value="{{ old('version', $addon->version) }}" placeholder="1.0.0">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="icon" class="form-label">Icon</label>
                            <input type="text" id="icon" name="icon" class="form-control" 
                                   value="{{ old('icon', $addon->icon) }}" placeholder="bi bi-puzzle">
                            <div class="form-text">Bootstrap Icons class (e.g., bi bi-star)</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h6 class="mb-0"><i class="bi bi-person me-2"></i>Author Information</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="author" class="form-label">Author</label>
                            <input type="text" id="author" name="author" class="form-control" 
                                   value="{{ old('author', $addon->author) }}" placeholder="Author Name">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="author_website" class="form-label">Author Website</label>
                            <input type="url" id="author_website" name="author_website" class="form-control" 
                                   value="{{ old('author_website', $addon->author_website) }}" placeholder="https://">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="website" class="form-label">Addon Website</label>
                        <input type="url" id="website" name="website" class="form-control" 
                               value="{{ old('website', $addon->website) }}" placeholder="https://">
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h6 class="mb-0"><i class="bi bi-gear me-2"></i>Settings</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="sort_order" class="form-label">Sort Order</label>
                            <input type="number" id="sort_order" name="sort_order" class="form-control"
                                   value="{{ old('sort_order', $addon->sort_order ?? 0) }}" min="0">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Status</label>
                            <div>
                                @if($addon->status === 'active')
                                    <span class="badge bg-success">Active</span>
                                @elseif($addon->status === 'inactive')
                                    <span class="badge bg-warning text-dark">Inactive</span>
                                @else
                                    <span class="badge bg-secondary">Not Installed</span>
                                @endif
                                @if($addon->is_core)
                                    <span class="badge bg-info text-dark ms-2">Core</span>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="settings" class="form-label">Addon Settings (JSON)</label>
                        <textarea id="settings" name="settings" rows="4" class="form-control font-monospace @error('settings') is-invalid @enderror"
                                  placeholder='{"key": "value"}'>{{ old('settings', $addon->settings ? json_encode($addon->settings, JSON_PRETTY_PRINT) : '') }}</textarea>
                        @error('settings')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text">Enter addon-specific settings in JSON format.</div>
                    </div>

                    @if(!$addon->is_core)
                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" id="is_core" name="is_core" value="1"
                               {{ old('is_core', $addon->is_core) ? 'checked' : '' }}>
                        <label class="form-check-label" for="is_core">
                            <i class="bi bi-info-circle text-info me-1"></i> Core Addon
                        </label>
                        <div class="form-text">Core addons cannot be uninstalled or deactivated.</div>
                    </div>
                    @else
                    <input type="hidden" name="is_core" value="1">
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle me-2"></i>
                        This is a core addon. Core addons cannot be uninstalled or deactivated.
                    </div>
                    @endif
                </div>
            </div>
        </form>
    </div>

    <div class="col-lg-4">
        <!-- Status Card -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white">
                <h6 class="mb-0"><i class="bi bi-activity me-2"></i>Status</h6>
            </div>
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <span class="text-muted">Current Status</span>
                    @if($addon->status === 'active')
                        <span class="badge bg-success">Active</span>
                    @elseif($addon->status === 'inactive')
                        <span class="badge bg-warning text-dark">Inactive</span>
                    @else
                        <span class="badge bg-secondary">Not Installed</span>
                    @endif
                </div>
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <span class="text-muted">Installed At</span>
                    <span>{{ $addon->installed_at ? $addon->installed_at->format('d M, Y') : 'N/A' }}</span>
                </div>
                <div class="d-flex justify-content-between align-items-center">
                    <span class="text-muted">Last Updated</span>
                    <span>{{ $addon->updated_at->format('d M, Y') }}</span>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        @if(!$addon->is_core && $addon->status !== 'uninstalled')
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white">
                <h6 class="mb-0"><i class="bi bi-lightning me-2"></i>Quick Actions</h6>
            </div>
            <div class="card-body">
                @if($addon->status === 'active')
                    <form method="POST" action="{{ route('admin.addons.deactivate', $addon->id) }}" class="mb-2">
                        @csrf
                        <button type="submit" class="btn btn-warning w-100">
                            <i class="bi bi-pause-circle me-1"></i> Deactivate Addon
                        </button>
                    </form>
                @else
                    <form method="POST" action="{{ route('admin.addons.activate', $addon->id) }}" class="mb-2">
                        @csrf
                        <button type="submit" class="btn btn-success w-100">
                            <i class="bi bi-play-circle me-1"></i> Activate Addon
                        </button>
                    </form>
                @endif
                <form method="POST" action="{{ route('admin.addons.destroy', $addon->id) }}" 
                      onsubmit="return confirm('Are you sure you want to uninstall this addon?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-outline-danger w-100">
                        <i class="bi bi-trash me-1"></i> Uninstall Addon
                    </button>
                </form>
            </div>
        </div>
        @endif
    </div>
</div>

<!-- Floating Buttons -->
<div class="floating-save-container">
    <a href="{{ route('admin.addons.index') }}" class="btn btn-secondary floating-reset-btn">
        <i class="bi bi-x-lg me-1"></i> Cancel
    </a>
    @if(!$addon->is_core)
    <button type="button" class="btn btn-outline-danger floating-reset-btn"
       onclick="if(confirm('Are you sure you want to uninstall this addon?')) { document.getElementById('deleteForm').submit(); }">
        <i class="bi bi-trash me-1"></i> Delete
    </button>
    <form id="deleteForm" method="POST" action="{{ route('admin.addons.destroy', $addon->id) }}" style="display: none;">
        @csrf
        @method('DELETE')
    </form>
    @endif
    <button type="submit" form="addonForm" class="btn btn-primary floating-save-btn">
        <i class="bi bi-check-lg me-1"></i> Update Addon
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
