<div class="row">
    <div class="col-lg-8">
        <!-- Webhooks List -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="bi bi-plug me-2"></i>Webhooks</h5>
            </div>
            <div class="card-body p-0">
                @if($webhooks->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Name</th>
                                <th>Event</th>
                                <th>URL</th>
                                <th>Status</th>
                                <th>Success Rate</th>
                                <th style="width: 150px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($webhooks as $webhook)
                            <tr>
                                <td>
                                    <div class="fw-medium">{{ $webhook->name }}</div>
                                    <small class="text-muted">{{ $webhook->method }}</small>
                                </td>
                                <td>
                                    <code class="small">{{ $webhook->event }}</code>
                                </td>
                                <td>
                                    <small class="text-truncate d-inline-block" style="max-width: 200px;" title="{{ $webhook->url }}">
                                        {{ $webhook->url }}
                                    </small>
                                </td>
                                <td>
                                    @if($webhook->is_active)
                                        <span class="badge bg-success">Active</span>
                                    @else
                                        <span class="badge bg-secondary">Inactive</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="progress flex-grow-1 me-2" style="height: 6px;">
                                            <div class="progress-bar bg-{{ $webhook->success_rate >= 80 ? 'success' : ($webhook->success_rate >= 50 ? 'warning' : 'danger') }}" 
                                                 style="width: {{ $webhook->success_rate }}%"></div>
                                        </div>
                                        <small class="text-muted">{{ $webhook->success_rate }}%</small>
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex gap-1">
                                        <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editWebhookModal{{ $webhook->id }}">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <form action="{{ route('admin.api-keys.webhooks.test', $webhook->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-outline-info" title="Test Webhook">
                                                <i class="bi bi-play"></i>
                                            </button>
                                        </form>
                                        <form action="{{ route('admin.api-keys.webhooks.toggle', $webhook->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-outline-{{ $webhook->is_active ? 'danger' : 'success' }}" title="{{ $webhook->is_active ? 'Disable' : 'Enable' }}">
                                                <i class="bi bi-{{ $webhook->is_active ? 'x-circle' : 'check-circle' }}"></i>
                                            </button>
                                        </form>
                                        <form action="{{ route('admin.api-keys.webhooks.destroy', $webhook->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete" onclick="return confirm('Are you sure you want to delete this webhook?')">
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
                    <i class="bi bi-plug text-muted" style="font-size: 3rem;"></i>
                    <p class="text-muted mb-2 mt-2">No webhooks found</p>
                    <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#createWebhookModal">
                        <i class="bi bi-plus-lg me-1"></i> Create First Webhook
                    </button>
                </div>
                @endif
            </div>
            @if($webhooks->hasPages())
            <div class="card-footer bg-white">
                {{ $webhooks->appends(request()->query())->links() }}
            </div>
            @endif
        </div>
    </div>
    
    <div class="col-lg-4">
        <!-- Create New Button -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body text-center py-4">
                <i class="bi bi-plus-circle text-primary" style="font-size: 2.5rem;"></i>
                <h5 class="mt-3">Add New Webhook</h5>
                <p class="text-muted small mb-3">Create webhook endpoints to receive real-time notifications about events in your store.</p>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createWebhookModal">
                    <i class="bi bi-plus-lg me-1"></i> Create Webhook
                </button>
            </div>
        </div>
        
        <!-- Available Events -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white py-3">
                <h6 class="mb-0"><i class="bi bi-lightning me-2"></i>Available Events</h6>
            </div>
            <div class="card-body p-0">
                <div class="list-group list-group-flush">
                    @foreach($events as $event => $label)
                    <div class="list-group-item py-2">
                        <code class="small">{{ $event }}</code>
                    </div>
                    @endforeach
                </div>
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
                        Webhooks send HTTP requests to your endpoint
                    </li>
                    <li class="mb-2">
                        <i class="bi bi-check-circle text-success me-2"></i>
                        Test webhooks before enabling
                    </li>
                    <li class="mb-2">
                        <i class="bi bi-check-circle text-success me-2"></i>
                        Configure retry logic for failed deliveries
                    </li>
                    <li class="mb-0">
                        <i class="bi bi-check-circle text-success me-2"></i>
                        Use secrets to verify webhook authenticity
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>

<!-- Create Webhook Modal -->
<div class="modal fade" id="createWebhookModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-plus-lg me-2"></i>Create New Webhook</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.api-keys.webhooks.store') }}" method="POST" id="createWebhookForm">
                @csrf
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Name <span class="text-danger">*</span></label>
                                <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" placeholder="My Webhook" value="{{ old('name') }}" required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Event <span class="text-danger">*</span></label>
                                <select name="event" class="form-select @error('event') is-invalid @enderror" required>
                                    @foreach($events as $value => $label)
                                        <option value="{{ $value }}" {{ old('event') == $value ? 'selected' : '' }}>{{ $label }} ({{ $value }})</option>
                                    @endforeach
                                </select>
                                @error('event')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Endpoint URL <span class="text-danger">*</span></label>
                        <input type="url" name="url" class="form-control @error('url') is-invalid @enderror" placeholder="https://your-server.com/webhook" value="{{ old('url') }}" required>
                        @error('url')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text">The URL that will receive the webhook POST requests</div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">HTTP Method</label>
                                <select name="method" class="form-select @error('method') is-invalid @enderror">
                                    @foreach(['POST', 'GET', 'PUT', 'PATCH'] as $method)
                                        <option value="{{ $method }}" {{ old('method', 'POST') == $method ? 'selected' : '' }}>{{ $method }}</option>
                                    @endforeach
                                </select>
                                @error('method')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Timeout (seconds)</label>
                                <input type="number" name="timeout" class="form-control @error('timeout') is-invalid @enderror" value="{{ old('timeout', 30) }}" min="5" max="300">
                                @error('timeout')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Retry Count</label>
                                <input type="number" name="retry_count" class="form-control @error('retry_count') is-invalid @enderror" value="{{ old('retry_count', 3) }}" min="0" max="10">
                                @error('retry_count')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">Number of retries on failure</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Secret (Optional)</label>
                                <input type="text" name="secret" class="form-control @error('secret') is-invalid @enderror" value="{{ old('secret') }}" placeholder="Your webhook secret">
                                @error('secret')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">Used to verify webhook authenticity</div>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="is_active" id="createWebhookActive" value="1" {{ old('is_active', true) ? 'checked' : '' }}>
                            <label class="form-check-label" for="createWebhookActive">Active</label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-lg me-1"></i> Create Webhook
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Webhook Modals -->
@foreach($webhooks as $webhook)
<div class="modal fade" id="editWebhookModal{{ $webhook->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-pencil me-2"></i>Edit Webhook</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.api-keys.webhooks.update', $webhook->id) }}" method="POST" id="editWebhookForm{{ $webhook->id }}">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    @php
                        $isEditingWebhook = old('_editing_webhook_id') == $webhook->id;
                    @endphp
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Name <span class="text-danger">*</span></label>
                                <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ $isEditingWebhook ? old('name') : $webhook->name }}" required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Event <span class="text-danger">*</span></label>
                                <select name="event" class="form-select @error('event') is-invalid @enderror" required>
                                    @foreach($events as $value => $label)
                                        <option value="{{ $value }}" {{ ($isEditingWebhook ? old('event') : $webhook->event) === $value ? 'selected' : '' }}>{{ $label }} ({{ $value }})</option>
                                    @endforeach
                                </select>
                                @error('event')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Endpoint URL <span class="text-danger">*</span></label>
                        <input type="url" name="url" class="form-control @error('url') is-invalid @enderror" value="{{ $isEditingWebhook ? old('url') : $webhook->url }}" required>
                        @error('url')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">HTTP Method</label>
                                <select name="method" class="form-select @error('method') is-invalid @enderror">
                                    @foreach(['POST', 'GET', 'PUT', 'PATCH'] as $method)
                                        <option value="{{ $method }}" {{ ($isEditingWebhook ? old('method') : $webhook->method) === $method ? 'selected' : '' }}>{{ $method }}</option>
                                    @endforeach
                                </select>
                                @error('method')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Timeout (seconds)</label>
                                <input type="number" name="timeout" class="form-control @error('timeout') is-invalid @enderror" value="{{ $isEditingWebhook ? old('timeout') : $webhook->timeout }}" min="5" max="300">
                                @error('timeout')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Retry Count</label>
                                <input type="number" name="retry_count" class="form-control @error('retry_count') is-invalid @enderror" value="{{ $isEditingWebhook ? old('retry_count') : $webhook->retry_count }}" min="0" max="10">
                                @error('retry_count')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Secret (Optional)</label>
                                <input type="text" name="secret" class="form-control @error('secret') is-invalid @enderror" value="{{ $isEditingWebhook ? old('secret') : $webhook->secret }}">
                                @error('secret')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="is_active" id="editWebhookActive{{ $webhook->id }}" value="1" {{ ($isEditingWebhook ? old('is_active') : $webhook->is_active) ? 'checked' : '' }}>
                            <label class="form-check-label" for="editWebhookActive{{ $webhook->id }}">Active</label>
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
