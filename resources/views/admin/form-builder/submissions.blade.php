@extends('admin.layouts.app')

@section('title', 'Submissions - ' . $form->name)

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-0"><i class="bi bi-inbox me-2"></i>Form Submissions</h4>
        <small class="text-muted">{{ $form->name }} - {{ $form->submissions_count }} total submissions</small>
    </div>
    <div>
        <a href="{{ route('admin.form-builder.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> Back to Forms
        </a>
        <a href="{{ route('admin.form-builder.edit', $form->id) }}" class="btn btn-outline-primary">
            <i class="bi bi-pencil me-1"></i> Edit Form
        </a>
        <a href="{{ route('admin.form-builder.submissions.export', $form->id) }}" class="btn btn-outline-success">
            <i class="bi bi-download me-1"></i> Export CSV
        </a>
    </div>
</div>

<!-- Filters -->
<div class="card border-0 shadow-sm mb-3">
    <div class="card-body py-3">
        <form method="GET" id="filterForm">
            <div class="row g-2 align-items-end">
                <div class="col-lg-4 col-md-6">
                    <label class="form-label small text-muted">Search</label>
                    <div class="input-group input-group-sm">
                        <span class="input-group-text"><i class="bi bi-search"></i></span>
                        <input type="text" name="search" id="liveSearch" class="form-control" 
                               placeholder="Search submissions..." value="{{ request('search') }}">
                    </div>
                </div>
                <div class="col-lg-2 col-md-3">
                    <label class="form-label small text-muted">Status</label>
                    <select name="status" id="filterStatus" class="form-select form-select-sm">
                        <option value="">All</option>
                        <option value="unread" {{ request('status') === 'unread' ? 'selected' : '' }}>Unread</option>
                        <option value="read" {{ request('status') === 'read' ? 'selected' : '' }}>Read</option>
                    </select>
                </div>
                <div class="col-lg-2 col-md-3">
                    <a href="{{ route('admin.form-builder.submissions', $form->id) }}" class="btn btn-sm btn-outline-secondary">
                        <i class="bi bi-x-lg me-1"></i> Reset
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Submissions Table -->
<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width: 50px;">#</th>
                        <th>User</th>
                        <th>IP Address</th>
                        <th>Data Preview</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th style="width: 120px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($submissions as $submission)
                    <tr class="{{ !$submission->is_read ? 'table-warning' : '' }}">
                        <td>
                            <span class="text-muted">{{ $submission->id }}</span>
                        </td>
                        <td>
                            @if($submission->user)
                                <div class="fw-medium">{{ $submission->user->name }}</div>
                                <small class="text-muted">{{ $submission->user->email }}</small>
                            @elseif($submission->guest_email)
                                <div class="fw-medium">{{ $submission->guest_email }}</div>
                                <small class="text-muted">Guest</small>
                            @else
                                <div class="text-muted">Guest</div>
                            @endif
                        </td>
                        <td>
                            <code class="small">{{ $submission->ip_address ?? '-' }}</code>
                        </td>
                        <td>
                            @php
                                $data = $submission->data_array;
                                $preview = [];
                                foreach (array_slice($form->fields->toArray(), 0, 2) as $field) {
                                    $value = $data[$field['name']] ?? '-';
                                    if (is_array($value)) {
                                        $value = implode(', ', $value);
                                    }
                                    $preview[] = $field['label'] . ': ' . (strlen($value) > 30 ? substr($value, 0, 30) . '...' : $value);
                                }
                            @endphp
                            <small>{{ implode(' | ', $preview) }}</small>
                        </td>
                        <td>
                            @if($submission->is_read)
                                <span class="badge bg-secondary">Read</span>
                            @else
                                <span class="badge bg-warning text-dark">Unread</span>
                            @endif
                        </td>
                        <td>
                            <small class="text-muted">{{ $submission->created_at->format('d M, Y h:i A') }}</small>
                        </td>
                        <td>
                            <div class="btn-group">
                                <a href="{{ route('admin.form-builder.submissions.show', [$form->id, $submission->id]) }}" 
                                   class="btn btn-sm btn-outline-primary" title="View">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <form action="{{ route('admin.form-builder.submissions.toggle-read', [$form->id, $submission->id]) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-sm {{ $submission->is_read ? 'btn-outline-warning' : 'btn-outline-success' }}" 
                                            title="{{ $submission->is_read ? 'Mark Unread' : 'Mark Read' }}">
                                        <i class="bi bi-{{ $submission->is_read ? 'envelope' : 'envelope-open' }}"></i>
                                    </button>
                                </form>
                                <form action="{{ route('admin.form-builder.submissions.destroy', [$form->id, $submission->id]) }}" 
                                      method="POST" class="d-inline" onsubmit="return confirm('Are you sure?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-5">
                            <i class="bi bi-inbox text-muted" style="font-size: 3rem;"></i>
                            <p class="text-muted mb-2 mt-2">No submissions yet</p>
                            <a href="{{ route('forms.show', $form->slug) }}" target="_blank" class="btn btn-sm btn-primary mt-1">
                                <i class="bi bi-box-arrow-up-right me-1"></i> View Form
                            </a>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if($submissions->hasPages())
        <div class="card-footer bg-white d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div class="text-muted small">
                Showing {{ $submissions->firstItem() }} - {{ $submissions->lastItem() }} of {{ $submissions->total() }} submissions
            </div>
            <div>
                {{ $submissions->appends(request()->query())->links() }}
            </div>
        </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const filterStatus = document.getElementById('filterStatus');
    const liveSearch = document.getElementById('liveSearch');
    const filterForm = document.getElementById('filterForm');
    
    function applyFilters() {
        filterForm.submit();
    }
    
    filterStatus.addEventListener('change', applyFilters);
    
    let searchTimeout;
    liveSearch.addEventListener('input', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(applyFilters, 500);
    });
});
</script>
@endpush
