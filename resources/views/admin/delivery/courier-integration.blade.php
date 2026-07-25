@extends('admin.layouts.app')

@section('title', 'Courier Integration')

@section('content')
<!-- Page Header -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1"><i class="bi bi-truck me-2"></i>Courier Integration</h4>
        <p class="text-muted mb-0 d-none d-md-block">Manage and integrate Bangladeshi courier services</p>
    </div>
    <a href="{{ route('admin.delivery.carriers.index') }}" class="btn btn-outline-primary">
        <i class="bi bi-gear me-1"></i><span class="d-none d-sm-inline">Manage Carriers</span>
    </a>
</div>

<!-- Statistics Cards -->
<div class="stat-card-row mb-4">
    <div class="stat-card stat-card-primary">
        <div class="stat-card-icon"><i class="bi bi-truck"></i></div>
        <div class="stat-card-content">
            <span class="stat-card-label">Total Couriers</span>
            <span class="stat-card-value">{{ $stats['total'] }}</span>
        </div>
    </div>
    <div class="stat-card stat-card-success">
        <div class="stat-card-icon"><i class="bi bi-check-circle"></i></div>
        <div class="stat-card-content">
            <span class="stat-card-label">Active Couriers</span>
            <span class="stat-card-value">{{ $stats['active'] }}</span>
        </div>
    </div>
    <div class="stat-card stat-card-warning">
        <div class="stat-card-icon"><i class="bi bi-key"></i></div>
        <div class="stat-card-content">
            <span class="stat-card-label">API Configured</span>
            <span class="stat-card-value">{{ $stats['api_configured'] }}</span>
        </div>
    </div>
    <div class="stat-card stat-card-info">
        <div class="stat-card-icon"><i class="bi bi-cash-coin"></i></div>
        <div class="stat-card-content">
            <span class="stat-card-label">COD Support</span>
            <span class="stat-card-value">{{ $stats['supports_cod'] }}</span>
        </div>
    </div>
</div>

<!-- Active Couriers Section -->
@if($activeCarriers->count() > 0)
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white">
        <h6 class="mb-0"><i class="bi bi-check2-circle text-success me-2"></i>Active Couriers</h6>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Courier</th>
                        <th>Type</th>
                        <th>Services</th>
                        <th>API Status</th>
                        <th>Base Rate</th>
                        <th>Tracking</th>
                        <th>COD</th>
                        <th style="width: 120px;" class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($activeCarriers as $carrier)
                    <tr>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="bg-light rounded p-2 me-2">
                                    <i class="bi bi-truck"></i>
                                </div>
                                <div>
                                    <strong>{{ $carrier->name }}</strong>
                                    @if($carrier->is_featured)
                                    <span class="badge bg-warning bg-opacity-10 text-warning ms-1">Featured</span>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="badge bg-light text-dark">{{ $carrier->carrier_type_label }}</span>
                        </td>
                        <td>
                            <span class="badge bg-light text-dark">{{ $carrier->service_type_label }}</span>
                        </td>
                        <td>
                            @if($carrier->is_api_configured)
                            <span class="badge bg-success bg-opacity-10 text-success">
                                <i class="bi bi-check-circle-fill me-1"></i> Configured
                            </span>
                            @else
                            <span class="badge bg-warning bg-opacity-10 text-warning">
                                <i class="bi bi-exclamation-circle-fill me-1"></i> Not Configured
                            </span>
                            @endif
                        </td>
                        <td>৳{{ number_format($carrier->base_rate, 0) }}</td>
                        <td>
                            @if($carrier->supports_tracking)
                            <i class="bi bi-check-circle-fill text-success"></i>
                            @else
                            <i class="bi bi-x-circle-fill text-muted"></i>
                            @endif
                        </td>
                        <td>
                            @if($carrier->supports_cod)
                            <i class="bi bi-check-circle-fill text-success"></i>
                            @else
                            <i class="bi bi-x-circle-fill text-muted"></i>
                            @endif
                        </td>
                        <td class="text-end">
                            <div class="btn-group">
                                <a href="{{ route('admin.delivery.carriers.edit', $carrier->id) }}" class="btn btn-sm btn-outline-primary" title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <form action="{{ route('admin.delivery.carriers.toggle-status', $carrier->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('POST')
                                    <button type="submit" class="btn btn-sm btn-outline-{{ $carrier->is_active ? 'warning' : 'success' }}" title="Toggle Status">
                                        <i class="bi bi-{{ $carrier->is_active ? 'pause' : 'play' }}-fill"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@else
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white">
        <h6 class="mb-0"><i class="bi bi-check2-circle text-success me-2"></i>Active Couriers</h6>
    </div>
    <div class="card-body text-center py-5">
        <i class="bi bi-truck text-muted" style="font-size: 3rem;"></i>
        <p class="text-muted mb-2 mt-2">No active couriers yet</p>
        <a href="#available-couriers" class="btn btn-sm btn-primary mt-1">
            <i class="bi bi-plus-lg me-1"></i> Add Your First Courier
        </a>
    </div>
</div>
@endif

