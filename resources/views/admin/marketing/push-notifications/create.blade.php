@extends('admin.layouts.app')

@section('title', 'Create Push Notification')

@section('content')
<!-- Page Header -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">Create Push Notification</h4>
    <a href="{{ route('admin.marketing.push-notifications.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i> Back to Notifications
    </a>
</div>

<form id="notificationForm" method="POST" action="{{ route('admin.marketing.push-notifications.store') }}" enctype="multipart/form-data">
    @csrf
    
    <div class="row">
        <div class="col-lg-8">
            <!-- Basic Info Card -->
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-white">
                    <h6 class="mb-0"><i class="bi bi-bell me-2"></i>Notification Details</h6>
                </div>
                <div class="card-body">
                    <!-- Title -->
                    <div class="mb-3">
                        <label for="title" class="form-label">Title <span class="text-danger">*</span></label>
                        <input type="text" id="title" name="title" class="form-control @error('title') is-invalid @enderror" 
                               value="{{ old('title') }}" required maxlength="255" placeholder="Enter notification title">
                        <div class="form-text">Maximum 255 characters</div>
                        @error('title')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Message -->
                    <div class="mb-3">
                        <label for="message" class="form-label">Message <span class="text-danger">*</span></label>
                        <textarea id="message" name="message" class="form-control @error('message') is-invalid @enderror" 
                                  rows="4" required maxlength="1000" placeholder="Enter notification message">{{ old('message') }}</textarea>
                        <div class="form-text">Maximum 1000 characters</div>
                        @error('message')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Image -->
                    <div class="mb-3">
                        <label for="image" class="form-label">Image (Optional)</label>
                        <input type="file" id="image" name="image" class="form-control @error('image') is-invalid @enderror" 
                               accept="image/jpeg,image/png,image/gif,image/webp" onchange="previewImage(this)">
                        <div class="form-text">Recommended size: 512x512 pixels. Max 2MB. Supported: JPEG, PNG, GIF, WebP</div>
                        @error('image')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div id="imagePreview" class="mt-2"></div>
                    </div>
                </div>
            </div>

            <!-- Target Card -->
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-white">
                    <h6 class="mb-0"><i class="bi bi-people me-2"></i>Target Audience</h6>
                </div>
                <div class="card-body">
                    <!-- Target Type -->
                    <div class="mb-3">
                        <label for="target_type" class="form-label">Send To</label>
                        <select id="target_type" name="target_type" class="form-select">
                            <option value="all" {{ old('target_type') == 'all' ? 'selected' : 'selected' }}>All Users</option>
                            <option value="specific_user" {{ old('target_type') == 'specific_user' ? 'selected' : '' }}>Specific User</option>
                            <option value="user_group" {{ old('target_type') == 'user_group' ? 'selected' : '' }}>User Group</option>
                            <option value="product" {{ old('target_type') == 'product' ? 'selected' : '' }}>Product Related</option>
                            <option value="category" {{ old('target_type') == 'category' ? 'selected' : '' }}>Category Related</option>
                        </select>
                        <div class="form-text">Select who should receive this notification</div>
                    </div>

                    <!-- Target ID (for specific user/product/category) -->
                    <div class="mb-3" id="targetIdSection" style="display: none;">
                        <label for="target_id" class="form-label" id="targetIdLabel">Select</label>
                        <select id="target_id" name="target_id" class="form-select">
                            <option value="">Select...</option>
                        </select>
                    </div>

                    <!-- Recipients Count Display -->
                    <div class="mb-3">
                        <div class="d-flex align-items-center">
                            <span class="text-muted me-2">Estimated Recipients:</span>
                            <span id="recipientsCount" class="fw-bold">0</span>
                            <span class="spinner-border spinner-border-sm ms-2" id="recipientsSpinner" style="display: none;"></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Action URL Card -->
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-white">
                    <h6 class="mb-0"><i class="bi bi-link-45deg me-2"></i>Action (Optional)</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="action_url" class="form-label">Redirect URL</label>
                        <input type="url" id="action_url" name="action_url" class="form-control @error('action_url') is-invalid @enderror" 
                               value="{{ old('action_url') }}" placeholder="https://example.com/product/123">
                        <div class="form-text">When users tap the notification, they will be redirected to this URL</div>
                        @error('action_url')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <!-- Schedule Card -->
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-white">
                    <h6 class="mb-0"><i class="bi bi-clock me-2"></i>Schedule</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">When to Send</label>
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="radio" name="schedule_type" id="scheduleNow" value="now" checked>
                            <label class="form-check-label" for="scheduleNow">
                                <i class="bi bi-send text-success me-1"></i> Send Now
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="schedule_type" id="scheduleLater" value="scheduled">
                            <label class="form-check-label" for="scheduleLater">
                                <i class="bi bi-calendar text-warning me-1"></i> Schedule for Later
                            </label>
                        </div>
                    </div>

                    <div class="mb-3" id="scheduledAtSection" style="display: none;">
                        <label for="scheduled_at" class="form-label">Schedule Date & Time</label>
                        <input type="datetime-local" id="scheduled_at" name="scheduled_at" class="form-control @error('scheduled_at') is-invalid @enderror" 
                               value="{{ old('scheduled_at') }}" min="{{ now()->format('Y-m-d\TH:i') }}">
                        @error('scheduled_at')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Preview Card -->
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-white">
                    <h6 class="mb-0"><i class="bi bi-phone me-2"></i>Preview</h6>
                </div>
                <div class="card-body">
                    <div class="bg-light rounded p-3">
                        <div class="d-flex align-items-start">
                            <div class="bg-primary rounded-circle p-2 me-2" style="width: 40px; height: 40px; flex-shrink: 0;">
                                <i class="bi bi-bell text-white"></i>
                            </div>
                            <div>
                                <div class="fw-bold" id="previewTitle">Notification Title</div>
                                <div class="text-muted small" id="previewMessage">Your notification message will appear here...</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tips Card -->
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-white">
                    <h6 class="mb-0"><i class="bi bi-lightbulb me-2"></i>Tips</h6>
                </div>
                <div class="card-body">
                    <ul class="mb-0 small text-muted">
                        <li class="mb-2">Keep the title short and catchy (max 50 characters)</li>
                        <li class="mb-2">Include a clear call-to-action in the message</li>
                        <li class="mb-2">Use an eye-catching image to increase engagement</li>
                        <li>Test with a small group first before sending to all users</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</form>

