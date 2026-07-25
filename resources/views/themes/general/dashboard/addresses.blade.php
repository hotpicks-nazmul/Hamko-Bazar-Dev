@extends('themes.general.layouts.app')

@section('title', 'My Addresses')

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- Breadcrumb -->
    <nav class="mb-6">
        <ol class="flex items-center space-x-2 text-sm">
            <li><a href="{{ route('home') }}" class="text-gray-500 hover:text-primary">Home</a></li>
            <li><i class="bi bi-chevron-right text-gray-400"></i></li>
            <li><a href="{{ route('account.dashboard') }}" class="text-gray-500 hover:text-primary">Account</a></li>
            <li><i class="bi bi-chevron-right text-gray-400"></i></li>
            <li class="text-gray-900 font-medium">Addresses</li>
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
                    <a href="{{ route('account.orders') }}" class="flex items-center p-3 rounded-lg text-gray-600 hover:bg-gray-50 mb-1">
                        <i class="bi bi-bag mr-3"></i> Orders
                    </a>
                    <a href="{{ route('account.wishlist') }}" class="flex items-center p-3 rounded-lg text-gray-600 hover:bg-gray-50 mb-1">
                        <i class="bi bi-heart mr-3"></i> Wishlist
                    </a>
                    <a href="{{ route('account.addresses') }}" class="flex items-center p-3 rounded-lg bg-primary/10 text-primary font-medium">
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
            <div class="bg-white rounded-xl shadow-sm overflow-hidden">
                <div class="p-6 border-b flex items-center justify-between">
                    <div>
                        <h1 class="text-xl font-bold text-gray-900">My Addresses</h1>
                        <p class="text-gray-500 text-sm mt-1">Manage your delivery addresses</p>
                    </div>
                    <button onclick="openAddressModal()" class="bg-primary text-white px-4 py-2 rounded-lg hover:bg-green-600 transition text-sm font-medium">
                        <i class="bi bi-plus-lg mr-1"></i> Add New Address
                    </button>
                </div>

                @if(session('success'))
                <div class="bg-green-50 border-l-4 border-green-500 text-green-700 px-4 py-3 mx-6 mt-4">
                    {{ session('success') }}
                </div>
                @endif

                @if($addresses->count() > 0)
                <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-4">
                    @foreach($addresses as $address)
                    <div class="border rounded-xl p-4 relative {{ $address->is_default ? 'border-primary bg-primary/5' : 'border-gray-200' }}">
                        @if($address->is_default)
                        <span class="absolute top-2 right-2 bg-primary text-white text-xs px-2 py-1 rounded">Default</span>
                        @endif
                        
                        <div class="pr-16">
                            <h3 class="font-semibold text-gray-900">{{ $address->name }}</h3>
                            <p class="text-sm text-gray-600 mt-1">{{ $address->phone }}</p>
                            <p class="text-sm text-gray-600 mt-2">
                                {{ $address->address }}<br>
                                {{ $address->city }}, {{ $address->state }} {{ $address->postcode }}
                            </p>
                        </div>
                        
                        <div class="flex items-center gap-2 mt-4 pt-4 border-t">
                            <button onclick="editAddress({{ $address->id }})" class="text-primary hover:text-green-700 text-sm font-medium">
                                <i class="bi bi-pencil mr-1"></i> Edit
                            </button>
                            @if(!$address->is_default)
                            <form action="{{ route('account.addresses.update', $address) }}" method="POST" class="inline">
                                @csrf
                                @method('PUT')
                                <input type="hidden" name="name" value="{{ $address->name }}">
                                <input type="hidden" name="phone" value="{{ $address->phone }}">
                                <input type="hidden" name="address" value="{{ $address->address }}">
                                <input type="hidden" name="city" value="{{ $address->city }}">
                                <input type="hidden" name="state" value="{{ $address->state }}">
                                <input type="hidden" name="postcode" value="{{ $address->postcode }}">
                                <input type="hidden" name="is_default" value="1">
                                <button type="submit" class="text-gray-600 hover:text-gray-800 text-sm font-medium">
                                    <i class="bi bi-check-circle mr-1"></i> Set Default
                                </button>
                            </form>
                            @endif
                            <form action="{{ route('account.addresses.destroy', $address) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this address?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-500 hover:text-red-700 text-sm font-medium">
                                    <i class="bi bi-trash mr-1"></i> Delete
                                </button>
                            </form>
                        </div>
                    </div>
                    @endforeach
                </div>
                @else
                <div class="p-12 text-center">
                    <div class="w-24 h-24 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="bi bi-geo-alt text-4xl text-gray-400"></i>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 mb-2">No addresses found</h3>
                    <p class="text-gray-500 mb-6">Add your first delivery address to get started</p>
                    <button onclick="openAddressModal()" class="inline-flex items-center bg-primary text-white px-6 py-3 rounded-lg hover:bg-green-600 transition font-medium">
                        <i class="bi bi-plus-lg mr-2"></i> Add New Address
                    </button>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Address Modal -->
