@extends('admin.layouts.app')

@section('title', 'System Update')

@section('content')

<!-- Stats Cards Row -->
<div class="stat-card-row mb-4">
    <div class="stat-card stat-card-primary">
        <div class="stat-card-icon"><i class="bi bi-code-square"></i></div>
        <div class="stat-card-content">
            <span class="stat-card-label">Current Version</span>
            <span class="stat-card-value">{{ $version['app_version'] ?? '1.0.0' }}</span>
        </div>
    </div>
    <div class="stat-card stat-card-{{ ($version['update_available'] ?? '0') == '1' ? 'warning' : 'success' }}">
        <div class="stat-card-icon"><i class="bi bi-{{ ($version['update_available'] ?? '0') == '1' ? 'cloud-download' : 'check-circle' }}"></i></div>
        <div class="stat-card-content">
            <span class="stat-card-label">Update Status</span>
            <span class="stat-card-value">{{ ($version['update_available'] ?? '0') == '1' ? $version['latest_version'] ?? 'Available' : 'Up to Date' }}</span>
        </div>
    </div>
    <div class="stat-card stat-card-info">
        <div class="stat-card-icon"><i class="bi bi-calendar-check"></i></div>
        <div class="stat-card-content">
            <span class="stat-card-label">Last Updated</span>
            <span class="stat-card-value">{{ $version['last_updated'] ?? 'Never' }}</span>
        </div>
    </div>
    <div class="stat-card stat-card-secondary">
        <div class="stat-card-icon"><i class="bi bi-search"></i></div>
        <div class="stat-card-content">
            <span class="stat-card-label">Last Check</span>
            <span class="stat-card-value">{{ $settings['last_check'] ?? 'Never' }}</span>
        </div>
    </div>
</div>

