@extends('themes.general.layouts.app')

@section('title', $product->name)

@push('styles')
<style>
/* ───────────── Image Gallery ───────────── */
.gallery-container {
    display: flex; gap: 12px; align-items: flex-start;
}
.gallery-thumbs {
    display: flex; flex-direction: column; gap: 10px;
    width: 90px; flex-shrink: 0;
    position: relative;
    padding: 4px 2px;
}
.gallery-thumb-nav {
    width: 100%; height: 24px; display: flex; align-items: center; justify-content: center;
    background: #f0f0f0; cursor: pointer; transition: all .2s; border-radius: 4px; flex-shrink: 0;
    position: relative; z-index: 1;
}
.gallery-thumb-nav:hover { background: #2D5A27; color: white; }
.gallery-thumb-nav i { font-size: 12px; }
#galleryThumbsContainer {
    max-height: 360px; overflow-y: auto; position: relative; scroll-behavior: smooth;
}
.gallery-thumb {
    width: 80px; height: 80px; border-radius: 10px; object-fit: cover; cursor: pointer;
    border: 2px solid transparent; transition: all .2s; flex-shrink: 0;
    opacity: 0.6;
}
.gallery-thumb:hover { opacity: 1; }
.gallery-thumb.active { 
    opacity: 1; 
    border-color: #2D5A27;
}
.gallery-main {
    position: relative; overflow: hidden; border-radius: 12px; background: #f9fafb;
    width: 100%; height: 450px; cursor: zoom-in;
    flex: 1;
}
.gallery-main .gallery-img {
    width: 100%; height: 100%; background-size: contain; background-position: center;
    background-repeat: no-repeat; transition: transform .15s ease-out;
    transform-origin: center center;
}
.gallery-main.zoomed .gallery-img {
    transform: scale(2.5);
}

