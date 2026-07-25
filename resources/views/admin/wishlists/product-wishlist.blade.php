@extends('admin.layouts.app')

@section('title', 'Wishlist — ' . $product->name)

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-0">Wishlist: {{ $product->name }}</h4>
        <small class="text-muted">
            {{ $wishlists->total() }} customer(s) saved this product
            @if($product->sku) · SKU {{ $product->sku }} @endif
            · ৳{{ number_format((float) $product->final_price, 2) }}
        </small>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('admin.products.edit', $product->id) }}" class="btn btn-outline-secondary">
            <i class="bi bi-pencil me-1"></i> Edit Product
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
                        <th>Customer</th>
                        <th>Saved On</th>
                        <th style="width: 120px;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @include('admin.wishlists.partials.product-wishlist-rows', ['wishlists' => $wishlists])
                </tbody>
            </table>
        </div>
    </div>
    @if($wishlists->hasPages())
    <div class="card-footer bg-white">{{ $wishlists->appends(request()->query())->links() }}</div>
    @endif
</div>
@endsection
