<!-- System Status Component -->
<!-- This component displays the current system version and update status -->
<!-- Usage: @include('themes.general.components.system-status') -->

@php
    // Get system version info
    $version = \App\Models\Setting::getSystemVersion();
    $settings = \App\Models\Setting::getSystemUpdateSettings();
    
    $currentVersion = $version['app_version'] ?? '1.0.0';
    $updateAvailable = ($version['update_available'] ?? '0') == '1';
    $latestVersion = $version['latest_version'] ?? $currentVersion;
    $lastUpdated = $version['last_updated'] ?? 'Never';
@endphp

<div class="system-status-component">
    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <div class="d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center">
                    <div class="bg-{{ $updateAvailable ? 'warning' : 'success' }} bg-opacity-10 p-3 rounded me-3">
                        <i class="bi bi-{{ $updateAvailable ? 'exclamation-triangle' : 'check-circle' }} text-{{ $updateAvailable ? 'warning' : 'success' }} fs-4"></i>
                    </div>
                    <div>
                        <div class="text-muted small">System Version</div>
                        <div class="h5 mb-0 fw-bold">{{ $currentVersion }}</div>
                    </div>
                </div>
                <div class="text-end">
                    @if($updateAvailable)
                        <span class="badge bg-warning text-dark">
                            <i class="bi bi-arrow-up me-1"></i> {{ $latestVersion }} Available
                        </span>
                    @else
                        <span class="badge bg-success">
                            <i class="bi bi-check me-1"></i> Up to Date
                        </span>
                    @endif
                </div>
            </div>
            
            @if($updateAvailable)
            <div class="mt-3">
                <a href="{{ route('admin.system.update') }}" class="btn btn-sm btn-warning">
                    <i class="bi bi-download me-1"></i> Update Now
                </a>
            </div>
            @endif
            
            <hr>
            
            <div class="row">
                <div class="col-6">
                    <div class="text-muted small">Last Updated</div>
                    <div class="fw-medium">{{ $lastUpdated }}</div>
                </div>
                <div class="col-6">
                    <div class="text-muted small">Auto Update</div>
                    <div class="fw-medium">
                        @if(($settings['auto_check_updates'] ?? '1') == '1')
                            <span class="text-success"><i class="bi bi-check-circle"></i> Enabled</span>
                        @else
                            <span class="text-muted"><i class="bi bi-x-circle"></i> Disabled</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- AJAX-based System Status (for dynamic updates) -->
<!-- To use AJAX version, include this script and add class 'ajax-system-status' to any element -->

@push('scripts')
<script>
(function() {
    // Only run if there's an element requesting AJAX version
    const ajaxElements = document.querySelectorAll('.ajax-system-status');
    if (ajaxElements.length === 0) return;
    
    // Fetch system status from API (public route)
    fetch('{{ route("api.system.version") }}')
        .then(response => response.json())
        .then(data => {
            if (data.success && data.data) {
                ajaxElements.forEach(el => {
                    const html = buildSystemStatusHTML(data.data);
                    el.innerHTML = html;
                });
            }
        })
        .catch(error => console.error('Error fetching system status:', error));
    
    function buildSystemStatusHTML(data) {
        const updateBadge = data.update_available 
            ? '<span class="badge bg-warning text-dark"><i class="bi bi-arrow-up me-1"></i> ' + data.latest_version + ' Available</span>'
            : '<span class="badge bg-success"><i class="bi bi-check me-1"></i> Up to Date</span>';
        
        return `
            <div class="d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center">
                    <div class="bg-${data.update_available ? 'warning' : 'success'} bg-opacity-10 p-2 rounded me-2">
                        <i class="bi bi-${data.update_available ? 'exclamation-triangle' : 'check-circle'} text-${data.update_available ? 'warning' : 'success'}"></i>
                    </div>
                    <div>
                        <div class="text-muted small">v${data.current_version}</div>
                    </div>
                </div>
                ${updateBadge}
            </div>
        `;
    }
})();
</script>
@endpush
