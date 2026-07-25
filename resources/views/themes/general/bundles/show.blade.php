@extends('themes.general.layouts.app')

@section('title', $bundle->name)

@push('styles')
<style>
.bundle-hero {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 40px 0;
    margin-bottom: 30px;
}
.bundle-hero h1 {
    font-size: 2.5rem;
    font-weight: 700;
}
.bundle-price-tag {
    background: white;
    color: #2D5A27;
    padding: 20px 30px;
    border-radius: 12px;
    text-align: center;
}
.bundle-price-tag .price {
    font-size: 2.5rem;
    font-weight: 700;
}
.bundle-price-tag .original {
    text-decoration: line-through;
    color: #999;
    font-size: 1.2rem;
}
.bundle-price-tag .savings {
    background: #d4edda;
    color: #155724;
    padding: 5px 15px;
    border-radius: 20px;
    font-weight: 600;
    margin-top: 10px;
    display: inline-block;
}
.product-item {
    border: 1px solid #eee;
    border-radius: 12px;
    padding: 15px;
    margin-bottom: 15px;
    transition: all 0.2s;
}
.product-item:hover {
    border-color: #2D5A27;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
}
.product-item img {
    width: 80px;
    height: 80px;
    object-fit: cover;
    border-radius: 8px;
}
.bundle-summary {
    background: #f8f9fa;
    border-radius: 12px;
    padding: 25px;
    position: sticky;
    top: 100px;
}
.bundle-timer {
    background: linear-gradient(135deg, #ff6b6b, #ee5a5a);
    color: white;
    padding: 15px;
    border-radius: 8px;
    text-align: center;
    margin-bottom: 20px;
}
.bundle-timer .timer-label {
    font-size: 0.9rem;
    opacity: 0.9;
}
.bundle-timer .timer-value {
    font-size: 1.5rem;
    font-weight: 700;
}
.quantity-selector {
    display: flex;
    align-items: center;
    gap: 10px;
}
.quantity-selector button {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    border: 1px solid #ddd;
    background: white;
}
.quantity-selector input {
    width: 60px;
    text-align: center;
    border: 1px solid #ddd;
    border-radius: 8px;
    height: 40px;
}
</style>
@endpush

@section('content')
<!-- Hero Section -->
<div class="bundle-hero">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-8">
                <nav aria-label="breadcrumb" class="mb-3">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}" class="text-white-50">Home</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('bundles.index') }}" class="text-white-50">Bundles</a></li>
                        <li class="breadcrumb-item active text-white">{{ $bundle->name }}</li>
                    </ol>
                </nav>
                <h1>{{ $bundle->name }}</h1>
                @if($bundle->description)
                <p class="lead mb-0 opacity-75">{{ $bundle->description }}</p>
                @endif
            </div>
            <div class="col-lg-4 text-lg-end mt-4 mt-lg-0">
                <div class="bundle-price-tag d-inline-block">
                    @if($bundle->original_price > $bundle->final_price)
                        <div class="original">৳{{ number_format($bundle->original_price, 2) }}</div>
                    @endif
                    <div class="price">৳{{ number_format($bundle->final_price, 2) }}</div>
                    @if($bundle->savings > 0)
                        <span class="savings">
                            <i class="bi bi-piggy-bank me-1"></i>Save ৳{{ number_format($bundle->savings, 2) }}
                        </span>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<div class="container">
    <div class="row">
        <!-- Products List -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0">
                        <i class="bi bi-box-seam me-2"></i>Bundle Contents ({{ $bundle->items->count() }} items)
                    </h5>
                </div>
                <div class="card-body">
                    @foreach($bundle->items as $item)
                    <div class="product-item d-flex align-items-center gap-3">
                        <img src="{{ $item->product->featured_image ? asset('storage/' . $item->product->featured_image) : asset('images/no-image.png') }}" 
                             alt="{{ $item->product->name }}">
                        <div class="flex-grow-1">
                            <h6 class="mb-1">
                                <a href="{{ route('products.show', $item->product->slug) }}" class="text-decoration-none">
                                    {{ $item->product->name }}
                                </a>
                            </h6>
                            <small class="text-muted">
                                @if($item->product->category)
                                    {{ $item->product->category->name }}
                                @endif
                            </small>
                        </div>
                        <div class="text-center">
                            <span class="badge bg-light text-dark">Qty: {{ $item->quantity }}</span>
                        </div>
                        <div class="text-end" style="min-width: 100px;">
                            @if($item->custom_price)
                                <div class="text-success fw-semibold">৳{{ number_format($item->custom_price, 2) }}</div>
                                <small class="text-decoration-line-through text-muted">৳{{ number_format($item->product->final_price, 2) }}</small>
                            @else
                                <div class="fw-semibold">৳{{ number_format($item->product->final_price, 2) }}</div>
                            @endif
                        </div>
                    </div>
                    @endforeach
                    
                    <!-- Total Section -->
                    <div class="border-top pt-3 mt-3">
                        <div class="d-flex justify-content-between mb-2">
                            <span>Original Total:</span>
                            <strong>৳{{ number_format($bundle->original_price, 2) }}</strong>
                        </div>
                        @if($bundle->savings > 0)
                        <div class="d-flex justify-content-between mb-2 text-success">
                            <span><i class="bi bi-tag-fill me-1"></i>Bundle Discount:</span>
                            <strong>-৳{{ number_format($bundle->savings, 2) }}</strong>
                        </div>
                        @endif
                        <div class="d-flex justify-content-between h5 mb-0">
                            <span>Bundle Price:</span>
                            <strong class="text-success">৳{{ number_format($bundle->final_price, 2) }}</strong>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Bundle Description -->
            @if($bundle->description)
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="bi bi-info-circle me-2"></i>About This Bundle</h5>
                </div>
                <div class="card-body">
                    {{ $bundle->description }}
                </div>
            </div>
            @endif
        </div>
        
        <!-- Sidebar -->
        <div class="col-lg-4">
            <div class="bundle-summary">
                <!-- Timer -->
                @if($bundle->expires_at && !$bundle->hasExpired())
                <div class="bundle-timer">
                    <div class="timer-label"><i class="bi bi-clock me-1"></i>Offer Ends In</div>
                    <div class="timer-value" id="countdown" data-expires="{{ $bundle->expires_at->toISOString() }}">
                        {{ $bundle->expires_at->diffForHumans() }}
                    </div>
                </div>
                @endif
                
                <!-- Availability Status -->
                @if(!$bundle->canBePurchasedBy(auth()->user()))
                    @if($bundle->hasExpired())
                        <div class="alert alert-danger">
                            <i class="bi bi-x-circle me-2"></i>This bundle has expired.
                        </div>
                    @elseif($bundle->max_purchases && $bundle->total_purchases >= $bundle->max_purchases)
                        <div class="alert alert-warning">
                            <i class="bi bi-exclamation-circle me-2"></i>This bundle is sold out.
                        </div>
                    @elseif(auth()->check() && $bundle->max_purchases_per_user)
                        @php $userPurchases = $bundle->purchases()->where('user_id', auth()->id())->count(); @endphp
                        @if($userPurchases >= $bundle->max_purchases_per_user)
                            <div class="alert alert-info">
                                <i class="bi bi-info-circle me-2"></i>You've reached the maximum purchase limit for this bundle.
                            </div>
                        @endif
                    @endif
                @endif
                
                <!-- Price Summary -->
                <div class="text-center mb-4">
                    <div class="h2 text-success mb-0">৳{{ number_format($bundle->final_price, 2) }}</div>
                    @if($bundle->original_price > $bundle->final_price)
                        <small class="text-muted">
                            <del>৳{{ number_format($bundle->original_price, 2) }}</del>
                            <span class="text-success ms-2">Save {{ $bundle->discount_percentage }}%</span>
                        </small>
                    @endif
                </div>
                
                <!-- Quantity -->
                <div class="mb-3">
                    <label class="form-label">Quantity</label>
                    <div class="quantity-selector">
                        <button type="button" onclick="decreaseQty()">
                            <i class="bi bi-dash"></i>
                        </button>
                        <input type="number" id="quantity" name="quantity" value="1" min="1" max="10" readonly>
                        <button type="button" onclick="increaseQty()">
                            <i class="bi bi-plus"></i>
                        </button>
                    </div>
                </div>
                
                <!-- Add to Cart -->
                <form action="{{ route('bundles.add-to-cart', $bundle->id) }}" method="POST" id="addToCartForm">
                    @csrf
                    <input type="hidden" name="quantity" id="qtyInput" value="1">
                    
                    @if($bundle->canBePurchasedBy(auth()->user()))
                    <button type="submit" class="btn btn-lg w-100 mb-3 text-white" style="background-color: var(--theme-primary, #4f46e5);">
                        <i class="bi bi-cart-plus me-2"></i>Add to Cart
                    </button>
                    <button type="button" class="btn btn-outline-primary btn-lg w-100" onclick="buyNow()">
                        <i class="bi bi-lightning-fill me-2"></i>Buy Now
                    </button>
                    @else
                    <button type="button" class="btn btn-secondary btn-lg w-100" disabled>
                        <i class="bi bi-x-circle me-2"></i>Unavailable
                    </button>
                    @endif
                </form>
                
                <!-- Stock Info -->
                @if($bundle->max_purchases)
                <div class="mt-3 text-center">
                    <small class="text-muted">
                        <i class="bi bi-people me-1"></i>
                        {{ $bundle->remaining_purchases }} remaining out of {{ $bundle->max_purchases }}
                    </small>
                    <div class="progress mt-2" style="height: 6px;">
                        <div class="progress-bar bg-success" 
                             style="width: {{ $bundle->max_purchases > 0 ? ($bundle->total_purchases / $bundle->max_purchases) * 100 : 0 }}%"></div>
                    </div>
                </div>
                @endif
                
                <!-- Features -->
                <div class="mt-4">
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <i class="bi bi-check-circle-fill text-success"></i>
                        <span>{{ $bundle->items->count() }} products included</span>
                    </div>
                    @if($bundle->savings > 0)
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <i class="bi bi-check-circle-fill text-success"></i>
                        <span>Save ৳{{ number_format($bundle->savings, 2) }}</span>
                    </div>
                    @endif
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <i class="bi bi-check-circle-fill text-success"></i>
                        <span>Free shipping</span>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-check-circle-fill text-success"></i>
                        <span>30-day returns</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Related Bundles -->
