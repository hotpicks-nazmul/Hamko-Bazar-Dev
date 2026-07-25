@extends('themes.general.layouts.app')

@section('title', 'Ticket: ' . $ticket->ticket_number)

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- Breadcrumb -->
    <nav class="mb-6">
        <ol class="flex items-center space-x-2 text-sm">
            <li><a href="{{ route('home') }}" class="text-gray-500 hover:text-primary">Home</a></li>
            <li><i class="bi bi-chevron-right text-gray-400"></i></li>
            <li><a href="{{ route('account.dashboard') }}" class="text-gray-500 hover:text-primary">Dashboard</a></li>
            <li><i class="bi bi-chevron-right text-gray-400"></i></li>
            <li><a href="{{ route('tickets.index') }}" class="text-gray-500 hover:text-primary">My Tickets</a></li>
            <li><i class="bi bi-chevron-right text-gray-400"></i></li>
            <li class="text-gray-900 font-medium">{{ $ticket->ticket_number }}</li>
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
            <!-- Ticket Header -->
            <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
                <div class="flex items-start justify-between mb-4">
                    <div>
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
                                {{ ucfirst($ticket->priority) }} Priority
                            </span>
                        </div>
                        <h2 class="text-xl font-semibold text-gray-900">{{ $ticket->subject }}</h2>
                    </div>
                    @if($ticket->status !== 'closed')
                    <button type="button"
                        class="px-4 py-2 text-red-600 border border-red-200 rounded-lg hover:bg-red-50 transition"
                        onclick="event.preventDefault(); if(confirm('Are you sure you want to close this ticket?')) { document.getElementById('closeForm').submit(); }">
                        <i class="bi bi-x-circle me-2"></i>
                        Close Ticket
                    </button>
                    <form id="closeForm" method="POST" action="{{ route('tickets.close', $ticket->id) }}" style="display: none;">
                        @csrf
                        @method('PUT')
                    </form>
                    @else
                    <span class="px-4 py-2 bg-gray-100 text-gray-500 rounded-lg">
                        <i class="bi bi-check-circle me-2"></i>
                        Closed
                    </span>
                    @endif
                </div>

                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
                    <div>
                        <p class="text-gray-500">Category</p>
                        <p class="font-medium">{{ ucfirst($ticket->category) }}</p>
                    </div>
                    <div>
                        <p class="text-gray-500">Created</p>
                        <p class="font-medium">{{ $ticket->created_at->format('M d, Y h:i A') }}</p>
                    </div>
                    <div>
                        <p class="text-gray-500">Last Updated</p>
                        <p class="font-medium">{{ $ticket->updated_at->format('M d, Y h:i A') }}</p>
                    </div>
                </div>
            </div>

            <!-- Conversation -->
            <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Conversation</h3>
                
                <!-- Original Message -->
                <div class="border border-gray-200 rounded-lg p-4 mb-4">
                    <div class="flex items-center gap-3 mb-3">
                        <div class="w-10 h-10 bg-primary/10 rounded-full flex items-center justify-center">
                            <i class="bi bi-person text-primary"></i>
                        </div>
                        <div>
                            <p class="font-medium text-gray-900">{{ auth()->user()->name }}</p>
                            <p class="text-sm text-gray-500">{{ $ticket->created_at->format('M d, Y at h:i A') }}</p>
                        </div>
                    </div>
                    <div class="prose max-w-none text-gray-700">
                        <p style="white-space: pre-wrap;">{{ $ticket->description }}</p>
                    </div>
                </div>

                <!-- Replies -->
                @forelse($ticket->replies as $reply)
                <div class="border border-gray-200 rounded-lg p-4 mb-4 {{ $reply->is_admin_reply ? 'bg-blue-50' : 'bg-gray-50' }}">
                    <div class="flex items-center gap-3 mb-3">
                        @if($reply->is_admin_reply)
                        <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center">
                            <i class="bi bi-headset text-green-600"></i>
                        </div>
                        <div>
                            <p class="font-medium text-gray-900">Support Team</p>
                            <p class="text-sm text-gray-500">{{ $reply->created_at->format('M d, Y at h:i A') }}</p>
                        </div>
                        @else
                        <div class="w-10 h-10 bg-primary/10 rounded-full flex items-center justify-center">
                            <i class="bi bi-person text-primary"></i>
                        </div>
                        <div>
                            <p class="font-medium text-gray-900">{{ auth()->user()->name }}</p>
                            <p class="text-sm text-gray-500">{{ $reply->created_at->format('M d, Y at h:i A') }}</p>
                        </div>
                        @endif
                    </div>
                    <div class="prose max-w-none text-gray-700">
                        <p style="white-space: pre-wrap;">{{ $reply->message }}</p>
                    </div>
                </div>
                @empty
                <div class="text-center py-8 text-gray-500">
                    <i class="bi bi-chat-square-text text-3xl mb-2"></i>
                    <p>No replies yet. Our support team will respond soon.</p>
                </div>
                @endforelse
            </div>

            <!-- Reply Form -->
            @if($ticket->status !== 'closed')
            <div class="bg-white rounded-xl shadow-sm p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Send a Reply</h3>
                <form action="{{ route('tickets.reply', $ticket->id) }}" method="POST">
                    @csrf
                    <div class="mb-4">
                        <textarea name="message" rows="4" 
                                  class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary/20 focus:border-primary @error('message') border-red-500 @enderror"
                                  placeholder="Type your message here..." required>{{ old('message') }}</textarea>
                        @error('message')
                        <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                        @enderror
                    </div>
                    <div class="flex items-center justify-between">
                        <p class="text-sm text-gray-500">Our support team typically responds within 24 hours.</p>
                        <button type="submit" class="px-6 py-2 bg-primary text-white rounded-lg hover:bg-primary/90 transition">
                            <i class="bi bi-send me-2"></i>
                            Send Reply
                        </button>
                    </div>
                </form>
            </div>
            @else
            <div class="bg-white rounded-xl shadow-sm p-6 text-center">
                <i class="bi bi-check-circle text-4xl text-gray-400 mb-4"></i>
                <h3 class="text-lg font-semibold text-gray-900 mb-2">This Ticket is Closed</h3>
                <p class="text-gray-500 mb-4">If you need further assistance, please create a new ticket.</p>
                <a href="{{ route('tickets.create') }}" class="px-6 py-2 bg-primary text-white rounded-lg hover:bg-primary/90 transition">
                    Create New Ticket
                </a>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
