@extends('admin.layouts.app')

@section('title', 'Bulk SMS')

@section('content')
<!-- Statistics Cards -->
<div class="stat-card-row mb-4" id="statsCards">
    <!-- Card 1 -->
    <div class="stat-card stat-card-primary">
        <div class="stat-card-icon"><i class="bi bi-people"></i></div>
        <div class="stat-card-content">
            <span class="stat-card-label">Customers with Phone</span>
            <span class="stat-card-value">{{ number_format($stats['total_customers'] ?? 0) }}</span>
        </div>
    </div>
    <!-- Card 2 -->
    <div class="stat-card stat-card-success">
        <div class="stat-card-icon"><i class="bi bi-bell"></i></div>
        <div class="stat-card-content">
            <span class="stat-card-label">Active Subscribers</span>
            <span class="stat-card-value">{{ number_format($stats['total_subscribers'] ?? 0) }}</span>
        </div>
    </div>
    <!-- Card 3 -->
    <div class="stat-card stat-card-info">
        <div class="stat-card-icon"><i class="bi bi-send"></i></div>
        <div class="stat-card-content">
            <span class="stat-card-label">Total Sent</span>
            <span class="stat-card-value">{{ number_format($stats['total_sent'] ?? 0) }}</span>
        </div>
    </div>
    <!-- Card 4 -->
    <div class="stat-card stat-card-warning">
        <div class="stat-card-icon"><i class="bi bi-check-circle"></i></div>
        <div class="stat-card-content">
            <span class="stat-card-label">Successful</span>
            <span class="stat-card-value">{{ number_format($stats['successful'] ?? 0) }}</span>
        </div>
    </div>
</div>

<!-- Header -->
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0">Bulk SMS</h4>
</div>

