@extends('admin.layouts.app')

@section('title', 'Backup & Restore')

@section('content')
<div class="mb-4">
    <h4 class="mb-0"><i class="bi bi-cloud-arrow-up me-2"></i>Backup & Restore</h4>
</div>

<div class="row">
    <div class="col-lg-8">
        <!-- Create Backup -->
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white">
                <h6 class="mb-0"><i class="bi bi-cloud-arrow-up me-2"></i>Create New Backup</h6>
            </div>
            <div class="card-body">
                <p class="text-muted mb-3">Create a full backup of your database and important configuration files.</p>
                <form action="{{ route('admin.backup.create') }}" method="POST" id="backupForm">
                    @csrf
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-cloud-arrow-up me-1"></i> Create Backup
                    </button>
                </form>
            </div>
        </div>

        <!-- Restore Backup -->
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white">
                <h6 class="mb-0"><i class="bi bi-cloud-arrow-down me-2"></i>Restore from Backup</h6>
            </div>
            <div class="card-body">
                <p class="text-muted mb-3">Upload a backup file (.zip) to restore your database and settings.</p>
                <form action="{{ route('admin.backup.restore') }}" method="POST" id="restoreForm" enctype="multipart/form-data">
                    @csrf
                    <div class="mb-3">
                        <label for="backup_file" class="form-label">Select Backup File <span class="text-danger">*</span></label>
                        <input type="file" id="backup_file" name="backup_file" class="form-control @error('backup_file') is-invalid @enderror" accept=".zip" required>
                        @error('backup_file')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text">Upload a .zip backup file</div>
                    </div>
                    <div class="alert alert-warning mb-3">
                        <i class="bi bi-exclamation-triangle me-1"></i> 
                        <strong>Warning:</strong> Restoring from a backup will replace your current data. Please create a backup of your current data before restoring.
                    </div>
                    <button type="submit" class="btn btn-warning">
                        <i class="bi bi-cloud-arrow-down me-1"></i> Restore Backup
                    </button>
                </form>
            </div>
        </div>

        <!-- Existing Backups -->
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white">
                <h6 class="mb-0"><i class="bi bi-folder-check me-2"></i>Existing Backups ({{ count($files) }})</h6>
            </div>
            <div class="card-body p-0">
                @if(count($files) > 0)
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>File Name</th>
                                    <th>Size</th>
                                    <th>Created</th>
                                    <th style="width: 150px;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($files as $file)
                                    <tr>
                                        <td>
                                            <i class="bi bi-file-earmark-zip text-primary me-2"></i>
                                            {{ $file['name'] }}
                                        </td>
                                        <td>{{ number_format($file['size'] / 1024, 2) }} KB</td>
                                        <td>{{ \Carbon\Carbon::createFromTimestamp($file['created_at'])->format('M d, Y h:i A') }}</td>
                                        <td>
                                            <div class="d-flex gap-2">
                                                <a href="{{ route('admin.backup.download', $file['name']) }}" class="btn btn-sm btn-outline-primary" title="Download">
                                                    <i class="bi bi-download"></i>
                                                </a>
                                                <form action="{{ route('admin.backup.delete') }}" method="POST" class="d-inline">
                                                    @csrf
                                                    <input type="hidden" name="file" value="{{ $file['name'] }}">
                                                    <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Are you sure you want to delete this backup?')" title="Delete">
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
                        <i class="bi bi-folder text-muted" style="font-size: 3rem;"></i>
                        <p class="text-muted mb-0 mt-2">No backups found</p>
                        <p class="text-muted small">Create your first backup using the form above</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <!-- Quick Info -->
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white">
                <h6 class="mb-0"><i class="bi bi-info-circle me-2"></i>Quick Info</h6>
            </div>
            <div class="card-body">
                <p class="text-muted small mb-2">Backup includes:</p>
                <ul class="list-unstyled small text-muted">
                    <li class="mb-2"><i class="bi bi-check-circle text-success me-1"></i> Database dump (.sql)</li>
                    <li class="mb-2"><i class="bi bi-check-circle text-success me-1"></i> Environment config (.env)</li>
                    <li class="mb-2"><i class="bi bi-check-circle text-success me-1"></i> Composer dependencies</li>
                    <li class="mb-0"><i class="bi bi-check-circle text-success me-1"></i> File system structure</li>
                </ul>
            </div>
        </div>

        <!-- Important Notes -->
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white">
                <h6 class="mb-0"><i class="bi bi-exclamation-triangle me-2 text-warning"></i>Important Notes</h6>
            </div>
            <div class="card-body">
                <ul class="list-unstyled small text-muted mb-0">
                    <li class="mb-2">1. Regular backups are recommended</li>
                    <li class="mb-2">2. Store backups securely off-site</li>
                    <li class="mb-2">3. Test restore on staging first</li>
                    <li class="mb-0">4. Keep backups for important dates</li>
                </ul>
            </div>
        </div>

        <!-- Other Settings Links -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white">
                <h6 class="mb-0"><i class="bi bi-link-45deg me-2"></i>Other Settings</h6>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="{{ route('admin.settings.general') }}" class="btn btn-sm btn-outline-secondary text-start">
                        <i class="bi bi-sliders me-1"></i> General Settings
                    </a>
                    <a href="{{ route('admin.settings.file-system') }}" class="btn btn-sm btn-outline-secondary text-start">
                        <i class="bi bi-hdd me-1"></i> File System & Cache
                    </a>
                    <a href="{{ route('admin.settings.email') }}" class="btn btn-sm btn-outline-secondary text-start">
                        <i class="bi bi-envelope me-1"></i> SMTP Settings
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
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
    // Show loading state when creating backup
    document.getElementById('backupForm').addEventListener('submit', function() {
        const btn = this.querySelector('button[type="submit"]');
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Creating Backup...';
    });

    // Show warning when restoring backup
    document.getElementById('restoreForm').addEventListener('submit', function(e) {
        if (!confirm('Are you sure you want to restore from this backup? This will replace your current data.')) {
            e.preventDefault();
            return false;
        }
        
        const btn = this.querySelector('button[type="submit"]');
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Restoring...';
    });
</script>
@endpush
