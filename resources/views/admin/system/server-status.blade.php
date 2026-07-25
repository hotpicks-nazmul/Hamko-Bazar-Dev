@extends('admin.layouts.app')

@section('title', 'Server Status')

@section('content')

<!-- Stats Cards Row -->
<div class="stat-card-row stat-card-row-6 mb-4" style="width: 100%; max-width: 100%;">
    <div class="stat-card stat-card-primary">
        <div class="stat-card-icon"><i class="bi bi-filetype-php"></i></div>
        <div class="stat-card-content">
            <span class="stat-card-label">PHP Version</span>
            <span class="stat-card-value">{{ $serverInfo['php_version'] }}</span>
        </div>
    </div>
    <div class="stat-card stat-card-success">
        <div class="stat-card-icon"><i class="bi bi-box-seam"></i></div>
        <div class="stat-card-content">
            <span class="stat-card-label">Laravel</span>
            <span class="stat-card-value">{{ $serverInfo['laravel_version'] }}</span>
        </div>
    </div>
    <div class="stat-card stat-card-info">
        <div class="stat-card-icon"><i class="bi bi-database"></i></div>
        <div class="stat-card-content">
            <span class="stat-card-label">Database</span>
            <span class="stat-card-value">{{ ucfirst($serverInfo['database_type']) }}</span>
        </div>
    </div>
    <div class="stat-card stat-card-secondary">
        <div class="stat-card-icon"><i class="bi bi-gear"></i></div>
        <div class="stat-card-content">
            <span class="stat-card-label">Max Execution</span>
            <span class="stat-card-value">{{ $serverInfo['max_execution_time'] }}s</span>
        </div>
    </div>
    <div class="stat-card stat-card-warning">
        <div class="stat-card-icon"><i class="bi bi-memory"></i></div>
        <div class="stat-card-content">
            <span class="stat-card-label">Memory Limit</span>
            <span class="stat-card-value">{{ $serverInfo['memory_limit'] }}</span>
        </div>
    </div>
    <div class="stat-card stat-card-danger">
        <div class="stat-card-icon"><i class="bi bi-hdd"></i></div>
        <div class="stat-card-content">
            <span class="stat-card-label">Server OS</span>
            <span class="stat-card-value">{{ $serverInfo['os'] }}</span>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-8">
        <!-- Server Information -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0 fw-semibold"><i class="bi bi-info-circle me-2"></i>Server Information</h5>
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
                            <span class="text-muted">Database Version</span>
                            <span class="fw-medium small">{{ $serverInfo['database_version'] }}</span>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <div class="d-flex justify-content-between border-bottom pb-2">
                            <span class="text-muted">Server Software</span>
                            <span class="fw-medium small">{{ $serverInfo['server_software'] }}</span>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <div class="d-flex justify-content-between border-bottom pb-2">
                            <span class="text-muted">Operating System</span>
                            <span class="fw-medium">{{ $serverInfo['os'] }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- PHP Configuration -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0 fw-semibold"><i class="bi bi-gear me-2"></i>PHP Configuration</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <div class="d-flex justify-content-between border-bottom pb-2">
                            <span class="text-muted">Max Execution Time</span>
                            <span class="fw-medium">{{ $serverInfo['max_execution_time'] }} seconds</span>
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
                            <span class="text-muted">Upload Max Filesize</span>
                            <span class="fw-medium">{{ $serverInfo['upload_max_filesize'] }}</span>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <div class="d-flex justify-content-between border-bottom pb-2">
                            <span class="text-muted">Post Max Size</span>
                            <span class="fw-medium">{{ $serverInfo['post_max_size'] }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- PHP Extensions -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0 fw-semibold"><i class="bi bi-puzzle me-2"></i>PHP Extensions</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    @foreach($serverInfo['extensions'] as $name => $loaded)
                    <div class="col-md-4 col-sm-6 mb-2">
                        <div class="d-flex align-items-center">
                            @if($loaded)
                            <i class="bi bi-check-circle-fill text-success me-2"></i>
                            <span class="text-success">{{ $name }}</span>
                            @else
                            <i class="bi bi-x-circle-fill text-danger me-2"></i>
                            <span class="text-danger">{{ $name }}</span>
                            @endif
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Directory Permissions -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0 fw-semibold"><i class="bi bi-folder-check me-2"></i>Directory Permissions</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    @foreach($serverInfo['directories'] as $path => $writable)
                    <div class="col-md-6 mb-2">
                        <div class="d-flex align-items-center justify-content-between border rounded p-2">
                            <span class="small">{{ $path }}</span>
                            @if($writable)
                            <span class="badge bg-success">
                                <i class="bi bi-check-lg me-1"></i> Writable
                            </span>
                            @else
                            <span class="badge bg-danger">
                                <i class="bi bi-x-lg me-1"></i> Not Writable
                            </span>
                            @endif
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <!-- Quick Stats -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0 fw-semibold"><i class="bi bi-speedometer2 me-2"></i>Quick Stats</h5>
            </div>
            <div class="card-body">
                @php
                    $health = $serverInfo['health'] ?? [];
                    $healthPercentage = $health['percentage'] ?? 0;
                    $healthColor = $healthPercentage >= 80 ? 'success' : ($healthPercentage >= 50 ? 'warning' : 'danger');
                @endphp
                <div class="text-center mb-4">
                    <div class="display-4 fw-bold text-{{ $healthColor }}">{{ $healthPercentage }}%</div>
                    <div class="text-muted">System Health</div>
                </div>
                <hr>
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">Extensions Loaded</span>
                    <span class="fw-medium">
                        {{ $health['extensions_loaded'] ?? 0 }}/{{ $health['extensions_total'] ?? 0 }}
                    </span>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">Directories Writable</span>
                    <span class="fw-medium">
                        {{ $health['directories_writable'] ?? 0 }}/{{ $health['directories_total'] ?? 0 }}
                    </span>
                </div>
            </div>
        </div>

        <!-- System Health Indicators -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0 fw-semibold"><i class="bi bi-heart-pulse me-2"></i>Health Status</h5>
            </div>
            <div class="card-body">
                @php
                    $dbConnected = $health['db_connected'] ?? false;
                    $cacheWorking = $health['cache_working'] ?? false;
                    $sessionActive = $health['session_active'] ?? false;
                @endphp
                <div class="d-flex align-items-center mb-3">
                    <i class="bi bi-{{ $dbConnected ? 'check-circle' : 'x-circle' }} text-{{ $dbConnected ? 'success' : 'danger' }} me-3"></i>
                    <div>
                        <div class="fw-medium">Database</div>
                        <div class="text-muted small">{{ $dbConnected ? 'Connected' : 'Not Connected' }}</div>
                    </div>
                </div>
                <div class="d-flex align-items-center mb-3">
                    <i class="bi bi-check-circle text-success me-3"></i>
                    <div>
                        <div class="fw-medium">PHP</div>
                        <div class="text-muted small">{{ $serverInfo['php_version'] }}</div>
                    </div>
                </div>
                <div class="d-flex align-items-center mb-3">
                    <i class="bi bi-{{ $cacheWorking ? 'check-circle' : 'x-circle' }} text-{{ $cacheWorking ? 'success' : 'danger' }} me-3"></i>
                    <div>
                        <div class="fw-medium">Cache</div>
                        <div class="text-muted small">{{ $cacheWorking ? 'Working' : 'Not Working' }}</div>
                    </div>
                </div>
                <div class="d-flex align-items-center mb-3">
                    @php
                        $dirsWritable = ($health['directories_writable'] ?? 0) === ($health['directories_total'] ?? 0);
                    @endphp
                    <i class="bi bi-{{ $dirsWritable ? 'check-circle' : 'x-circle' }} text-{{ $dirsWritable ? 'success' : 'danger' }} me-3"></i>
                    <div>
                        <div class="fw-medium">Storage</div>
                        <div class="text-muted small">{{ $dirsWritable ? 'Writable' : 'Permission Issues' }}</div>
                    </div>
                </div>
                <div class="d-flex align-items-center">
                    <i class="bi bi-{{ $sessionActive ? 'check-circle' : 'x-circle' }} text-{{ $sessionActive ? 'success' : 'danger' }} me-3"></i>
                    <div>
                        <div class="fw-medium">Sessions</div>
                        <div class="text-muted small">{{ $sessionActive ? 'Active' : 'Not Active' }}</div>
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
                    <a href="{{ route('admin.system.update') }}" class="btn btn-sm btn-outline-secondary text-start">
                        <i class="bi bi-arrow-up-circle me-1"></i> System Update
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
</style>
@endpush