<!-- Available Bangladeshi Couriers -->
<div class="card border-0 shadow-sm mb-4" id="available-couriers">
    <div class="card-header bg-white">
        <h6 class="mb-0"><i class="bi bi-grid me-2"></i>Popular Bangladeshi Couriers</h6>
    </div>
    <div class="card-body">
        <p class="text-muted mb-3 d-none d-md-block">Click on a courier to add it to your system. You can configure API settings after adding.</p>
        <div class="row g-3">
            @foreach($bangladeshiCouriers as $courier)
            @php
                $isAdded = $carriers->where('slug', $courier['slug'])->first();
            @endphp
            <div class="col-6 col-lg-3 col-md-4 col-sm-6">
                <div class="card border {{ $isAdded ? 'border-success' : 'border' }} h-100 courier-card" style="cursor: pointer;" 
                     onclick="handleCourierClick('{{ $courier['name'] }}', {{ $isAdded ? 'true' : 'false' }}, '{{ $isAdded ? route('admin.delivery.carriers.edit', $isAdded->id) : '' }}')">
                    <div class="card-body text-center">
                        <div class="rounded-circle bg-light p-3 d-inline-block mb-3">
                            <i class="bi bi-truck fs-4 text-primary"></i>
                        </div>
                        <h6 class="mb-1">{{ $courier['name'] }}</h6>
                        <p class="text-muted small mb-2">{{ $courier['description'] }}</p>
                        <div class="d-flex justify-content-center gap-2 flex-wrap mb-2">
                            @if($courier['supports_cod'])
                            <span class="badge bg-success text-white small">COD</span>
                            @endif
                            @if($courier['supports_tracking'])
                            <span class="badge bg-info text-white small">Tracking</span>
                            @endif
                            <span class="badge bg-secondary text-white small">{{ $courier['estimated_delivery_days'] }} days</span>
                        </div>
                        <div class="mt-2">
                            @if($isAdded)
                            <span class="badge bg-success">
                                <i class="bi bi-check-circle me-1"></i> Added
                            </span>
                            <a href="{{ route('admin.delivery.carriers.edit', $isAdded->id) }}" class="btn btn-sm btn-outline-primary mt-2">
                                <i class="bi bi-pencil me-1"></i> Configure
                            </a>
                            @else
                            <button type="button" class="btn btn-sm btn-primary mt-2" onclick="event.stopPropagation(); addCourier('{{ $courier['name'] }}')">
                                <i class="bi bi-plus-lg me-1"></i> Add Courier
                            </button>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</div>

<!-- API Configuration Info -->
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white">
        <h6 class="mb-0"><i class="bi bi-info-circle me-2"></i>API Configuration Guide</h6>
    </div>
    <div class="card-body">
        <div class="row g-4">
            <div class="col-md-6">
                <h6 class="fw-bold">How to Configure Courier API</h6>
                <ol class="text-muted small">
                    <li class="mb-2">Click "Add Courier" to add a courier service</li>
                    <li class="mb-2">Click "Configure" on the courier card</li>
                    <li class="mb-2">Enter your API credentials (API Key, Secret, Token)</li>
                    <li class="mb-2">Set the API mode (Sandbox/Production)</li>
                    <li class="mb-2">Save the configuration</li>
                </ol>
            </div>
            <div class="col-md-6">
                <h6 class="fw-bold">Features</h6>
                <ul class="list-unstyled text-muted small">
                    <li class="mb-2"><i class="bi bi-check text-success me-2"></i>Automatic tracking number generation</li>
                    <li class="mb-2"><i class="bi bi-check text-success me-2"></i>Real-time tracking status updates</li>
                    <li class="mb-2"><i class="bi bi-check text-success me-2"></i>Cash on Delivery (COD) support</li>
                    <li class="mb-2"><i class="bi bi-check text-success me-2"></i>Shipping rate calculation</li>
                    <li class="mb-2"><i class="bi bi-check text-success me-2"></i>Bulk shipment booking</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<!-- Add Courier Modal -->
<div class="modal fade" id="addCourierModal" tabindex="-1" aria-labelledby="addCourierModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addCourierModalLabel">Add Courier</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('admin.delivery.courier-integration.add') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <p>You are about to add the following courier service:</p>
                    <div class="alert alert-info">
                        <strong id="selectedCourierName"></strong>
                    </div>
                    <p class="text-muted small">After adding, you can configure the API credentials from the carriers management page.</p>
                    <input type="hidden" name="courier_name" id="courierNameInput">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-plus-lg me-1"></i> Add Courier
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .courier-card {
        transition: all 0.2s ease;
    }
    .courier-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }
    .courier-card.border-success {
        background-color: rgba(25, 135, 84, 0.02);
    }
</style>
@endpush

@push('scripts')
<script>
    function addCourier(courierName) {
        document.getElementById('selectedCourierName').textContent = courierName;
        document.getElementById('courierNameInput').value = courierName;
        var modal = new bootstrap.Modal(document.getElementById('addCourierModal'));
        modal.show();
    }

    function handleCourierClick(courierName, isAdded, editUrl) {
        if (isAdded && editUrl) {
            window.location.href = editUrl;
        }
    }
</script>
@endpush