@if($relatedBundles ?? false && $relatedBundles->count() > 0)
<div class="container mt-5">
    <h4 class="mb-4">You May Also Like</h4>
    <div class="row g-4">
        @foreach($relatedBundles as $relatedBundle)
        <div class="col-md-4">
            <div class="card h-100">
                <div class="position-relative">
                    <img src="{{ $relatedBundle->featured_image ? asset('storage/' . $relatedBundle->featured_image) : asset('images/no-image.png') }}" 
                         class="card-img-top" style="height: 180px; object-fit: cover;" alt="{{ $relatedBundle->name }}">
                    @if($relatedBundle->discount_percentage > 0)
                    <span class="badge bg-danger position-absolute top-0 end-0 m-2">
                        {{ $relatedBundle->discount_percentage }}% OFF
                    </span>
                    @endif
                </div>
                <div class="card-body">
                    <h6 class="card-title">{{ $relatedBundle->name }}</h6>
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <span class="h5 text-success mb-0">৳{{ number_format($relatedBundle->final_price, 2) }}</span>
                            @if($relatedBundle->original_price > $relatedBundle->final_price)
                                <small class="text-muted text-decoration-line-through ms-1">৳{{ number_format($relatedBundle->original_price, 2) }}</small>
                            @endif
                        </div>
                        <a href="{{ route('bundles.show', $relatedBundle->slug) }}" class="btn btn-sm btn-outline-primary">
                            View
                        </a>
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>
</div>
@endif
@endsection

