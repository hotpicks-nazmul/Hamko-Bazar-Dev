@extends('themes.general.layouts.app')

@section('title', 'Login')

@section('content')
<div class="min-h-screen flex items-center justify-center bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8">
        <div>
            <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">
                Sign in to your account
            </h2>
            <p class="mt-2 text-center text-sm text-gray-600">
                Don't have an account?
                <a href="{{ route('register') }}" class="font-medium text-halal-green hover:text-halal-dark">
                    Register
                </a>
            </p>
        </div>
        
        @php
            $socialSettings = \App\Models\Setting::where('group', 'social_login')->pluck('value', 'key');
            $googleEnabled = ($socialSettings['google_enabled'] ?? '0') === '1';
            $facebookEnabled = ($socialSettings['facebook_enabled'] ?? '0') === '1';
        @endphp
        
        @if($googleEnabled || $facebookEnabled)
        <div class="space-y-3">
            @if($googleEnabled)
            <a href="{{ route('social.login', 'google') }}" class="w-full flex items-center justify-center gap-3 bg-white border border-gray-300 text-gray-700 py-3 px-4 rounded-lg hover:bg-gray-50 transition-colors">
                <svg class="w-5 h-5" viewBox="0 0 24 24">
                    <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                    <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                    <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
                    <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
                </svg>
                <span class="font-medium">Continue with Google</span>
            </a>
            @endif
            
            @if($facebookEnabled)
            <a href="{{ route('social.login', 'facebook') }}" class="w-full flex items-center justify-center gap-3 bg-[#1877F2] text-white py-3 px-4 rounded-lg hover:bg-[#166FE5] transition-colors">
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                </svg>
                <span class="font-medium">Continue with Facebook</span>
            </a>
            @endif
            
            @if($googleEnabled || $facebookEnabled)
            <div class="relative">
                <div class="absolute inset-0 flex items-center">
                    <div class="w-full border-t border-gray-300"></div>
                </div>
                <div class="relative flex justify-center text-sm">
                    <span class="px-2 bg-gray-50 text-gray-500">Or login with phone &amp; password</span>
                </div>
            </div>
            @endif
        </div>
        @endif

        @if(session('status'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                {{ session('status') }}
            </div>
        @endif

        @if(session('error'))
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                {{ session('error') }}
            </div>
        @endif

        @if($errors->any())
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                <ul class="list-disc list-inside">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form class="mt-8 space-y-6" action="{{ route('login.phone-password') }}" method="POST">
            @csrf

            <div class="rounded-md shadow-sm space-y-4">
                <div>
                    <label for="phone" class="block text-sm font-medium text-gray-700">Phone Number</label>
                    <div class="flex mt-1">
                        <div class="relative" style="min-width:105px">
                            <div class="inline-flex items-center gap-1.5 px-3 py-2 border border-r-0 border-gray-300 rounded-l-md bg-gray-100 text-gray-600 text-sm">
                                <img src="https://flagcdn.com/16x12/bd.png" alt="" class="w-4 h-3 inline-block">
                                <span>+880</span>
                            </div>
                        </div>
                        <input id="phone" name="phone" type="tel" required maxlength="15" oninput="this.value=this.value.replace(/\D/g,'')"
                            class="appearance-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-r-md focus:outline-none focus:ring-halal-green focus:border-halal-green sm:text-sm"
                            placeholder="Phone Number" value="{{ old('phone') }}">
                    </div>
                </div>
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
                    <input id="password" name="password" type="password" autocomplete="current-password" required
                        class="mt-1 appearance-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-md focus:outline-none focus:ring-halal-green focus:border-halal-green sm:text-sm"
                        placeholder="Password">
                </div>
            </div>

            <div>
                <button type="submit"
                    class="group relative w-full flex justify-center py-3 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-halal-green hover:text-amber-400 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-halal-green">
                    <i class="bi bi-box-arrow-in-right mr-2"></i> Sign in
                </button>
            </div>
        </form>

        <div class="text-center">
            <a href="{{ route('login.phone') }}" class="text-sm font-medium text-halal-green hover:text-halal-dark">
                <i class="bi bi-phone mr-1"></i> Login with OTP
            </a>
        </div>
    </div>
</div>
@endsection