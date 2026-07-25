@extends('themes.general.layouts.app')

@section('title', 'My Support Tickets')

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- Breadcrumb -->
    <nav class="mb-6">
        <ol class="flex items-center space-x-2 text-sm">
            <li><a href="{{ route('home') }}" class="text-gray-500 hover:text-primary">Home</a></li>
            <li><i class="bi bi-chevron-right text-gray-400"></i></li>
            <li><a href="{{ route('account.dashboard') }}" class="text-gray-500 hover:text-primary">Dashboard</a></li>
            <li><i class="bi bi-chevron-right text-gray-400"></i></li>
            <li class="text-gray-900 font-medium">My Tickets</li>
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
                <div class="p-4">
                    <ul class="space-y-1">
                        <li>
                            <a href="{{ route('account.dashboard') }}" class="flex items-center gap-3 px-4 py-3 rounded-lg text-gray-600 hover:bg-gray-50 hover:text-primary transition">
                                <i class="bi bi-grid-1x2"></i>
                                <span>Dashboard</span>
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('account.profile') }}" class="flex items-center gap-3 px-4 py-3 rounded-lg text-gray-600 hover:bg-gray-50 hover:text-primary transition">
                                <i class="bi bi-person"></i>
                                <span>My Profile</span>
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('account.orders') }}" class="flex items-center gap-3 px-4 py-3 rounded-lg text-gray-600 hover:bg-gray-50 hover:text-primary transition">
                                <i class="bi bi-bag"></i>
                                <span>My Orders</span>
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('account.wishlist') }}" class="flex items-center gap-3 px-4 py-3 rounded-lg text-gray-600 hover:bg-gray-50 hover:text-primary transition">
                                <i class="bi bi-heart"></i>
                                <span>Wishlist</span>
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('tickets.index') }}" class="flex items-center gap-3 px-4 py-3 rounded-lg bg-primary/10 text-primary font-medium">
                                <i class="bi bi-ticket-detailed"></i>
                                <span>Support Tickets</span>
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('account.addresses') }}" class="flex items-center gap-3 px-4 py-3 rounded-lg text-gray-600 hover:bg-gray-50 hover:text-primary transition">
                                <i class="bi bi-geo-alt"></i>
                                <span>Addresses</span>
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="lg:col-span-3">
            <div class="bg-white rounded-xl shadow-sm p-6">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-xl font-semibold text-gray-900">My Support Tickets</h2>
                    <a href="{{ route('tickets.create') }}" class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary/90 transition flex items-center gap-2">
                        <i class="bi bi-plus-lg"></i>
                        <span>Create New Ticket</span>
                    </a>
                </div>

                @if($tickets->isEmpty())
                <div class="text-center py-12">
                    <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="bi bi-ticket-detailed text-3xl text-gray-400"></i>
                    </div>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">No tickets yet</h3>
                    <p class="text-gray-500 mb-4">You haven't created any support tickets yet.</p>
                    <a href="{{ route('tickets.create') }}" class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary/90 transition">
                        Create Your First Ticket
                    </a>
                </div>
                @else
                <div class="space-y-4">
                    @foreach($tickets as $ticket)
                    <div class="border border-gray-200 rounded-lg p-4 hover:border-primary/30 transition">
                        <div class="flex items-start justify-between">
                            <div class="flex-1">
                                <div class="flex items-center gap-3 mb-2">
                                    <span class="text-sm font-mono text-gray-500">{{ $ticket->ticket_number }}</span>
                                    <span class="px-2 py-1 text-xs rounded-full 
                                        @if($ticket->status === 'open') bg-primary/10 text-primary
                                        @elseif($ticket->status === 'pending') bg-yellow-100 text-yellow-700
                                        @elseif($ticket->status === 'answered') bg-blue-100 text-blue-700
                                        @elseif($ticket->status === 'solved') bg-green-100 text-green-700
                                        @else bg-gray-100 text-gray-700
                                        @endif">
                                        {{ ucfirst($ticket->status) }}
                                    </span>
                                    <span class="px-2 py-1 text-xs rounded-full 
                                        @if($ticket->priority === 'urgent') bg-red-100 text-red-700
                                        @elseif($ticket->priority === 'high') bg-orange-100 text-orange-700
                                        @elseif($ticket->priority === 'medium') bg-blue-100 text-blue-700
                                        @else bg-gray-100 text-gray-700
                                        @endif">
                                        {{ ucfirst($ticket->priority) }}
                                    </span>
                                </div>
                                <h3 class="font-medium text-gray-900 mb-1">{{ $ticket->subject }}</h3>
                                <p class="text-sm text-gray-500">Created on {{ $ticket->created_at->format('M d, Y') }}</p>
                            </div>
                            <a href="{{ route('tickets.show', $ticket->id) }}" class="px-4 py-2 text-primary hover:bg-primary/5 rounded-lg transition">
                                View
                            </a>
                        </div>
                    </div>
                    @endforeach
                </div>

                <!-- Pagination -->
                @if($tickets->hasPages())
                <div class="mt-6">
                    {{ $tickets->links() }}
                </div>
                @endif
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
