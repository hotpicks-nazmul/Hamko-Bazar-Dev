@extends('themes.general.layouts.app')

@section('title', 'Payment Cancelled')

@section('content')
{{-- routes/web.php has pointed /checkout/cancel at this view since the payment gateway
     cancel_url was wired, and the view never existed: the route answered 500 for every
     customer who pressed "Cancel" on the gateway page. --}}
<div class="container mx-auto px-4 py-12">
    <div class="max-w-2xl mx-auto text-center">
        <div class="w-24 h-24 mx-auto bg-amber-100 rounded-full flex items-center justify-center mb-6">
            <i class="bi bi-x-circle text-5xl text-amber-500"></i>
        </div>

        <h1 class="text-3xl font-bold text-gray-800 mb-4">Payment Cancelled</h1>
        <p class="text-gray-600 mb-8">
            Your payment was cancelled and no money has been taken. Your cart is still waiting for you.
        </p>

        <div class="flex flex-col sm:flex-row gap-3 justify-center">
            <a href="{{ route('cart.index') }}"
               class="inline-flex items-center justify-center bg-halal-green text-white px-6 py-3 rounded-lg hover:text-amber-400 transition-colors font-medium">
                <i class="bi bi-cart me-2"></i>Back to Cart
            </a>
            <a href="{{ route('checkout.index') }}"
               class="inline-flex items-center justify-center bg-amber-500 text-white px-6 py-3 rounded-lg hover:bg-amber-600 transition-colors font-medium">
                <i class="bi bi-arrow-repeat me-2"></i>Try Checkout Again
            </a>
        </div>
    </div>
</div>
@endsection
