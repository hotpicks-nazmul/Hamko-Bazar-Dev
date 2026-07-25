@extends('themes.general.layouts.app')

@section('title', request('search') ? 'Search: ' . request('search') : 'Products')

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- Page Header -->
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-800">
            @if(request('search'))
                Search results for "{{ request('search') }}"
            @elseif(request('category'))
                @php $cat = $categories->firstWhere('slug', request('category')); @endphp
                {{ $cat ? $cat->name : 'Products' }}
            @else
                All Products
            @endif
        </h1>
        <p class="text-gray-600 mt-2">Browse our premium quality products</p>
    </div>

    <div class="flex flex-col lg:flex-row gap-8">
        <!-- Sidebar Filters -->
        <aside class="lg:w-1/4">
            <form method="GET" action="{{ route('products.index') }}" id="filter-form" class="space-y-6">
                <!-- Categories -->
                <div class="bg-white rounded-xl shadow-md p-6">
                    <h3 class="font-bold text-lg mb-4 flex items-center justify-between">
                        Categories
                        @if(request('category'))
                        <a href="{{ route('products.index', array_diff_key(request()->query(), ['category' => ''])) }}" class="ajax-link text-xs text-red-500 hover:text-red-700 font-normal">
                            <i class="bi bi-x-circle"></i> Clear
                        </a>
                        @endif
                    </h3>
                    <div class="space-y-1 max-h-[500px] overflow-y-auto" id="categoryTree">
                        @foreach($categories ?? [] as $category)
                        @php 
                            $catIconMap = [
                                'houseware' => 'fa-solid fa-basket-shopping',
                                'furniture' => 'fa-solid fa-chair',
                                'cookware' => 'fa-solid fa-kitchen-set',
                            ];
                            $icon = $category->icon ?? ($catIconMap[$category->slug] ?? 'bi bi-tag-fill');
                            $hasChildren = $category->children->where('status', 'active')->count() > 0;
                            $isActiveParent = request('category') == $category->slug;
                        @endphp
                        <div class="category-tree-item">
                            <div class="flex items-center">
                                @if($hasChildren)
                                <button onclick="toggleCategoryTree(event, this)" class="p-1 mr-1 text-gray-400 hover:text-gray-700 transition-colors flex-shrink-0">
                                    <i class="bi bi-chevron-right text-xs transition-transform duration-200"></i>
                                </button>
                                @else
                                <span class="w-6 flex-shrink-0"></span>
                                @endif
                                <a href="{{ $isActiveParent ? route('products.index', array_diff_key(request()->query(), ['category' => ''])) : route('products.index', array_merge(request()->query(), ['category' => $category->slug])) }}" 
                                   data-slug="{{ $category->slug }}"
                                   class="category-filter-link flex items-center gap-2 px-2.5 py-1.5 rounded-lg text-sm hover:bg-gray-50 transition-colors flex-1 {{ $isActiveParent ? 'bg-halal-green text-white hover:bg-halal-green' : 'text-gray-700' }}">
                                    <i class="{{ $icon }} w-4 text-center text-xs {{ $isActiveParent ? 'text-white' : 'text-halal-green' }}"></i>
                                    <span class="truncate">{{ $category->name }}</span>
                                </a>
                            </div>
                            @if($hasChildren)
                            <div class="category-children hidden ml-4 mt-0.5 space-y-0.5 border-l-2 border-gray-100 pl-2">
                                @foreach($category->children->where('status', 'active') as $child)
                                @php
                                    $isActiveChild = request('category') == $child->slug;
                                    $childIcon = $child->icon ?? 'bi bi-dot';
                                @endphp
                                <div class="flex items-center">
                                    <a href="{{ $isActiveChild ? route('products.index', array_diff_key(request()->query(), ['category' => ''])) : route('products.index', array_merge(request()->query(), ['category' => $child->slug])) }}" 
                                       data-slug="{{ $child->slug }}"
                                       class="category-filter-link flex items-center gap-2 px-2.5 py-1.5 rounded-lg text-sm hover:bg-gray-50 transition-colors flex-1 {{ $isActiveChild ? 'bg-halal-green/80 text-white hover:bg-halal-green/80' : 'text-gray-500' }}">
                                        <i class="{{ $childIcon }} w-3 text-center text-[10px] {{ $isActiveChild ? 'text-white' : 'text-gray-300' }}"></i>
                                        <span class="truncate">{{ $child->name }}</span>
                                    </a>
                                </div>
                                @endforeach
                            </div>
                            @endif
                        </div>
                        @endforeach
                    </div>
                </div>

                <!-- Price Range -->
                <div class="bg-white rounded-xl shadow-md p-6">
                    <h3 class="font-bold text-lg mb-4">Price Range</h3>
                    <div class="flex items-center gap-2">
                        <input type="number" name="min_price" value="{{ request('min_price') }}" placeholder="Min" 
                            class="w-1/2 px-3 py-2 border border-gray-200 rounded-lg text-sm focus:border-halal-green focus:outline-none">
                        <span class="text-gray-400">-</span>
                        <input type="number" name="max_price" value="{{ request('max_price') }}" placeholder="Max" 
                            class="w-1/2 px-3 py-2 border border-gray-200 rounded-lg text-sm focus:border-halal-green focus:outline-none">
                    </div>
                    <button type="submit" class="mt-3 w-full bg-halal-green text-white text-sm py-2 rounded-lg hover:text-amber-400 transition-colors">
                        Apply Price
                    </button>
                </div>

                <!-- Brands -->
                @if(isset($brands) && $brands->count() > 0)
                <div class="bg-white rounded-xl shadow-md p-6">
                    <h3 class="font-bold text-lg mb-4 flex items-center justify-between">
                        Brands
                        @if(request('brand'))
                        <a href="{{ route('products.index', array_diff_key(request()->query(), ['brand' => ''])) }}" class="ajax-link text-xs text-red-500 hover:text-red-700 font-normal">
                            <i class="bi bi-x-circle"></i> Clear
                        </a>
                        @endif
                    </h3>
                    <div class="space-y-2 max-h-48 overflow-y-auto">
                        @foreach($brands as $brand)
                        <label class="flex items-center gap-3 px-2 py-1.5 rounded-lg hover:bg-gray-50 cursor-pointer transition-colors">
                            <input type="checkbox" name="brand[]" value="{{ $brand->id }}" 
                                {{ in_array((string)$brand->id, (array)request('brand', [])) ? 'checked' : '' }}
                                class="rounded border-gray-300 text-halal-green focus:ring-halal-green">
                            <span class="text-sm text-gray-700">{{ $brand->name }}</span>
                        </label>
                        @endforeach
                    </div>
                </div>
                @endif

                <!-- Product Status -->
                <div class="bg-white rounded-xl shadow-md p-6">
                    <h3 class="font-bold text-lg mb-4">Product Status</h3>
                    <div class="space-y-3">
                        <label class="flex items-center gap-3 px-2 py-1.5 rounded-lg hover:bg-gray-50 cursor-pointer transition-colors">
                            <input type="checkbox" name="featured" value="1" {{ request('featured') ? 'checked' : '' }}
                                class="rounded border-gray-300 text-halal-green focus:ring-halal-green">
                            <span class="text-sm text-gray-700">Featured</span>
                        </label>
                        <label class="flex items-center gap-3 px-2 py-1.5 rounded-lg hover:bg-gray-50 cursor-pointer transition-colors">
                            <input type="checkbox" name="on_sale" value="1" {{ request('on_sale') ? 'checked' : '' }}
                                class="rounded border-gray-300 text-halal-green focus:ring-halal-green">
                            <span class="text-sm text-gray-700">On Sale</span>
                        </label>
                        <label class="flex items-center gap-3 px-2 py-1.5 rounded-lg hover:bg-gray-50 cursor-pointer transition-colors">
                            <input type="checkbox" name="in_stock" value="1" {{ request('in_stock') ? 'checked' : '' }}
                                class="rounded border-gray-300 text-halal-green focus:ring-halal-green">
                            <span class="text-sm text-gray-700">In Stock Only</span>
                        </label>
                    </div>
                </div>

                <!-- Rating Filter -->
                <div class="bg-white rounded-xl shadow-md p-6">
                    <h3 class="font-bold text-lg mb-4">Minimum Rating</h3>
                    <div class="space-y-2">
                        @foreach([4, 3, 2, 1] as $star)
                        <a href="{{ route('products.index', array_merge(request()->query(), ['rating' => request('rating') == $star ? '' : $star])) }}" 
                           class="flex items-center gap-2 px-2 py-1.5 rounded-lg hover:bg-gray-50 transition-colors {{ request('rating') == $star ? 'bg-halal-cream' : '' }}">
                            <div class="flex text-yellow-400">
                                @for ($i = 1; $i <= 5; $i++)
                                    <i class="bi {{ $i <= $star ? 'bi-star-fill' : 'bi-star' }} text-sm"></i>
                                @endfor
                            </div>
                            <span class="text-xs text-gray-500">&amp; up</span>
                            @if(request('rating') == $star)
                            <i class="bi bi-check-circle-fill text-halal-green text-xs ml-auto"></i>
                            @endif
                        </a>
                        @endforeach
                    </div>
                </div>

                <!-- Hidden fields to preserve other query params -->
                @if(request('sort') && request('sort') !== 'latest')
                <input type="hidden" name="sort" value="{{ request('sort') }}">
                @endif
            </form>

            <!-- Clear All Filters -->
            @if(count(request()->query()) > 0)
            <div class="mt-4">
                <a href="{{ route('products.index') }}" 
                   class="ajax-link flex items-center justify-center gap-2 w-full px-4 py-2.5 border border-red-200 text-red-500 rounded-xl hover:bg-red-50 transition-colors text-sm font-medium">
                    <i class="bi bi-x-circle"></i> Clear All Filters
                </a>
            </div>
            @endif
        </aside>

        <!-- Products Grid -->
        <div class="lg:w-3/4" id="products-grid">
            @include('themes.general.products._grid')
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .view-toggle.active {
        background-color: var(--theme-primary);
        color: white;
    }
    .view-toggle:not(.active) {
        background-color: white;
        color: #6b7280;
    }
    .view-toggle:not(.active):hover {
        background-color: #f3f4f6;
    }
