@extends('themes.general.layouts.app')

@section('title', 'My Dashboard')

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- Breadcrumb -->
    <nav class="mb-6">
        <ol class="flex items-center space-x-2 text-sm">
            <li><a href="{{ route('home') }}" class="text-gray-500 hover:text-primary">Home</a></li>
            <li><i class="bi bi-chevron-right text-gray-400"></i></li>
            <li class="text-gray-900 font-medium">Dashboard</li>
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
                    <a href="{{ route('account.dashboard') }}" class="flex items-center p-3 rounded-lg bg-primary/10 text-primary font-medium">
                        <i class="bi bi-grid mr-3"></i> Dashboard
                    </a>
                    <a href="{{ route('account.profile') }}" class="flex items-center p-3 rounded-lg text-gray-600 hover:bg-gray-50 mb-1">
                        <i class="bi bi-person mr-3"></i> Profile
                    </a>
                    <a href="{{ route('account.orders') }}" class="flex items-center p-3 rounded-lg text-gray-600 hover:bg-gray-50 mb-1">
                        <i class="bi bi-bag mr-3"></i> Orders
                    </a>
                    <a href="{{ route('account.wishlist') }}" class="flex items-center p-3 rounded-lg text-gray-600 hover:bg-gray-50 mb-1">
                        <i class="bi bi-heart mr-3"></i> Wishlist
                    </a>
                    <a href="{{ route('account.addresses') }}" class="flex items-center p-3 rounded-lg text-gray-600 hover:bg-gray-50 mb-1">
                        <i class="bi bi-geo-alt mr-3"></i> Addresses
                    </a>
                    <a href="{{ route('tickets.index') }}" class="flex items-center p-3 rounded-lg text-gray-600 hover:bg-gray-50 mb-1">
                        <i class="bi bi-ticket-detailed mr-3"></i> Support Tickets
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
            <!-- Welcome Message -->
            <div class="bg-white rounded-xl shadow-sm overflow-hidden mb-6">
                <div class="p-6">
                    <h1 class="text-2xl font-bold text-gray-900">Welcome back, {{ auth()->user()->name }}!</h1>
                    <p class="text-gray-500 mt-1">Here's what's happening with your account</p>
                </div>
            </div>

            <!-- Stats Cards -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                <!-- Orders Card -->
                <div class="bg-white rounded-xl shadow-sm p-6">
                    <div class="flex items-center">
                        <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                            <i class="bi bi-bag text-xl text-blue-600"></i>
                        </div>
                        <div class="ml-4">
                            <p class="text-gray-500 text-sm">Total Orders</p>
                            <p class="text-2xl font-bold text-gray-900">{{ $totalOrders ?? 0 }}</p>
                        </div>
                    </div>
                </div>

                <!-- Wishlist Card -->
                <div class="bg-white rounded-xl shadow-sm p-6">
                    <div class="flex items-center">
                        <div class="w-12 h-12 bg-red-100 rounded-lg flex items-center justify-center">
                            <i class="bi bi-heart text-xl text-red-600"></i>
                        </div>
                        <div class="ml-4">
                            <p class="text-gray-500 text-sm">Wishlist</p>
                            <p class="text-2xl font-bold text-gray-900">{{ $wishlistCount ?? 0 }}</p>
                        </div>
                    </div>
                </div>

                <!-- Cart Card -->
                <div class="bg-white rounded-xl shadow-sm p-6">
                    <div class="flex items-center">
                        <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                            <i class="bi bi-cart text-xl text-green-600"></i>
                        </div>
                        <div class="ml-4">
                            <p class="text-gray-500 text-sm">Cart Items</p>
                            <p class="text-2xl font-bold text-gray-900">{{ $cartCount ?? 0 }}</p>
                        </div>
                    </div>
                </div>

                <!-- Spent Card -->
                <div class="bg-white rounded-xl shadow-sm p-6">
                    <div class="flex items-center">
                        <div class="w-12 h-12 bg-yellow-100 rounded-lg flex items-center justify-center">
                            <i class="bi bi-currency-dollar text-xl text-yellow-600"></i>
                        </div>
                        <div class="ml-4">
                            <p class="text-gray-500 text-sm">Total Spent</p>
                            <p class="text-2xl font-bold text-gray-900">৳{{ number_format($totalSpent ?? 0, 0) }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Orders -->
            <div class="bg-white rounded-xl shadow-sm overflow-hidden">
                <div class="p-6 border-b">
                    <div class="flex items-center justify-between">
                        <h2 class="text-xl font-bold text-gray-900">Recent Orders</h2>
                        <a href="{{ route('account.orders') }}" class="text-primary hover:text-green-700 text-sm font-medium">
                            View All <i class="bi bi-arrow-right ml-1"></i>
                        </a>
                    </div>
                </div>

                @if(isset($recentOrders) && $recentOrders->count() > 0)
                <div class="divide-y">
                    @foreach($recentOrders as $order)
                    <div class="p-6 flex items-center justify-between">
                        <div class="flex items-center gap-4">
                            <div class="w-12 h-12 bg-gray-100 rounded-lg flex items-center justify-center">
                                <i class="bi bi-bag text-gray-600"></i>
                            </div>
                            <div>
                                <p class="font-semibold text-gray-900">Order #{{ $order->id }}</p>
                                <p class="text-sm text-gray-500">{{ $order->created_at->format('M d, Y') }}</p>
                            </div>
                        </div>
                        <div class="text-right">
                            <span class="px-3 py-1 text-xs rounded-full 
                                @if($order->status == 'completed') bg-green-100 text-green-800
                                @elseif($order->status == 'processing') bg-blue-100 text-blue-800
                                @elseif($order->status == 'pending') bg-yellow-100 text-yellow-800
                                @elseif($order->status == 'shipped') bg-purple-100 text-purple-800
                                @else bg-gray-100 text-gray-800
                                @endif">
                                {{ ucfirst($order->status) }}
                            </span>
                            <p class="text-lg font-bold text-gray-900 mt-1">৳{{ number_format($order->total, 0) }}</p>
                        </div>
                    </div>
                    @endforeach
                </div>
                @else
                <div class="p-12 text-center">
                    <div class="w-20 h-20 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="bi bi-bag text-3xl text-gray-400"></i>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">No orders yet</h3>
                    <p class="text-gray-500 mb-6">Start shopping to see your orders here</p>
                    <a href="{{ route('products.index') }}" class="inline-flex items-center bg-halal-green text-white px-6 py-3 rounded-lg hover:text-amber-400 transition-colors font-medium">
                        <i class="bi bi-shopping-bag mr-2"></i> Start Shopping
                    </a>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
