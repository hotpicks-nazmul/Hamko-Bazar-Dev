<div class="bg-white rounded-xl shadow-md p-4 mb-6 flex flex-wrap items-center justify-between gap-4">
    <div class="flex flex-wrap items-center gap-2">
        <span class="text-sm text-gray-500 font-medium">{{ $products->total() }} product(s)</span>
        @if(request('search'))
        <span class="inline-flex items-center gap-1 px-2.5 py-1 bg-gray-100 rounded-full text-xs text-gray-600">
            Search: "{{ request('search') }}"
            <a href="{{ route('products.index', array_diff_key(request()->query(), ['search' => ''])) }}" class="text-red-400 hover:text-red-600 ml-1 ajax-link">
                <i class="bi bi-x"></i>
            </a>
        </span>
        @endif
        @if(request('category'))
        <span class="inline-flex items-center gap-1 px-2.5 py-1 bg-gray-100 rounded-full text-xs text-gray-600">
            {{ $categories->firstWhere('slug', request('category'))?->name ?? request('category') }}
            <a href="{{ route('products.index', array_diff_key(request()->query(), ['category' => ''])) }}" class="text-red-400 hover:text-red-600 ml-1 ajax-link">
                <i class="bi bi-x"></i>
            </a>
        </span>
        @endif
        @if(request('featured'))
        <span class="inline-flex items-center gap-1 px-2.5 py-1 bg-gray-100 rounded-full text-xs text-gray-600">
            Featured
            <a href="{{ route('products.index', array_diff_key(request()->query(), ['featured' => ''])) }}" class="text-red-400 hover:text-red-600 ml-1 ajax-link">
                <i class="bi bi-x"></i>
            </a>
        </span>
        @endif
        @if(request('on_sale'))
        <span class="inline-flex items-center gap-1 px-2.5 py-1 bg-gray-100 rounded-full text-xs text-gray-600">
            On Sale
            <a href="{{ route('products.index', array_diff_key(request()->query(), ['on_sale' => ''])) }}" class="text-red-400 hover:text-red-600 ml-1 ajax-link">
                <i class="bi bi-x"></i>
            </a>
        </span>
        @endif
        @if(request('in_stock'))
        <span class="inline-flex items-center gap-1 px-2.5 py-1 bg-gray-100 rounded-full text-xs text-gray-600">
            In Stock
            <a href="{{ route('products.index', array_diff_key(request()->query(), ['in_stock' => ''])) }}" class="text-red-400 hover:text-red-600 ml-1 ajax-link">
                <i class="bi bi-x"></i>
            </a>
        </span>
        @endif
    </div>

    <div class="flex items-center gap-3">
        <!-- View Toggle -->
        <div class="flex items-center border border-gray-200 rounded-lg overflow-hidden">
            <button type="button" data-view="grid" class="view-toggle px-2.5 py-1.5 text-sm transition-colors" title="3 Column Grid">
                <i class="bi bi-grid-3x3-gap-fill"></i>
            </button>
            <button type="button" data-view="grid-4" class="view-toggle px-2.5 py-1.5 text-sm transition-colors" title="4 Column Grid">
                <i class="bi bi-grid"></i>
            </button>
            <button type="button" data-view="list" class="view-toggle px-2.5 py-1.5 text-sm transition-colors" title="List View">
                <i class="bi bi-list-ul"></i>
            </button>
        </div>

        <label for="sort-select" class="text-sm text-gray-500 whitespace-nowrap">Sort by:</label>
        <select id="sort-select" name="sort" form="filter-form"
            class="text-sm border border-gray-200 rounded-lg px-3 py-1.5 focus:border-halal-green focus:outline-none"
            onchange="this.form.submit()">
            <option value="latest" {{ request('sort') == 'latest' || !request('sort') ? 'selected' : '' }}>Latest</option>
            <option value="price_low" {{ request('sort') == 'price_low' ? 'selected' : '' }}>Price: Low to High</option>
            <option value="price_high" {{ request('sort') == 'price_high' ? 'selected' : '' }}>Price: High to Low</option>
            <option value="name" {{ request('sort') == 'name' ? 'selected' : '' }}>Name: A-Z</option>
            <option value="on_sale" {{ request('sort') == 'on_sale' ? 'selected' : '' }}>Biggest Discount</option>
            <option value="rating" {{ request('sort') == 'rating' ? 'selected' : '' }}>Highest Rated</option>
            <option value="oldest" {{ request('sort') == 'oldest' ? 'selected' : '' }}>Oldest</option>
        </select>
    </div>
</div>

@if($products->count() > 0)
<!-- Grid View -->
<div id="products-view-grid" class="grid grid-cols-1 sm:grid-cols-2 gap-6">
    @foreach($products as $product)
        @include('themes.general.partials.product-card', ['product' => $product])
    @endforeach
</div>

<script>
(function(){
    var g = document.getElementById('products-view-grid');
    if (g) {
        var v = localStorage.getItem('productView');
        if (v === 'grid-4') g.style.gridTemplateColumns = 'repeat(4, minmax(0, 1fr))';
        else g.style.gridTemplateColumns = 'repeat(3, minmax(0, 1fr))';
    }
})();
</script>

<!-- List View -->
<div id="products-view-list" class="hidden flex flex-col gap-4">
    @foreach($products as $product)
        @include('themes.general.partials.product-card-list', ['product' => $product])
    @endforeach
</div>

<div class="mt-8">
    {{ $products->links('vendor.pagination.tailwind') }}
</div>
@else
<div class="bg-white rounded-xl shadow-md p-12 text-center">
    <i class="bi bi-box-seam text-6xl text-gray-300"></i>
    <h3 class="text-xl font-bold text-gray-600 mt-4">No Products Found</h3>
    <p class="text-gray-500 mt-2">Try adjusting your filters or search criteria.</p>
    <a href="{{ route('products.index') }}" class="inline-block mt-4 px-6 py-2 bg-halal-green text-white rounded-lg hover:text-amber-400 transition-colors ajax-link">
        Clear All Filters
    </a>
</div>
@endif