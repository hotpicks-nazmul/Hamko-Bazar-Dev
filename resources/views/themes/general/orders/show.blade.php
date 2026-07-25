@extends('themes.general.layouts.app')

@section('title', 'Order Details')

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- Breadcrumb -->
    <nav class="mb-6">
        <ol class="flex items-center space-x-2 text-sm">
            <li><a href="{{ route('home') }}" class="text-gray-500 hover:text-primary">Home</a></li>
            <li><i class="bi bi-chevron-right text-gray-400"></i></li>
            <li><a href="{{ route('account.dashboard') }}" class="text-gray-500 hover:text-primary">Account</a></li>
            <li><i class="bi bi-chevron-right text-gray-400"></i></li>
            <li><a href="{{ route('account.orders') }}" class="text-gray-500 hover:text-primary">Orders</a></li>
            <li><i class="bi bi-chevron-right text-gray-400"></i></li>
            <li class="text-gray-900 font-medium">#{{ $order->order_number }}</li>
        </ol>
    </nav>

    <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
        <!-- Sidebar -->
        <div class="lg:col-span-1">
            <div class="bg-white rounded-xl shadow-sm overflow-hidden lg:sticky lg:top-24">
                <div class="p-6 text-center border-b">
                    <div class="w-20 h-20 bg-primary/10 rounded-full flex items-center justify-center mx-auto mb-3 overflow-hidden">
                        @if(auth()->user()->avatar)
                        <img src="{{ Storage::url(auth()->user()->avatar) }}" alt="{{ auth()->user()->name }}" class="w-full h-full object-cover">
                        @else
                        <i class="bi bi-person text-3xl text-primary"></i>
                        @endif
                    </div>
                    <h3 class="font-semibold text-gray-900">{{ auth()->user()->name }}</h3>
                    <p class="text-sm text-gray-500">{{ auth()->user()->email }}</p>
                </div>
                <nav class="p-4">
                    <a href="{{ route('account.dashboard') }}" class="flex items-center p-3 rounded-lg text-gray-600 hover:bg-gray-50 mb-1">
                        <i class="bi bi-grid mr-3"></i> Dashboard
                    </a>
                    <a href="{{ route('account.profile') }}" class="flex items-center p-3 rounded-lg text-gray-600 hover:bg-gray-50 mb-1">
                        <i class="bi bi-person mr-3"></i> Profile
                    </a>
                    <a href="{{ route('account.orders') }}" class="flex items-center p-3 rounded-lg bg-primary/10 text-primary font-medium">
                        <i class="bi bi-bag mr-3"></i> Orders
                    </a>
                    <a href="{{ route('account.wishlist') }}" class="flex items-center p-3 rounded-lg text-gray-600 hover:bg-gray-50 mb-1">
                        <i class="bi bi-heart mr-3"></i> Wishlist
                    </a>
                    <a href="{{ route('account.addresses') }}" class="flex items-center p-3 rounded-lg text-gray-600 hover:bg-gray-50 mb-1">
                        <i class="bi bi-geo-alt mr-3"></i> Addresses
                    </a>
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button type="submit" class="w-full flex items-center p-3 rounded-lg text-red-500 hover:bg-red-50">
                            <i class="bi bi-box-arrow-right mr-3"></i> Logout
                        </button>
                    </form>
                </nav>
            </div>
        </div>

        <!-- Main Content -->
        <div class="lg:col-span-3">
            @if(session('success'))
            <div class="bg-green-50 border-l-4 border-green-500 text-green-700 px-4 py-3 rounded-lg mb-6">
                {{ session('success') }}
            </div>
            @endif

            @if(session('error'))
            <div class="bg-red-50 border-l-4 border-red-500 text-red-700 px-4 py-3 rounded-lg mb-6">
                {{ session('error') }}
            </div>
            @endif

            <!-- Order Header -->
            <div class="bg-white rounded-xl shadow-sm overflow-hidden mb-6">
                <div class="p-6 border-b flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                    <div>
                        <h1 class="text-xl font-bold text-gray-900">Order #{{ $order->order_number }}</h1>
                        <p class="text-gray-500 text-sm mt-1">Placed on {{ $order->created_at->format('M d, Y \a\t h:i A') }}</p>
                    </div>
                    <div class="flex items-center gap-3">
                        @php
                            $statusColors = [
                                'pending' => 'bg-yellow-100 text-yellow-800',
                                'processing' => 'bg-blue-100 text-blue-800',
                                'confirmed' => 'bg-teal-100 text-teal-800',
                                'shipped' => 'bg-purple-100 text-purple-800',
                                'delivered' => 'bg-green-100 text-green-800',
                                'cancelled' => 'bg-red-100 text-red-800',
                            ];
                        @endphp
                        <span class="px-4 py-2 rounded-full text-sm font-medium {{ $statusColors[$order->status] ?? 'bg-gray-100 text-gray-800' }}">
                            {{ ucfirst($order->status) }}
                        </span>
                        @if(in_array($order->status, ['pending', 'processing']))
                        <form action="{{ route('account.orders.cancel', $order) }}" method="POST" onsubmit="return confirm('Are you sure you want to cancel this order?')">
                            @csrf
                            @method('POST')
                            <button type="submit" class="bg-red-500 text-white px-4 py-2 rounded-lg hover:bg-red-600 transition text-sm font-medium">
                                Cancel Order
                            </button>
                        </form>
                        @endif
                    </div>
                </div>

                <!-- Order Status Timeline -->
                <div class="p-6">
                    <h3 class="font-semibold text-gray-900 mb-4">Order Status</h3>
                    <div class="flex items-center justify-between">
                        @php
                            $statuses = ['pending', 'confirmed', 'processing', 'shipped', 'delivered'];
                            $currentIndex = array_search($order->status, $statuses);
                            if ($currentIndex === false) $currentIndex = -1;
                        @endphp
                        @foreach($statuses as $index => $status)
                        <div class="flex flex-col items-center {{ $index < 4 ? 'flex-1' : '' }}">
                            <div class="w-10 h-10 rounded-full flex items-center justify-center {{ $index <= $currentIndex ? 'bg-primary text-white' : 'bg-gray-200 text-gray-400' }}">
                                @if($status === 'pending')
                                <i class="bi bi-clock"></i>
                                @elseif($status === 'processing')
                                <i class="bi bi-gear"></i>
                                @elseif($status === 'confirmed')
                                <i class="bi bi-check-circle"></i>
                                @elseif($status === 'shipped')
                                <i class="bi bi-truck"></i>
                                @else
                                <i class="bi bi-check-lg"></i>
                                @endif
                            </div>
                            <span class="text-xs mt-2 {{ $index <= $currentIndex ? 'text-gray-900 font-medium' : 'text-gray-400' }}">{{ ucfirst($status) }}</span>
                        </div>
                        @if($index < 4)
                        <div class="flex-1 h-1 mx-2 {{ $index < $currentIndex ? 'bg-primary' : 'bg-gray-200' }}"></div>
                        @endif
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <!-- Shipping Address -->
                <div class="bg-white rounded-xl shadow-sm p-6">
                    <h3 class="font-semibold text-gray-900 mb-3">Shipping Address</h3>
                    <p class="text-gray-600">
                        {{ $order->shipping_name ?? $order->billing_name }}<br>
                        {{ $order->shipping_address ?? $order->billing_address }}<br>
                        {{ $order->shipping_city ?? $order->billing_city }}, {{ $order->shipping_postcode ?? $order->billing_postcode }}<br>
                        Phone: {{ $order->shipping_phone ?? $order->billing_phone }}
                    </p>
                </div>

                <!-- Payment Info -->
                <div class="bg-white rounded-xl shadow-sm p-6">
                    <h3 class="font-semibold text-gray-900 mb-3">Payment Information</h3>
                    <div class="space-y-2">
                        <div class="flex justify-between">
                            <span class="text-gray-600">Payment Method</span>
                            <span class="font-medium">{{ ucfirst($order->payment_method ?? 'Cash on Delivery') }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Payment Status</span>
                            @php
                                $paymentColors = [
                                    'pending' => 'text-yellow-600',
                                    'paid' => 'text-green-600',
                                    'failed' => 'text-red-600',
                                ];
                            @endphp
                            <span class="font-medium {{ $paymentColors[$order->payment_status] ?? 'text-gray-600' }}">{{ ucfirst($order->payment_status ?? 'pending') }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Order Items -->
            <div class="bg-white rounded-xl shadow-sm overflow-hidden mb-6">
                <div class="p-6 border-b">
                    <h3 class="font-semibold text-gray-900">Order Items</h3>
                </div>
                <div class="divide-y">
                    @foreach($order->items as $item)
                    <div class="p-6 flex items-center gap-4">
                        <div class="w-20 h-20 bg-gray-100 rounded-lg overflow-hidden flex-shrink-0">
                            @if($item->product && $item->product->featured_image)
                            <img src="{{ $item->product->featured_image }}" alt="{{ $item->product_name }}" class="w-full h-full object-cover" onerror="this.onerror=null;this.src='https://placehold.co/80?text=No+Image'">
                            @else
                            <div class="w-full h-full flex items-center justify-center">
                                <i class="bi bi-image text-gray-400 text-2xl"></i>
                            </div>
                            @endif
                        </div>
                        <div class="flex-1">
                            <h4 class="font-medium text-gray-900">{{ $item->product_name }}</h4>
                            @if($item->variant)
                            <p class="text-sm text-gray-500">{{ $item->variant }}</p>
                            @endif
                            <p class="text-sm text-gray-500">Qty: {{ $item->quantity }}</p>
                        </div>
                        <div class="text-right">
                            <p class="font-medium text-gray-900">৳{{ number_format($item->price * $item->quantity, 2) }}</p>
                            <p class="text-sm text-gray-500">৳{{ number_format($item->price, 2) }} each</p>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>

            <!-- Order Summary -->
            <div class="bg-white rounded-xl shadow-sm p-6">
                <h3 class="font-semibold text-gray-900 mb-4">Order Summary</h3>
                <div class="space-y-3">
                    <div class="flex justify-between">
                        <span class="text-gray-600">Subtotal</span>
                        <span class="text-gray-900">৳{{ number_format($order->subtotal, 2) }}</span>
                    </div>
                    @if($order->discount > 0)
                    <div class="flex justify-between text-green-600">
                        <span>Discount</span>
                        <span>-৳{{ number_format($order->discount, 2) }}</span>
                    </div>
                    @endif
                    <div class="flex justify-between">
                        <span class="text-gray-600">Shipping</span>
                        <span class="text-gray-900">{{ $order->shipping_cost > 0 ? '৳' . number_format($order->shipping_cost, 2) : 'Free' }}</span>
                    </div>
                    @if($order->tax > 0)
                    <div class="flex justify-between">
                        <span class="text-gray-600">Tax</span>
                        <span class="text-gray-900">৳{{ number_format($order->tax, 2) }}</span>
                    </div>
                    @endif
                    <div class="border-t pt-3 flex justify-between">
                        <span class="font-semibold text-gray-900">Total</span>
                        <span class="font-bold text-xl text-gray-900">৳{{ number_format($order->total, 2) }}</span>
                    </div>
                </div>
            </div>

            <!-- Back Button -->
            <div class="mt-6">
                <a href="{{ route('account.orders') }}" class="inline-flex items-center text-primary hover:text-green-700">
                    <i class="bi bi-arrow-left mr-2"></i> Back to Orders
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