<!-- Filters Card -->
<div class="card border-0 shadow-sm mb-3">
    <div class="card-body py-3">
        <form method="GET" id="filterForm">
            <div class="row g-2 align-items-end">
                <!-- Search Input -->
                <div class="col-lg-3 col-md-4 col-sm-6">
                    <label class="form-label small text-muted">Search</label>
                    <div class="input-group input-group-sm">
                        <span class="input-group-text"><i class="bi bi-search"></i></span>
                        <input type="text" name="search" id="liveSearch" class="form-control" 
                               placeholder="Search messages..." value="{{ request('search') }}">
                    </div>
                </div>
                
                <!-- Status Filter -->
                <div class="col-lg-2 col-md-3 col-sm-6">
                    <label class="form-label small text-muted">Status</label>
                    <select name="status" id="filterStatus" class="form-select form-select-sm">
                        <option value="">All Status</option>
                        <option value="sent" {{ request('status') == 'sent' ? 'selected' : '' }}>Sent</option>
                        <option value="partial" {{ request('status') == 'partial' ? 'selected' : '' }}>Partial</option>
                        <option value="failed" {{ request('status') == 'failed' ? 'selected' : '' }}>Failed</option>
                    </select>
                </div>
                
                <!-- Reset Button -->
                <div class="col-lg-2 col-md-4 col-sm-8">
                    <a href="{{ route('admin.marketing.bulk-sms.index') }}" class="btn btn-sm btn-outline-secondary">
                        <i class="bi bi-x-lg me-1"></i> Reset
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Send SMS Form -->
<div class="row mb-4">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white">
                <h6 class="mb-0"><i class="bi bi-send me-2"></i>Send New SMS</h6>
            </div>
            <div class="card-body">
                <form id="smsForm" method="POST" action="{{ route('admin.marketing.bulk-sms.send') }}">
                    @csrf
                    
                    <!-- Recipients Type -->
                    <div class="mb-3">
                        <label for="recipientsType" class="form-label">Recipients <span class="text-danger">*</span></label>
                        <select name="recipients_type" id="recipientsType" class="form-select">
                            <option value="">Select recipients...</option>
                            <option value="all_customers">All Customers (with phone)</option>
                            <option value="subscribers">SMS Subscribers</option>
                            <option value="registered_users">Registered Users Only</option>
                            <option value="specific_numbers">Specific Phone Numbers</option>
                        </select>
                        @error('recipients_type')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Specific Numbers (shown when selected) -->
                    <div class="mb-3" id="specificNumbersField" style="display: none;">
                        <label for="specificNumbers" class="form-label">Phone Numbers <span class="text-danger">*</span></label>
                        <textarea name="specific_numbers" id="specificNumbers" class="form-control" rows="3" placeholder="Enter phone numbers separated by commas&#10;Example: 01712345678, 01987654321, 01812345678"></textarea>
                        <div class="form-text">Enter multiple phone numbers separated by commas</div>
                        @error('specific_numbers')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Recipient Count Display -->
                    <div class="mb-3" id="recipientCountDisplay">
                        <div class="alert alert-info mb-0 py-2">
                            <i class="bi bi-info-circle me-2"></i>
                            <span id="recipientCountText">Select recipients to see count</span>
                        </div>
                    </div>

                    <!-- Message -->
                    <div class="mb-3">
                        <label for="message" class="form-label">Message <span class="text-danger">*</span></label>
                        <textarea name="message" id="message" class="form-control @error('message') is-invalid @enderror" rows="5" placeholder="Enter your message..." maxlength="1600" oninput="updateCharCount()"></textarea>
                        <div class="d-flex justify-content-between">
                            <div class="form-text">SMS will be sent as plain text</div>
                            <div class="form-text"><span id="charCount">0</span>/1600 characters</div>
                        </div>
                        @error('message')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Quick Messages -->
                    <div class="mb-3">
                        <label class="form-label">Quick Messages</label>
                        <div class="d-flex flex-wrap gap-2">
                            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="insertQuickMessage('Hello! Thank you for shopping with us.')">
                                Welcome
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="insertQuickMessage('Your order has been shipped! Track it here:')">
                                Order Shipped
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="insertQuickMessage('Flash Sale! Get 50% off on all items today only. Shop now!')">
                                Flash Sale
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="insertQuickMessage('Your OTP code is: ')">
                                OTP Template
                            </button>
                        </div>
                    </div>

                    <!-- Preview -->
                    <div class="mb-3">
                        <label class="form-label">Message Preview</label>
                        <div class="bg-light p-3 rounded" id="messagePreview" style="min-height: 60px; font-family: monospace;">
                            <span class="text-muted">Your message preview will appear here...</span>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Sidebar Tips -->
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white">
                <h6 class="mb-0"><i class="bi bi-lightbulb me-2"></i>Tips</h6>
            </div>
            <div class="card-body">
                <ul class="list-unstyled mb-0 small">
                    <li class="mb-2"><i class="bi bi-check-circle text-success me-2"></i>Keep messages under 160 characters for single SMS</li>
                    <li class="mb-2"><i class="bi bi-check-circle text-success me-2"></i>Longer messages will be split into multiple SMS</li>
                    <li class="mb-2"><i class="bi bi-check-circle text-success me-2"></i>Use +88 before phone numbers for international format</li>
                    <li class="mb-0"><i class="bi bi-check-circle text-success me-2"></i>Remove any spaces from phone numbers</li>
                </ul>
            </div>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white">
                <h6 class="mb-0"><i class="bi bi-gear me-2"></i>SMS Gateway</h6>
            </div>
            <div class="card-body">
                <p class="text-muted small mb-2">Configure your SMS gateway to enable actual SMS delivery.</p>
                <a href="{{ route('admin.otp.credentials') }}" class="btn btn-sm btn-outline-primary">
                    <i class="bi bi-sliders me-1"></i> SMS Gateway Settings
                </a>
            </div>
        </div>
    </div>
</div>

