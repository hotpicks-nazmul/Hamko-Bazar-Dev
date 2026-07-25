@extends('themes.general.layouts.app')

@section('title', 'My Orders')

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- Breadcrumb -->
    <nav class="mb-6">
        <ol class="flex items-center space-x-2 text-sm">
            <li><a href="{{ route('home') }}" class="text-gray-500 hover:text-primary">Home</a></li>
            <li><i class="bi bi-chevron-right text-gray-400"></i></li>
            <li><a href="{{ route('account.dashboard') }}" class="text-gray-500 hover:text-primary">Account</a></li>
            <li><i class="bi bi-chevron-right text-gray-400"></i></li>
            <li class="text-gray-900 font-medium">Orders</li>
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
            <div class="bg-white rounded-xl shadow-sm overflow-hidden">
                <div class="p-6 border-b">
                    <h1 class="text-xl font-bold text-gray-900">My Orders</h1>
                    <p class="text-gray-500 text-sm mt-1">View and track your orders</p>
                </div>

                @if(session('success'))
                <div class="bg-green-50 border-l-4 border-green-500 text-green-700 px-4 py-3 mx-6 mt-4">
                    {{ session('success') }}
                </div>
                @endif

                @if($orders->count() > 0)
                <div class="divide-y">
                    @foreach($orders as $order)
                    <div class="p-6">
                        <div class="flex items-center justify-between mb-4">
                            <div>
                                <p class="text-sm text-gray-500">Order #{{ $order->order_number }}</p>
                                <p class="text-xs text-gray-400">{{ $order->created_at->format('M d, Y \a\t h:i A') }}</p>
                            </div>
                            <div class="text-right">
                                @php
                                    $statusColors = [
                                        'pending' => 'bg-yellow-100 text-yellow-800',
                                        'processing' => 'bg-blue-100 text-blue-800',
                                        'shipped' => 'bg-purple-100 text-purple-800',
                                        'delivered' => 'bg-green-100 text-green-800',
                                        'cancelled' => 'bg-red-100 text-red-800',
                                    ];
                                @endphp
                                <span class="px-3 py-1 rounded-full text-xs font-medium {{ $statusColors[$order->status] ?? 'bg-gray-100 text-gray-800' }}">
                                    {{ ucfirst($order->status) }}
                                </span>
                            </div>
                        </div>

                        <!-- Order Items Preview -->
                        <div class="flex items-center gap-4 mb-4">
                            @foreach($order->items->take(3) as $item)
                            <div class="w-16 h-16 bg-gray-100 rounded-lg overflow-hidden">
                                @if($item->product && $item->product->featured_image)
                                <img src="{{ $item->product->featured_image }}" alt="{{ $item->product_name }}" class="w-full h-full object-cover" onerror="this.onerror=null;this.src='https://placehold.co/64?text=No+Image'">
                                @else
                                <div class="w-full h-full flex items-center justify-center">
                                    <i class="bi bi-image text-gray-400"></i>
                                </div>
                                @endif
                            </div>
                            @endforeach
                            @if($order->items->count() > 3)
                            <div class="w-16 h-16 bg-gray-100 rounded-lg flex items-center justify-center">
                                <span class="text-sm text-gray-500">+{{ $order->items->count() - 3 }}</span>
                            </div>
                            @endif
                        </div>

                        <div class="flex items-center justify-between">
                            <div>
                                <span class="text-lg font-bold text-gray-900">৳{{ number_format($order->total, 2) }}</span>
                                <span class="text-sm text-gray-500 ml-2">{{ $order->items->count() }} items</span>
                            </div>
                            <div class="flex items-center gap-2">
                                @if(in_array($order->status, ['pending', 'processing']))
                                <form action="{{ route('account.orders.cancel', $order) }}" method="POST" onsubmit="return confirm('Are you sure you want to cancel this order?')">
                                    @csrf
                                    @method('POST')
                                    <button type="submit" class="text-red-500 hover:text-red-700 text-sm font-medium">
                                        Cancel
                                    </button>
                                </form>
                                @endif
                                <a href="{{ route('account.orders.show', $order) }}" class="bg-primary text-white px-4 py-2 rounded-lg hover:bg-green-600 transition text-sm font-medium">
                                    View Details
                                </a>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>

                <!-- Pagination -->
                @if($orders->hasPages())
                <div class="p-6 border-t">
                    {{ $orders->links() }}
                </div>
                @endif
                @else
                <div class="p-12 text-center">
                    <div class="w-24 h-24 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="bi bi-bag text-4xl text-gray-400"></i>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 mb-2">No orders yet</h3>
                    <p class="text-gray-500 mb-6">Start shopping to see your orders here</p>
                    <a href="{{ route('products.index') }}" class="inline-flex items-center bg-primary text-white px-6 py-3 rounded-lg hover:bg-green-600 transition font-medium">
                        <i class="bi bi-shopping-bag mr-2"></i> Start Shopping
                    </a>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
