@extends('admin.layouts.app')

@section('title', 'Create Quotation')

@section('content')
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1">Create Quotation</h4>
            <p class="text-muted mb-0">Create a new quotation for customer</p>
        </div>
        <a href="{{ route('admin.quotations.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> Back to Quotations
        </a>
    </div>

    <form id="quotationForm" method="POST" action="{{ route('admin.quotations.store') }}">
        @csrf
        
        <div class="row">
            <!-- Main Content -->
            <div class="col-lg-8">
                <!-- Customer Information -->
                <div class="card border-0 shadow-sm mb-3">
                    <div class="card-header bg-white">
                        <h6 class="mb-0"><i class="bi bi-person me-2"></i>Customer Information</h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label class="form-label">Select Existing Customer <span class="text-muted">(Optional)</span></label>
                                <select class="form-select" id="customerSelect" onchange="fillCustomerDetails()">
                                    <option value="">-- New Customer --</option>
                                    @foreach($customers as $customer)
                                    <option value="{{ $customer->id }}" 
                                            data-name="{{ $customer->name }}"
                                            data-email="{{ $customer->email }}"
                                            data-phone="{{ $customer->phone }}">
                                        {{ $customer->name }} ({{ $customer->email }})
                                    </option>
                                    @endforeach
                                </select>
                                <input type="hidden" name="user_id" id="userId">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Customer Name <span class="text-danger">*</span></label>
                                <input type="text" name="customer_name" class="form-control @error('customer_name') is-invalid @enderror" 
                                       value="{{ old('customer_name') }}" required>
                                @error('customer_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Email</label>
                                <input type="email" name="customer_email" class="form-control @error('customer_email') is-invalid @enderror" 
                                       value="{{ old('customer_email') }}">
                                @error('customer_email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Phone</label>
                                <input type="text" name="customer_phone" class="form-control @error('customer_phone') is-invalid @enderror" 
                                       value="{{ old('customer_phone') }}">
                                @error('customer_phone')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Valid Until <span class="text-danger">*</span></label>
                                <input type="date" name="valid_until" class="form-control @error('valid_until') is-invalid @enderror" 
                                       value="{{ old('valid_until', $defaultValidUntil) }}" required>
                                @error('valid_until')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Address</label>
                            <textarea name="customer_address" class="form-control @error('customer_address') is-invalid @enderror" rows="2">{{ old('customer_address') }}</textarea>
                            @error('customer_address')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="row">
                            <div class="col-md-3 mb-3">
                                <label class="form-label">City</label>
                                <input type="text" name="customer_city" class="form-control @error('customer_city') is-invalid @enderror" 
                                       value="{{ old('customer_city') }}">
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label">State</label>
                                <input type="text" name="customer_state" class="form-control @error('customer_state') is-invalid @enderror" 
                                       value="{{ old('customer_state') }}">
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label">Postcode</label>
                                <input type="text" name="customer_postcode" class="form-control @error('customer_postcode') is-invalid @enderror" 
                                       value="{{ old('customer_postcode') }}">
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label">Country</label>
                                <input type="text" name="customer_country" class="form-control @error('customer_country') is-invalid @enderror" 
                                       value="{{ old('customer_country') }}">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quotation Items -->
                <div class="card border-0 shadow-sm mb-3">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center">
                        <h6 class="mb-0"><i class="bi bi-box me-2"></i>Quotation Items</h6>
                        <button type="button" class="btn btn-sm btn-outline-primary" onclick="addItemRow()">
                            <i class="bi bi-plus-lg me-1"></i> Add Item
                        </button>
                    </div>
                    <div class="card-body">
                        <div id="itemsContainer">
                            <!-- Items will be added here -->
                        </div>
                        @error('items')
                            <div class="alert alert-danger mt-2">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <!-- Notes & Terms -->
                <div class="card border-0 shadow-sm mb-3">
                    <div class="card-header bg-white">
                        <h6 class="mb-0"><i class="bi bi-file-text me-2"></i>Notes & Terms</h6>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label">Notes</label>
                            <textarea name="notes" class="form-control @error('notes') is-invalid @enderror" rows="3" placeholder="Any additional notes for the customer...">{{ old('notes') }}</textarea>
                            @error('notes')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Terms & Conditions</label>
                            <textarea name="terms_conditions" class="form-control @error('terms_conditions') is-invalid @enderror" rows="3" placeholder="Payment terms, delivery conditions, etc...">{{ old('terms_conditions') }}</textarea>
                            @error('terms_conditions')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="col-lg-4">
                <!-- Summary -->
                <div class="card border-0 shadow-sm mb-3">
                    <div class="card-header bg-white">
                        <h6 class="mb-0"><i class="bi bi-calculator me-2"></i>Summary</h6>
                    </div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Subtotal:</span>
                            <span class="fw-medium" id="summarySubtotal">0.00</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Tax:</span>
                            <span class="fw-medium" id="summaryTax">0.00</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Discount:</span>
                            <span class="fw-medium" id="summaryDiscount">0.00</span>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between">
                            <span class="fw-bold">Total:</span>
                            <span class="fw-bold text-primary" id="summaryTotal">0.00</span>
                        </div>
                    </div>
                </div>

                <!-- Help -->
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white">
                        <h6 class="mb-0"><i class="bi bi-info-circle me-2"></i>Help</h6>
                    </div>
                    <div class="card-body">
                        <div class="form-text">
                            <ul class="mb-0 ps-3">
                                <li>Select products from the dropdown or enter custom items</li>
                                <li>Quotations are valid until the specified date</li>
                                <li>Customers can accept quotations to proceed with orders</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>

<!-- Floating Save Button -->
<div class="floating-save-container">
    <a href="{{ route('admin.quotations.index') }}" class="btn btn-secondary floating-reset-btn">
        <i class="bi bi-x-lg me-1"></i> Cancel
    </a>
    <button type="submit" form="quotationForm" class="btn btn-primary floating-save-btn">
        <i class="bi bi-check-lg me-1"></i> Create Quotation
    </button>
</div>
@endsection

@push('styles')
<style>
    /* Add padding at bottom to prevent floating button overlap */
    .content-area {
        padding-bottom: 100px !important;
    }
    .item-row {
        background: #f8f9fa;
        border-radius: 8px;
        padding: 15px;
        margin-bottom: 15px;
    }
    .item-row:last-child {
        margin-bottom: 0;
    }
</style>
@endpush

@push('scripts')
<script>
    let itemCounter = 0;
    const products = @json($products);

    function fillCustomerDetails() {
        const select = document.getElementById('customerSelect');
        const option = select.options[select.selectedIndex];
        
        if (option.value) {
            document.getElementById('userId').value = option.value;
            document.querySelector('[name="customer_name"]').value = option.dataset.name || '';
            document.querySelector('[name="customer_email"]').value = option.dataset.email || '';
            document.querySelector('[name="customer_phone"]').value = option.dataset.phone || '';
        } else {
            document.getElementById('userId').value = '';
            document.querySelector('[name="customer_name"]').value = '';
            document.querySelector('[name="customer_email"]').value = '';
            document.querySelector('[name="customer_phone"]').value = '';
        }
    }

    function addItemRow(product = null) {
        itemCounter++;
        const container = document.getElementById('itemsContainer');
        
        const row = document.createElement('div');
        row.className = 'item-row';
        row.id = `itemRow${itemCounter}`;
        
        row.innerHTML = `
            <div class="row align-items-end">
                <div class="col-md-4 mb-2">
                    <label class="form-label small">Product</label>
                    <select class="form-select form-select-sm product-select" onchange="onProductChange(${itemCounter})" data-row="${itemCounter}">
                        <option value="">-- Custom Item --</option>
                        ${products.map(p => `<option value="${p.id}" data-price="${p.price}" data-name="${p.name}">${p.name} (${p.sku || 'No SKU'})</option>`).join('')}
                    </select>
                    <input type="hidden" name="items[${itemCounter}][product_id]" id="productId${itemCounter}">
                </div>
                <div class="col-md-4 mb-2">
                    <label class="form-label small">Item Name <span class="text-danger">*</span></label>
                    <input type="text" name="items[${itemCounter}][product_name]" class="form-control form-control-sm item-name" 
                           id="itemName${itemCounter}" required value="${product ? product.name : ''}">
                </div>
                <div class="col-md-2 mb-2">
                    <label class="form-label small">Quantity <span class="text-danger">*</span></label>
                    <input type="number" name="items[${itemCounter}][quantity]" class="form-control form-control-sm item-qty" 
                           id="itemQty${itemCounter}" value="1" min="1" required onchange="calculateRow(${itemCounter})">
                </div>
                <div class="col-md-2 mb-2">
                    <label class="form-label small">Unit Price <span class="text-danger">*</span></label>
                    <input type="number" name="items[${itemCounter}][unit_price]" class="form-control form-control-sm item-price" 
                           id="itemPrice${itemCounter}" value="${product ? product.price : '0'}" min="0" step="0.01" required onchange="calculateRow(${itemCounter})">
                </div>
                <div class="col-md-10 mb-2">
                    <label class="form-label small">Description</label>
                    <input type="text" name="items[${itemCounter}][description]" class="form-control form-control-sm" 
                           placeholder="Optional description">
                </div>
                <div class="col-md-2 mb-2 text-end">
                    <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeItemRow(${itemCounter})">
                        <i class="bi bi-trash"></i>
                    </button>
                </div>
            </div>
            <div class="row">
                <div class="col-12 text-end">
                    <small class="text-muted">Total: <span class="fw-medium row-total" id="rowTotal${itemCounter}">0.00</span></small>
                </div>
            </div>
        `;
        
        container.appendChild(row);
        calculateRow(itemCounter);
        
        if (product) {
            const select = row.querySelector('.product-select');
            select.value = product.id;
            document.getElementById(`productId${itemCounter}`).value = product.id;
        }
    }

    function removeItemRow(id) {
        const row = document.getElementById(`itemRow${id}`);
        if (row) {
            row.remove();
            calculateTotals();
        }
    }

    function onProductChange(rowId) {
        const select = document.querySelector(`#itemRow${rowId} .product-select`);
        const option = select.options[select.selectedIndex];
        
        if (option.value) {
            document.getElementById(`productId${rowId}`).value = option.value;
            document.getElementById(`itemName${rowId}`).value = option.dataset.name;
            document.getElementById(`itemPrice${rowId}`).value = option.dataset.price;
        } else {
            document.getElementById(`productId${rowId}`).value = '';
            document.getElementById(`itemName${rowId}`).value = '';
            document.getElementById(`itemPrice${rowId}`).value = '0';
        }
        
        calculateRow(rowId);
    }

    function calculateRow(rowId) {
        const qty = parseFloat(document.getElementById(`itemQty${rowId}`).value) || 0;
        const price = parseFloat(document.getElementById(`itemPrice${rowId}`).value) || 0;
        const total = qty * price;
        
        document.getElementById(`rowTotal${rowId}`).textContent = total.toFixed(2);
        calculateTotals();
    }

    function calculateTotals() {
        let subtotal = 0;
        
        document.querySelectorAll('.row-total').forEach(el => {
            subtotal += parseFloat(el.textContent) || 0;
        });
        
        const tax = 0; // Tax can be calculated here if needed
        const discount = 0; // Discount can be calculated here if needed
        const total = subtotal + tax - discount;
        
        document.getElementById('summarySubtotal').textContent = subtotal.toFixed(2);
        document.getElementById('summaryTax').textContent = tax.toFixed(2);
        document.getElementById('summaryDiscount').textContent = discount.toFixed(2);
        document.getElementById('summaryTotal').textContent = total.toFixed(2);
    }

    // Add one empty row by default
    document.addEventListener('DOMContentLoaded', function() {
        addItemRow();
    });
</script>
@endpush
