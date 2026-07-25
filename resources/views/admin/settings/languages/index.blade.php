@extends('admin.layouts.app')

@section('title', 'Languages')

@section('content')
<!-- Success/Error Alerts -->
@if(session('success'))
<div class="alert alert-success alert-dismissible fade show" role="alert">
    <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif

@if(session('error'))
<div class="alert alert-danger alert-dismissible fade show" role="alert">
    <i class="bi bi-exclamation-triangle me-2"></i>{{ session('error') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif

@if($errors->any())
<div class="alert alert-danger alert-dismissible fade show" role="alert">
    <i class="bi bi-exclamation-triangle me-2"></i>Please fix the following errors:
    <ul class="mb-0 mt-2">
        @foreach($errors->all() as $error)
        <li>{{ $error }}</li>
        @endforeach
    </ul>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">Languages</h4>
    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addLanguageModal">
        <i class="bi bi-plus-lg me-1"></i> Add New Language
    </button>
</div>

<!-- Frontend Language Switcher Toggle -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h6 class="mb-1">Frontend Language Switcher</h6>
                <p class="text-muted small mb-0">Show language switcher dropdown on the frontend header</p>
            </div>
            <form action="{{ route('admin.settings.languages.toggleFrontend') }}" method="POST" id="frontendSwitchForm">
                @csrf
                <input type="hidden" name="status" value="{{ $frontendLanguageSwitcher ? 0 : 1 }}">
                <button type="submit" class="btn btn-sm {{ $frontendLanguageSwitcher ? 'btn-success' : 'btn-secondary' }}">
                    {{ $frontendLanguageSwitcher ? 'ON' : 'OFF' }}
                </button>
            </form>
        </div>
    </div>
</div>

<!-- Statistics Cards -->
<div class="stat-card-row mb-4">
    <div class="stat-card stat-card-primary">
        <div class="stat-card-icon"><i class="bi bi-translate"></i></div>
        <div class="stat-card-content">
            <span class="stat-card-label">Total Languages</span>
            <span class="stat-card-value">{{ $languages->count() }}</span>
        </div>
    </div>
    <div class="stat-card stat-card-success">
        <div class="stat-card-icon"><i class="bi bi-check-circle"></i></div>
        <div class="stat-card-content">
            <span class="stat-card-label">Active</span>
            <span class="stat-card-value">{{ $languages->where('is_active', true)->count() }}</span>
        </div>
    </div>
    <div class="stat-card stat-card-warning">
        <div class="stat-card-icon"><i class="bi bi-arrow-left-right"></i></div>
        <div class="stat-card-content">
            <span class="stat-card-label">RTL Languages</span>
            <span class="stat-card-value">{{ $languages->where('is_rtl', true)->count() }}</span>
        </div>
    </div>
    <div class="stat-card stat-card-info">
        <div class="stat-card-icon"><i class="bi bi-star"></i></div>
        <div class="stat-card-content">
            <span class="stat-card-label">Default</span>
            <span class="stat-card-value">{{ $languages->where('is_default', true)->first()->name ?? 'None' }}</span>
        </div>
    </div>
</div>

<!-- Languages Table -->
<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width: 60px;">#</th>
                        <th>Language</th>
                        <th>Code</th>
                        <th>Native Name</th>
                        <th style="width: 80px;">RTL</th>
                        <th style="width: 80px;">Default</th>
                        <th style="width: 80px;">Status</th>
                        <th style="width: 120px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($languages as $language)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>
                            <div class="d-flex align-items-center">
                                <span class="fs-5 me-2">{{ $language->flag }}</span>
                                <span class="fw-medium">{{ $language->name }}</span>
                            </div>
                        </td>
                        <td><code>{{ $language->code }}</code></td>
                        <td>{{ $language->native_name }}</td>
                        <td>
                            @if($language->is_rtl)
                            <span class="badge bg-warning">RTL</span>
                            @else
                            <span class="badge bg-secondary">LTR</span>
                            @endif
                        </td>
                        <td>
                            @if($language->is_default)
                            <span class="badge bg-success"><i class="bi bi-check me-1"></i>Default</span>
                            @else
                            <form action="{{ route('admin.settings.languages.setDefault', $language->id) }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-outline-success">Set Default</button>
                            </form>
                            @endif
                        </td>
                        <td>
                            @if($language->is_active)
                            <span class="badge bg-success">Active</span>
                            @else
                            <span class="badge bg-secondary">Inactive</span>
                            @endif
                        </td>
                        <td>
                            <div class="dropdown">
                                <button class="btn btn-sm btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown">
                                    <i class="bi bi-three-dots-vertical"></i>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    <li>
                                        <button class="dropdown-item" data-bs-toggle="modal" data-bs-target="#editLanguageModal{{ $language->id }}">
                                            <i class="bi bi-pencil me-2"></i>Edit
                                        </button>
                                    </li>
                                    @if(!$language->is_default)
                                    <li>
                                        <form action="{{ route('admin.settings.languages.destroy', $language->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="dropdown-item text-danger" onclick="return confirm('Are you sure you want to delete this language?')">
                                                <i class="bi bi-trash me-2"></i>Delete
                                            </button>
                                        </form>
                                    </li>
                                    @endif
                                </ul>
                            </div>
                        </td>
                    </tr>
                    
                    <!-- Edit Language Modal -->
                    <div class="modal fade" id="editLanguageModal{{ $language->id }}" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">Edit Language</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <form action="{{ route('admin.settings.languages.update', $language->id) }}" method="POST">
                                    @csrf
                                    @method('PUT')
                                    <div class="modal-body">
                                        <div class="mb-3">
                                            <label for="name{{ $language->id }}" class="form-label">Language Name <span class="text-danger">*</span></label>
                                            <input type="text" id="name{{ $language->id }}" name="name" class="form-control" value="{{ $language->name }}" required>
                                        </div>
                                        <div class="mb-3">
                                            <label for="code{{ $language->id }}" class="form-label">Code <span class="text-danger">*</span></label>
                                            <input type="text" id="code{{ $language->id }}" name="code" class="form-control" value="{{ $language->code }}" required>
                                            <div class="form-text">ISO 639-1 code (e.g., en, bn, es)</div>
                                        </div>
                                        <div class="mb-3">
                                            <label for="native_name{{ $language->id }}" class="form-label">Native Name</label>
                                            <input type="text" id="native_name{{ $language->id }}" name="native_name" class="form-control" value="{{ $language->native_name }}">
                                        </div>
                                        <div class="mb-3">
                                            <label for="flag{{ $language->id }}" class="form-label">Flag Emoji</label>
                                            <input type="text" id="flag{{ $language->id }}" name="flag" class="form-control" value="{{ $language->flag }}">
                                            <div class="form-text">Enter flag emoji (e.g., 🇺🇸, 🇧🇩)</div>
                                        </div>
                                        <div class="mb-3">
                                            <label for="sort_order{{ $language->id }}" class="form-label">Sort Order</label>
                                            <input type="number" id="sort_order{{ $language->id }}" name="sort_order" class="form-control" value="{{ $language->sort_order }}" min="0">
                                        </div>
                                        <div class="mb-3">
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" type="checkbox" id="is_rtl{{ $language->id }}" name="is_rtl" value="1" {{ $language->is_rtl ? 'checked' : '' }}>
                                                <label class="form-check-label" for="is_rtl{{ $language->id }}">Right-to-Left (RTL)</label>
                                            </div>
                                        </div>
                                        <div class="mb-3">
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" type="checkbox" id="is_active{{ $language->id }}" name="is_active" value="1" {{ $language->is_active ? 'checked' : '' }}>
                                                <label class="form-check-label" for="is_active{{ $language->id }}">Active</label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                        <button type="submit" class="btn btn-primary">Update Language</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center py-5">
                            <i class="bi bi-translate text-muted" style="font-size: 3rem;"></i>
                            <p class="text-muted mb-2 mt-2">No languages found</p>
                            <button type="button" class="btn btn-sm btn-primary mt-1" data-bs-toggle="modal" data-bs-target="#addLanguageModal">
                                <i class="bi bi-plus-lg me-1"></i> Add First Language
                            </button>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Add Language Modal -->
<div class="modal fade" id="addLanguageModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New Language</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('admin.settings.languages.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="name" class="form-label">Language Name <span class="text-danger">*</span></label>
                        <input type="text" id="name" name="name" class="form-control" placeholder="e.g., English" required>
                    </div>
                    <div class="mb-3">
                        <label for="code" class="form-label">Code <span class="text-danger">*</span></label>
                        <input type="text" id="code" name="code" class="form-control" placeholder="e.g., en" required>
                        <div class="form-text">ISO 639-1 code (e.g., en, bn, es, fr, de)</div>
                    </div>
                    <div class="mb-3">
                        <label for="native_name" class="form-label">Native Name</label>
                        <input type="text" id="native_name" name="native_name" class="form-control" placeholder="e.g., English, বাংলা, Español">
                    </div>
                    <div class="mb-3">
                        <label for="flag" class="form-label">Flag Emoji</label>
                        <input type="text" id="flag" name="flag" class="form-control" placeholder="e.g., 🇺🇸">
                        <div class="form-text">Enter flag emoji (e.g., 🇺🇸, 🇧🇩, 🇪🇸)</div>
                    </div>
                    <div class="mb-3">
                        <label for="sort_order" class="form-label">Sort Order</label>
                        <input type="number" id="sort_order" name="sort_order" class="form-control" value="0" min="0">
                    </div>
                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="is_rtl" name="is_rtl" value="1">
                            <label class="form-check-label" for="is_rtl">Right-to-Left (RTL)</label>
                        </div>
                        <div class="form-text">Enable for languages like Arabic, Hebrew, Persian</div>
                    </div>
                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="is_default" name="is_default" value="1">
                            <label class="form-check-label" for="is_default">Set as Default</label>
                        </div>
                    </div>
                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" checked>
                            <label class="form-check-label" for="is_active">Active</label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Language</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Auto-dismiss alerts
    setTimeout(function() {
        $('.alert').fadeOut('slow');
    }, 3000);
</script>
@endpush