<!-- SMS History -->
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white">
        <h6 class="mb-0"><i class="bi bi-clock-history me-2"></i>SMS History</h6>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Message</th>
                        <th>Recipients</th>
                        <th>Status</th>
                        <th>Sent Date</th>
                        <th style="width: 100px;">Actions</th>
                    </tr>
                </thead>
                <tbody id="tableBody">
                    @forelse($smsHistory as $sms)
                        @php
                            $search = request('search');
                            $isMatch = $search && stripos($sms['message'], $search) !== false;
                        @endphp
                        <tr class="{{ $isMatch ? 'table-warning' : '' }}">
                            <td>
                                <div class="text-truncate" style="max-width: 300px;" title="{{ $sms['message'] }}">
                                    {{ Str::limit($sms['message'], 50) }}
                                </div>
                            </td>
                            <td>
                                <div>{{ $sms['success_count'] }}/{{ $sms['total_recipients'] }}</div>
                                <small class="text-muted">
                                    @switch($sms['recipients_type'])
                                        @case('all_customers')
                                            All Customers
                                            @break
                                        @case('subscribers')
                                            Subscribers
                                            @break
                                        @case('registered_users')
                                            Registered Users
                                            @break
                                        @case('specific_numbers')
                                            Specific Numbers
                                            @break
                                        @default
                                            {{ $sms['recipients_type'] }}
                                    @endswitch
                                </small>
                            </td>
                            <td>
                                @switch($sms['status'])
                                    @case('sent')
                                        <span class="badge bg-success"><i class="bi bi-check-circle me-1"></i>Sent</span>
                                        @break
                                    @case('partial')
                                        <span class="badge bg-warning"><i class="bi bi-exclamation-circle me-1"></i>Partial</span>
                                        @break
                                    @case('failed')
                                        <span class="badge bg-danger"><i class="bi bi-x-circle me-1"></i>Failed</span>
                                        @break
                                    @default
                                        <span class="badge bg-secondary">{{ $sms['status'] }}</span>
                                @endswitch
                            </td>
                            <td>
                                <div>{{ \Carbon\Carbon::parse($sms['created_at'])->format('M d, Y') }}</div>
                                <small class="text-muted">{{ \Carbon\Carbon::parse($sms['created_at'])->format('h:i A') }}</small>
                            </td>
                            <td>
                                <button type="button" class="btn btn-sm btn-outline-info" onclick="viewSms('{{ $sms['id'] }}', {{ json_encode($sms['message']) }}, {{ $sms['total_recipients'] }}, {{ $sms['success_count'] }}, {{ $sms['failed_count'] }})">
                                    <i class="bi bi-eye"></i>
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-danger" onclick="deleteSms('{{ $sms['id'] }}')">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-5">
                                <i class="bi bi-phone text-muted" style="font-size: 3rem;"></i>
                                <p class="text-muted mb-2 mt-2">No SMS campaigns found</p>
                                <p class="text-muted small">Send your first SMS using the form above</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if($smsHistory->hasPages())
            <div class="card-footer bg-white d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div class="text-muted small">
                    Showing {{ $smsHistory->firstItem() }} - {{ $smsHistory->lastItem() }} of {{ $smsHistory->total() }} campaigns
                </div>
                <div>
                    {{ $smsHistory->appends(request()->query())->links() }}
                </div>
            </div>
        @endif
    </div>
</div>

<!-- Floating Save Button Container -->
<div class="floating-save-container">
    <button type="button" class="btn btn-secondary floating-reset-btn" onclick="resetForm()">
        <i class="bi bi-x-lg me-1"></i> Reset
    </button>
    <button type="submit" form="smsForm" class="btn btn-primary floating-save-btn" id="sendBtn" disabled>
        <i class="bi bi-send me-1"></i> Send SMS
    </button>
</div>

<!-- SMS Detail Modal -->
<div class="modal fade" id="smsDetailModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">SMS Campaign Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Message</label>
                    <div class="bg-light p-3 rounded" id="detailMessage"></div>
                </div>
                <div class="row">
                    <div class="col-6 mb-3">
                        <label class="form-label">Total Recipients</label>
                        <div class="h5 mb-0" id="detailTotal"></div>
                    </div>
                    <div class="col-6 mb-3">
                        <label class="form-label">Status</label>
                        <div id="detailStatus"></div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-6">
                        <label class="form-label">Successful</label>
                        <div class="h5 mb-0 text-success" id="detailSuccess"></div>
                    </div>
                    <div class="col-6">
                        <label class="form-label">Failed</label>
                        <div class="h5 mb-0 text-danger" id="detailFailed"></div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    /* Add padding at bottom to prevent floating button overlap */
    .content-area {
        padding-bottom: 100px !important;
    }
    
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

