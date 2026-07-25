@extends('themes.general.layouts.app')

@section('title', 'My Data')

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- Breadcrumb -->
    <nav class="mb-6">
        <ol class="flex items-center space-x-2 text-sm">
            <li><a href="{{ route('home') }}" class="text-gray-500 hover:text-primary">Home</a></li>
            <li><i class="bi bi-chevron-right text-gray-400"></i></li>
            <li><a href="{{ route('account.dashboard') }}" class="text-gray-500 hover:text-primary">Account</a></li>
            <li><i class="bi bi-chevron-right text-gray-400"></i></li>
            <li class="text-gray-900 font-medium">My Data</li>
        </ol>
    </nav>

    <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
        <!-- Sidebar -->
        <div class="lg:col-span-1">
            <div class="bg-white rounded-xl shadow-sm overflow-hidden lg:sticky lg:top-24">
                <div class="p-6 text-center border-b">
                    <div class="w-20 h-20 bg-primary/10 rounded-full flex items-center justify-center mx-auto mb-3 overflow-hidden">
                        @if($user->avatar)
                        <img src="{{ Storage::url($user->avatar) }}" alt="{{ $user->name }}" class="w-full h-full object-cover">
                        @else
                        <i class="bi bi-person text-3xl text-primary"></i>
                        @endif
                    </div>
                    <h3 class="font-semibold text-gray-900">{{ $user->name }}</h3>
                    <p class="text-sm text-gray-500">{{ $user->email }}</p>
                </div>
                <nav class="p-4">
                    <a href="{{ route('account.dashboard') }}" class="flex items-center p-3 rounded-lg text-gray-600 hover:bg-gray-50 mb-1">
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
                    <a href="{{ route('account.my-data') }}" class="flex items-center p-3 rounded-lg bg-primary/10 text-primary font-medium">
                        <i class="bi bi-download mr-3"></i> My Data
                    </a>
                    <a href="{{ route('account.notifications') }}" class="flex items-center p-3 rounded-lg text-gray-600 hover:bg-gray-50 mb-1">
                        <i class="bi bi-bell mr-3"></i> Notifications
                    </a>
                    <form action="{{ route('logout') }}" method="POST" class="mb-1">
                        @csrf
                        <button type="submit" class="w-full flex items-center p-3 rounded-lg text-red-600 hover:bg-red-50">
                            <i class="bi bi-box-arrow-right mr-3"></i> Logout
                        </button>
                    </form>
                </nav>
            </div>
        </div>

        <!-- Main Content -->
        <div class="lg:col-span-3">
            <div class="bg-white rounded-xl shadow-sm p-6">
                <h2 class="text-xl font-semibold text-gray-900 mb-6">My Data Export</h2>
                
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
                    <div class="flex">
                        <i class="bi bi-info-circle text-blue-600 text-xl mr-3 mt-0.5"></i>
                        <div>
                            <h4 class="font-medium text-blue-900">Data Export</h4>
                            <p class="text-sm text-blue-700 mt-1">
                                Download a copy of your personal data including orders, wishlist, addresses, and profile information.
                                This is useful for GDPR compliance and data portability.
                            </p>
                        </div>
                    </div>
                </div>

                <form action="{{ route('account.my-data.export') }}" method="POST" id="exportForm">
                    @csrf
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 mb-3">Select Data to Export</label>
                        <div class="space-y-3">
                            <label class="flex items-center p-4 border border-gray-200 rounded-lg hover:bg-gray-50 cursor-pointer transition">
                                <input type="radio" name="export_type" value="all" class="text-primary focus:ring-primary" checked>
                                <div class="ml-3">
                                    <span class="block text-sm font-medium text-gray-900">All Data</span>
                                    <span class="block text-sm text-gray-500">Export all your data including orders, wishlist, addresses, and notifications</span>
                                </div>
                            </label>
                            
                            <label class="flex items-center p-4 border border-gray-200 rounded-lg hover:bg-gray-50 cursor-pointer transition">
                                <input type="radio" name="export_type" value="orders" class="text-primary focus:ring-primary">
                                <div class="ml-3">
                                    <span class="block text-sm font-medium text-gray-900">Orders</span>
                                    <span class="block text-sm text-gray-500">Export your order history and details</span>
                                </div>
                            </label>
                            
                            <label class="flex items-center p-4 border border-gray-200 rounded-lg hover:bg-gray-50 cursor-pointer transition">
                                <input type="radio" name="export_type" value="wishlist" class="text-primary focus:ring-primary">
                                <div class="ml-3">
                                    <span class="block text-sm font-medium text-gray-900">Wishlist</span>
                                    <span class="block text-sm text-gray-500">Export your wishlist items</span>
                                </div>
                            </label>
                            
                            <label class="flex items-center p-4 border border-gray-200 rounded-lg hover:bg-gray-50 cursor-pointer transition">
                                <input type="radio" name="export_type" value="addresses" class="text-primary focus:ring-primary">
                                <div class="ml-3">
                                    <span class="block text-sm font-medium text-gray-900">Addresses</span>
                                    <span class="block text-sm text-gray-500">Export your saved addresses</span>
                                </div>
                            </label>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary" id="exportBtn">
                        <i class="bi bi-download mr-2"></i> Export Data
                    </button>
                </form>

                <hr class="my-8 border-gray-200">

                <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                    <div class="flex">
                        <i class="bi bi-exclamation-triangle text-yellow-600 text-xl mr-3 mt-0.5"></i>
                        <div>
                            <h4 class="font-medium text-yellow-900">Data Privacy</h4>
                            <p class="text-sm text-yellow-700 mt-1">
                                Your data is exported in JSON format. The exported data is for your personal use only. 
                                If you have any concerns about your data or wish to delete your account, please contact support.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.getElementById('exportForm').addEventListener('submit', function() {
        const btn = document.getElementById('exportBtn');
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm mr-2"></span> Exporting...';
    });
</script>
@endpush
@endsection