/* Badge — Sale / New */
.badge-sale { position: absolute; top: 12px; left: 12px; z-index: 5;
    background: linear-gradient(135deg,#dc2626,#ef4444); color: #fff; padding: 4px 12px;
    border-radius: 6px; font-size: .8rem; font-weight: 700; box-shadow: 0 2px 8px rgba(220,38,38,.3); }

/* ───────────── Trust Badges ───────────── */
.trust-badge { display: flex; align-items: center; gap: 10px; padding: 10px 14px;
    background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 10px; font-size: .85rem; }

/* ───────────── Tabs ───────────── */
.tab-btn { padding: 10px 20px; font-weight: 600; font-size: .9rem; color: #6b7280;
    border-bottom: 3px solid transparent; cursor: pointer; transition: all .2s;
    background: none; white-space: nowrap; }
.tab-btn:hover { color: #2D5A27; }
.tab-btn.active { color: #2D5A27; border-bottom-color: #2D5A27; }
.tab-pane { display: none; width: 100%; }
.tab-pane.active { display: block; }

/* ───────────── Stock urgency ───────────── */
.stock-bar { height: 6px; border-radius: 3px; background: #e5e7eb; overflow: hidden; }
.stock-bar-fill { height: 100%; border-radius: 3px; transition: width .6s; }

/* ───────────── Price ───────────── */
.price-current { font-size: 1.75rem; font-weight: 800; color: #2D5A27; }
.price-old { font-size: 1.1rem; color: #9ca3af; text-decoration: line-through; }

/* ───────────── Responsive ───────────── */
@media (max-width: 1024px) {
    .gallery-main { height: 350px; }
    .gallery-container { flex-direction: column-reverse; }
    .gallery-thumbs { 
        flex-direction: row; width: 100%; overflow-x: auto; padding-bottom: 4px;
        gap: 8px; max-height: none; scrollbar-height: thin;
    }
    .gallery-thumb { width: 60px; height: 60px; }
    .gallery-thumb-nav { display: none; }
    #galleryThumbsContainer { max-height: none; overflow-x: auto; overflow-y: hidden; display: flex; gap: 8px; }
    .gallery-thumbs { flex-direction: row; width: 100%; }
}
@media (max-width: 640px) {
    .gallery-main { height: 280px; }
    .tab-btn { font-size: .8rem; padding: 8px 12px; }
}
</style>
@endpush

@section('content')
<div class="bg-gray-50 min-h-screen pb-12">

<!-- Breadcrumb -->
<div class="bg-white border-b">
    <div class="container mx-auto px-4 py-3">
        <nav class="flex items-center gap-1.5 text-sm text-gray-500 flex-wrap">
            <a href="{{ route('home') }}" class="hover:text-halal-green transition-colors"><i class="bi bi-house-door-fill"></i></a>
            <i class="bi bi-chevron-right text-xs"></i>
            <a href="{{ route('products.index') }}" class="hover:text-halal-green transition-colors">Products</a>
            @if($product->category)
                @php $crumbCats = []; $tmp = $product->category; while($tmp) { $crumbCats[] = $tmp; $tmp = $tmp->parent; } $crumbCats = array_reverse($crumbCats); @endphp
                @foreach($crumbCats as $cat)
                    <i class="bi bi-chevron-right text-xs"></i>
                    <a href="{{ route('products.index', ['category' => $cat->slug]) }}" class="hover:text-halal-green transition-colors">{{ $cat->name }}</a>
                @endforeach
            @endif
            <i class="bi bi-chevron-right text-xs"></i>
            <span class="text-gray-800 font-medium truncate max-w-[200px]">{{ $product->name }}</span>
        </nav>
    </div>
</div>

<!-- Schema.org -->
@php
$schema = [
    '@context' => 'https://schema.org',
    '@type' => 'Product',
    'name' => $product->name,
    'description' => $product->short_description ?: $product->long_description,
    'sku' => $product->sku ?? 'PROD-'.$product->id,
    'brand' => ['@type' => 'Brand', 'name' => $product->brand ?? 'Hamko'],
    'offers' => [
        '@type' => 'Offer',
        'url' => request()->url(),
        'priceCurrency' => 'BDT',
        'price' => $product->isOnSale() ? $product->sale_price : $product->price,
        'availability' => $product->quantity > 0 ? 'https://schema.org/InStock' : 'https://schema.org/OutOfStock',
        'itemCondition' => 'https://schema.org/NewCondition',
    ],
    'image' => $product->featured_image ?? $product->image ?? '',
];
@endphp
<script type="application/ld+json">{!! json_encode($schema) !!}</script>

<!-- Main Product Card -->
<div class="container mx-auto px-4 -mt-1">
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">

        <!-- Top Section: Gallery + Info -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-0">

            <!-- ★ Image Gallery (Left) -->
            <div class="lg:col-span-5 p-4 sm:p-6 bg-gray-50/50">
                @php
                    $img = $product->featured_image ?? $product->image ?? '';
                    $imgUrl = 'https://placehold.co/500x500?text=No+Image';
                    if ($img) {
                        if (str_starts_with($img, 'http')) $imgUrl = $img;
                        elseif (str_starts_with($img, '/storage/')) $imgUrl = $img;
                        elseif (str_starts_with($img, '/uploads/')) $imgUrl = asset($img);
                        else $imgUrl = asset('storage/'.$img);
                    }
                    $gallery = [];
                    if ($product->images) {
                        $decoded = is_string($product->images) ? json_decode($product->images, true) : $product->images;
                        $gallery = is_array($decoded) ? $decoded : [];
                    }
                @endphp

                <div class="gallery-container">
                    <!-- Thumbnails (vertical on desktop) -->
                    @if(!empty($gallery) || $img)
                    <div class="gallery-thumbs">
                        <div class="gallery-thumb-nav" onclick="scrollGallery('up')"><i class="bi bi-chevron-up"></i></div>
                        <div id="galleryThumbsContainer">
                        @if($img)
                        <img src="{{ $imgUrl }}" alt="" class="gallery-thumb active"
                             onclick="changeImage(this,'{{ $imgUrl }}')">
                        @endif
                        @foreach($gallery as $gi)
                        @php
                            $gUrl = $gi;
                            if (!str_starts_with($gi,'http') && !str_starts_with($gi,'/storage/') && !str_starts_with($gi,'/uploads/'))
                                $gUrl = asset('storage/'.$gi);
                            elseif (str_starts_with($gi,'/uploads/'))
                                $gUrl = asset($gi);
                        @endphp
                        <img src="{{ $gUrl }}" alt="" class="gallery-thumb"
                             onclick="changeImage(this,'{{ $gUrl }}')">
                        @endforeach
                        <!-- Variant Images from database -->
                        @if(isset($allVariantImages) && $allVariantImages->count() > 0)
                        @foreach($allVariantImages as $vImg)
                        @php
                            $vImgUrl = $vImg->image;
                            if (!str_starts_with($vImgUrl,'http') && !str_starts_with($vImgUrl,'/storage/'))
                                $vImgUrl = asset('storage/'.$vImgUrl);
                        @endphp
                        <img src="{{ $vImgUrl }}" alt="" class="gallery-thumb variant-thumb"
                             data-key="{{ $vImg->combination_key }}"
                             onclick="changeImage(this,'{{ $vImgUrl }}')">
                        @endforeach
                        @endif
                        </div>
                        <div class="gallery-thumb-nav" onclick="scrollGallery('down')"><i class="bi bi-chevron-down"></i></div>
                    </div>
                    @endif

                    <!-- Main Image -->
                    <div class="gallery-main" id="mainImageContainer" onmouseenter="zoomIn()" onmouseleave="zoomOut()" onmousemove="zoomMove(event)">
                        <div class="gallery-img" id="mainImg" style="background-image:url('{{ $imgUrl }}')"></div>
                        @if($product->isOnSale())
                        <span class="badge-sale">-{{ $product->discount_percentage }}% OFF</span>
                        @endif
                    </div>
                </div>
            </div>

            <!-- ★ Product Info (Right) -->
            <div class="lg:col-span-7 p-4 sm:p-6 lg:p-8 flex flex-col">

                <!-- Category tag -->
                @if($product->category)
                <a href="{{ route('products.index', ['category' => $product->category->slug]) }}"
                   class="inline-flex items-center gap-1.5 text-xs font-semibold uppercase tracking-wider text-halal-green bg-halal-green/5 px-3 py-1.5 rounded-full w-fit mb-3 hover:bg-halal-green/10 transition-colors">
                    <i class="bi bi-folder2-open"></i> {{ $product->category->name }}
                </a>
                @endif

                <!-- Title -->
                <h1 class="text-2xl sm:text-3xl font-bold text-gray-900 leading-tight">{{ $product->name }}</h1>

                <!-- Rating + SKU row -->
                <div class="flex flex-wrap items-center gap-x-4 gap-y-1 mt-2 text-sm">
                    <div class="flex items-center gap-1.5">
                        <div class="flex text-amber-400 text-sm">
                            @php $ar = $product->average_rating; @endphp
                            @for($i=1;$i<=5;$i++)
                                <i class="bi bi-star{{ $ar>=$i?'-fill':($ar>=$i-0.5?'-half':'') }}"></i>
                            @endfor
                        </div>
                        <span class="text-gray-500">({{ $product->approved_reviews_count ?? 0 }} reviews)</span>
                    </div>
                    @if($product->sku)
                    <span class="text-gray-400">Product Code: <span class="text-gray-600 font-medium">{{ $product->product_code }}</span></span>
                    @endif
                </div>

                @php $basePrice = $product->isOnSale() ? $product->sale_price : $product->price; @endphp

                <!-- Price Calculation -->
                <div class="mt-4">
                    <div class="flex items-baseline gap-2 text-sm">
                        <span class="text-gray-600">Base Price:</span>
                        <span class="font-semibold text-gray-800" id="basePriceDisplay">৳{{ number_format($basePrice) }}</span>
                    </div>
                    <div class="mt-1 text-sm" id="attrPriceRow">
                        <div id="selectedAttrsList"></div>
                        <div id="selectedColorRow" class="flex items-baseline gap-2" style="display:none;">
                            <span class="text-gray-600">Color:</span>
                            <span class="font-semibold text-halal-green" id="selectedColorDisplay"></span>
                        </div>
                    </div>
                    <div class="flex items-baseline gap-2 text-lg mt-1 border-t border-gray-200 pt-1">
                        <span class="font-semibold text-gray-800">Total:</span>
                        <span class="price-current" id="displayPrice">৳{{ number_format($basePrice) }}</span>
                    </div>
                    @if($product->isOnSale())
                        <div class="flex items-center gap-2 mt-2">
                            <span class="text-xs font-semibold text-white bg-red-500 px-2 py-0.5 rounded-full" id="saveBadge">Save ৳{{ number_format($product->price - $product->sale_price) }}</span>
                            <span class="price-old" id="originalPrice">৳{{ number_format($product->price) }}</span>
                        </div>
                    @endif
                </div>

                <!-- Short description -->
                @if($product->short_description)
                <p class="mt-3 text-gray-600 text-sm leading-relaxed">{!! format_product_description($product->short_description, false) !!}</p>
                @endif

                <!-- Stock + urgency -->
                <div class="mt-4 space-y-2">
                    @if($product->quantity > 0)
                        <div class="flex items-center gap-2">
                            <span class="inline-flex items-center gap-1 text-sm font-medium text-green-700 bg-green-50 px-3 py-1 rounded-full">
                                <i class="bi bi-check-circle-fill"></i> In Stock
                            </span>
                            <span class="text-sm text-gray-500" id="stockCount">({{ $product->quantity }} available)</span>
                        </div>
                        @if($product->quantity <= 10)
                        <div class="flex items-center gap-2 text-sm text-orange-600">
                            <i class="bi bi-exclamation-triangle-fill"></i>
                            <span>Only <strong>{{ $product->quantity }} left</strong> — order soon!</span>
                        </div>
                        <div class="stock-bar max-w-xs">
                            <div class="stock-bar-fill bg-orange-500" style="width: {{ min(100, ($product->quantity / 50) * 100) }}%"></div>
                        </div>
                        @endif
                    @else
                        <span class="inline-flex items-center gap-1 text-sm font-medium text-red-600 bg-red-50 px-3 py-1 rounded-full">
                            <i class="bi bi-x-circle-fill"></i> Out of Stock
                        </span>
                    @endif
                </div>

                <!-- Color -->
                @php
                $showColors = null;
                if (isset($colorOptions) && !empty($colorOptions)) {
                    $showColors = collect($colorOptions);
                } elseif (isset($colors) && $colors->count() > 0) {
                    $showColors = $colors;
                }
                @endphp
                @if($showColors)
                <div class="mt-5">
                    <h3 class="text-sm font-semibold text-gray-800 mb-2">Color: <span id="selColorName" class="text-halal-green font-normal">Select</span></h3>
                    <div class="flex flex-wrap gap-2.5">
                        @foreach($showColors as $index => $color)
                        @php 
                        $colorImage = is_array($color) ? ($color['image'] ?? null) : ($color->image ?? null);
                        $colorHex = is_array($color) ? ($color['hex_code'] ?? '#000000') : ($color->hex_code ?? '#000000');
                        $colorId = is_array($color) ? ($color['id'] ?? '') : ($color->id ?? '');
                        $colorName = is_array($color) ? ($color['name'] ?? '') : ($color->name ?? '');
                        $colorQty = is_array($color) ? ($color['quantity'] ?? '') : ($color->quantity ?? '');
                        $colorPrice = is_array($color) ? ($color['price'] ?? 0) : ($color->price ?? 0);
                        @endphp
        <button type="button"
                class="color-btn w-10 h-10 rounded-full border-2 transition-all hover:scale-110 {{ $index === 0 ? 'ring-2 ring-halal-green ring-offset-2' : 'border-gray-300' }}"
                style="background:{{ $colorHex }}"
                data-id="{{ $colorId }}" data-name="{{ $colorName }}" data-hex="{{ $colorHex }}" data-adj="{{ $colorPrice }}"
                @if($colorImage) data-img="{{ asset('storage/' . $colorImage) }}" @endif
                @if(!empty($colorQty)) data-stock="{{ $colorQty }}" @endif
                title="{{ $colorName }}">
        </button>
        @endforeach
    </div>
    <input type="hidden" id="selColorId" value="">
</div>
@endif

<!-- Attributes -->
@if(isset($attributeOptions) && is_array($attributeOptions) && !empty($attributeOptions))
<div class="mt-5 space-y-4">
    @foreach($attributeOptions as $attrName => $options)
    <div>
        <h3 class="text-sm font-semibold text-gray-800 mb-2">{{ $attrName }}: <span id="attrSel{{ Str::slug($attrName) }}" class="text-halal-green font-normal">Select</span></h3>
        <div class="flex flex-wrap gap-2">
            @foreach($options as $option)
            <button type="button"
                    class="attr-btn border-2 rounded-lg px-3 py-2 text-sm transition-all hover:border-halal-green border-gray-300 flex items-center gap-2"
                    data-attr="{{ Str::slug($attrName) }}" data-attr-name="{{ $attrName }}" data-val="{{ $option['value'] }}" data-vid="{{ $option['id'] }}"
                    data-price="{{ $option['price'] ?? 0 }}"
                    @if(!empty($option['image'])) data-img="{{ asset('storage/' . $option['image']) }}" @endif
                    @if(!empty($option['color_code'])) style="border-left:4px solid {{ $option['color_code'] }}" @endif>
                @if(!empty($option['image']))
                <img src="{{ asset('storage/' . $option['image']) }}" class="w-6 h-6 object-cover rounded">
                @endif
                <span>{{ $option['value'] }}</span>
                @if(($option['price'] ?? 0) != 0)
                <span class="text-xs font-semibold text-halal-green">{{ ($option['price'] ?? 0) > 0 ? '+' : '' }}৳{{ number_format($option['price']) }}</span>
                @endif
            </button>
            @endforeach
        </div>
    </div>
    @endforeach
</div>
@endif

                <!-- Variants -->
                @if(isset($variantOptions) && !empty($variantOptions))
                <div class="mt-5 space-y-4" id="variantSelector">
                    @foreach($variantOptions as $attrName => $values)
                    <div>
                        <h3 class="text-sm font-semibold text-gray-800 mb-2">{{ $attrName }}: <span id="varSel{{ Str::slug($attrName) }}" class="text-halal-green font-normal">Select</span></h3>
                        <div class="flex flex-wrap gap-2">
                            @foreach($values as $valueName => $option)
                            <button type="button" class="var-btn border-2 rounded-lg px-4 py-2 text-sm transition-all hover:border-halal-green border-gray-300"
                                    data-attr="{{ Str::slug($attrName) }}" data-val="{{ $valueName }}">{{ $valueName }}</button>
                            @endforeach
                        </div>
                        <input type="hidden" id="varVal{{ Str::slug($attrName) }}" value="">
                    </div>
                    @endforeach
                </div>
                @endif

                <!-- Trust badges + Delivery -->
                <div class="mt-5 grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div class="trust-badge">
                        <i class="bi bi-truck text-halal-green text-lg"></i>
                        <div><p class="font-medium text-gray-800 text-sm">Free Delivery</p>
                        <p class="text-gray-500 text-xs">On orders over ৳500</p></div>
                    </div>
                    <div class="trust-badge">
                        <i class="bi bi-arrow-return-left text-halal-green text-lg"></i>
                        <div><p class="font-medium text-gray-800 text-sm">Easy Returns</p>
                        <p class="text-gray-500 text-xs">30-day return policy</p></div>
                    </div>
                    <div class="trust-badge">
                        <i class="bi bi-shield-check text-halal-green text-lg"></i>
                        <div><p class="font-medium text-gray-800 text-sm">Secure Payment</p>
                        <p class="text-gray-500 text-xs">SSL encrypted checkout</p></div>
                    </div>
                    <div class="trust-badge">
                        <i class="bi bi-award text-halal-green text-lg"></i>
                        <div><p class="font-medium text-gray-800 text-sm">100% Authentic</p>
                        <p class="text-gray-500 text-xs">Genuine products guaranteed</p></div>
                    </div>
                </div>

                <!-- Add to Cart Bar -->
                <div class="mt-6 pt-5 border-t border-gray-100">
                    <div class="flex flex-wrap items-center gap-3">
                        <div class="flex items-center border-2 border-gray-200 rounded-xl overflow-hidden">
                            <button onclick="qtyChange(-1)" class="px-3 py-2.5 hover:bg-gray-100 transition-colors text-gray-600"><i class="bi bi-dash"></i></button>
                            <input type="number" id="qty-detail" value="1" min="1" max="{{ $product->quantity }}"
                                   class="w-14 text-center border-x border-gray-200 py-2.5 text-sm font-semibold [appearance:textfield] [&::-webkit-inner-spin-button]:appearance-none [&::-webkit-outer-spin-button]:appearance-none">
                            <button onclick="qtyChange(1)" class="px-3 py-2.5 hover:bg-gray-100 transition-colors text-gray-600"><i class="bi bi-plus"></i></button>
                        </div>

                        @if($product->quantity > 0)
                        <button onclick="addToCart({{ $product->id }})"
                                class="flex-1 min-w-[180px] bg-halal-green text-white font-semibold py-3 px-6 rounded-xl hover:text-amber-400 transition-all flex items-center justify-center gap-2 shadow-sm hover:shadow-md active:scale-[0.98]">
                            <i class="bi bi-cart-plus text-lg"></i> Add to Cart
                        </button>
                        <button onclick="buyNow({{ $product->id }})"
                                class="flex-1 min-w-[140px] bg-amber-500 text-white font-semibold py-3 px-6 rounded-xl hover:bg-amber-600 transition-all flex items-center justify-center gap-2 shadow-sm hover:shadow-md active:scale-[0.98]">
                            <i class="bi bi-bag-check text-lg"></i> Buy Now
                        </button>
                        @else
                        <button disabled class="flex-1 min-w-[180px] bg-gray-300 text-gray-500 font-semibold py-3 px-6 rounded-xl cursor-not-allowed flex items-center justify-center gap-2">
                            <i class="bi bi-x-circle"></i> Out of Stock
                        </button>
                        @endif

                        <button id="wishlist-btn-{{ $product->id }}" onclick="addToWishlist({{ $product->id }})"
                                class="w-12 h-12 flex items-center justify-center border-2 rounded-xl hover:border-red-300 hover:text-red-500 hover:bg-red-50 transition-all {{ $isInWishlist ?? false ? 'text-red-500 border-red-300 bg-red-50' : 'border-gray-200 text-gray-400' }}"
                                title="Add to Wishlist">
                            <i class="bi bi-heart text-lg"></i>
                        </button>

                        <div class="relative group/share">
                            <button class="w-12 h-12 flex items-center justify-center border-2 border-gray-200 rounded-xl hover:border-blue-300 hover:text-blue-500 hover:bg-blue-50 transition-all text-gray-400"
                                    title="Share">
                                <i class="bi bi-share text-lg"></i>
                            </button>
                            <div class="absolute right-0 top-full mt-2 bg-white rounded-xl shadow-xl border border-gray-100 p-2 opacity-0 invisible group-hover/share:opacity-100 group-hover/share:visible transition-all z-20 flex gap-1">
                                <a href="https://wa.me/?text={{ urlencode($product->name.' - '.request()->url()) }}" target="_blank"
                                   class="w-10 h-10 flex items-center justify-center rounded-lg hover:bg-green-50 text-green-600 transition-colors"><i class="bi bi-whatsapp"></i></a>
                                <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode(request()->url()) }}" target="_blank"
                                   class="w-10 h-10 flex items-center justify-center rounded-lg hover:bg-blue-50 text-blue-600 transition-colors"><i class="bi bi-facebook"></i></a>
                                <button onclick="copyLink()"
                                        class="w-10 h-10 flex items-center justify-center rounded-lg hover:bg-gray-100 text-gray-600 transition-colors"><i class="bi bi-link-45deg"></i></button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @php $defaultTab = $product->long_description ? 'desc' : (!empty($product->specs) ? 'specs' : 'desc'); @endphp

        <!-- ★ Tabs: Description | Specs | Reviews | Q&A -->
        <div class="border-t border-gray-100">
            <div class="px-4 sm:px-6 lg:px-8">
                <div class="flex overflow-x-auto -mb-px gap-1 border-b border-gray-100" id="tabHeaders">
                    @if($product->long_description)
                    <button class="tab-btn {{ $defaultTab === 'desc' ? 'active' : '' }}" data-tab="desc"><i class="bi bi-file-text me-1.5"></i>Description</button>
                    @endif
                    @if($product->long_description || !empty($product->specs))
                    <button class="tab-btn {{ $defaultTab === 'specs' ? 'active' : '' }}" data-tab="specs"><i class="bi bi-list-check me-1.5"></i>Specifications</button>
                    @endif
                    <button class="tab-btn" data-tab="reviews"><i class="bi bi-chat-quote me-1.5"></i>Reviews ({{ $product->approved_reviews_count ?? 0 }})</button>
                    <button class="tab-btn" data-tab="qa"><i class="bi bi-question-circle me-1.5"></i>Q&A</button>
                </div>

                <!-- Tab: Description -->
                @if($product->long_description)
                <div class="tab-pane {{ $defaultTab === 'desc' ? 'active' : '' }} py-6" id="tab-desc">
                    <div class="prose max-w-none text-gray-600 leading-relaxed">
                        {!! format_product_description($product->long_description) !!}
                    </div>
                </div>
                @endif

                <!-- Tab: Specifications -->
                @if($product->long_description || !empty($product->specs))
                <div class="tab-pane py-6" id="tab-specs">
                    <div class="max-w-2xl">
                        <table class="w-full text-sm">
                            <tbody>
                                @foreach([['Product Code', $product->product_code ?? 'N/A'], ['Category', $product->category->name ?? 'N/A'], ['Stock', $product->quantity > 0 ? $product->quantity.' units' : 'Out of Stock'], ['Weight', $product->weight ? $product->weight.' kg' : 'N/A'], ['Dimensions', $product->dimensions ?? 'N/A']] as $spec)
                                <tr class="border-b border-gray-100">
                                    <td class="py-2.5 pr-6 font-medium text-gray-700 w-40">{{ $spec[0] }}</td>
                                    <td class="py-2.5 text-gray-600">{{ $spec[1] }}</td>
                                </tr>
                                @endforeach
                                @if(!empty($product->specs))
                                    @foreach($product->specs as $spec)
                                    @if(!empty($spec['key']) && !empty($spec['value']))
                                    <tr class="border-b border-gray-100">
                                        <td class="py-2.5 pr-6 font-medium text-gray-700 w-40">{{ $spec['key'] }}</td>
                                        <td class="py-2.5 text-gray-600">{{ $spec['value'] }}</td>
                                    </tr>
                                    @endif
                                    @endforeach
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
                @endif

                <!-- Tab: Reviews -->
                <div class="tab-pane py-6" id="tab-reviews">
                    @include('themes.general.partials.product-reviews', ['product' => $product, 'reviews' => $reviews ?? collect()])
                </div>

                <!-- Tab: Q&A -->
                <div class="tab-pane py-6" id="tab-qa">
                    @include('themes.general.partials.product-qa', ['product' => $product])
                </div>
            </div>
        </div>
    </div>

    <!-- Related Products -->
    @php $relatedProducts = $relatedProducts ?? collect(); @endphp
    <div class="mt-10">
        <div class="flex items-center justify-between mb-5">
            <h2 class="text-xl font-bold text-gray-800 flex items-center gap-2">
                <i class="bi bi-grid-3x3-gap text-halal-green"></i> Related Products
            </h2>
            <span class="text-sm text-gray-500">{{ $relatedProducts->count() }} items</span>
        </div>
        @if($relatedProducts->count() > 0)
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-4">
            @foreach($relatedProducts as $rp)
                @include('themes.general.partials.product-card', ['product' => $rp])
            @endforeach
        </div>
        @else
        <div class="text-center py-8 text-gray-500">
            <i class="bi bi-inbox text-4xl mb-2"></i>
            <p>No related products found</p>
        </div>
        @endif
    </div>

</div>
</div>

<!-- Lightbox -->
<div class="fixed inset-0 z-[9999] bg-black/95 hidden items-center justify-center" id="lightbox" onclick="closeLightbox()">
    <button class="absolute top-4 right-6 text-white text-3xl hover:opacity-70 transition-opacity z-10" onclick="closeLightbox()">&times;</button>
    
    <div class="relative max-w-[90vw] max-h-[90vh] overflow-hidden" onclick="event.stopPropagation()">
        <img src="" alt="" class="max-w-full max-h-[80vh] object-contain rounded-lg transition-transform duration-200" id="lbImg">
    </div>
    
    <div class="absolute bottom-6 left-1/2 -translate-x-1/2 flex gap-3">
        <button class="bg-white/20 text-white px-4 py-2 rounded-lg hover:bg-white/30 transition-colors text-sm" onclick="event.stopPropagation();lbZoomIn()"><i class="bi bi-zoom-in me-1"></i>Zoom In</button>
        <button class="bg-white/20 text-white px-4 py-2 rounded-lg hover:bg-white/30 transition-colors text-sm" onclick="event.stopPropagation();lbZoomOut()"><i class="bi bi-zoom-out me-1"></i>Zoom Out</button>
        <button class="bg-white/20 text-white px-4 py-2 rounded-lg hover:bg-white/30 transition-colors text-sm" onclick="event.stopPropagation();lbReset()"><i class="bi bi-arrows-angle-contract me-1"></i>Reset</button>
    </div>
</div>

@push('scripts')
<script>
/* ════════════ Gallery Zoom ════════════ */
let currentZoomSrc = '{{ $imgUrl }}';
let isZoomed = false;

function changeImage(el, src) {
    document.getElementById('mainImg').style.backgroundImage = "url('"+src+"')";
    currentZoomSrc = src;
    document.querySelectorAll('.gallery-thumb').forEach(t => t.classList.remove('active'));
    el.classList.add('active');
}

function scrollGallery(direction) {
    const container = document.getElementById('galleryThumbsContainer');
    if (!container) return;
    const scrollAmount = 90;
    if (direction === 'up') {
        container.scrollBy({ top: -scrollAmount, behavior: 'smooth' });
    } else {
        container.scrollBy({ top: scrollAmount, behavior: 'smooth' });
    }
}

function zoomIn() {
    document.getElementById('mainImageContainer').classList.add('zoomed');
    isZoomed = true;
}

function zoomOut() {
    document.getElementById('mainImageContainer').classList.remove('zoomed');
    isZoomed = false;
}

function zoomMove(e) {
    if (!isZoomed) return;
    const c = document.getElementById('mainImageContainer'), r = c.getBoundingClientRect();
    const x = e.clientX - r.left, y = e.clientY - r.top;
    const pctX = (x / r.width) * 100, pctY = (y / r.height) * 100;
    document.getElementById('mainImg').style.transformOrigin = pctX + '% ' + pctY + '%';
}

/* ════════════ Qty ════════════ */
function qtyChange(d) {
    const i = document.getElementById('qty-detail');
    if (i) {
        let v = parseInt(i.value) + d;
        const max = parseInt(i.max) || 99;
        if (v < 1) v = 1; if (v > max) v = max;
        i.value = v;
        updateTotalPrice();
    }
}

function updateTotalPrice() {
    const qtyInput = document.getElementById('qty-detail');
    const qty = parseInt(qtyInput?.value) || 1;
    let totalAdj = selectedColorAdj || 0;
    Object.values(selectedAttrs).forEach(a => { 
        totalAdj += parseFloat(a.price) || 0; 
    });
    const unitPrice = basePrice + totalAdj;
    const totalPrice = unitPrice * qty;
    const displayPriceEl = document.getElementById('displayPrice');
    if (displayPriceEl) displayPriceEl.textContent = '৳' + totalPrice.toLocaleString();
}

/* ════════════ Quantity Controls ════════════ */
function decreaseQty() {
    const qtyInput = document.getElementById('qty');
    if (qtyInput) {
        let val = parseInt(qtyInput.value) || 1;
        if (val > 1) {
            qtyInput.value = val - 1;
        }
    }
}

function increaseQty() {
    const qtyInput = document.getElementById('qty');
    if (qtyInput) {
        let val = parseInt(qtyInput.value) || 1;
        if (val < 99) {
            qtyInput.value = val + 1;
        }
    }
}

/* ════════════ Add to Cart ════════════ */
function addToCart(id) {
    const qtyInput = document.getElementById('qty-detail');
    let qty = 1;
    if (qtyInput && qtyInput.value) {
        qty = parseInt(qtyInput.value) || 1;
    }
    
    // Limit quantity to max 99
    if (qty > 99) qty = 99;
    if (qty < 1) qty = 1;
    
    console.log('addToCart - quantity:', qty);
    
    const displayPriceEl = document.getElementById('displayPrice');
    const priceText = displayPriceEl ? displayPriceEl.textContent.replace(/[^\d]/g, '') : '0';
    const price = parseInt(priceText) || 0;

    const colorIdEl = document.getElementById('selColorId');
    const colorId = colorIdEl ? colorIdEl.value : null;

    // Send attributes as array of value_ids for backend compatibility
    const attributes = Object.keys(selectedAttrs)
        .map(attr => selectedAttrs[attr]?.vid)
        .filter(vid => vid);

    console.log('addToCart - selectedAttrs:', selectedAttrs, 'attributes:', attributes);

    const payload = {
        product_id: id,
        quantity: qty,
        price: price,
        color_id: colorId,
        attributes: attributes,
        variant_image: currentVariantImage || null
    };

    fetch('/cart/add', {
        method: 'POST',
        headers: {'Content-Type':'application/json','X-CSRF-TOKEN':document.querySelector('meta[name="csrf-token"]').content,'Accept':'application/json'},
        body: JSON.stringify(payload)
}).then(r=>r.json()).then(d=>{
        if (d.success) { showToast('Added to cart!','success');
            document.querySelectorAll('.cart-count').forEach(el => el.textContent = d.cart_count);
            if (typeof loadCart === 'function') loadCart();
            if (typeof updateCartUI === 'function') updateCartUI();
        } else showToast(d.message||'Error','error');
    }).catch(()=>showToast('Error adding to cart','error'));
}

/* ════════════ Buy Now ════════════ */
function buyNow(id) {
    const qtyInput = document.getElementById('qty-detail');
    let qty = 1;
    if (qtyInput && qtyInput.value) {
        qty = parseInt(qtyInput.value) || 1;
    }
    if (qty > 99) qty = 99;
    if (qty < 1) qty = 1;

    const displayPriceEl = document.getElementById('displayPrice');
    const priceText = displayPriceEl ? displayPriceEl.textContent.replace(/[^\d]/g, '') : '0';
    const price = parseInt(priceText) || 0;

    const colorIdEl = document.getElementById('selColorId');
    const colorId = colorIdEl ? colorIdEl.value : null;

    const attributes = Object.keys(selectedAttrs)
        .map(attr => selectedAttrs[attr]?.vid)
        .filter(vid => vid);

    const payload = {
        product_id: id,
        quantity: qty,
        price: price,
        color_id: colorId,
        attributes: attributes,
        variant_image: currentVariantImage || null
    };

    fetch('/cart/add', {
        method: 'POST',
        headers: {'Content-Type':'application/json','X-CSRF-TOKEN':document.querySelector('meta[name="csrf-token"]').content,'Accept':'application/json'},
        body: JSON.stringify(payload)
    }).then(r=>r.json()).then(d=>{
        if (d.success) {
            window.location.href = '{{ route("checkout.index") }}';
        } else {
            showToast(d.message||'Error','error');
        }
    }).catch(()=>showToast('Error processing order','error'));
}

/* ════════════ Wishlist ════════════ */
function addToWishlist(id) {
    fetch('/api/wishlist/toggle', {
        method:'POST', headers:{'Content-Type':'application/json','X-CSRF-TOKEN':document.querySelector('meta[name="csrf-token"]').content,'Accept':'application/json'},
        body: JSON.stringify({product_id:id})
    }).then(r=>r.json()).then(d=>{
        if (d.success) {
            showToast(d.message,'success');
            // Update header wishlist count
            const wc = document.querySelector('.wishlist-count');
            if (wc) {
                let currentCount = parseInt(wc.textContent) || 0;
                wc.textContent = d.added ? currentCount + 1 : Math.max(0, currentCount - 1);
                wc.classList.remove('hidden');
            }
            // Update button visual state
            const btn = document.getElementById('wishlist-btn-' + id);
            if (btn) {
                if (d.added) {
                    btn.classList.add('text-red-500', 'border-red-300', 'bg-red-50');
                    btn.classList.remove('text-gray-400', 'border-gray-200');
                } else {
                    btn.classList.remove('text-red-500', 'border-red-300', 'bg-red-50');
                    btn.classList.add('text-gray-400', 'border-gray-200');
                }
            }
        }
        else if (d.login_required) { showToast('Please login','error'); setTimeout(()=>window.location.href='{{ route("login") }}',1500); }
    }).catch(()=>showToast('Error','error'));
}

/* ════════════ Toast ════════════ */
function showToast(msg, type='info') {
    const t = document.createElement('div');
    t.className = 'fixed bottom-4 right-4 z-50 px-5 py-3 rounded-xl text-white font-medium shadow-lg '+(type==='success'?'bg-green-600':'bg-red-600')+' animate-fade-in';
    t.textContent = msg;
    document.body.appendChild(t);
    setTimeout(()=>t.remove(),3000);
}

/* ════════════ Share Link Copy ════════════ */
function copyLink() {
    navigator.clipboard.writeText(window.location.href).then(()=>showToast('Link copied!','success'));
}

/* ════════════ Tabs ════════════ */
document.addEventListener('DOMContentLoaded', function(){
    document.querySelectorAll('.tab-btn').forEach(btn => {
        btn.addEventListener('click', function(){
            document.querySelectorAll('.tab-btn').forEach(b=>b.classList.remove('active'));
            document.querySelectorAll('.tab-pane').forEach(p=>p.classList.remove('active'));
            this.classList.add('active');
            const pane = document.getElementById('tab-'+this.dataset.tab);
            if (pane) pane.classList.add('active');
        });
    });
    updateTotalPrice();
});

/* ════════════ Variables (declared first) ════════════ */
let selectedColorAdj = 0;
let selectedAttrs = {};
const basePrice = {{ $basePrice }};

/* ════════════ Price Calculation ════════════ */
function updatePrice() {
    let totalAdj = selectedColorAdj || 0;
    Object.values(selectedAttrs).forEach(a => { 
        totalAdj += parseFloat(a.price) || 0; 
    });
    const newPrice = basePrice + totalAdj;
    const displayPriceEl = document.getElementById('displayPrice');
    if (displayPriceEl) displayPriceEl.textContent = '৳' + newPrice.toLocaleString();
    
    // Update base price display
    const basePriceDisplay = document.getElementById('basePriceDisplay');
    if (basePriceDisplay) basePriceDisplay.textContent = '৳' + basePrice.toLocaleString();
    
    // Update selected attributes list
    const attrsListEl = document.getElementById('selectedAttrsList');
    const colorRowEl = document.getElementById('selectedColorRow');
    const colorDisplayEl = document.getElementById('selectedColorDisplay');
    let attrsHtml = '';
    
    // Add color if selected
    const colorId = document.getElementById('selColorId')?.value;
    const colorNameEl = document.getElementById('selColorName');
    if (colorId && colorNameEl) {
        const colorName = colorNameEl.textContent;
        if (colorName && colorName !== 'Select') {
            colorRowEl.style.display = 'flex';
            const colorAdj = selectedColorAdj || 0;
            const priceStr = colorAdj !== 0 ? (colorAdj > 0 ? ' (+৳' + colorAdj.toLocaleString() + ')' : ' (৳' + colorAdj.toLocaleString() + ')') : '';
            colorDisplayEl.innerHTML = colorName + '<span class="text-halal-green text-xs">' + priceStr + '</span>';
        } else {
            colorRowEl.style.display = 'none';
        }
    } else {
        colorRowEl.style.display = 'none';
    }
    
    // Add attributes
    Object.values(selectedAttrs).forEach(a => {
        if (a && a.val) {
            const price = parseFloat(a.price) || 0;
            attrsHtml += '<div class="flex items-baseline gap-2">';
            attrsHtml += '<span class="text-gray-600">' + (a.attrName || a.attr) + ':</span>';
            attrsHtml += '<span class="font-semibold text-halal-green">' + a.val + (price !== 0 ? ' <span class="text-xs">(' + (price > 0 ? '+' : '') + '৳' + price.toLocaleString() + ')</span>' : '') + '</span>';
            attrsHtml += '</div>';
        }
    });
    
    if (attrsListEl) attrsListEl.innerHTML = attrsHtml;
    updateTotalPrice();
}

/* ════════════ Stock & SKU Update ════════════ */
const productData = {
    stock: @json($product->quantity),
    sku: @json($product->sku ?? ''),
    productCode: @json($product->product_code ?? ''),
    attrValues: @json($attributeOptions ?? []),
    colorValues: @json($colorOptions ?? []),
};

function updateStockSku() {
    // Try to find matching stock/sku from selected attributes and color
    let stock = productData.stock;
    let sku = productData.sku;

    // Check color stock/sku first
    const colorId = document.getElementById('selColorId')?.value;
    if (colorId) {
        const colorVal = productData.colorValues.find(c => String(c.id) === String(colorId));
        if (colorVal) {
            if (colorVal.quantity !== undefined && colorVal.quantity !== null) {
                stock = parseInt(colorVal.quantity) || 0;
            }
            if (colorVal.sku) {
                sku = colorVal.sku;
            }
        }
    }

    // Check attribute stock/sku (attribute values override color)
    Object.keys(selectedAttrs).forEach(attr => {
        const attrVal = selectedAttrs[attr];
        if (attrVal && attrVal.vid) {
            // Search through all attribute options for this value
            Object.keys(productData.attrValues).forEach(attrName => {
                const options = productData.attrValues[attrName];
                if (options) {
                    const option = options.find(o => String(o.id) === String(attrVal.vid));
                    if (option) {
                        if (option.quantity !== undefined && option.quantity !== null) {
                            stock = parseInt(option.quantity) || 0;
                        }
                        if (option.sku) {
                            sku = option.sku;
                        }
                    }
                }
            });
        }
    });

    // Update stock display
    const stockEl = document.getElementById('stockCount');
    if (stockEl) {
        stockEl.textContent = '(' + stock + ' available)';
    }

    // Update SKU display
    const skuEl = document.querySelector('.text-gray-400');
    if (skuEl && sku) {
        const codeEl = skuEl.querySelector('span');
        if (codeEl) codeEl.textContent = sku;
    }

    // Update add to cart button
    const addBtn = document.querySelector('button[onclick*="addToCart"]');
    if (addBtn) {
        if (stock > 0) {
            addBtn.disabled = false;
            addBtn.classList.remove('bg-gray-300', 'text-gray-500', 'cursor-not-allowed');
            addBtn.classList.add('bg-halal-green', 'text-white');
            addBtn.innerHTML = '<i class="bi bi-cart-plus text-lg"></i> Add to Cart';
        } else {
            addBtn.disabled = true;
            addBtn.classList.remove('bg-halal-green', 'text-white');
            addBtn.classList.add('bg-gray-300', 'text-gray-500', 'cursor-not-allowed');
            addBtn.innerHTML = '<i class="bi bi-x-circle"></i> Out of Stock';
        }
    }

    // Update qty max
    const qtyInput = document.getElementById('qty');
    if (qtyInput) {
        qtyInput.max = stock;
        if (parseInt(qtyInput.value) > stock) {
            qtyInput.value = stock;
        }
    }
}

/* ════════════ Variant Image Loader ════════════ */
const productId = {{ $product->id }};
let currentVariantImage = null;

async function loadVariantImage() {
    const colorId = document.getElementById('selColorId')?.value;
    const parts = [];

    // Collect selected attributes using actual attribute name (not slug)
    Object.keys(selectedAttrs).forEach(slug => {
        const attrData = selectedAttrs[slug];
        if (attrData?.vid) {
            const attrName = attrData.attrName || slug;
            parts.push(attrName + '_' + attrData.vid);
        }
    });

    // Add color if selected (use 'color' as type to match DB keys)
    if (colorId) {
        parts.push('color_' + colorId);
    }

    // Generate combination key (sort alphabetically to match DB)
    parts.sort();
    const key = parts.join('_');
    
    if (!key) {
        console.log('loadVariantImage: no key');
        return;
    }

    console.log('loadVariantImage: key=' + key + ' colorId=' + colorId + ' selectedAttrs:', selectedAttrs);
    
    try {
        console.log('loadVariantImage: fetching /api/product/' + productId + '/variant-image?key=' + encodeURIComponent(key));
        const response = await fetch('/api/product/' + productId + '/variant-image?key=' + encodeURIComponent(key));
        const data = await response.json();
        console.log('loadVariantImage: response data:', JSON.stringify(data));
        console.log('loadVariantImage: mainImg style:', document.getElementById('mainImg')?.style?.backgroundImage);
        
        if (data.image) {
            currentVariantImage = data.image;
            // Update main image if different
            const mainImg = document.getElementById('mainImg');
            if (mainImg && mainImg.style.backgroundImage !== "url('" + data.image + "')") {
                mainImg.style.backgroundImage = "url('" + data.image + "')";
                currentZoomSrc = data.image;
            }
            // Highlight matching variant thumbnail (case-insensitive comparison)
            document.querySelectorAll('.variant-thumb').forEach(thumb => {
                thumb.classList.remove('active');
                if (thumb.dataset.key && thumb.dataset.key.toLowerCase() === key.toLowerCase()) {
                    thumb.classList.add('active');
                    // Remove active from non-variant thumbnails
                    document.querySelectorAll('.gallery-thumb:not(.variant-thumb)').forEach(t => t.classList.remove('active'));
                    // Auto-scroll the selected thumbnail into view
                    setTimeout(() => {
                        thumb.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                    }, 50);
                }
            });
        } else {
            // No variant image found - reset to default
            currentVariantImage = null;
            // Remove active from variant thumbnails
            document.querySelectorAll('.variant-thumb').forEach(thumb => thumb.classList.remove('active'));
            // Set first non-variant thumbnail as active
            const firstThumb = document.querySelector('.gallery-thumb:not(.variant-thumb)');
            if (firstThumb) {
                document.querySelectorAll('.gallery-thumb').forEach(t => t.classList.remove('active'));
                firstThumb.classList.add('active');
                // Auto-scroll to first thumbnail
                setTimeout(() => {
                    firstThumb.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                }, 50);
            }
        }
    } catch (error) {
        console.error('Error loading variant image:', error);
    }
}

/* ════════════ Color Selection ════════════ */
document.querySelectorAll('.color-btn').forEach(b=>{
    b.addEventListener('click', function(){
        document.querySelectorAll('.color-btn').forEach(c=>{c.classList.remove('ring-2','ring-halal-green','ring-offset-2'); c.classList.add('border-gray-300');});
        this.classList.add('ring-2','ring-halal-green','ring-offset-2'); this.classList.remove('border-gray-300');
        document.getElementById('selColorId').value = this.dataset.id;
        document.getElementById('selColorName').textContent = this.dataset.name;
        selectedColorAdj = parseFloat(this.dataset.adj) || 0;
        if (this.dataset.img) changeImage(this, this.dataset.img);
        updatePrice();
        updateStockSku();
        loadVariantImage();
    });
});

// Auto-select first color on load
const firstColor = document.querySelector('.color-btn');
if (firstColor) {
    firstColor.click();
    updatePrice();
}

/* ════════════ Attribute Selection ════════════ */
document.querySelectorAll('.attr-btn').forEach(b=>{
    b.addEventListener('click', function(){
        const attr = this.dataset.attr;
        document.querySelectorAll('.attr-btn[data-attr="'+attr+'"]').forEach(c=>{c.classList.remove('border-halal-green','bg-halal-green/10'); c.classList.add('border-gray-300');});
        this.classList.remove('border-gray-300'); this.classList.add('border-halal-green','bg-halal-green/10');
        const priceVal = parseFloat(this.dataset.price) || 0;
        console.log('Attr click - data-price:', this.dataset.price, 'parsed:', priceVal);
        selectedAttrs[attr] = { val: this.dataset.val, vid: this.dataset.vid, price: priceVal, attrName: this.dataset.attrName || attr };
        const attrInput = document.getElementById('attrVal'+attr);
        if (attrInput) attrInput.value = this.dataset.vid;
        const sel = document.getElementById('attrSel'+attr);
        if (sel) sel.textContent = this.dataset.val;
        updateStockSku();
        if (this.dataset.img) changeImage(this, this.dataset.img);
        updatePrice();
        loadVariantImage();
    });
});

// Auto-select first option of each attribute on load
document.querySelectorAll('.attr-btn').forEach(b => {
    const attr = b.dataset.attr;
    if (!selectedAttrs[attr]) {
        b.click();
    }
});
updatePrice();
loadVariantImage();

/* ════════════ Variant Selection ════════════ */
let selVariants = {};
document.querySelectorAll('.var-btn').forEach(b=>{
    b.addEventListener('click', function(){
        const attr = this.dataset.attr, val = this.dataset.val;
        selVariants[attr] = val;
        document.querySelectorAll('.var-btn[data-attr="'+attr+'"]').forEach(c=>{c.classList.remove('border-halal-green','bg-halal-green/10'); c.classList.add('border-gray-300');});
        this.classList.remove('border-gray-300'); this.classList.add('border-halal-green','bg-halal-green/10');
        document.getElementById('varVal'+attr).value = val;
        const sel = document.getElementById('varSel'+attr);
        if (sel) sel.textContent = val;
    });
});

/* ════════════ Sticky Add to Cart ════════════ */
window.addEventListener('scroll', function(){
    const el = document.getElementById('stickyAddToCart');
    if (!el) return;
    const rect = el.previousElementSibling ? el.previousElementSibling.getBoundingClientRect() : {bottom:0};
    // Show when add-to-cart button is out of view
    const addBtn = document.querySelector('button[onclick*="addToCart"]');
    if (addBtn) {
        const btnRect = addBtn.getBoundingClientRect();
        el.style.display = btnRect.top < -100 ? 'flex' : 'none';
    }
});

/* ════════════ Lightbox ════════════ */
let lbZoom = 1;
function openLightbox() {
    const src = currentZoomSrc;
    document.getElementById('lightbox').classList.remove('hidden'); document.getElementById('lightbox').classList.add('flex');
    document.getElementById('lbImg').src = src;
    document.body.style.overflow = 'hidden'; lbZoom = 1;
}
function closeLightbox() {
    document.getElementById('lightbox').classList.add('hidden'); document.getElementById('lightbox').classList.remove('flex');
    document.getElementById('lbImg').style.transform = 'scale(1)';
    document.getElementById('lbImg').style.transformOrigin = 'center center';
    lbZoom = 1; document.body.style.overflow = '';
}
function lbZoomIn() { if (lbZoom < 4) { lbZoom += 0.5; updateLbZoom(); } }
function lbZoomOut() { if (lbZoom > 1) { lbZoom -= 0.5; updateLbZoom(); } }
function lbReset() { lbZoom = 1; updateLbZoom(); }
function updateLbZoom() {
    const img = document.getElementById('lbImg');
    if (lbZoom === 1) {
        img.style.transform = 'scale(1)';
        img.style.transformOrigin = 'center center';
    } else {
        img.style.transform = 'scale('+lbZoom+')';
        img.style.transformOrigin = 'center center';
    }
}
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') { closeLightbox(); closeQuestionModal(); closeReviewModal(); }
    if (document.getElementById('lightbox').classList.contains('flex')) {
        if (e.key === '+' || e.key === '=') lbZoomIn();
        if (e.key === '-') lbZoomOut();
    }
});

/* ════════════ Modals ════════════ */
function openQuestionModal() {
    const m = document.getElementById('askQuestionModal'); if(m){m.style.display='flex';document.body.style.overflow='hidden';}
}
function closeQuestionModal() {
    const m = document.getElementById('askQuestionModal'); if(m){m.style.display='none';document.body.style.overflow='';}
}
function openReviewModal() {
    const m = document.getElementById('reviewModal'); if(m){m.style.display='flex';document.body.style.overflow='hidden';}
}
function closeReviewModal() {
    const m = document.getElementById('reviewModal'); if(m){m.style.display='none';document.body.style.overflow='';}
}
</script>
@endpush

<!-- Review Modal -->
@auth
<div class="popup-modal" id="reviewModal" style="display:none;">
    <div class="popup-overlay" onclick="closeReviewModal()"></div>
    <div class="popup-content">
        <div class="popup-header popup-header-review">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-white/20 rounded-lg flex items-center justify-center"><i class="bi bi-pencil-square text-white text-xl"></i></div>
                <div><h5 class="text-white font-bold text-lg">Submit Review</h5><p class="text-white/70 text-sm">Share your experience</p></div>
            </div>
            <button class="popup-close" onclick="closeReviewModal()"><i class="bi bi-x-lg"></i></button>
        </div>
        <form action="{{ route('reviews.store') }}" method="POST" id="reviewForm">
            @csrf
            <input type="hidden" name="product_id" value="{{ $product->id }}">
            <div class="popup-body">
                <div class="flex items-center gap-3 p-3 bg-gray-50 rounded-xl mb-4">
                    <div class="w-12 h-12 rounded-lg overflow-hidden flex-shrink-0 border">@if($product->image)<img src="{{ asset($product->image) }}" class="w-full h-full object-cover">@else<div class="w-full h-full bg-gray-200 flex items-center justify-center"><i class="bi bi-box text-gray-400"></i></div>@endif</div>
                    <div><p class="text-xs text-gray-500 uppercase tracking-wide">Reviewing</p><h6 class="font-semibold text-gray-800 truncate">{{ $product->name }}</h6></div>
                </div>
                <div class="mb-4">
                    <label class="text-sm font-semibold text-gray-700 mb-2 block">Your Rating <span class="text-red-500">*</span></label>
                    <div class="star-rating-container p-3 bg-amber-50 rounded-xl border border-amber-100">
                        <div class="star-rating flex justify-center gap-2" id="starRating">
                            @for($i=1;$i<=5;$i++)
                            <button type="button" class="star-btn w-10 h-10 rounded-lg border-2 border-gray-200 hover:border-halal-gold hover:bg-yellow-100 hover:scale-110 transition-all duration-300 flex items-center justify-center" data-rating="{{ $i }}">
                                <i class="bi bi-star text-gray-300 hover:text-halal-gold text-lg transition-colors"></i>
                            </button>
                            @endfor
                        </div>
                        <p class="text-center text-sm text-gray-500 mt-2" id="ratingText">Click to rate</p>
                    </div>
                    <input type="hidden" name="rating" id="ratingInput" required>
                </div>
                <div class="mb-4">
                    <label class="text-sm font-semibold text-gray-700 mb-1.5 block">Title <span class="text-red-500">*</span></label>
                    <input type="text" name="title" class="w-full border-2 border-gray-200 rounded-xl px-4 py-2.5 focus:ring-2 focus:ring-amber-200 focus:border-halal-gold transition-all text-sm" placeholder="Summarize your experience" required>
                </div>
                <div class="mb-4">
                    <label class="text-sm font-semibold text-gray-700 mb-1.5 block">Review <span class="text-red-500">*</span></label>
                    <textarea name="comment" class="w-full border-2 border-gray-200 rounded-xl px-4 py-3 focus:ring-2 focus:ring-amber-200 focus:border-halal-gold transition-all resize-none text-sm" rows="3" placeholder="What did you like or dislike?" required></textarea>
                </div>
                <div class="p-3 bg-blue-50 rounded-xl border border-blue-100">
                    <p class="text-xs font-medium text-blue-800 mb-1"><i class="bi bi-info-circle me-1"></i>Guidelines</p>
                    <ul class="text-xs text-blue-600 space-y-0.5"><li><i class="bi bi-check2 me-1"></i>Be honest and specific</li><li><i class="bi bi-check2 me-1"></i>Avoid offensive content</li></ul>
                </div>
            </div>
            <div class="popup-footer flex justify-end gap-3">
                <button type="button" class="popup-btn-cancel" onclick="closeReviewModal()">Cancel</button>
                <button type="submit" class="popup-btn-submit popup-btn-review"><i class="bi bi-send me-2"></i>Submit Review</button>
            </div>
        </form>
    </div>
</div>
@endauth

<!-- Q&A Modal -->
<div class="popup-modal" id="askQuestionModal" style="display:none;">
    <div class="popup-overlay" onclick="closeQuestionModal()"></div>
    <div class="popup-content">
        <div class="popup-header">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-white/20 rounded-lg flex items-center justify-center"><i class="bi bi-question-circle text-white text-xl"></i></div>
                <div><h5 class="text-white font-bold text-lg">Ask a Question</h5><p class="text-white/70 text-sm">Get answers from our team</p></div>
            </div>
            <button class="popup-close" onclick="closeQuestionModal()"><i class="bi bi-x-lg"></i></button>
        </div>
        <form action="{{ route('product-qa.store') }}" method="POST">
            @csrf
            <input type="hidden" name="product_id" value="{{ $product->id }}">
            <div class="popup-body">
                <div class="flex items-center gap-3 p-3 bg-gray-50 rounded-xl mb-4">
                    <div class="w-12 h-12 rounded-lg overflow-hidden flex-shrink-0 border">@if($product->image)<img src="{{ asset($product->image) }}" class="w-full h-full object-cover">@else<div class="w-full h-full bg-gray-200 flex items-center justify-center"><i class="bi bi-box text-gray-400"></i></div>@endif</div>
                    <div><p class="text-xs text-gray-500 uppercase tracking-wide">About</p><h6 class="font-semibold text-gray-800 truncate">{{ $product->name }}</h6></div>
                </div>
                <div class="mb-4">
                    <label class="text-sm font-semibold text-gray-700 mb-2 block">Your Question <span class="text-red-500">*</span></label>
                    <textarea name="question" class="w-full border-2 border-gray-200 rounded-xl px-4 py-3 focus:ring-2 focus:ring-halal-green/20 focus:border-halal-green transition-all resize-none text-sm" rows="3" placeholder="What would you like to know?" required></textarea>
                </div>
                <div class="flex items-center justify-between p-3 bg-gray-50 rounded-xl border border-gray-200">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 bg-halal-green/10 rounded-lg flex items-center justify-center"><i class="bi bi-incognito text-halal-green"></i></div>
                        <div><p class="font-medium text-gray-700 text-sm">Post Anonymously</p><p class="text-xs text-gray-500">Your name won't be visible</p></div>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" name="is_anonymous" class="sr-only peer" value="1">
                        <div class="w-11 h-6 bg-gray-300 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-halal-green/20 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-halal-green"></div>
                    </label>
                </div>
            </div>
            <div class="popup-footer flex justify-end gap-3">
                <button type="button" class="popup-btn-cancel" onclick="closeQuestionModal()">Cancel</button>
                <button type="submit" class="popup-btn-submit"><i class="bi bi-send me-2"></i>Submit Question</button>
            </div>
        </form>
    </div>
</div>

<!-- Star rating script -->
<script>
document.addEventListener('DOMContentLoaded', function(){
    const stars = document.querySelectorAll('.star-btn'), input = document.getElementById('ratingInput'), text = document.getElementById('ratingText');
    const labels = {0:'Click to rate',1:'Poor',2:'Fair',3:'Good',4:'Very Good',5:'Excellent!'};
    function update(r, h){
        stars.forEach((b,i)=>{
            const ic = b.querySelector('i');
            if(i<r){ic.className='bi bi-star-fill text-halal-gold text-lg';b.classList.add('border-halal-gold','bg-yellow-50');b.classList.remove('border-gray-200');}
            else{ic.className='bi bi-star text-gray-300 text-lg';b.classList.remove('border-halal-gold','bg-yellow-50');b.classList.add('border-gray-200');}
        });
        if(text){text.textContent=labels[r]||labels[0];text.className='text-center text-sm '+(r>0?'font-medium text-halal-gold mt-2':'text-gray-500 mt-2');}
    }
    stars.forEach(b=>{
        b.addEventListener('click',function(){input.value=this.dataset.rating;update(parseInt(this.dataset.rating));});
        b.addEventListener('mouseenter',function(){update(parseInt(this.dataset.rating));});
    });
    const container = document.getElementById('starRating');
    if(container) container.addEventListener('mouseleave',function(){update(parseInt(input.value)||0);});
});
</script>

<!-- Include partials for reviews list and Q&A list -->
@push('scripts')
<script>
// Review voting
document.querySelectorAll('.vote-btn').forEach(b=>{
    b.addEventListener('click',function(){
        const id=this.dataset.reviewId,hlp=this.dataset.isHelpful,card=this.closest('.review-card');
        if(!card) return;
        fetch('/reviews/'+id+'/vote',{method:'POST',headers:{'Content-Type':'application/json','X-CSRF-TOKEN':document.querySelector('meta[name="csrf-token"]').content,'Accept':'application/json'},body:JSON.stringify({is_helpful:hlp==='1'})})
        .then(r=>r.json()).then(d=>{
            if(d.success){
                card.querySelector('.helpful-count').textContent=d.helpful_count;
                card.querySelector('.not-helpful-count').textContent=d.not_helpful_count;
                const hb=card.querySelector('[data-is-helpful="1"]'),nb=card.querySelector('[data-is-helpful="0"]');
                [hb,nb].forEach(b=>{b.classList.remove('border-green-500','bg-green-50','text-green-700','border-red-500','bg-red-50','text-red-700');b.classList.add('border-gray-300','hover:bg-gray-100');b.querySelector('i').className=b.querySelector('i').className.replace('-fill','');});
                const t=hlp==='1'?hb:nb;
                t.classList.remove('border-gray-300','hover:bg-gray-100');
                t.classList.add(hlp==='1'?'border-green-500 bg-green-50 text-green-700':'border-red-500 bg-red-50 text-red-700');
                t.querySelector('i').classList.add('-fill');
                showToast('Thank you!','success');
            }
        }).catch(()=>showToast('Error','error'));
    });
});

// Q&A voting
document.querySelectorAll('.qa-vote-btn').forEach(b=>{
    b.addEventListener('click',function(){
        const id=this.dataset.qaId,hlp=this.dataset.isHelpful,card=this.closest('.qa-card');
        if(!card) return;
        fetch('/product-qa/'+id+'/vote',{method:'POST',headers:{'Content-Type':'application/json','X-CSRF-TOKEN':document.querySelector('meta[name="csrf-token"]').content,'Accept':'application/json'},body:JSON.stringify({is_helpful:hlp==='1'})})
        .then(r=>r.json()).then(d=>{
            if(d.success){
                const hb=card.querySelector('[data-is-helpful="1"]'),nb=card.querySelector('[data-is-helpful="0"]');
                hb.innerHTML='<i class="bi bi-hand-thumbs-up'+(hlp==='1'?'-fill':'')+' me-1"></i>'+d.helpful_count;
                nb.innerHTML='<i class="bi bi-hand-thumbs-down'+(hlp==='0'?'-fill':'')+' me-1"></i>'+d.not_helpful_count;
                [hb,nb].forEach(b=>{b.className='inline-flex items-center px-3 py-1 rounded-full text-sm border border-gray-300 hover:bg-gray-100 transition-colors qa-vote-btn';});
                const t=hlp==='1'?hb:nb;
                t.classList.add(hlp==='1'?'border-green-500 bg-green-50 text-green-700':'border-red-500 bg-red-50 text-red-700');
                showToast('Thank you!','success');
            }
        }).catch(()=>showToast('Error','error'));
    });
});
</script>
@endpush

@endsection
