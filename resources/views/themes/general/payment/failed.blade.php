@extends('themes.general.layouts.app')

@section('title', 'Payment Failed')

@section('content')
<div class="min-h-screen flex items-center justify-center px-4 py-12" style="background: linear-gradient(135deg, #fef2f2 0%, #fff 50%, #fef2f2 100%);">
    <div class="max-w-md w-full bg-white rounded-2xl shadow-xl overflow-hidden">
        <!-- Top accent bar -->
        <div class="h-2 bg-gradient-to-r from-red-500 to-red-400"></div>
        
        <div class="p-8 text-center">
            <!-- Icon -->
            <div class="mx-auto w-20 h-20 bg-red-50 rounded-full flex items-center justify-center mb-6">
                <svg class="w-10 h-10 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z"/>
                </svg>
            </div>

            <!-- Title -->
            <h1 class="text-2xl font-bold text-gray-900 mb-2">Payment Failed</h1>
            
            <!-- Message -->
            <p class="text-gray-500 mb-2">We were unable to process your payment.</p>
            <p class="text-sm text-gray-400 mb-8">This could be due to insufficient funds, a declined transaction, or a temporary issue with the payment gateway. Please try again or choose a different payment method.</p>

            <!-- Divider -->
            <div class="border-t border-gray-100 mb-6"></div>

            <!-- What to do next -->
            <div class="bg-gray-50 rounded-xl p-4 mb-6 text-left">
                <h3 class="text-sm font-semibold text-gray-700 mb-3">What would you like to do?</h3>
                <ul class="space-y-2">
                    <li class="flex items-start gap-2 text-sm text-gray-600">
                        <svg class="w-4 h-4 text-red-400 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                        <span>Go back to checkout and try a different payment method</span>
                    </li>
                    <li class="flex items-start gap-2 text-sm text-gray-600">
                        <svg class="w-4 h-4 text-red-400 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                        <span>Check your card/bKash balance and try again</span>
                    </li>
                    <li class="flex items-start gap-2 text-sm text-gray-600">
                        <svg class="w-4 h-4 text-red-400 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                        <span>Contact our support team if the issue persists</span>
                    </li>
                </ul>
            </div>

            <!-- Buttons -->
            <div class="space-y-3">
                <a href="{{ route('checkout.index') }}" 
                   class="block w-full py-3 px-4 bg-gradient-to-r from-red-500 to-red-600 text-white font-medium rounded-xl hover:from-red-600 hover:to-red-700 transition-all duration-200 shadow-md hover:shadow-lg">
                    <i class="bi bi-arrow-left me-2"></i>Back to Checkout
                </a>
                <a href="{{ route('home') }}" 
                   class="block w-full py-3 px-4 bg-gray-50 text-gray-600 font-medium rounded-xl hover:bg-gray-100 transition-all duration-200 border border-gray-200">
                    Return to Home
                </a>
            </div>

            <!-- Order reference -->
            @if(isset($orderNumber))
            <p class="mt-6 text-xs text-gray-400">
                Order reference: <span class="font-mono">{{ $orderNumber }}</span>
            </p>
            @endif
        </div>
    </div>
</div>
@endsection
