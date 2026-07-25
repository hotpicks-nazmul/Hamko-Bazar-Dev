@extends('themes.general.layouts.app')

@section('title', 'Order Successful')

@section('content')
<div class="container mx-auto px-4 py-12">
    <div class="max-w-2xl mx-auto text-center">
        <div class="w-24 h-24 mx-auto bg-green-100 rounded-full flex items-center justify-center mb-6">
            <i class="bi bi-check-circle text-5xl text-green-500"></i>
        </div>
        
        <h1 class="text-3xl font-bold text-gray-800 mb-4">Thank You for Your Order!</h1>
        <p class="text-gray-600 mb-8">Your order has been placed successfully. We'll send you a confirmation email shortly.</p>
        
        @if(isset($order))
        <div class="bg-white rounded-lg shadow-sm p-6 mb-8 text-left">
            <div class="flex justify-between items-center mb-4">
                <h3 class="font-bold text-lg">Order #{{ $order->order_number }}</h3>
                @php
                    $statusColors = [
                        'pending' => 'bg-yellow-100 text-yellow-800',
                        'processing' => 'bg-blue-100 text-blue-800',
                        'shipped' => 'bg-purple-100 text-purple-800',
                        'delivered' => 'bg-green-100 text-green-800',
                        'cancelled' => 'bg-red-100 text-red-800',
                    ];
                    $badgeClass = $statusColors[$order->status ?? 'pending'] ?? 'bg-gray-100 text-gray-800';
                @endphp
                <span class="px-3 py-1 text-xs rounded-full {{ $badgeClass }}">
                    {{ ucfirst($order->status ?? 'Pending') }}
                </span>
            </div>
            
            <div class="border-t pt-4">
                <h4 class="font-medium mb-3">Order Items</h4>
                <div class="space-y-3">
                    @foreach($order->items as $item)
                    <div class="flex justify-between items-center">
                        <div>
                            <p class="font-medium">{{ $item->product_name }}</p>
                            <p class="text-sm text-gray-500">Qty: {{ $item->quantity }}</p>
                        </div>
                        <p class="font-medium">৳{{ number_format($item->total, 2) }}</p>
                    </div>
                    @endforeach
                </div>
            </div>
            
            <div class="border-t mt-4 pt-4">
                <div class="flex justify-between mb-2">
                    <span class="text-gray-600">Subtotal</span>
                    <span>৳{{ number_format($order->subtotal, 2) }}</span>
                </div>
                <div class="flex justify-between mb-2">
                    <span class="text-gray-600">Shipping</span>
                    <span>{{ $order->shipping_cost > 0 ? '৳' . number_format($order->shipping_cost, 2) : 'Free' }}</span>
                </div>
                @if($order->discount > 0)
                <div class="flex justify-between mb-2 text-green-600">
                    <span>Discount</span>
                    <span>-৳{{ number_format($order->discount, 2) }}</span>
                </div>
                @endif
                <div class="flex justify-between font-bold text-lg border-t mt-2 pt-2">
                    <span>Total</span>
                    <span class="text-halal-green">৳{{ number_format($order->total, 2) }}</span>
                </div>
            </div>
        </div>
        @endif
        
        <div class="flex flex-col sm:flex-row gap-4 justify-center">
            <a href="{{ route('home') }}" class="bg-halal-green text-white px-8 py-3 rounded-lg font-medium hover:text-amber-400 transition-colors">
                <i class="bi bi-house mr-2"></i> Continue Shopping
            </a>
            @auth
            <a href="{{ route('account.orders') }}" class="bg-white border border-halal-green text-halal-green px-8 py-3 rounded-lg font-medium hover:bg-green-50 transition-colors">
                <i class="bi bi-bag mr-2"></i> View Orders
            </a>
            @endauth
        </div>
    </div>
</div>
@endsection