@push('scripts')
<script>
// Quantity controls
function increaseQty() {
    const input = document.getElementById('quantity');
    const max = parseInt(input.max) || 10;
    if (parseInt(input.value) < max) {
        input.value = parseInt(input.value) + 1;
        document.getElementById('qtyInput').value = input.value;
    }
}

function decreaseQty() {
    const input = document.getElementById('quantity');
    if (parseInt(input.value) > 1) {
        input.value = parseInt(input.value) - 1;
        document.getElementById('qtyInput').value = input.value;
    }
}

// Buy now
function buyNow() {
    const form = document.getElementById('addToCartForm');
    const action = form.getAttribute('action');
    form.setAttribute('action', action + '?buy_now=1');
    form.submit();
}

// Countdown timer
@if($bundle->expires_at && !$bundle->hasExpired())
const countdownEl = document.getElementById('countdown');
const expiresAt = new Date(countdownEl.dataset.expires);

function updateCountdown() {
    const now = new Date();
    const diff = expiresAt - now;
    
    if (diff <= 0) {
        countdownEl.textContent = 'Expired';
        location.reload();
        return;
    }
    
    const days = Math.floor(diff / (1000 * 60 * 60 * 24));
    const hours = Math.floor((diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
    const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
    const seconds = Math.floor((diff % (1000 * 60)) / 1000);
    
    if (days > 0) {
        countdownEl.textContent = `${days}d ${hours}h ${minutes}m`;
    } else {
        countdownEl.textContent = `${hours}h ${minutes}m ${seconds}s`;
    }
}

updateCountdown();
setInterval(updateCountdown, 1000);
@endif
</script>
@endpush