@push('scripts')
<script>
    // Auto-scroll to first validation error on page load
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

    // Toggle specific numbers field
    document.getElementById('recipientsType').addEventListener('change', function() {
        const specificNumbersField = document.getElementById('specificNumbersField');
        const recipientCountDisplay = document.getElementById('recipientCountDisplay');
        
        if (this.value === 'specific_numbers') {
            specificNumbersField.style.display = 'block';
            recipientCountDisplay.style.display = 'none';
        } else {
            specificNumbersField.style.display = 'none';
            recipientCountDisplay.style.display = 'block';
        }
        
        updateSendButton();
    });

    // Update character count
    function updateCharCount() {
        const message = document.getElementById('message').value;
        document.getElementById('charCount').textContent = message.length;
        
        const preview = document.getElementById('messagePreview');
        if (message.trim()) {
            preview.innerHTML = message.replace(/\n/g, '<br>');
        } else {
            preview.innerHTML = '<span class="text-muted">Your message preview will appear here...</span>';
        }
        
        updateSendButton();
    }

    // Update recipient count
    function updateRecipientCount() {
        const type = document.getElementById('recipientsType').value;
        
        if (!type) {
            document.getElementById('recipientCountText').textContent = 'Select recipients to see count';
            document.getElementById('sendBtn').disabled = true;
            return;
        }
        
        if (type === 'specific_numbers') {
            const numbers = document.getElementById('specificNumbers').value;
            const count = numbers ? numbers.split(',').filter(n => n.trim()).length : 0;
            document.getElementById('recipientCountText').textContent = count + ' recipient(s)';
            document.getElementById('sendBtn').disabled = count === 0;
            return;
        }
        
        // Fetch count from server
        fetch('{{ route("admin.marketing.bulk-sms.recipient-count") }}?type=' + type)
            .then(response => response.json())
            .then(data => {
                document.getElementById('recipientCountText').textContent = data.count + ' recipient(s)';
                document.getElementById('sendBtn').disabled = data.count === 0;
            })
            .catch(error => {
                console.error('Error:', error);
                document.getElementById('recipientCountText').textContent = 'Error getting count';
            });
    }

    // Update send button state
    function updateSendButton() {
        const type = document.getElementById('recipientsType').value;
        const message = document.getElementById('message').value.trim();
        
        let hasRecipients = false;
        
        if (type === 'specific_numbers') {
            const numbers = document.getElementById('specificNumbers').value;
            hasRecipients = numbers && numbers.split(',').filter(n => n.trim()).length > 0;
        } else if (type) {
            const countText = document.getElementById('recipientCountText').textContent;
            const match = countText.match(/(\d+)/);
            hasRecipients = match && parseInt(match[1]) > 0;
        }
        
        document.getElementById('sendBtn').disabled = !type || !message || !hasRecipients;
    }

    // Insert quick message
    function insertQuickMessage(text) {
        document.getElementById('message').value = text;
        updateCharCount();
    }

    // Reset form
    function resetForm() {
        document.getElementById('smsForm').reset();
        document.getElementById('specificNumbersField').style.display = 'none';
        document.getElementById('recipientCountText').textContent = 'Select recipients to see count';
        document.getElementById('charCount').textContent = '0';
        document.getElementById('messagePreview').innerHTML = '<span class="text-muted">Your message preview will appear here...</span>';
        document.getElementById('sendBtn').disabled = true;
    }

    // View SMS details
    function viewSms(id, message, total, success, failed) {
        document.getElementById('detailMessage').textContent = message;
        document.getElementById('detailTotal').textContent = total;
        
        let statusHtml = '';
        if (failed === 0) {
            statusHtml = '<span class="badge bg-success">Sent</span>';
        } else if (success > 0) {
            statusHtml = '<span class="badge bg-warning">Partial</span>';
        } else {
            statusHtml = '<span class="badge bg-danger">Failed</span>';
        }
        document.getElementById('detailStatus').innerHTML = statusHtml;
        
        document.getElementById('detailSuccess').textContent = success;
        document.getElementById('detailFailed').textContent = failed;
        
        new bootstrap.Modal(document.getElementById('smsDetailModal')).show();
    }

    // Delete SMS campaign
    function deleteSms(id) {
        if (!confirm('Are you sure you want to delete this SMS campaign?')) {
            return;
        }
        
        const url = '{{ route("admin.marketing.bulk-sms.destroy", ["id" => "ID_PLACEHOLDER"]) }}'.replace('ID_PLACEHOLDER', id);
        
        fetch(url, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Error deleting SMS campaign');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error deleting SMS campaign');
        });
    }

    // Update count when specific numbers change
    document.getElementById('specificNumbers').addEventListener('input', updateRecipientCount);

    // Live search for SMS history
    let searchTimeout;
    const searchInput = document.getElementById('liveSearch');
    const filterStatus = document.getElementById('filterStatus');

    if (searchInput) {
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                submitFilterForm();
            }, 300);
        });
    }

    if (filterStatus) {
        filterStatus.addEventListener('change', function() {
            submitFilterForm();
        });
    }

    function submitFilterForm() {
        const params = new URLSearchParams();
        const search = searchInput.value.trim();
        const status = filterStatus.value;
        
        if (search) params.set('search', search);
        if (status) params.set('status', status);
        
        const queryString = params.toString();
        const url = '{{ route("admin.marketing.bulk-sms.index") }}' + (queryString ? '?' + queryString : '');
        window.location.href = url;
    }
</script>
@endpush
