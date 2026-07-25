@extends('themes.general.layouts.app')

@section('title', 'All Categories')
@section('meta_description', 'Browse all product categories. Shop premium quality halal meat, poultry, seafood, fruits, vegetables, dairy and more.')

@section('content')
<div class="min-h-screen bg-gray-50">
    <!-- Breadcrumb -->
    <div class="bg-white border-b">
        <div class="container mx-auto px-4 py-3">
            <nav class="flex items-center text-sm text-gray-600">
                <a href="{{ route('home') }}" class="hover:text-halal-green">Home</a>
                <i class="bi bi-chevron-right mx-2 text-xs"></i>
                <span class="text-gray-800 font-medium">All Categories</span>
            </nav>
        </div>
    </div>

    <!-- Page Header -->
    <div class="bg-gradient-to-r from-halal-green to-green-700 text-white py-12">
        <div class="container mx-auto px-4 text-center">
            <h1 class="font-poppins text-4xl font-bold mb-3">Shop by Category</h1>
            <p class="text-green-100 text-lg max-w-2xl mx-auto">Explore our wide range of premium halal products, carefully sourced and quality assured for your family</p>
            <div class="flex items-center justify-center mt-6 space-x-2">
                <div class="w-12 h-1 bg-white/50 rounded-full"></div>
                <div class="w-3 h-3 bg-halal-gold rounded-full"></div>
                <div class="w-12 h-1 bg-white/50 rounded-full"></div>
            </div>
        </div>
    </div>

    <!-- Categories Grid -->
    <div class="container mx-auto px-4 py-12">
        @if($categories->isEmpty())
            <div class="text-center py-16">
                <i class="bi bi-folder text-6xl text-gray-300 mb-4"></i>
                <h3 class="text-xl font-semibold text-gray-600">No categories available</h3>
                <p class="text-gray-500 mt-2">Please check back later</p>
            </div>
        @else
            <!-- Main Categories with Images - Amazon Style -->
            @php
                $categoryIcons = [
                    'houseware' => 'fa-solid fa-basket-shopping',
                    'furniture' => 'fa-solid fa-chair',
                    'cookware' => 'fa-solid fa-kitchen-set',
                    'basin' => 'bi bi-droplet',
                    'bowl' => 'bi bi-circle',
                    'jug' => 'bi bi-cup',
                    'rack' => 'bi bi-grid-3x3-gap',
                    'box' => 'bi bi-box',
                    'plate' => 'bi bi-circle-half',
                    'baby-potty' => 'bi bi-heart',
                    'water-pot' => 'bi bi-droplet-half',
                    'soap-case' => 'bi bi-dispenser',
                    'dust-pan' => 'bi bi-bucket',
                    'tool' => 'bi bi-wrench',
                    'card-holder' => 'bi bi-credit-card',
                    'chopping-board' => 'bi bi-layout-text-window',
                    'basket' => 'bi bi-basket',
                    'container' => 'bi bi-boxes',
                    'mug' => 'bi bi-cup',
                    'glass-holder' => 'bi bi-cup-straw',
                    'washing-net' => 'bi bi-filter',
                    'cup' => 'bi bi-cup',
                    'tray' => 'bi bi-layout',
                    'organizer' => 'bi bi-grid',
                    'glass' => 'bi bi-cup',
                    'dish-cover' => 'bi bi-shield',
                    'flower-tub' => 'bi bi-flower1',
                    'hanger' => 'bi bi-hanger',
                    'dish' => 'bi bi-circle',
                    'water-ladle' => 'bi bi-droplet',
                    'oven' => 'bi bi-stove',
                    'padel-bin' => 'bi bi-trash',
                    'pen-stand' => 'bi bi-pen',
                    'rice-picker' => 'bi bi-hand-index',
                    'spicy-jar' => 'bi bi-jar',
                    'spice-box' => 'bi bi-box',
                    'swing-bin' => 'bi bi-trash',
                    'bucket' => 'bi bi-bucket',
                    'rice-washer' => 'bi bi-droplet',
                    'dish-holder' => 'bi bi-layout-text-window',
                    'paperfile-holder' => 'bi bi-folder',
                    'clip' => 'bi bi-paperclip',
                    'waterbottle' => 'bi bi-droplet',
                    'chair' => 'bi bi-person-workspace',
                    'table' => 'bi bi-layout',
                    'wardrobe' => 'bi bi-door-open',
                    'cabinet' => 'bi bi-boxes',
                    'almirah' => 'bi bi-door-open',
                    'electric-kitchen-appliance' => 'bi bi-lightning',
                    'pressure-cooker' => 'bi bi-thermometer-high',
                    'stainless-steel' => 'bi bi-shield-shaded',
                    'non-stick-product' => 'bi bi-egg-fried',
                    'aluminum-product' => 'bi bi-shield',
                    'classic-super-korai' => 'bi bi-circle',
                    'hotpot' => 'bi bi-fire',
                    'gas-stove' => 'bi bi-stove',
                    'rice-cooker' => 'bi bi-thermometer-half',
                    'kettle' => 'bi bi-cup-hot',
                    'blender-and-grinder' => 'bi bi-gear',
                    'induction-cooker' => 'bi bi-lightning',
                    'infrared-cooker' => 'bi bi-lightning',
                    'ss-pressure-cooker-3-layer' => 'bi bi-thermometer-high',
                    'oval-shape-pressure-cooker' => 'bi bi-thermometer-high',
                    'straight-shape-pressure-cooker' => 'bi bi-thermometer-high',
                    'saucepan' => 'bi bi-cup-hot',
                    'ss-fry-pan-three-layer' => 'bi bi-circle-half',
                    'ss-wok-pan-three-layer' => 'bi bi-circle-half',
                    'ss-fry-pan' => 'bi bi-circle-half',
                    'ss-two-side-handle-pot' => 'bi bi-cup-hot',
                    'ss-bowl' => 'bi bi-circle',
                    'ss-plate' => 'bi bi-circle-half',
                    'ss-milk-pan' => 'bi bi-cup-hot',
                    'ss-3layer-saucepan' => 'bi bi-cup-hot',
                    'wokpan' => 'bi bi-circle-half',
                    'frypan' => 'bi bi-circle-half',
                    'alu-saucepan' => 'bi bi-cup-hot',
                    'alu-deep-curry-pan' => 'bi bi-cup-hot',
                    'classic-super-korai-product' => 'bi bi-circle',
                    'hotpot-product' => 'bi bi-fire',
                    'casserole-pan' => 'bi bi-cup-hot',
                    'pizza-pan' => 'bi bi-circle',
                    'ruti-towa' => 'bi bi-circle',
                    'non-stick-gift-set' => 'bi bi-gift',
                    'lunch-carrier' => 'bi bi-bag',
                    'classic-box' => 'bi bi-box',
                ];
                $categoryColors = [
                    'houseware' => ['bg' => 'from-emerald-500 to-emerald-600', 'light' => 'bg-emerald-100', 'text' => 'text-emerald-600'],
                    'furniture' => ['bg' => 'from-amber-500 to-amber-600', 'light' => 'bg-amber-100', 'text' => 'text-amber-600'],
                    'cookware' => ['bg' => 'from-red-500 to-red-600', 'light' => 'bg-red-100', 'text' => 'text-red-600'],
                ];
                $defaultIcon = 'bi bi-tag-fill';
                $defaultColor = ['bg' => 'from-halal-green to-green-600', 'light' => 'bg-green-100', 'text' => 'text-halal-green'];
            @endphp
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($categories as $category)
                    @php
                        $icon = $category->icon ?? ($categoryIcons[$category->slug] ?? $defaultIcon);
                        $color = $categoryColors[$category->slug] ?? $defaultColor;
                    @endphp
                <div class="group bg-white rounded-2xl shadow-sm hover:shadow-xl transition-all duration-500 overflow-hidden border border-gray-100 hover:border-transparent">
                    <a href="{{ route('products.index', ['category' => $category->slug]) }}" class="block">
                        <!-- Category Icon Area -->
                        <div class="relative bg-gradient-to-br {{ $color['bg'] }} p-8 flex items-center justify-center overflow-hidden" style="min-height: 220px;">
                            <!-- Background Pattern -->
                            <div class="absolute inset-0 opacity-10">
                                <div class="absolute -top-10 -right-10 w-40 h-40 bg-white rounded-full"></div>
                                <div class="absolute -bottom-10 -left-10 w-32 h-32 bg-white rounded-full"></div>
                            </div>
                            
                            <!-- Icon -->
                            <div class="w-24 h-24 bg-white/20 rounded-3xl flex items-center justify-center backdrop-blur-sm group-hover:bg-white/30 transition-all duration-500 transform group-hover:scale-110 group-hover:rotate-3">
                                <i class="{{ $icon }} text-5xl text-white"></i>
                            </div>
                            
                            <!-- Product Count Badge -->
                            <div class="absolute top-4 right-4 bg-white/90 backdrop-blur-sm px-3 py-1 rounded-full shadow-sm">
                                <span class="text-halal-green font-semibold text-sm">{{ $category->total_products_count ?? 0 }}</span>
                                <span class="text-gray-600 text-xs ml-1">Products</span>
                            </div>
                            
                            <!-- Arrow Icon on Hover -->
                            <div class="absolute bottom-4 right-4 w-10 h-10 bg-white rounded-full flex items-center justify-center shadow-lg transform translate-y-4 opacity-0 group-hover:translate-y-0 group-hover:opacity-100 transition-all duration-300">
                                <i class="bi bi-arrow-right text-halal-green"></i>
                            </div>
                        </div>
                        
                        <!-- Category Info -->
                        <div class="p-5">
                            <h3 class="font-poppins text-xl font-bold text-gray-800 group-hover:text-halal-green transition-colors mb-2">
                                {{ $category->name }}
                            </h3>
                            @if($category->description)
                                <p class="text-gray-600 text-sm line-clamp-2 mb-3">{{ $category->description }}</p>
                            @endif
                            
                            <!-- Subcategories Preview -->
                            @if($category->children->count() > 0)
                                <div class="flex flex-wrap gap-2 mt-3">
                                    @foreach($category->children->take(3) as $child)
                                        <span class="inline-flex items-center text-xs bg-gray-100 text-gray-700 px-2 py-1 rounded-full">
                                            <i class="bi bi-dot text-halal-green mr-1"></i>
                                            {{ $child->name }}
                                        </span>
                                    @endforeach
                                    @if($category->children->count() > 3)
                                        <span class="text-xs text-gray-500 px-2 py-1">+{{ $category->children->count() - 3 }} more</span>
                                    @endif
                                </div>
                            @endif
                        </div>
                    </a>
                </div>
                @endforeach
            </div>

            <!-- Subcategories Section - Full Width -->
            @php
                $categoriesWithChildren = $categories->filter(fn($cat) => $cat->children->count() > 0);
            @endphp
            
            @if($categoriesWithChildren->count() > 0)
            <div class="mt-16">
                <div class="flex items-center justify-center mb-8">
                    <div class="h-px bg-gray-200 flex-1 max-w-xs"></div>
                    <h2 class="font-poppins text-2xl font-bold text-gray-800 mx-6 text-center">Browse Subcategories</h2>
                    <div class="h-px bg-gray-200 flex-1 max-w-xs"></div>
                </div>
                
                @foreach($categoriesWithChildren as $category)
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 mb-6">
                    <div class="flex items-center justify-between mb-4">
                        <a href="{{ route('products.index', ['category' => $category->slug]) }}" class="flex items-center group">
                            @php
                                $subIcon = $category->icon ?? ($categoryIcons[$category->slug] ?? $defaultIcon);
                                $subColor = $categoryColors[$category->slug] ?? $defaultColor;
                            @endphp
                            <div class="w-12 h-12 rounded-lg flex items-center justify-center mr-3 {{ $subColor['light'] }}">
                                <i class="{{ $subIcon }} text-xl {{ $subColor['text'] }}"></i>
                            </div>
                            <div>
                                <h3 class="font-poppins text-lg font-bold text-gray-800 group-hover:text-halal-green transition-colors">{{ $category->name }}</h3>
                                <span class="text-sm text-gray-500">{{ $category->children->count() }} subcategories</span>
                            </div>
                        </a>
                        <a href="{{ route('products.index', ['category' => $category->slug]) }}" class="text-halal-green hover:text-halal-dark font-medium text-sm flex items-center">
                            View All <i class="bi bi-arrow-right ml-1"></i>
                        </a>
                    </div>
                    
                    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-3">
                        @include('themes.general.partials.category-children-grid', ['items' => $category->children])
                    </div>
                </div>
                @endforeach
            </div>
            @endif
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Add smooth hover effects
    document.querySelectorAll('.category-card').forEach(card => {
        card.addEventListener('mouseenter', function() {
            this.querySelector('.category-image').classList.add('scale-105');
        });
        card.addEventListener('mouseleave', function() {
            this.querySelector('.category-image').classList.remove('scale-105');
        });
    });
</script>
@endpush