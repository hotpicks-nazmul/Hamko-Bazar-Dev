@extends('themes.general.layouts.app')

@section('title', $category->name)

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- Category Header -->
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-800">{{ $category->name }}</h1>
        @if($category->description)
        <p class="text-gray-600 mt-2">{{ $category->description }}</p>
        @endif
    </div>

    <!-- Products Grid -->
    @if($products->count() > 0)
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
        @foreach($products as $product)
            @include('themes.general.partials.product-card', ['product' => $product])
        @endforeach
    </div>
    
    <!-- Pagination -->
    <div class="mt-8">
        {{ $products->links() }}
    </div>
    @else
    <div class="bg-white rounded-xl shadow-md p-12 text-center">
        <i class="bi bi-box-seam text-6xl text-gray-300"></i>
        <h3 class="text-xl font-bold text-gray-600 mt-4">No Products Found</h3>
        <p class="text-gray-500 mt-2">No products available in this category yet.</p>
        <a href="{{ route('products.index') }}" class="mt-4 inline-block bg-halal-green text-white px-6 py-2 rounded-lg hover:text-amber-400">
            Browse All Products
        </a>
    </div>
    @endif
</div>
@endsection
