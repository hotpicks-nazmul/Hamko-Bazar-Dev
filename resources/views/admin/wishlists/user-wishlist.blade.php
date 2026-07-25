@extends('admin.layouts.app')

@section('title', 'Wishlist — ' . $user->name)

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-0">Wishlist: {{ $user->name }}</h4>
        <small class="text-muted">{{ $user->email }} · {{ $wishlists->total() }} product(s) saved</small>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('admin.customers.show', $user->id) }}" class="btn btn-outline-secondary">
            <i class="bi bi-person me-1"></i> Customer Record
        </a>
        <a href="{{ route('admin.wishlists.index') }}" class="btn btn-outline-primary">
            <i class="bi bi-arrow-left me-1"></i> All Wishlists
        </a>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Product</th>
                        <th class="text-end">Price</th>
                        <th>Saved On</th>
                        <th style="width: 120px;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @include('admin.wishlists.partials.user-wishlist-rows', ['wishlists' => $wishlists])
                </tbody>
            </table>
        </div>
    </div>
    @if($wishlists->hasPages())
    <div class="card-footer bg-white">{{ $wishlists->appends(request()->query())->links() }}</div>
    @endif
</div>
@endsection