@if(session('success'))
<div class="alert alert-success alert-dismissible fade show" role="alert">
    <i class="bi bi-check-circle me-1"></i> {{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

@if(session('error'))
<div class="alert alert-danger alert-dismissible fade show" role="alert">
    <i class="bi bi-exclamation-circle me-1"></i> {{ session('error') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

@if(session('info'))
<div class="alert alert-info alert-dismissible fade show" role="alert">
    <i class="bi bi-info-circle me-1"></i> {{ session('info') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

<div class="row">
    <div class="col-lg-8">
        <!-- Current Version Status -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0 fw-semibold"><i class="bi bi-info-circle me-2"></i>Current System Status</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <div class="d-flex align-items-center">
                            <i class="bi bi-check2-circle text-success fs-4 me-3"></i>
                            <div>
                                <div class="text-muted small">Current Version</div>
                                <div class="h4 mb-0 fw-bold">{{ $version['app_version'] ?? '1.0.0' }}</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <div class="d-flex align-items-center">
                            <i class="bi bi-{{ ($version['update_available'] ?? '0') == '1' ? 'cloud-download text-warning' : 'check-circle text-success' }} fs-4 me-3"></i>
                            <div>
                                <div class="text-muted small">Update Status</div>
                                <div class="h6 mb-0">
                                    @if(($version['update_available'] ?? '0') == '1')
                                        <span class="badge bg-warning text-dark">
                                            <i class="bi bi-arrow-up me-1"></i> {{ $version['latest_version'] ?? '' }} Available
                                        </span>
                                    @else
                                        <span class="badge bg-success">
                                            <i class="bi bi-check me-1"></i> Up to Date
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row mt-3">
                    <div class="col-md-6">
                        <div class="text-muted small">Last Updated</div>
                        <div class="fw-medium">
                            {{ $version['last_updated'] ?? 'Never' }}
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="text-muted small">Last Check</div>
                        <div class="fw-medium">
                            {{ $settings['last_check'] ?? 'Never' }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Update Actions -->
        <form id="updateForm" method="POST" action="{{ route('admin.system.update.perform') }}">
            @csrf
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0 fw-semibold"><i class="bi bi-arrow-repeat me-2"></i>Update Actions</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <button type="submit" name="update_type" value="check" class="btn btn-outline-primary w-100 p-3">
                                <i class="bi bi-search fs-4 d-block mb-2"></i>
                                <strong>Check for Updates</strong>
                                <div class="small text-muted">Check if new version available</div>
                            </button>
                        </div>
                        <div class="col-md-4">
                            <button type="submit" name="update_type" value="backup" class="btn btn-outline-secondary w-100 p-3">
                                <i class="bi bi-cloud-upload fs-4 d-block mb-2"></i>
                                <strong>Create Backup</strong>
                                <div class="small text-muted">Backup database & files</div>
                            </button>
                        </div>
                        <div class="col-md-4">
                            <button type="button" class="btn btn-success w-100 p-3" data-bs-toggle="modal" data-bs-target="#installUpdateModal">
                                <i class="bi bi-download fs-4 d-block mb-2"></i>
                                <strong>Install Update</strong>
                                <div class="small">Update to latest version</div>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </form>

        <!-- Server Information -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0 fw-semibold"><i class="bi bi-server me-2"></i>Server Information</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <div class="d-flex justify-content-between border-bottom pb-2">
                            <span class="text-muted">PHP Version</span>
                            <span class="fw-medium">{{ $serverInfo['php_version'] }}</span>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <div class="d-flex justify-content-between border-bottom pb-2">
                            <span class="text-muted">Laravel Version</span>
                            <span class="fw-medium">{{ $serverInfo['laravel_version'] }}</span>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <div class="d-flex justify-content-between border-bottom pb-2">
                            <span class="text-muted">Database Type</span>
                            <span class="fw-medium">{{ ucfirst($serverInfo['database_type']) }}</span>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <div class="d-flex justify-content-between border-bottom pb-2">
                            <span class="text-muted">Max Execution Time</span>
                            <span class="fw-medium">{{ $serverInfo['max_execution_time'] }}s</span>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <div class="d-flex justify-content-between border-bottom pb-2">
                            <span class="text-muted">Memory Limit</span>
                            <span class="fw-medium">{{ $serverInfo['memory_limit'] }}</span>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <div class="d-flex justify-content-between border-bottom pb-2">
                            <span class="text-muted">Upload Max Size</span>
                            <span class="fw-medium">{{ $serverInfo['upload_max_filesize'] }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <!-- Update Settings -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0 fw-semibold"><i class="bi bi-gear me-2"></i>Update Settings</h5>
            </div>
            <div class="card-body">
                <form id="settingsForm" method="POST" action="{{ route('admin.system.update.settings') }}">
                    @csrf
                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" id="auto_check_updates" name="auto_check_updates" 
                               {{ ($settings['auto_check_updates'] ?? '1') == '1' ? 'checked' : '' }}>
                        <label class="form-check-label" for="auto_check_updates">
                            <i class="bi bi-clock text-primary me-1"></i> Auto Check Updates
                        </label>
                        <div class="form-text small">Automatically check for updates daily</div>
                    </div>

                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" id="auto_install_security" name="auto_install_security" 
                               {{ ($settings['auto_install_security'] ?? '0') == '1' ? 'checked' : '' }}>
                        <label class="form-check-label" for="auto_install_security">
                            <i class="bi bi-shield-check text-success me-1"></i> Auto Install Security
                        </label>
                        <div class="form-text small">Automatically install security patches</div>
                    </div>

                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" id="notify_on_update" name="notify_on_update" 
                               {{ ($settings['notify_on_update'] ?? '1') == '1' ? 'checked' : '' }}>
                        <label class="form-check-label" for="notify_on_update">
                            <i class="bi bi-bell text-warning me-1"></i> Notify on Update
                        </label>
                        <div class="form-text small">Send notification when update available</div>
                    </div>

                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" id="backup_before_update" name="backup_before_update" 
                               {{ ($settings['backup_before_update'] ?? '1') == '1' ? 'checked' : '' }}>
                        <label class="form-check-label" for="backup_before_update">
                            <i class="bi bi-cloud-arrow-up text-info me-1"></i> Backup Before Update
                        </label>
                        <div class="form-text small">Create backup before installing updates</div>
                    </div>

                    <div class="mb-3">
                        <label for="update_channel" class="form-label">Update Channel</label>
                        <select id="update_channel" name="update_channel" class="form-select">
                            <option value="stable" {{ ($settings['update_channel'] ?? 'stable') == 'stable' ? 'selected' : '' }}>
                                Stable (Recommended)
                            </option>
                            <option value="beta" {{ ($settings['update_channel'] ?? '') == 'beta' ? 'selected' : '' }}>
                                Beta
                            </option>
                            <option value="development" {{ ($settings['update_channel'] ?? '') == 'development' ? 'selected' : '' }}>
                                Development
                            </option>
                        </select>
                        <div class="form-text">Choose which updates to receive</div>
                    </div>

                    <div class="mb-3">
                        <label for="update_server_url" class="form-label">Update Server URL</label>
                        <input type="url" class="form-control" id="update_server_url" name="update_server_url"
                               value="{{ $settings['update_server_url'] ?? 'https://api.yourdomain.com/updates/check' }}"
                               placeholder="https://api.yourdomain.com/updates/check">
                        <div class="form-text">URL of your update server API (leave default for official updates)</div>
                    </div>

                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-check-lg me-1"></i> Save Settings
                    </button>
                </form>
            </div>
        </div>

        <!-- System Health -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0 fw-semibold"><i class="bi bi-heart-pulse me-2"></i>System Health</h5>
            </div>
            <div class="card-body">
                <div class="d-flex align-items-center mb-3">
                    <i class="bi bi-check-circle text-success me-3"></i>
                    <div>
                        <div class="fw-medium">Database Connection</div>
                        <div class="text-muted small">Connected</div>
                    </div>
                </div>
                <div class="d-flex align-items-center mb-3">
                    <i class="bi bi-check-circle text-success me-3"></i>
                    <div>
                        <div class="fw-medium">Cache System</div>
                        <div class="text-muted small">Working</div>
                    </div>
                </div>
                <div class="d-flex align-items-center mb-3">
                    <i class="bi bi-check-circle text-success me-3"></i>
                    <div>
                        <div class="fw-medium">File Storage</div>
                        <div class="text-muted small">Writable</div>
                    </div>
                </div>
                <div class="d-flex align-items-center">
                    <i class="bi bi-check-circle text-success me-3"></i>
                    <div>
                        <div class="fw-medium">Cron Jobs</div>
                        <div class="text-muted small">Active</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Links -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0 fw-semibold">Quick Links</h5>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="{{ route('admin.system.server-status') }}" class="btn btn-sm btn-outline-secondary text-start">
                        <i class="bi bi-server me-1"></i> Server Status
                    </a>
                    <a href="{{ route('admin.settings.file-system') }}" class="btn btn-sm btn-outline-secondary text-start">
                        <i class="bi bi-hdd me-1"></i> File System & Cache
                    </a>
                    <a href="{{ route('admin.settings.index') }}" class="btn btn-sm btn-outline-secondary text-start">
                        <i class="bi bi-gear me-1"></i> All Settings
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Install Update Modal -->
<div class="modal fade" id="installUpdateModal" tabindex="-1" aria-labelledby="installUpdateModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="installUpdateModalLabel">
                    <i class="bi bi-download me-2"></i>Install Update
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-warning">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    <strong>Warning:</strong> Before installing updates, it's recommended to create a backup.
                </div>
                <p>You are about to install the latest system update. This will:</p>
                <ul>
                    <li>Run database migrations</li>
                    <li>Update system files</li>
                    <li>Clear cache and optimize</li>
                </ul>
                <div class="form-check form-switch mt-3">
                    <input class="form-check-input" type="checkbox" id="modal_backup" checked>
                    <label class="form-check-label" for="modal_backup">
                        Create backup before updating
                    </label>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="bi bi-x-lg me-1"></i> Cancel
                </button>
                <form action="{{ route('admin.system.update.perform') }}" method="POST" class="d-inline">
                    @csrf
                    <input type="hidden" name="update_type" value="install">
                    <input type="hidden" name="backup_before_update" id="modal_backup_value" value="1">
                    <input type="hidden" name="new_version" value="1.0.1">
                    <button type="submit" class="btn btn-success">
                        <i class="bi bi-download me-1"></i> Install Now
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection

@push('styles')
<style>
    /* Force Bootstrap Icons to display on this page */
    .stat-card-icon i,
    .stat-card-icon i::before,
    .bi::before,
    [class*="bi bi-"]::before {
        display: inline-block !important;
        visibility: visible !important;
        opacity: 1 !important;
        font-family: 'bootstrap-icons' !important;
    }
    
    /* Override icon colors for stat cards */
    .stat-card-primary .stat-card-icon i::before { color: #0d6efd !important; }
    .stat-card-success .stat-card-icon i::before { color: #198754 !important; }
    .stat-card-info .stat-card-icon i::before { color: #0dcaf0 !important; }
    .stat-card-warning .stat-card-icon i::before { color: #ffc107 !important; }
    .stat-card-danger .stat-card-icon i::before { color: #dc3545 !important; }
    .stat-card-secondary .stat-card-icon i::before { color: #6c757d !important; }
    
    /* Make the whole icon colored */
    .stat-card-icon i { color: inherit !important; }
    
    /* Force specific icons used in Current System Status section */
    .bi-check2-circle::before { content: "\F26D" !important; color: #198754 !important; }
    .bi-cloud-download::before { content: "\F34A" !important; color: inherit !important; }
    
    /* Text color utility classes */
    .text-success { color: #198754 !important; }
    .text-primary { color: #0d6efd !important; }
    .text-warning { color: #ffc107 !important; }
    .text-danger { color: #dc3545 !important; }
</style>
@endpush

@push('scripts')
<script>
    // Update modal checkbox value
    document.getElementById('modal_backup').addEventListener('change', function() {
        document.getElementById('modal_backup_value').value = this.checked ? '1' : '0';
    });
</script>
@endpush