<div id="addressModal" class="fixed inset-0 bg-black/50 z-50 hidden items-center justify-center">
    <div class="bg-white rounded-xl shadow-xl max-w-lg w-full mx-4 max-h-[90vh] overflow-y-auto">
        <div class="p-6 border-b flex items-center justify-between">
            <h2 id="modalTitle" class="text-xl font-bold text-gray-900">Add New Address</h2>
            <button onclick="closeAddressModal()" class="text-gray-400 hover:text-gray-600">
                <i class="bi bi-x-lg text-xl"></i>
            </button>
        </div>
        
        <form id="addressForm" action="{{ route('account.addresses.store') }}" method="POST">
            @csrf
            <div class="p-6 space-y-4">
                <input type="hidden" name="address_id" id="address_id">
                <input type="hidden" name="_method" id="formMethod" value="POST">
                
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Full Name</label>
                        <input type="text" name="name" id="addr_name" required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Phone</label>
                        <input type="tel" name="phone" id="addr_phone" required
                            pattern="(01[0-9]{9}|\+[1-9][0-9]{7,14})"
                            title="BD: 01XXXXXXXXX or International: +XXXXXXXXX"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none">
                    </div>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Street Address</label>
                    <textarea name="address" id="addr_address" rows="2" required
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none"></textarea>
                </div>
                
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">City</label>
                        <input type="text" name="city" id="addr_city" required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">State/District</label>
                        <input type="text" name="state" id="addr_state" required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none">
                    </div>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Postcode</label>
                    <input type="text" name="postcode" id="addr_postcode" required
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none">
                </div>
                
                <div class="flex items-center">
                    <input type="checkbox" name="is_default" id="addr_is_default" value="1" class="w-4 h-4 text-primary border-gray-300 rounded focus:ring-primary">
                    <label for="addr_is_default" class="ml-2 text-sm text-gray-700">Set as default address</label>
                </div>
            </div>
            
            <div class="p-6 border-t bg-gray-50 flex justify-end gap-3">
                <button type="button" onclick="closeAddressModal()" class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-100 transition">
                    Cancel
                </button>
                <button type="submit" class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-green-600 transition font-medium">
                    Save Address
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    const addresses = {!! json_encode($addresses->map(function($a) { return ['id' => $a->id, 'name' => $a->name, 'phone' => $a->phone, 'address' => $a->address, 'city' => $a->city, 'state' => $a->state, 'postcode' => $a->postcode, 'country' => $a->country, 'is_default' => $a->is_default]; })) !!};
    
    function openAddressModal() {
        document.getElementById('modalTitle').textContent = 'Add New Address';
        document.getElementById('addressForm').action = '{{ route("account.addresses.store") }}';
        document.getElementById('formMethod').value = 'POST';
        document.getElementById('address_id').value = '';
        document.getElementById('addressForm').reset();
        document.getElementById('addressModal').classList.remove('hidden');
        document.getElementById('addressModal').classList.add('flex');
    }
    
    function closeAddressModal() {
        document.getElementById('addressModal').classList.add('hidden');
        document.getElementById('addressModal').classList.remove('flex');
    }
    
    function editAddress(addressId) {
        const address = addresses.find(a => a.id === addressId);
        if (!address) return;
        
        document.getElementById('modalTitle').textContent = 'Edit Address';
        document.getElementById('addressForm').action = '{{ route("account.addresses.update", ["address" => "ID"]) }}'.replace('ID', addressId);
        document.getElementById('formMethod').value = 'PUT';
        
        document.getElementById('addr_name').value = address.name;
        document.getElementById('addr_phone').value = address.phone;
        document.getElementById('addr_address').value = address.address;
        document.getElementById('addr_city').value = address.city;
        document.getElementById('addr_state').value = address.state;
        document.getElementById('addr_postcode').value = address.postcode;
        document.getElementById('addr_is_default').checked = address.is_default;
        
        document.getElementById('addressModal').classList.remove('hidden');
        document.getElementById('addressModal').classList.add('flex');
    }
    
    // Close modal on outside click
    document.getElementById('addressModal').addEventListener('click', function(e) {
        if (e.target === this) {
            closeAddressModal();
        }
    });
</script>
@endpush
@endsection
