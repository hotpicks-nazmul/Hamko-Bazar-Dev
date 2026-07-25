@extends('themes.general.layouts.app')

@section('title', 'Checkout')

@section('content')
<div class="container mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold text-gray-800 mb-8">Checkout</h1>

    @if($cart->isEmpty())
    <div class="text-center py-12">
        <div class="w-24 h-24 mx-auto bg-gray-100 rounded-full flex items-center justify-center mb-4">
            <i class="bi bi-cart-x text-4xl text-gray-400"></i>
        </div>
        <h4 class="text-gray-600 font-medium mb-2">Your cart is empty</h4>
        <a href="{{ route('home') }}" class="inline-block bg-halal-green text-white px-6 py-2 rounded-full hover:text-amber-400 transition-colors">Shop Now</a>
    </div>
    @else
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8" id="checkout-content">
        <div class="lg:col-span-2">
            @php $phoneLocal = $user->phone ?? ''; $phoneLocal = ltrim($phoneLocal, '+'); if(str_starts_with($phoneLocal,'880')) $phoneLocal = '0' . substr($phoneLocal,3); elseif(str_starts_with($phoneLocal,'88')) $phoneLocal = '0' . substr($phoneLocal,2); @endphp
            @if($addresses->isNotEmpty() && auth()->check())
            <div class="bg-white p-6 rounded-lg shadow-sm mb-6 border border-halal-green">
                <h3 class="text-lg font-bold mb-4"><i class="bi bi-geo-alt text-halal-green me-2"></i>Delivery Address</h3>
                <div class="space-y-3 max-h-44 overflow-y-auto pr-1">
                     @foreach($addresses as $address)
                     @php $addrJson = json_encode(['id'=>$address->id,'first_name'=>$address->first_name,'last_name'=>$address->last_name,'phone'=>$phoneLocal,'address'=>$address->address,'city'=>$address->city,'state'=>$address->state,'postcode'=>$address->postcode,'country'=>$address->country,'email'=>$user->email]); @endphp
                     <label class="flex items-start p-3 border rounded-lg cursor-pointer hover:bg-gray-50 transition-colors {{ $loop->first ? 'border-halal-green bg-green-50' : 'border-gray-300' }}" data-address='{{ $addrJson }}'>
                         <input type="radio" name="address_choice" value="saved_{{ $address->id }}" {{ $loop->first ? 'checked' : '' }} onchange="useSavedAddress(this)" class="mt-1 mr-3">
                         <div class="flex-1">
                             <span class="font-medium text-sm">{{ $address->name }}@if($address->is_default) <span class="text-xs text-halal-green">(Default)</span>@endif</span>
                             <div class="bg-gray-50 p-3 rounded-lg mt-2 text-sm text-gray-600">
                                 <p>{{ $address->address }}, {{ $address->city }}, {{ $address->state }} - {{ $address->postcode }}</p>
                                 <p>{{ $phoneLocal }} | {{ $user->email }}</p>
                             </div>
                         </div>
                     </label>
                     @endforeach
                </div>
                <div class="mt-3">
                    <label class="flex items-center p-3 border border-gray-300 rounded-lg cursor-pointer hover:bg-gray-50 transition-colors">
                        <input type="radio" name="address_choice" value="new" onchange="showNewAddressForm()" class="mr-3">
                        <span class="font-medium text-sm">Enter a new address</span>
                    </label>
                </div>
            </div>
            @endif

            <div id="addressFormSection" style="{{ $addresses->isNotEmpty() && auth()->check() ? 'display: none;' : '' }}">
                <div class="bg-white p-6 rounded-lg shadow-sm mb-6 billing-card">
                    <h3 class="text-lg font-bold mb-4">Billing Details</h3>
                    <form id="checkoutForm">
                        @csrf
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label for="billing_first_name" class="block text-sm font-medium text-gray-700 mb-1">First Name *</label>
                                <input type="text" name="billing_first_name" id="billing_first_name" value="{{ $user->first_name ?? '' }}" required class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-halal-green">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Last Name *</label>
                                <input type="text" name="billing_last_name" id="billing_last_name" value="{{ $user->last_name ?? '' }}" required class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-halal-green">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                                <input type="email" name="billing_email" id="billing_email" value="{{ $user->email ?? '' }}" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-halal-green">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Phone *</label>
                                <div class="flex">
                                    <span class="inline-flex items-center gap-1 px-3 border border-r-0 border-gray-300 rounded-l-lg bg-gray-100 text-gray-600 text-sm select-none whitespace-nowrap">🇧🇩 +880</span>
                                    <input type="tel" name="billing_phone" id="billing_phone" value="{{ old('billing_phone', $phoneLocal) }}" required maxlength="11" oninput="this.value=this.value.replace(/\D/g,'')" class="w-full border border-gray-300 rounded-r-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-halal-green">
                                </div>
                                <span class="error-message" id="error-billing_phone"></span>
                            </div>
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Address *</label>
                                <input type="text" name="billing_address" id="billing_address" required class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-halal-green">
                                <span class="error-message" id="error-billing_address"></span>
                            </div>

                            @if($checkoutMode === 'international')
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Country *</label>
                                <select name="billing_country" id="billing_country" required class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-halal-green" onchange="onCountryChange()">
                                    <option value="">Select Country</option>
                                    @foreach($countries as $country)
                                        <option value="{{ $country->name }}">{{ $country->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            @else
                            <input type="hidden" name="billing_country" id="billing_country" value="{{ $defaultCountryName }}">
                            @endif

                            <div class="searchable-select-wrapper">
                                <label class="block text-sm font-medium text-gray-700 mb-1">State *</label>
                                <div class="searchable-select" data-select-id="billing_state">
                                    <input type="text" class="searchable-select-input w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-halal-green" placeholder="Select State" readonly autocomplete="off">
                                    <input type="hidden" name="billing_state" id="billing_state">
                                    <select name="billing_state_id" id="billing_state_id" required class="hidden-select" onchange="onStateChange()">
                                        <option value="">Select State</option>
                                    </select>
                                    <div class="searchable-select-dropdown hidden">
                                        <input type="text" class="dropdown-search-input w-full px-3 py-2 border-b border-gray-200 focus:outline-none" placeholder="Search..." autocomplete="off">
                                        <div class="dropdown-options max-h-48 overflow-y-auto">
                                            <div class="searchable-select-option text-muted" style="cursor:default;color:#9ca3af;">No states available</div>
                                        </div>
                                    </div>
                                </div>
                                <span class="error-message" id="error-billing_state_id"></span>
                            </div>

                            <div class="searchable-select-wrapper">
                                <label class="block text-sm font-medium text-gray-700 mb-1">City *</label>
                                <div class="searchable-select" data-select-id="billing_city_id">
                                    <input type="text" class="searchable-select-input w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-halal-green bg-gray-100" placeholder="Select a state first" autocomplete="off" disabled readonly>
                                    <input type="hidden" name="billing_city" id="billing_city">
                                    <select name="billing_city_id" id="billing_city_id" required class="hidden-select" onchange="onCityChange()">
                                        <option value="">Select City</option>
                                    </select>
                                    <div class="searchable-select-dropdown hidden">
                                        <input type="text" class="dropdown-search-input w-full px-3 py-2 border-b border-gray-200 focus:outline-none" placeholder="Search..." autocomplete="off">
                                        <div class="dropdown-options max-h-48 overflow-y-auto">
                                            <div class="searchable-select-option text-muted" style="cursor:default;color:#9ca3af;">Select a state first</div>
                                        </div>
                                    </div>
                                </div>
                                <span class="error-message" id="error-billing_city_id"></span>
                            </div>

                            <div class="searchable-select-wrapper">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Area *</label>
                                <div class="searchable-select" data-select-id="billing_area_id">
                                    <input type="text" class="searchable-select-input w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-halal-green bg-gray-100" placeholder="Select a city first" autocomplete="off" disabled readonly>
                                    <select name="billing_area_id" id="billing_area_id" class="hidden-select" onchange="onAreaChange()" required>
                                        <option value="">Select Area</option>
                                    </select>
                                    <div class="searchable-select-dropdown hidden">
                                        <input type="text" class="dropdown-search-input w-full px-3 py-2 border-b border-gray-200 focus:outline-none" placeholder="Search..." autocomplete="off">
                                        <div class="dropdown-options max-h-48 overflow-y-auto">
                                            <div class="searchable-select-option text-muted" style="cursor:default;color:#9ca3af;">Select a city first</div>
                                        </div>
                                    </div>
                                </div>
                                <span class="error-message" id="error-billing_area_id"></span>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Postcode</label>
                                <input type="text" name="billing_postcode" id="billing_postcode" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-halal-green">
                                <span class="error-message" id="error-billing_postcode"></span>
                            </div>
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Order Notes</label>
                                <textarea name="notes" rows="3" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-halal-green" placeholder="Notes about your order, e.g. special notes for delivery"></textarea>
                            </div>
                        </div>

                        <div class="mt-6">
                            <h4 class="font-medium mb-3">Shipping Method</h4>
                            <div id="shippingOptionsContainer" class="space-y-2">
                                @php
                                    $defaultDelivery = $cart->getSubtotal() >= 500 ? 0 : 60;
                                @endphp
                                <label class="flex items-center p-3 border rounded-lg cursor-pointer hover:bg-gray-50 {{ $defaultDelivery === 0 ? 'border-halal-green bg-green-50' : '' }}">
                                    <input type="radio" name="shipping_method" value="home_delivery" checked
                                        class="mr-3" onchange="selectShippingMethod('home_delivery', {{ $defaultDelivery }})">
                                    <div class="flex-1">
                                        <div class="font-medium">Home Delivery</div>
                                        <div class="text-sm text-gray-500">3-5 business days</div>
                                    </div>
                                    <div class="font-medium {{ $defaultDelivery === 0 ? 'text-halal-green' : '' }}">
                                        {{ $defaultDelivery === 0 ? 'Free' : '৳' . $defaultDelivery }}
                                    </div>
                                </label>
                            </div>
                        </div>

                        <div class="mt-6">
                            <h4 class="font-medium mb-3">Payment Method</h4>
                            <div class="space-y-2">
                                @forelse($paymentGateways as $gateway)
                                <label class="flex items-center p-3 border rounded-lg cursor-pointer hover:bg-gray-50 {{ $loop->first ? 'border-halal-green bg-green-50' : '' }}">
                                    <input type="radio" name="payment_method" value="{{ $gateway->slug }}" {{ $loop->first ? 'checked' : '' }} class="mr-3">
                                    @if($gateway->logo)
                                    <img src="{{ Storage::url($gateway->logo) }}" alt="{{ $gateway->name }}" class="w-8 h-8 object-contain mr-2">
                                    @else
                                    <i class="bi bi-credit-card mr-2"></i>
                                    @endif
                                    <span>{{ $gateway->name }}</span>
                                    @if($gateway->test_mode)
                                    <span class="ml-2 text-xs bg-yellow-100 text-yellow-800 px-2 py-0.5 rounded">Test</span>
                                    @endif
                                </label>
                                @empty
                                <label class="flex items-center p-3 border rounded-lg cursor-pointer hover:bg-gray-50">
                                    <input type="radio" name="payment_method" value="cod" checked class="mr-3">
                                    <span>Cash on Delivery</span>
                                </label>
                                @endforelse
                            </div>
                        </div>

                        <div class="mt-6">
                            <label class="flex items-center">
                                <input type="checkbox" name="terms" id="terms" required class="mr-2">
                                <span class="text-sm text-gray-600">I agree to the <a href="{{ route('terms') }}" class="text-halal-green hover:underline">Terms and Conditions</a></span>
                            </label>
                            <span class="error-message" id="error-terms"></span>
                        </div>
                    </form>
                </div>
            </div>

            <div class="bg-white p-6 rounded-lg shadow-sm mb-6" id="checkout-items-section">
                <h3 class="text-lg font-bold mb-4">Order Items</h3>
                @foreach($cart->items as $item)
                <div class="flex items-center space-x-4 bg-white p-4 rounded-lg shadow-sm mb-4" id="checkout-item-{{ $item['cart_item_id'] }}">
                    @php
                        $imageUrl = isset($item['image'])
                            ? (str_starts_with($item['image'], 'http') ? $item['image']
                            : (str_starts_with($item['image'], '/storage/') ? $item['image']
                            : (str_starts_with($item['image'], '/uploads/') ? asset($item['image'])
                            : asset('storage/' . $item['image']))))
                            : 'https://placehold.co/80';
                    @endphp
                    <img src="{{ $imageUrl }}" alt="{{ $item['name'] }}" class="w-16 h-16 object-cover rounded-lg">
                    <div class="flex-1">
                        <h4 class="font-medium text-gray-800">{{ $item['name'] }}</h4>
                        <p class="text-halal-green font-bold">৳{{ number_format($item['price'], 2) }}</p>
                        <div class="flex items-center space-x-2 mt-2">
                            <button data-cart-item="{{ $item['cart_item_id'] }}" data-action="dec" class="qty-btn w-7 h-7 bg-gray-100 rounded flex items-center justify-center hover:bg-gray-200">
                                <i class="bi bi-dash text-sm"></i>
                            </button>
                            <span class="font-medium qty-display" id="qty-{{ $item['cart_item_id'] }}">{{ $item['quantity'] }}</span>
                            <button data-cart-item="{{ $item['cart_item_id'] }}" data-action="inc" class="qty-btn w-7 h-7 bg-gray-100 rounded flex items-center justify-center hover:bg-gray-200">
                                <i class="bi bi-plus text-sm"></i>
                            </button>
                        </div>
                    </div>
                    <div class="text-right">
                        <p class="font-bold text-gray-800">৳{{ number_format($item['price'] * $item['quantity'], 2) }}</p>
                        <button data-cart-item="{{ $item['cart_item_id'] }}" data-action="remove" class="qty-btn text-red-500 hover:text-red-700 text-sm mt-1">
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        <div class="bg-white p-6 rounded-lg shadow-sm h-fit sticky top-24" id="checkout-sidebar">
            <h3 class="text-lg font-bold mb-4">Order Summary</h3>
            <div class="space-y-2 mb-4">
                <div class="flex justify-between text-gray-600">
                    <span>Subtotal</span>
                    <span id="checkoutSubtotal">৳{{ number_format($cart->getSubtotal(), 2) }}</span>
                </div>
                <div class="flex justify-between text-gray-600">
                    <span>Delivery</span>
                    <span id="checkoutDelivery" class="{{ $defaultDelivery === 0 ? 'text-halal-green' : '' }}">{{ $defaultDelivery === 0 ? 'Free' : '৳' . $defaultDelivery }}</span>
                </div>
                <hr>
                <div class="flex justify-between font-bold text-lg">
                    <span>Total</span>
                    <span id="checkoutTotal" class="text-halal-green">৳{{ number_format($cart->getSubtotal() + $defaultDelivery, 2) }}</span>
                </div>
            </div>
            @if($defaultDelivery === 0)
            <div id="freeDeliveryMessage" class="text-halal-green text-sm mb-4">
                <i class="bi bi-truck mr-1"></i>You have free delivery!
            </div>
            @endif
            <button type="button" id="placeOrderBtn" onclick="processCheckout(event)" class="w-full bg-halal-green text-white py-3 rounded-lg font-medium hover:text-amber-400 transition-colors">
                Place Order
            </button>
            <a href="{{ route('cart.index') }}" class="block w-full text-center py-3 text-halal-green hover:underline mt-2">
                <i class="bi bi-arrow-left mr-1"></i> Back to Cart
            </a>
        </div>
    </div>
    @endif
</div>

<style>
.searchable-select { position: relative; z-index: 50; }
.searchable-select.open { z-index: 999; }
.searchable-select .hidden-select { position: absolute; left: -9999px; opacity: 0; height: 0; width: 0; pointer-events: none; }
.searchable-select-dropdown {
    position: absolute; top: 100%; left: 0; right: 0; z-index: 1000;
    background: #fff; border: 1px solid #d1d5db; border-radius: 0 0 8px 8px;
    box-shadow: 0 8px 24px rgba(0,0,0,0.15); margin-top: 2px;
}
.searchable-select-dropdown .dropdown-options {
    max-height: 220px; overflow-y: auto;
}
.searchable-select-dropdown.hidden { display: none; }
.searchable-select-option {
    padding: 8px 14px; cursor: pointer; font-size: 14px;
    border-bottom: 1px solid #f3f4f6; transition: background 0.15s;
}
.searchable-select-option:last-child { border-bottom: none; }
.searchable-select-option:hover, .searchable-select-option.highlighted { background: #dcfce7; color: #166534; }
.searchable-select-option.selected { background: #22c55e; color: #fff; }
.searchable-select-input:focus { border-color: #22c55e !important; box-shadow: 0 0 0 2px rgba(34,197,94,0.2) !important; }
.searchable-select-input::placeholder { color: #9ca3af; }
.billing-card { overflow: visible !important; }
.billing-card .grid { overflow: visible !important; }
.error-message { display: none; color: #dc2626; font-size: 0.8rem; margin-top: 4px; }
.error-message.visible { display: block; }
.is-invalid { border-color: #dc2626 !important; }
.is-invalid:focus { box-shadow: 0 0 0 2px rgba(220,38,38,0.2) !important; }
</style>

<script>
let checkoutSubtotal = {{ $cart->getSubtotal() }};
let checkoutDelivery = {{ $defaultDelivery }};
let checkoutTotal = checkoutSubtotal + checkoutDelivery;
let selectedShippingMethod = 'home_delivery';

const checkoutMode = '{{ $checkoutMode }}';
const defaultCountry = '{{ $defaultCountryName }}';

function initSearchableSelect(container) {
    const input = container.querySelector('.searchable-select-input');
    const dropdown = container.querySelector('.searchable-select-dropdown');
    const searchInput = dropdown.querySelector('.dropdown-search-input');
    const optionsContainer = dropdown.querySelector('.dropdown-options');
    const select = container.querySelector('select');
    const hiddenInput = container.querySelector('input[type="hidden"]');

    function filterOptions(query) {
        const options = optionsContainer.querySelectorAll('.searchable-select-option');
        const q = query.toLowerCase().trim();
        options.forEach(opt => {
            opt.style.display = !q || opt.textContent.toLowerCase().includes(q) ? 'block' : 'none';
        });
    }

    function setSelectOptions(opts, emptyMsg, searchPlaceholder) {
        select.innerHTML = '';
        optionsContainer.innerHTML = '';
        const placeholder = document.createElement('option');
        placeholder.value = '';
        placeholder.textContent = 'Select';
        select.appendChild(placeholder);
        if (opts.length === 0) {
            const msg = document.createElement('div');
            msg.className = 'searchable-select-option text-muted';
            msg.textContent = emptyMsg || 'No options available';
            msg.style.cursor = 'default';
            msg.style.color = '#9ca3af';
            optionsContainer.appendChild(msg);
        } else {
            opts.forEach(o => {
                const opt = document.createElement('option');
                opt.value = o.value;
                opt.textContent = o.text;
                select.appendChild(opt);
                const div = document.createElement('div');
                div.className = 'searchable-select-option';
                div.dataset.value = o.value;
                div.textContent = o.text;
                div.addEventListener('click', () => selectOption(o.value, o.text));
                optionsContainer.appendChild(div);
            });
        }
        select.value = '';
        input.value = '';
        input.placeholder = opts.length > 0 ? (searchPlaceholder || 'Search...') : (emptyMsg || 'No options available');
        if (searchInput) searchInput.value = '';
    }

    function selectOption(value, text) {
        select.value = value;
        input.value = text;
        closeDropdown();
        select.dispatchEvent(new Event('change', { bubbles: true }));
    }

    function highlightNext() {
        const visible = [...optionsContainer.querySelectorAll('.searchable-select-option')].filter(o => o.style.display !== 'none');
        const idx = visible.findIndex(o => o.classList.contains('highlighted'));
        visible.forEach(o => o.classList.remove('highlighted'));
        if (idx < visible.length - 1) visible[idx + 1]?.classList.add('highlighted');
        else visible[0]?.classList.add('highlighted');
    }

    function highlightPrev() {
        const visible = [...optionsContainer.querySelectorAll('.searchable-select-option')].filter(o => o.style.display !== 'none');
        const idx = visible.findIndex(o => o.classList.contains('highlighted'));
        visible.forEach(o => o.classList.remove('highlighted'));
        if (idx > 0) visible[idx - 1]?.classList.add('highlighted');
        else visible[visible.length - 1]?.classList.add('highlighted');
    }

    function selectHighlighted() {
        const hl = optionsContainer.querySelector('.searchable-select-option.highlighted');
        if (hl) selectOption(hl.dataset.value, hl.textContent);
    }

    function rebuildOptions() {
        optionsContainer.innerHTML = '';
        [...select.options].forEach(opt => {
            if (!opt.value) return;
            const div = document.createElement('div');
            div.className = 'searchable-select-option';
            div.dataset.value = opt.value;
            div.textContent = opt.text;
            div.addEventListener('click', () => selectOption(opt.value, opt.text));
            optionsContainer.appendChild(div);
        });
        filterOptions(searchInput ? searchInput.value : '');
    }

    function openDropdown() {
        if (input.disabled) return;
        var hasOptions = optionsContainer.querySelectorAll('.searchable-select-option[data-value]').length > 0;
        if (!hasOptions) {
            rebuildOptions();
        }
        dropdown.classList.remove('hidden');
        container.classList.add('open');
        filterOptions(searchInput ? searchInput.value : '');
        if (searchInput) setTimeout(function() { searchInput.focus(); }, 50);
    }

    function closeDropdown() {
        dropdown.classList.add('hidden');
        container.classList.remove('open');
    }

    input.addEventListener('click', openDropdown);
    input.addEventListener('focus', openDropdown);

    if (searchInput) {
        searchInput.addEventListener('input', function() {
            filterOptions(this.value);
        });

        searchInput.addEventListener('keydown', function(e) {
            if (e.key === 'ArrowDown') { e.preventDefault(); highlightNext(); }
            else if (e.key === 'ArrowUp') { e.preventDefault(); highlightPrev(); }
            else if (e.key === 'Enter') { e.preventDefault(); selectHighlighted(); }
            else if (e.key === 'Escape') { closeDropdown(); input.blur(); }
        });
    }

    document.addEventListener('click', function(e) {
        if (!container.contains(e.target)) closeDropdown();
    });

    // Attach click handlers to pre-rendered options
    optionsContainer.querySelectorAll('.searchable-select-option[data-value]').forEach(opt => {
        opt.addEventListener('click', () => selectOption(opt.dataset.value, opt.textContent));
    });

    // If select already has a value, show it
    if (select.value) {
        const selectedOpt = [...select.options].find(o => o.value === select.value);
        if (selectedOpt) input.value = selectedOpt.text;
    }

    // Expose methods for dynamic options
    container.rebuildOptions = rebuildOptions;
    container.setSelectOptions = setSelectOptions;
}

document.addEventListener('DOMContentLoaded', async function() {
    document.querySelectorAll('.searchable-select').forEach(initSearchableSelect);
    await loadStates();
    var selectedAddress = document.querySelector('input[name="address_choice"]:checked');
    if (selectedAddress && selectedAddress.value.startsWith('saved_')) {
        useSavedAddress(selectedAddress);
    }
    document.getElementById('checkoutForm').addEventListener('submit', function(e) {
        e.preventDefault();
        runCheckout();
    });
});

async function loadStates(country) {
    const c = country || document.getElementById('billing_country')?.value || defaultCountry;
    const params = c ? `?country=${encodeURIComponent(c)}` : '';
    try {
        const response = await fetch(`/checkout/get-states${params}`);
        const data = await response.json();
        const stateContainer = document.querySelector('.searchable-select[data-select-id="billing_state"]');
        const opts = [];
        if (data.success && data.states) {
            data.states.forEach(s => opts.push({ value: s.id, text: s.name }));
        }
        stateContainer.setSelectOptions(opts, 'No states available', 'Select State');
    } catch (e) { console.error('Error loading states:', e); }
}

async function loadCities(stateId) {
    if (!stateId) return;
    try {
        const response = await fetch(`/checkout/get-cities?state_id=${stateId}`);
        const data = await response.json();
        const cityContainer = document.querySelector('.searchable-select[data-select-id="billing_city_id"]');
        const cityOpts = [];
        if (data.success && data.cities) {
            data.cities.forEach(c => cityOpts.push({ value: c.id, text: c.name }));
        }
        cityContainer.setSelectOptions(cityOpts, 'No cities available for this state', 'Select City');
    } catch (e) { console.error('Error loading cities:', e); }
}

async function loadAreas(cityId) {
    if (!cityId) return;
    try {
        const response = await fetch(`/checkout/get-areas?city_id=${cityId}`);
        const data = await response.json();
        const areaContainer = document.querySelector('.searchable-select[data-select-id="billing_area_id"]');
        const opts = [];
        if (data.success && data.areas) {
            data.areas.forEach(a => opts.push({ value: a.id, text: a.name }));
        }
        areaContainer.setSelectOptions(opts, 'No areas available', 'Select Area');
    } catch (e) { console.error('Error loading areas:', e); }
}

async function onStateChange() {
    const stateSelect = document.getElementById('billing_state_id');
    const stateName = stateSelect.options[stateSelect.selectedIndex]?.text || '';
    document.getElementById('billing_state').value = stateName;

    const cityContainer = document.querySelector('.searchable-select[data-select-id="billing_city_id"]');
    const cityInput = cityContainer.querySelector('.searchable-select-input');
    cityInput.value = '';
    cityInput.disabled = true;
    cityInput.classList.add('bg-gray-100');
    var cityErrorEl = document.getElementById('error-billing_city_id');
    if (cityErrorEl) { cityErrorEl.textContent = ''; cityErrorEl.classList.remove('visible'); }

    const areaContainer = document.querySelector('.searchable-select[data-select-id="billing_area_id"]');
    const areaInput = areaContainer.querySelector('.searchable-select-input');
    areaInput.value = '';
    areaInput.disabled = true;
    areaInput.classList.add('bg-gray-100');
    areaInput.placeholder = 'Select a city first';
    areaContainer.setSelectOptions([]);

    var stateErrorEl = document.getElementById('error-billing_state_id');
    if (stateErrorEl) { stateErrorEl.textContent = ''; stateErrorEl.classList.remove('visible'); }
    stateSelect.classList.remove('is-invalid');

    if (stateSelect.value) {
        cityInput.placeholder = 'Loading cities...';
        await loadCities(stateSelect.value);
        cityInput.disabled = false;
        cityInput.classList.remove('bg-gray-100');
        cityInput.placeholder = 'Search city...';
    } else {
        cityInput.placeholder = 'Select a state first';
    }
    updateShippingOptions();
}

async function onCountryChange() {
    const stateContainer = document.querySelector('.searchable-select[data-select-id="billing_state"]');
    stateContainer.setSelectOptions([], 'No states available', 'Select State');

    const cityContainer = document.querySelector('.searchable-select[data-select-id="billing_city_id"]');
    const cityInput = cityContainer.querySelector('.searchable-select-input');
    cityInput.value = '';
    cityInput.disabled = true;
    cityInput.classList.add('bg-gray-100');
    cityInput.placeholder = 'Select a state first';
    cityContainer.setSelectOptions([]);

    const areaContainer = document.querySelector('.searchable-select[data-select-id="billing_area_id"]');
    const areaInput = areaContainer.querySelector('.searchable-select-input');
    areaInput.value = '';
    areaInput.disabled = true;
    areaInput.classList.add('bg-gray-100');
    areaInput.placeholder = 'Select a city first';
    areaContainer.setSelectOptions([]);

    await loadStates();
}

async function onCityChange() {
    const citySelect = document.getElementById('billing_city_id');
    var cityErrorEl = document.getElementById('error-billing_city_id');
    if (cityErrorEl) { cityErrorEl.textContent = ''; cityErrorEl.classList.remove('visible'); }
    citySelect.classList.remove('is-invalid');

    const cityId = citySelect.value;
    const cityName = citySelect.options[citySelect.selectedIndex]?.text || '';
    document.getElementById('billing_city').value = cityName;

    const areaContainer = document.querySelector('.searchable-select[data-select-id="billing_area_id"]');
    const areaInput = areaContainer.querySelector('.searchable-select-input');
    areaInput.value = '';
    var areaErrorEl = document.getElementById('error-billing_area_id');
    if (areaErrorEl) { areaErrorEl.textContent = ''; areaErrorEl.classList.remove('visible'); }
    areaContainer.querySelector('select')?.classList.remove('is-invalid');
    areaContainer.setSelectOptions([]);

    if (cityId) {
        areaInput.disabled = false;
        areaInput.classList.remove('bg-gray-100');
        try {
            const response = await fetch(`/checkout/get-areas?city_id=${cityId}`);
            const data = await response.json();
            const opts = [];
            if (data.success && data.areas) {
                data.areas.forEach(a => opts.push({ value: a.id, text: a.name }));
            }
            areaContainer.setSelectOptions(opts, 'No areas available', 'Select Area');
        } catch (e) { console.error('Error loading areas:', e); }
    } else {
        areaInput.disabled = true;
        areaInput.classList.add('bg-gray-100');
        areaInput.placeholder = 'Select a city first';
    }
    updateShippingOptions();
}

function onAreaChange() {
    updateShippingOptions();
}

async function useSavedAddress(radioEl) {
    document.getElementById('addressFormSection').style.display = 'none';
    document.getElementById('terms').checked = true;
    var label = radioEl.closest('label');
    if (!label) return;
    try {
        var addr = JSON.parse(label.dataset.address);
        document.getElementById('billing_first_name').value = addr.first_name || '';
        document.getElementById('billing_last_name').value = addr.last_name || '';
        document.getElementById('billing_email').value = addr.email || '';
        document.getElementById('billing_phone').value = addr.phone || '';
        document.getElementById('billing_address').value = addr.address || '';
        document.getElementById('billing_city').value = addr.city || '';
        document.getElementById('billing_postcode').value = addr.postcode || '';

        // Set country if select exists
        var countryEl = document.getElementById('billing_country');
        if (countryEl && countryEl.tagName === 'SELECT' && addr.country) {
            countryEl.value = addr.country;
        }

        // Match state in dropdown by text
        var stateSelect = document.getElementById('billing_state_id');
        if (stateSelect && addr.state) {
            for (var i = 0; i < stateSelect.options.length; i++) {
                if (stateSelect.options[i].text.toLowerCase() === addr.state.toLowerCase()) {
                    stateSelect.value = stateSelect.options[i].value;
                    var stateInput = document.querySelector('.searchable-select[data-select-id="billing_state"] .searchable-select-input');
                    if (stateInput) stateInput.value = stateSelect.options[i].text;
                    document.getElementById('billing_state').value = addr.state;
                    await onStateChange();

                    // Match city in dropdown by text
                    await new Promise(function(r) { setTimeout(r, 200); });
                    var citySelect = document.getElementById('billing_city_id');
                    if (citySelect && addr.city) {
                        // Wait for city options to actually load
                        for (var w = 0; w < 20; w++) {
                            if (citySelect.options.length > 1) break;
                            await new Promise(function(r) { setTimeout(r, 100); });
                        }
                        for (var j = 0; j < citySelect.options.length; j++) {
                            if (citySelect.options[j].text.toLowerCase() === addr.city.toLowerCase()) {
                                citySelect.value = citySelect.options[j].value;
                                var cityInput = document.querySelector('.searchable-select[data-select-id="billing_city_id"] .searchable-select-input');
                                if (cityInput) cityInput.value = citySelect.options[j].text;
                                await onCityChange();

                                // Wait for area options to actually load
                                var areaSelect = document.getElementById('billing_area_id');
                                for (var w2 = 0; w2 < 20; w2++) {
                                    if (areaSelect && areaSelect.options.length > 1) break;
                                    await new Promise(function(r) { setTimeout(r, 100); });
                                }
                                if (areaSelect && areaSelect.options.length > 1) {
                                    var firstOpt = areaSelect.options[1];
                                    areaSelect.value = firstOpt.value;
                                    var areaInput = document.querySelector('.searchable-select[data-select-id="billing_area_id"] .searchable-select-input');
                                    if (areaInput) {
                                        areaInput.value = firstOpt.text;
                                        areaInput.disabled = false;
                                        areaInput.classList.remove('bg-gray-100');
                                    }
                                    updateShippingOptions();
                                }
                                break;
                            }
                        }
                    }
                    break;
                }
            }
        }
    } catch(e) { console.error('useSavedAddress error:', e); }
}


function showNewAddressForm() {
    document.getElementById('addressFormSection').style.display = 'block';
    document.getElementById('addressFormSection').scrollIntoView({ behavior: 'smooth', block: 'start' });
}

async function updateShippingOptions() {
    const citySelect = document.getElementById('billing_city_id');
    const areaSelect = document.getElementById('billing_area_id');
    const cityId = citySelect?.value || '';
    const areaId = areaSelect?.value || '';

    try {
        const response = await fetch(`/checkout/shipping-options?city=${encodeURIComponent(cityId)}&area=${encodeURIComponent(areaId)}&subtotal=${checkoutSubtotal}`, {
            credentials: 'same-origin'
        });
        const data = await response.json();
        if (data.success && data.options) {
            renderShippingOptions(data.options);
        }
    } catch (error) {
        console.error('Error loading shipping options:', error);
    }
}

function renderShippingOptions(options) {
    const container = document.getElementById('shippingOptionsContainer');
    if (!container) return;
    let html = options.map(option => {
        const isSelected = option.id === selectedShippingMethod;
        return `
            <label class="flex items-center p-3 border rounded-lg cursor-pointer hover:bg-gray-50 ${isSelected ? 'border-halal-green bg-green-50' : ''}">
                <input type="radio" name="shipping_method" value="${option.id}" ${isSelected ? 'checked' : ''}
                    class="mr-3" onchange="selectShippingMethod('${option.id}', ${option.cost})">
                <div class="flex-1">
                    <div class="font-medium">${option.name}</div>
                    <div class="text-sm text-gray-500">${option.estimated_days || ''}</div>
                </div>
                <div class="font-medium ${option.cost === 0 ? 'text-halal-green' : ''}">
                    ${option.cost === 0 ? 'Free' : '৳' + option.cost.toLocaleString()}
                </div>
            </label>
        `;
    }).join('');
    container.innerHTML = html;
}

function selectShippingMethod(method, cost) {
    selectedShippingMethod = method;
    checkoutDelivery = cost;
    checkoutTotal = checkoutSubtotal + checkoutDelivery;
    updateOrderSummary();
    document.querySelectorAll('input[name="shipping_method"]').forEach(radio => {
        const label = radio.closest('label');
        if (radio.value === method) {
            label.classList.add('border-halal-green', 'bg-green-50');
            label.classList.remove('border-gray-300');
        } else {
            label.classList.remove('border-halal-green', 'bg-green-50');
            label.classList.add('border-gray-300');
        }
    });
}

function updateOrderSummary() {
    const subtotalEl = document.getElementById('checkoutSubtotal');
    const deliveryEl = document.getElementById('checkoutDelivery');
    const totalEl = document.getElementById('checkoutTotal');
    const freeDeliveryMsg = document.getElementById('freeDeliveryMessage');
    if (subtotalEl) subtotalEl.textContent = '৳' + checkoutSubtotal.toLocaleString();
    if (deliveryEl) {
        deliveryEl.textContent = checkoutDelivery === 0 ? 'Free' : '৳' + checkoutDelivery.toLocaleString();
        deliveryEl.className = checkoutDelivery === 0 ? 'text-halal-green' : '';
    }
    if (totalEl) totalEl.textContent = '৳' + checkoutTotal.toLocaleString();
    if (freeDeliveryMsg) freeDeliveryMsg.style.display = checkoutDelivery === 0 ? 'block' : 'none';
}

document.addEventListener('click', function(e) {
    var btn = e.target.closest('.qty-btn');
    if (!btn) return;
    var cartItemId = btn.dataset.cartItem;
    var action = btn.dataset.action;
    if (action === 'remove') {
        fetch('/cart/remove', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
            body: JSON.stringify({ cart_item_id: cartItemId })
        }).then(function(r) { return r.json(); }).then(function(data) {
            if (data.success) {
                var el = document.getElementById('checkout-item-' + cartItemId);
                if (el) el.remove();
                var subtotal = 0;
                document.querySelectorAll('[id^="checkout-item-"]').forEach(function(row) {
                    var t = row.querySelector('.text-right .font-bold');
                    if (t) subtotal += parseFloat(t.textContent.replace(/[^0-9.]/g, '')) || 0;
                });
                checkoutSubtotal = subtotal;
                checkoutTotal = subtotal + checkoutDelivery;
                updateOrderSummary();
                if (typeof updateCartCount === 'function') updateCartCount();
                if (document.querySelectorAll('[id^="checkout-item-"]').length === 0) {
                    showEmptyCartState();
                }
            }
        });
        return;
    }
    var qtySpan = document.getElementById('qty-' + cartItemId);
    if (!qtySpan) return;
    var currentQty = parseInt(qtySpan.textContent);
    if (isNaN(currentQty)) return;
    var newQty = action === 'inc' ? currentQty + 1 : currentQty - 1;
    if (newQty < 1) {
        fetch('/cart/remove', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
            body: JSON.stringify({ cart_item_id: cartItemId })
        }).then(function(r) { return r.json(); }).then(function(data) {
            if (data.success) {
                var el = document.getElementById('checkout-item-' + cartItemId);
                if (el) el.remove();
                var subtotal = 0;
                document.querySelectorAll('[id^="checkout-item-"]').forEach(function(row) {
                    var t = row.querySelector('.text-right .font-bold');
                    if (t) subtotal += parseFloat(t.textContent.replace(/[^0-9.]/g, '')) || 0;
                });
                checkoutSubtotal = subtotal;
                checkoutTotal = subtotal + checkoutDelivery;
                updateOrderSummary();
                if (typeof updateCartCount === 'function') updateCartCount();
                if (document.querySelectorAll('[id^="checkout-item-"]').length === 0) {
                    showEmptyCartState();
                }
            }
        });
        return;
    }
    fetch('/cart/update', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
        body: JSON.stringify({ cart_item_id: cartItemId, quantity: newQty })
    }).then(function(r) { return r.json(); }).then(function(data) {
        if (data.success) {
            qtySpan.textContent = newQty;
            var itemRow = qtySpan.closest('[id^="checkout-item-"]');
            if (itemRow) {
                var priceText = itemRow.querySelector('.text-halal-green.font-bold');
                var unitPrice = priceText ? parseFloat(priceText.textContent.replace(/[^0-9.]/g, '')) : 0;
                var totalEl = itemRow.querySelector('.text-right .font-bold');
                if (totalEl) totalEl.textContent = '৳' + (unitPrice * newQty).toFixed(2);
                var subtotal = 0;
                document.querySelectorAll('[id^="checkout-item-"]').forEach(function(row) {
                    var t = row.querySelector('.text-right .font-bold');
                    if (t) subtotal += parseFloat(t.textContent.replace(/[^0-9.]/g, '')) || 0;
                });
                checkoutSubtotal = subtotal;
                checkoutTotal = subtotal + checkoutDelivery;
                updateOrderSummary();
            }
        }
    });
});

function clearErrors() {
    document.querySelectorAll('.error-message').forEach(function(el) {
        el.textContent = '';
        el.classList.remove('visible');
    });
    document.querySelectorAll('.is-invalid').forEach(function(el) {
        el.classList.remove('is-invalid');
    });
}

function showError(fieldId, message) {
    var el = document.getElementById(fieldId);
    if (el) { el.classList.add('is-invalid'); }
    var errorEl = document.getElementById('error-' + fieldId);
    if (errorEl) {
        errorEl.textContent = message;
        errorEl.classList.add('visible');
    }
    showCheckoutNotification(message, 'error');
    if (el) { try { el.focus(); } catch(e) {} }
}

function showServerErrors(errors) {
    for (var field in errors) {
        if (errors.hasOwnProperty(field) && errors[field].length > 0) {
            showError(field, errors[field][0]);
        }
    }
}

async function runCheckout() {
    clearErrors();

    if (document.querySelectorAll('[id^="checkout-item-"]').length === 0) {
        showCheckoutNotification('Your cart is empty. Please add items before placing an order.', 'error');
        return;
    }

    var hasError = false;
    var addressEl = document.getElementById('billing_address');
    if (!addressEl?.value?.trim()) { showError('billing_address', 'Please enter your address.'); hasError = true; }

    var phoneEl = document.getElementById('billing_phone');
    if (!phoneEl?.value?.trim()) { showError('billing_phone', 'Please enter your phone number.'); hasError = true; }

    var cityEl = document.getElementById('billing_city_id');
    if (!cityEl?.value) { showError('billing_city_id', 'Please select a city.'); hasError = true; }

    var areaEl = document.getElementById('billing_area_id');
    if (!areaEl?.value) { showError('billing_area_id', 'Please select an area.'); hasError = true; }

    var stateEl = document.getElementById('billing_state_id');
    if (!stateEl?.value) { showError('billing_state_id', 'Please select your state.'); hasError = true; }

    var termsEl = document.getElementById('terms');
    if (!termsEl?.checked) { showError('terms', 'Please agree to the terms and conditions.'); hasError = true; }

    if (hasError) return;

    try {
        var form = document.getElementById('checkoutForm');
        var formData = new FormData(form);
        var csrfToken = document.querySelector('meta[name="csrf-token"]');
        var response = await fetch('/checkout/process', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrfToken ? csrfToken.content : '' },
            credentials: 'same-origin',
            body: formData
        });
        var data = await response.json();
        if (response.ok && data.success) {
            if (data.method === 'bkash') {
                processBkashPayment(data.paymentID, data.order_id, data.sdk_url, data.amount);
            } else {
                window.location.href = data.redirect || '{{ route("home") }}';
            }
        } else if (data.errors) {
            showServerErrors(data.errors);
        } else {
            showCheckoutNotification(data.message || 'Checkout failed. Please try again.', 'error');
        }
    } catch (error) {
        console.error('Error processing checkout:', error);
        showCheckoutNotification('An error occurred. Please try again.', 'error');
    }
}

function processCheckout(event) {
    runCheckout();
}

/**
 * bKash Checkout SDK — starts the popup payment flow.
 * Loads the SDK dynamically if not already loaded, then inits.
 */
function processBkashPayment(paymentID, orderId, sdkUrl, amount) {
    if (typeof bKash === 'undefined') {
        var script = document.createElement('script');
        script.src = sdkUrl || 'https://scripts.pay.bka.sh/versions/1.2.0-beta/checkout/bKash-checkout.js';
        script.onload = function() { initBkash(paymentID, orderId, amount); };
        script.onerror = function() {
            showCheckoutNotification('Failed to load bKash payment SDK. Please try again.', 'error');
        };
        document.head.appendChild(script);
    } else {
        initBkash(paymentID, orderId, amount);
    }
}

function initBkash(paymentID, orderId, amount) {
    // Add a hidden bKash trigger button if not already present
    if (!document.getElementById('bKash_button')) {
        var btn = document.createElement('button');
        btn.id = 'bKash_button';
        btn.style.display = 'none';
        document.body.appendChild(btn);
    }

    bKash.init({
        paymentMode: 'checkout',
        paymentRequest: { amount: amount, intent: 'sale' },
        createRequest: function() {
            // Payment already created on backend — notify SDK with existing paymentID
            bKash.create().onSuccess({ paymentID: paymentID });
        },
        executeRequestOnAuthorization: function() {
            return fetch('/payment/bkash/execute', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                },
                body: JSON.stringify({ paymentID: paymentID })
            }).then(function(r) { return r.json(); })
            .then(function(data) {
                if (data && (data.transactionStatus === 'Completed' || data.success)) {
                    window.location.href = '/checkout/success/' + (data.order_id || orderId);
                } else {
                    showCheckoutNotification('bKash payment was not completed.', 'error');
                }
            });
        },
        onClose: function() {
            showCheckoutNotification('bKash payment was cancelled.', 'error');
        },
        onSuccess: function(data) {
            // handled in executeRequestOnAuthorization
        },
        onError: function(error) {
            showCheckoutNotification('bKash payment failed. Please try again.', 'error');
        }
    });

    // Trigger the bKash button click to start the SDK flow
    document.getElementById('bKash_button').click();
}

function showEmptyCartState() {
    var content = document.getElementById('checkout-content');
    if (content) {
        content.outerHTML = '<div class="text-center py-12"><div class="w-24 h-24 mx-auto bg-gray-100 rounded-full flex items-center justify-center mb-4"><i class="bi bi-cart-x text-4xl text-gray-400"></i></div><h4 class="text-gray-600 font-medium mb-2">Your cart is empty</h4><a href="/" class="inline-block bg-halal-green text-white px-6 py-2 rounded-full hover:text-amber-400 transition-colors">Shop Now</a></div>';
    }
}

function showCheckoutNotification(message, type) {
    var notif = document.getElementById('checkout-notif');
    if (notif) notif.remove();
    notif = document.createElement('div');
    notif.id = 'checkout-notif';
    notif.className = 'fixed bottom-4 right-4 px-6 py-3 rounded-lg shadow-lg text-white flex items-center space-x-2';
    notif.style.cssText = 'z-index:99999;position:fixed;bottom:20px;right:20px;padding:12px 24px;border-radius:8px;box-shadow:0 4px 20px rgba(0,0,0,0.3);color:#fff;font-weight:500;';
    notif.style.backgroundColor = type === 'success' ? '#22c55e' : '#ef4444';
    notif.innerHTML = '<span>' + message + '</span>';
    document.body.appendChild(notif);
    setTimeout(function(){ notif.remove(); }, 4000);
}
</script>

@endsection