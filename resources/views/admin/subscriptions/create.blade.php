@extends('admin.layouts.app')

@section('title', 'Create Subscription')

@section('content')
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1">Create Subscription</h4>
            <p class="text-muted mb-0">Create a new recurring subscription for a customer</p>
        </div>
        <a href="{{ route('admin.subscriptions.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> Back to Subscriptions
        </a>
    </div>

    <form id="subscriptionForm" method="POST" action="{{ route('admin.subscriptions.store') }}">
        @csrf
        
        <div class="row">
            <!-- Main Content -->
            <div class="col-lg-8">
                <!-- Customer Selection -->
                <div class="card border-0 shadow-sm mb-3">
                    <div class="card-header bg-white">
                        <h6 class="mb-0"><i class="bi bi-person me-2"></i>Customer Information</h6>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label">Select Customer <span class="text-danger">*</span></label>
                            <select name="customer_id" id="customerSelect" class="form-select @error('customer_id') is-invalid @enderror" required>
                                <option value="">Choose a customer...</option>
                                @foreach($customers as $customer)
                                    <option value="{{ $customer->id }}" data-email="{{ $customer->email }}" data-phone="{{ $customer->phone }}">
                                        {{ $customer->name }} - {{ $customer->phone }} ({{ $customer->email }})
                                    </option>
                                @endforeach
                            </select>
                            @error('customer_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Product Selection -->
                <div class="card border-0 shadow-sm mb-3">
                    <div class="card-header bg-white">
                        <h6 class="mb-0"><i class="bi bi-box me-2"></i>Product Information</h6>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label">Select Product <span class="text-danger">*</span></label>
                            <select name="product_id" id="productSelect" class="form-select @error('product_id') is-invalid @enderror" required>
                                <option value="">Choose a product...</option>
                                @foreach($products as $product)
                                    <option value="{{ $product->id }}" data-price="{{ $product->price }}" data-sku="{{ $product->sku }}" data-stock="{{ $product->quantity }}">
                                        {{ $product->name }} (SKU: {{ $product->sku }}) - ৳{{ number_format($product->price, 2) }}
                                    </option>
                                @endforeach
                            </select>
                            @error('product_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Selected Product Preview -->
                        <div id="productPreview" class="d-none mt-3 p-3 bg-light rounded">
                            <div class="d-flex align-items-center">
                                <div id="productImage" class="me-3"></div>
                                <div>
                                    <h6 class="mb-1" id="previewName"></h6>
                                    <p class="mb-0 text-muted small">
                                        SKU: <span id="previewSku"></span> | 
                                        Stock: <span id="previewStock"></span> | 
                                        Price: ৳<span id="previewPrice"></span>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Subscription Plan Details -->
                <div class="card border-0 shadow-sm mb-3">
                    <div class="card-header bg-white">
                        <h6 class="mb-0"><i class="bi bi-arrow-repeat me-2"></i>Subscription Plan Details</h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Plan Name <span class="text-danger">*</span></label>
                                <input type="text" name="plan_name" class="form-control @error('plan_name') is-invalid @enderror" 
                                       placeholder="e.g., Monthly Coffee Delivery" value="{{ old('plan_name') }}" required>
                                @error('plan_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Billing Frequency <span class="text-danger">*</span></label>
                                <select name="billing_frequency" class="form-select @error('billing_frequency') is-invalid @enderror" required>
                                    <option value="weekly" {{ old('billing_frequency') === 'weekly' ? 'selected' : '' }}>Weekly</option>
                                    <option value="bi_weekly" {{ old('billing_frequency') === 'bi_weekly' ? 'selected' : '' }}>Bi-Weekly</option>
                                    <option value="monthly" {{ old('billing_frequency', 'monthly') === 'monthly' ? 'selected' : '' }}>Monthly</option>
                                    <option value="quarterly" {{ old('billing_frequency') === 'quarterly' ? 'selected' : '' }}>Quarterly</option>
                                    <option value="semi_annually" {{ old('billing_frequency') === 'semi_annually' ? 'selected' : '' }}>Semi-Annually</option>
                                    <option value="annually" {{ old('billing_frequency') === 'annually' ? 'selected' : '' }}>Annually</option>
                                </select>
                                @error('billing_frequency')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Quantity <span class="text-danger">*</span></label>
                                <input type="number" name="quantity" id="quantity" class="form-control @error('quantity') is-invalid @enderror" 
                                       value="{{ old('quantity', 1) }}" min="1" required>
                                @error('quantity')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Start Date <span class="text-danger">*</span></label>
                                <input type="date" name="start_date" class="form-control @error('start_date') is-invalid @enderror" 
                                       value="{{ old('start_date', date('Y-m-d')) }}" required>
                                @error('start_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">End Date (Optional)</label>
                                <input type="date" name="end_date" class="form-control @error('end_date') is-invalid @enderror" 
                                       value="{{ old('end_date') }}">
                                <small class="text-muted">Leave empty for ongoing subscription</small>
                                @error('end_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Total Billing Cycles (Optional)</label>
                                <input type="number" name="total_billing_cycles" class="form-control @error('total_billing_cycles') is-invalid @enderror" 
                                       value="{{ old('total_billing_cycles') }}" min="1">
                                <small class="text-muted">Leave empty for unlimited cycles</small>
                                @error('total_billing_cycles')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Initial Status</label>
                                <select name="status" class="form-select">
                                    <option value="pending">Pending</option>
                                    <option value="active">Active</option>
                                </select>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea name="description" class="form-control @error('description') is-invalid @enderror" 
                                      rows="3" placeholder="Describe the subscription plan...">{{ old('description') }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Price Preview -->
                        <div class="alert alert-info d-flex justify-content-between align-items-center" id="pricePreview">
                            <span><i class="bi bi-calculator me-2"></i>Estimated Total:</span>
                            <strong class="fs-5">৳<span id="totalPrice">0.00</span></strong>
                        </div>
                    </div>
                </div>

                <!-- Shipping Address -->
                <div class="card border-0 shadow-sm mb-3">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center">
                        <h6 class="mb-0"><i class="bi bi-geo-alt me-2"></i>Shipping Address</h6>
                        <button type="button" class="btn btn-sm btn-outline-primary" id="fillFromCustomer">
                            <i class="bi bi-person-lines-fill me-1"></i> Fill from Customer
                        </button>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">First Name <span class="text-danger">*</span></label>
                                <input type="text" name="shipping_first_name" id="shipping_first_name" 
                                       class="form-control @error('shipping_first_name') is-invalid @enderror" 
                                       value="{{ old('shipping_first_name') }}" required>
                                @error('shipping_first_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Last Name <span class="text-danger">*</span></label>
                                <input type="text" name="shipping_last_name" id="shipping_last_name" 
                                       class="form-control @error('shipping_last_name') is-invalid @enderror" 
                                       value="{{ old('shipping_last_name') }}" required>
                                @error('shipping_last_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Email <span class="text-danger">*</span></label>
                                <input type="email" name="shipping_email" id="shipping_email" 
                                       class="form-control @error('shipping_email') is-invalid @enderror" 
                                       value="{{ old('shipping_email') }}" required>
                                @error('shipping_email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Phone <span class="text-danger">*</span></label>
                                <input type="text" name="shipping_phone" id="shipping_phone" 
                                       class="form-control @error('shipping_phone') is-invalid @enderror" 
                                       value="{{ old('shipping_phone') }}" required>
                                @error('shipping_phone')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Address <span class="text-danger">*</span></label>
                            <textarea name="shipping_address" id="shipping_address" 
                                      class="form-control @error('shipping_address') is-invalid @enderror" 
                                      rows="2" required>{{ old('shipping_address') }}</textarea>
                            @error('shipping_address')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">City <span class="text-danger">*</span></label>
                                <input type="text" name="shipping_city" id="shipping_city" 
                                       class="form-control @error('shipping_city') is-invalid @enderror" 
                                       value="{{ old('shipping_city') }}" required>
                                @error('shipping_city')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">State <span class="text-danger">*</span></label>
                                <input type="text" name="shipping_state" id="shipping_state" 
                                       class="form-control @error('shipping_state') is-invalid @enderror" 
                                       value="{{ old('shipping_state') }}" required>
                                @error('shipping_state')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Postcode <span class="text-danger">*</span></label>
                                <input type="text" name="shipping_postcode" id="shipping_postcode" 
                                       class="form-control @error('shipping_postcode') is-invalid @enderror" 
                                       value="{{ old('shipping_postcode') }}" required>
                                @error('shipping_postcode')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Country <span class="text-danger">*</span></label>
                                <input type="text" name="shipping_country" id="shipping_country" 
                                       class="form-control @error('shipping_country') is-invalid @enderror" 
                                       value="{{ old('shipping_country', 'Bangladesh') }}" required>
                                @error('shipping_country')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Notes -->
                <div class="card border-0 shadow-sm mb-3">
                    <div class="card-header bg-white">
                        <h6 class="mb-0"><i class="bi bi-sticky me-2"></i>Additional Notes</h6>
                    </div>
                    <div class="card-body">
                        <textarea name="notes" class="form-control @error('notes') is-invalid @enderror" 
                                  rows="3" placeholder="Any additional notes...">{{ old('notes') }}</textarea>
                        @error('notes')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="col-lg-4">
                <!-- Summary Card -->
                <div class="card border-0 shadow-sm mb-3">
                    <div class="card-header bg-white">
                        <h6 class="mb-0"><i class="bi bi-calculator me-2"></i>Summary</h6>
                    </div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Unit Price:</span>
                            <span>৳<span id="summaryUnitPrice">0.00</span></span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Quantity:</span>
                            <span id="summaryQuantity">1</span>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between">
                            <span class="fw-bold">Total:</span>
                            <span class="fw-bold text-primary">৳<span id="summaryTotal">0.00</span></span>
                        </div>
                    </div>
                </div>

                <!-- Tips Card -->
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white">
                        <h6 class="mb-0"><i class="bi bi-lightbulb me-2"></i>Tips</h6>
                    </div>
                    <div class="card-body">
                        <ul class="list-unstyled mb-0 small">
                            <li class="mb-2"><i class="bi bi-check-circle text-success me-2"></i>Verify customer details before creating</li>
                            <li class="mb-2"><i class="bi bi-check-circle text-success me-2"></i>Check product stock availability</li>
                            <li class="mb-2"><i class="bi bi-check-circle text-success me-2"></i>Set appropriate billing frequency</li>
                            <li><i class="bi bi-check-circle text-success me-2"></i>First billing will occur on start date</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </form>

<!-- Floating Save Button -->
<div class="floating-save-container">
    <a href="{{ route('admin.subscriptions.index') }}" class="btn btn-secondary floating-reset-btn">
        <i class="bi bi-x-lg me-1"></i> Cancel
    </a>
    <button type="submit" form="subscriptionForm" class="btn btn-primary floating-save-btn">
        <i class="bi bi-check-lg me-1"></i> Create Subscription
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
    const products = @json($products);
    let selectedProduct = null;

    // Product selection change
    document.getElementById('productSelect').addEventListener('change', function() {
        const productId = this.value;
        if (!productId) {
            document.getElementById('productPreview').classList.add('d-none');
            selectedProduct = null;
            updatePrice();
            return;
        }

        selectedProduct = products.find(p => p.id == productId);
        if (selectedProduct) {
            showProductPreview(selectedProduct);
            updatePrice();
        }
    });

    // Quantity change
    document.getElementById('quantity').addEventListener('input', updatePrice);

    function showProductPreview(product) {
        document.getElementById('productPreview').classList.remove('d-none');
        document.getElementById('previewName').textContent = product.name;
        document.getElementById('previewSku').textContent = product.sku;
        document.getElementById('previewStock').textContent = product.quantity;
        document.getElementById('previewPrice').textContent = parseFloat(product.price).toFixed(2);
        
        // Product image
        let imageHtml = '';
        if (product.featured_image) {
            let imageUrl = product.featured_image;
            if (!imageUrl.startsWith('/storage/') && !imageUrl.startsWith('http')) {
                imageUrl = '/storage/' + imageUrl;
            }
            imageHtml = `<img src="${imageUrl}" alt="" class="rounded" style="width: 60px; height: 60px; object-fit: cover;">`;
        } else {
            imageHtml = `<div class="bg-secondary rounded d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;"><i class="bi bi-image text-white fs-4"></i></div>`;
        }
        document.getElementById('productImage').innerHTML = imageHtml;
    }

    function updatePrice() {
        if (!selectedProduct) {
            document.getElementById('totalPrice').textContent = '0.00';
            document.getElementById('summaryUnitPrice').textContent = '0.00';
            document.getElementById('summaryQuantity').textContent = '1';
            document.getElementById('summaryTotal').textContent = '0.00';
            return;
        }

        const quantity = parseInt(document.getElementById('quantity').value) || 1;
        const unitPrice = parseFloat(selectedProduct.price);
        const total = unitPrice * quantity;

        document.getElementById('totalPrice').textContent = total.toFixed(2);
        document.getElementById('summaryUnitPrice').textContent = unitPrice.toFixed(2);
        document.getElementById('summaryQuantity').textContent = quantity;
        document.getElementById('summaryTotal').textContent = total.toFixed(2);
    }

    // Fill shipping from customer
    document.getElementById('fillFromCustomer').addEventListener('click', function() {
        const customerSelect = document.getElementById('customerSelect');
        const customerId = customerSelect.value;
        
        if (!customerId) {
            alert('Please select a customer first.');
            return;
        }

        // Fetch customer details
        fetch(`{{ route('admin.subscriptions.customer-details', '') }}/${customerId}`)
            .then(res => res.json())
            .then(data => {
                if (data.address) {
                    document.getElementById('shipping_first_name').value = data.address.first_name || '';
                    document.getElementById('shipping_last_name').value = data.address.last_name || '';
                    document.getElementById('shipping_email').value = data.address.email || data.email || '';
                    document.getElementById('shipping_phone').value = data.address.phone || data.phone || '';
                    document.getElementById('shipping_address').value = data.address.address || '';
                    document.getElementById('shipping_city').value = data.address.city || '';
                    document.getElementById('shipping_state').value = data.address.state || '';
                    document.getElementById('shipping_postcode').value = data.address.postcode || '';
                    document.getElementById('shipping_country').value = data.address.country || 'Bangladesh';
                } else {
                    // Fill basic info
                    document.getElementById('shipping_email').value = data.email || '';
                    document.getElementById('shipping_phone').value = data.phone || '';
                }
            })
            .catch(err => {
                console.error('Error fetching customer details:', err);
                alert('Failed to fetch customer details.');
            });
    });

    // Customer selection - auto fill email and phone
    document.getElementById('customerSelect').addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        if (this.value) {
            const email = selectedOption.getAttribute('data-email');
            const phone = selectedOption.getAttribute('data-phone');
            
            // Only fill if fields are empty
            if (!document.getElementById('shipping_email').value) {
                document.getElementById('shipping_email').value = email || '';
            }
            if (!document.getElementById('shipping_phone').value) {
                document.getElementById('shipping_phone').value = phone || '';
            }
        }
    });
</script>
@endpush