</style>
@endpush

@push('scripts')
<script>
    function toggleCategoryTree(event, btn) {
        event.preventDefault();
        const children = btn.closest('.category-tree-item').querySelector('.category-children');
        const icon = btn.querySelector('i');
        if (children) {
            children.classList.toggle('hidden');
            icon.classList.toggle('rotate-90');
        }
    }

    document.addEventListener('DOMContentLoaded', function () {
        const productsGrid = document.getElementById('products-grid');
        const productsBase = '{{ route('products.index') }}';
        let isLoading = false;

        function loadProducts(url) {
            if (isLoading) return;
            isLoading = true;
            productsGrid.innerHTML = '<div class="text-center py-12"><div class="inline-block w-8 h-8 border-4 border-halal-green border-t-transparent rounded-full animate-spin"></div><p class="text-gray-500 mt-3 text-sm">Updating products...</p></div>';

            fetch(url, {
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
            })
            .then(res => res.json())
            .then(data => {
                productsGrid.style.opacity = '0';
                productsGrid.style.transform = 'translateY(10px)';
                productsGrid.innerHTML = data.html;
                void productsGrid.offsetHeight;
                productsGrid.style.transition = 'opacity 0.35s ease-out, transform 0.35s ease-out';
                productsGrid.style.opacity = '1';
                productsGrid.style.transform = 'translateY(0)';
                setTimeout(() => {
                    productsGrid.style.transition = '';
                    productsGrid.style.opacity = '';
                    productsGrid.style.transform = '';
                }, 400);
                productsGrid.scrollIntoView({ behavior: 'smooth', block: 'start' });
                window.history.pushState({}, '', url);
                isLoading = false;
                attachEvents();
                applyViewMode();
                updateSidebarActiveState(url);
            })
            .catch(() => {
                isLoading = false;
                window.location.href = url;
            });
        }

        function applyViewMode() {
            const saved = localStorage.getItem('productView') || 'grid';
            const grid = document.getElementById('products-view-grid');
            const list = document.getElementById('products-view-list');
            if (!grid || !list) return;
            if (saved === 'list') {
                grid.classList.add('hidden');
                list.classList.remove('hidden');
            } else {
                list.classList.add('hidden');
                grid.classList.remove('hidden');
                if (saved === 'grid-4') {
                    grid.style.gridTemplateColumns = 'repeat(4, minmax(0, 1fr))';
                } else {
                    grid.style.gridTemplateColumns = 'repeat(3, minmax(0, 1fr))';
                }
            }
            document.querySelectorAll('.view-toggle').forEach(t => {
                t.classList.toggle('active', t.dataset.view === saved);
            });
        }

        function updateSidebarActiveState(url) {
            const params = new URLSearchParams(url.includes('?') ? url.split('?')[1] : '');
            const catSlug = params.get('category');
            document.querySelectorAll('.category-filter-link').forEach(link => {
                const linkSlug = link.dataset.slug;
                const isActive = catSlug && linkSlug === catSlug;
                link.classList.toggle('bg-halal-green', isActive);
                link.classList.toggle('text-white', isActive);
                link.classList.toggle('hover:bg-halal-green', isActive);
                link.classList.toggle('text-gray-700', !isActive);
            });
        }

        function attachEvents() {
            // View toggle buttons
            document.querySelectorAll('.view-toggle').forEach(btn => {
                btn.onclick = function () {
                    const view = this.dataset.view;
                    localStorage.setItem('productView', view);
                    applyViewMode();
                };
            });

            // Filter form submit via AJAX
            const form = document.getElementById('filter-form');
            form.onsubmit = function (e) {
                e.preventDefault();
                const params = new URLSearchParams(new FormData(form));
                const sortVal = document.getElementById('sort-select')?.value;
                if (sortVal && sortVal !== 'latest') params.set('sort', sortVal);
                loadProducts(productsBase + '?' + params.toString());
            };

            // Auto-submit on checkbox/select change
            form.querySelectorAll('input[type="checkbox"]').forEach(cb => {
                cb.onchange = function () { form.requestSubmit(); };
            });
            const sortSelect = document.getElementById('sort-select');
            if (sortSelect) sortSelect.onchange = function () { form.requestSubmit(); };

            // Price inputs auto-submit
            form.querySelectorAll('input[name="min_price"], input[name="max_price"]').forEach(input => {
                input.onchange = function () {
                    const min = form.querySelector('input[name="min_price"]').value;
                    const max = form.querySelector('input[name="max_price"]').value;
                    if (min || max) form.requestSubmit();
                };
            });

            // All links to products index (not product detail) load via AJAX
            // Exclude nav-menu-link so menu highlighting works via full page load
            document.querySelectorAll('a[href]').forEach(link => {
                const href = link.getAttribute('href');
                if (href && !link.classList.contains('nav-menu-link') && (href === productsBase || href.startsWith(productsBase + '?'))) {
                    link.addEventListener('click', function (e) {
                        e.preventDefault();
                        loadProducts(this.href);
                    });
                }
            });
        }

        attachEvents();
        applyViewMode();
        updateSidebarActiveState(window.location.href);

        // Browser back/forward
        window.addEventListener('popstate', function () {
            loadProducts(window.location.href);
        });
    });
</script>
@endpush