<!-- Floating Buttons -->
<div class="floating-save-container">
    <a href="{{ route('admin.marketing.push-notifications.index') }}" class="btn btn-secondary floating-reset-btn">
        <i class="bi bi-x-lg me-1"></i> Cancel
    </a>
    <button type="submit" form="notificationForm" class="btn btn-primary floating-save-btn">
        <i class="bi bi-check-lg me-1"></i> Create Notification
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
    // Image preview function
    function previewImage(input) {
        const preview = document.getElementById('imagePreview');
        preview.innerHTML = '';
        
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                preview.innerHTML = '<img src="' + e.target.result + '" class="img-thumbnail" style="max-width: 200px; max-height: 200px;">';
            };
            reader.readAsDataURL(input.files[0]);
        }
    }

    // Schedule type toggle
    const scheduleNow = document.getElementById('scheduleNow');
    const scheduleLater = document.getElementById('scheduleLater');
    const scheduledAtSection = document.getElementById('scheduledAtSection');

    scheduleNow.addEventListener('change', function() {
        scheduledAtSection.style.display = 'none';
    });

    scheduleLater.addEventListener('change', function() {
        scheduledAtSection.style.display = 'block';
    });

    // Preview update
    const titleInput = document.getElementById('title');
    const messageInput = document.getElementById('message');
    const previewTitle = document.getElementById('previewTitle');
    const previewMessage = document.getElementById('previewMessage');

    titleInput.addEventListener('input', function() {
        previewTitle.textContent = this.value || 'Notification Title';
    });

    messageInput.addEventListener('input', function() {
        previewMessage.textContent = this.value || 'Your notification message will appear here...';
    });

    // Target type change handler
    const targetType = document.getElementById('target_type');
    const targetIdSection = document.getElementById('targetIdSection');
    const targetIdLabel = document.getElementById('targetIdLabel');
    const targetIdSelect = document.getElementById('target_id');
    const recipientsCount = document.getElementById('recipientsCount');
    const recipientsSpinner = document.getElementById('recipientsSpinner');

    const products = @json($products);
    const categories = @json($categories);
    const users = @json($users);

    function updateTargetOptions(type) {
        targetIdSelect.innerHTML = '<option value="">Select...</option>';
        
        switch(type) {
            case 'specific_user':
                targetIdLabel.textContent = 'Select User';
                users.forEach(user => {
                    const option = document.createElement('option');
                    option.value = user.id;
                    option.textContent = user.name + ' (' + user.email + ')';
                    targetIdSelect.appendChild(option);
                });
                targetIdSection.style.display = 'block';
                break;
            case 'product':
                targetIdLabel.textContent = 'Select Product';
                products.forEach(product => {
                    const option = document.createElement('option');
                    option.value = product.id;
                    option.textContent = product.name + ' (' + product.sku + ')';
                    targetIdSelect.appendChild(option);
                });
                targetIdSection.style.display = 'block';
                break;
            case 'category':
                targetIdLabel.textContent = 'Select Category';
                categories.forEach(category => {
                    const option = document.createElement('option');
                    option.value = category.id;
                    option.textContent = category.name;
                    targetIdSelect.appendChild(option);
                });
                targetIdSection.style.display = 'block';
                break;
            case 'user_group':
                targetIdLabel.textContent = 'Select User Group';
                targetIdSection.style.display = 'block';
                break;
            default:
                targetIdSection.style.display = 'none';
        }
        
        updateRecipientsCount();
    }

    function updateRecipientsCount() {
        const type = targetType.value;
        
        if (type === 'all') {
            // Fetch all users count via AJAX
            recipientsSpinner.style.display = 'inline-block';
            fetch('{{ route("admin.marketing.push-notifications.recipient-count") }}?target_type=all', {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            })
            .then(res => res.json())
            .then(data => {
                recipientsCount.textContent = data.count;
                recipientsSpinner.style.display = 'none';
            })
            .catch(() => {
                recipientsCount.textContent = '0';
                recipientsSpinner.style.display = 'none';
            });
        } else if (type === 'specific_user' && targetIdSelect.value) {
            recipientsCount.textContent = '1';
        } else {
            recipientsCount.textContent = '0';
        }
    }

    targetType.addEventListener('change', function() {
        updateTargetOptions(this.value);
    });

    targetIdSelect.addEventListener('change', function() {
        updateRecipientsCount();
    });

    // Initialize on page load
    updateTargetOptions(targetType.value);
</script>
@endpush
