@extends('themes.general.layouts.app')

@php
$heroSettings = \App\Models\Setting::where('group', 'hero')->get()->keyBy('key');
$hero = function($key, $default = '') use ($heroSettings) {
    return $heroSettings->has($key) ? $heroSettings[$key]->value : $default;
};
$heroJson = function($key) use ($hero) {
    return json_decode($hero($key, '{}'), true);
};

// Get hero type (default to 'standard')
$heroType = $hero('hero_type', 'standard');

// Get sliders if hero type is slider
$sliders = $heroType === 'slider' ? \App\Models\Slider::where('is_active', true)->orderBy('order')->get() : collect();

// Home Page Settings
$homeSettings = \App\Models\Setting::where('group', 'homepage')->get()->keyBy('key');
$home = function($key, $default = '') use ($homeSettings) {
    return $homeSettings->has($key) ? $homeSettings[$key]->value : $default;
};

// Product columns - single setting for all sections
$productColumns = (int) $home('homepage_product_columns', 6);

// Section order with default fallback
$sectionOrder = $sectionOrder ?? ['categories', 'featured', 'banner', 'new_arrivals', 'why_choose_us', 'sale', 'testimonials', 'blog', 'dynamic_banners'];
@endphp

@section('content')
@if($heroType === 'slider' && $sliders->count() > 0)
<!-- Hero Slider Section -->
<section class="hero-slider-section">
    <div id="heroSlider" class="hero-slider">
        <!-- Indicators -->
        <div class="hero-slider-indicators">
            @foreach($sliders as $index => $slider)
            <button type="button" data-slide="{{ $index }}" 
                class="hero-indicator {{ $index === 0 ? 'active' : '' }}"></button>
            @endforeach
        </div>
        
        <!-- Slides -->
        <div class="hero-slider-wrapper">
            @foreach($sliders as $index => $slider)
            <div class="hero-slide {{ $index === 0 ? 'active' : '' }}" data-index="{{ $index }}">
                <img src="{{ asset($slider->image) }}" class="hero-slide-img" alt="{{ $slider->title }}">
                <div class="hero-slide-overlay"></div>
                <div class="hero-slide-content">
                    <div class="container mx-auto px-4">
                        <div class="max-w-2xl text-white">
                            @if($slider->subtitle)
                            <p class="hero-slide-subtitle">{{ $slider->subtitle }}</p>
                            @endif
                            <h1 class="hero-slide-title">{{ $slider->title }}</h1>
                            <a href="{{ $slider->link ?? route('products.index') }}" class="hero-slide-btn" style="display: inline-flex !important; align-items: center !important; background-color: {{ $slider->button_color ?? '#D4AF37' }} !important; color: {{ $slider->button_text_color ?? '#FFFFFF' }} !important; padding: 1rem 2rem; border-radius: 0.75rem; font-weight: 600; text-decoration: none; transition: all 0.3s ease; box-shadow: 0 10px 25px rgba(212, 175, 55, 0.3);">
                                {{ $slider->button_text ?? 'Shop Now' }}
                                <i class="bi {{ $slider->button_icon ?? 'bi-arrow-right' }} ms-2" style="color: {{ $slider->button_icon_color ?? '#FFFFFF' }} !important;"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        
        <!-- Controls -->
        <button class="hero-slider-prev" type="button">
            <i class="bi bi-chevron-left"></i>
        </button>
        <button class="hero-slider-next" type="button">
            <i class="bi bi-chevron-right"></i>
        </button>
    </div>
    
    <!-- Features Bar -->
    <div class="hero-features-bar">
        <div class="container mx-auto px-4">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 py-4">
                <div class="flex items-center justify-center text-white py-2">
                    <div class="w-12 h-12 bg-halal-gold/20 rounded-xl flex items-center justify-center mr-3">
                        <i class="{{ $hero('hero_feature1_icon', 'bi bi-truck') }} text-halal-gold text-xl"></i>
                    </div>
                    <div>
                        <p class="font-medium">{{ $hero('hero_feature1_title', 'Free Delivery') }}</p>
                        <p class="text-xs text-gray-300">{{ $hero('hero_feature1_subtitle', 'Orders over  Tk500') }}</p>
                    </div>
                </div>
                <div class="flex items-center justify-center text-white py-2">
                    <div class="w-12 h-12 bg-green-500/20 rounded-xl flex items-center justify-center mr-3">
                        <i class="{{ $hero('hero_feature2_icon', 'bi bi-shield-check') }} text-green-400 text-xl"></i>
                    </div>
                    <div>
                        <p class="font-medium">{{ $hero('hero_feature2_title', '100% Halal') }}</p>
                        <p class="text-xs text-gray-300">{{ $hero('hero_feature2_subtitle', 'Certified Quality') }}</p>
                    </div>
                </div>
                <div class="flex items-center justify-center text-white py-2">
                    <div class="w-12 h-12 bg-blue-500/20 rounded-xl flex items-center justify-center mr-3">
                        <i class="{{ $hero('hero_feature3_icon', 'bi bi-cash-coin') }} text-blue-400 text-xl"></i>
                    </div>
                    <div>
                        <p class="font-medium">{{ $hero('hero_feature3_title', 'Best Prices') }}</p>
                        <p class="text-xs text-gray-300">{{ $hero('hero_feature3_subtitle', 'Guaranteed') }}</p>
                    </div>
                </div>
                <div class="flex items-center justify-center text-white py-2">
                    <div class="w-12 h-12 bg-purple-500/20 rounded-xl flex items-center justify-center mr-3">
                        <i class="{{ $hero('hero_feature4_icon', 'bi bi-headset') }} text-purple-400 text-xl"></i>
                    </div>
                    <div>
                        <p class="font-medium">{{ $hero('hero_feature4_title', '24/7 Support') }}</p>
                        <p class="text-xs text-gray-300">{{ $hero('hero_feature4_subtitle', 'Always Here') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@else
<!-- Hero Section - Standard (Default) -->
<section class="relative min-h-[600px] md:min-h-[700px] overflow-hidden">
    <!-- Background with overlay -->
    <div class="absolute inset-0">
        <img src="{{ $hero('hero_background_image', 'https://images.unsplash.com/photo-1542838132-92c53300491e?w=1920') }}" 
            alt="Fresh Food Background" 
            class="w-full h-full object-cover">
        <div class="absolute inset-0 bg-gradient-to-r from-halal-dark/95 via-halal-dark/85 to-halal-green/70"></div>
    </div>
    
    <!-- Animated shapes -->
    <div class="absolute inset-0 overflow-hidden">
        <div class="absolute -top-20 -right-20 w-72 h-72 bg-halal-gold/20 rounded-full blur-3xl animate-pulse"></div>
        <div class="absolute -bottom-20 -left-20 w-96 h-96 bg-halal-green/30 rounded-full blur-3xl animate-pulse" style="animation-delay: 1s;"></div>
        <div class="absolute top-1/2 right-1/4 w-64 h-64 bg-yellow-400/10 rounded-full blur-2xl animate-pulse" style="animation-delay: 2s;"></div>
    </div>
    
    <div class="container mx-auto px-4 py-16 md:py-24 relative z-10">
        <div class="flex flex-col md:flex-row items-center">
            <div class="md:w-1/2 text-white mb-8 md:mb-0">
                <!-- Badge -->
                <div class="inline-flex items-center bg-halal-gold/20 border border-halal-gold/30 text-halal-gold px-4 py-2 rounded-full text-sm font-medium mb-6 backdrop-blur-sm">
                    <i class="{{ $hero('hero_badge_icon', 'bi bi-patch-check-fill') }} mr-2"></i> 
                    <span>{{ $hero('hero_badge_text', 'Trusted by 10,000+ Customers') }}</span>
                </div>
                
                <!-- Main Heading -->
                <h1 class="font-poppins text-4xl md:text-5xl lg:text-6xl font-bold mb-6 leading-tight">
                    {{ $hero('hero_title_line1', 'Fresh') }} <span class="text-halal-gold">{{ $hero('hero_title_highlight1', 'Halal Food') }}</span><br>
                    {{ $hero('hero_title_line2', 'Delivered Fresh') }}<br>
                    <span class="text-green-300">{{ $hero('hero_title_line3', 'To Your Door') }}</span>
                </h1>
                
                <!-- Description -->
                <p class="text-lg md:text-xl text-gray-200 mb-8 max-w-lg leading-relaxed">
                    {{ $hero('hero_description', 'Premium quality halal meat, poultry, seafood & groceries. 100% certified halal, farm-fresh, delivered within hours across Bangladesh.') }}
                </p>
                
                <!-- CTA Buttons -->
                <div class="flex flex-wrap gap-4 mb-8">
                    <a href="{{ route($hero('hero_cta1_link', 'products.index')) }}" class="group bg-halal-gold text-white px-8 py-4 rounded-xl font-semibold hover:bg-yellow-500 transition-all duration-300 inline-flex items-center shadow-lg shadow-halal-gold/30 hover:shadow-xl hover:shadow-halal-gold/40 hover:-translate-y-1">
                        <i class="{{ $hero('hero_cta1_icon', 'bi bi-cart3') }} mr-2 group-hover:animate-bounce"></i> 
                        {{ $hero('hero_cta1_text', 'Shop Now') }}
                        <i class="bi bi-arrow-right ml-2 group-hover:translate-x-1 transition-transform"></i>
                    </a>
                    <a href="{{ route($hero('hero_cta2_link', 'products.index'), $heroJson('hero_cta2_params') ?? ['sort' => 'discount']) }}" class="group bg-white/10 backdrop-blur-sm border border-white/20 text-white px-8 py-4 rounded-xl font-semibold hover:bg-white/20 transition-all duration-300 inline-flex items-center">
                        <i class="{{ $hero('hero_cta2_icon', 'bi bi-fire') }} text-orange-400 mr-2"></i> 
                        {{ $hero('hero_cta2_text', 'Hot Deals') }}
                        <span class="ml-2 bg-red-500 text-white text-xs px-2 py-0.5 rounded-full">{{ $hero('hero_cta2_badge', 'UP TO 30% OFF') }}</span>
                    </a>
                </div>
            </div>
            
            <!-- Hero Image Side -->
            <div class="md:w-1/2 relative">
                <!-- Main Image -->
                <div class="relative">
                    <!-- Decorative circles -->
                    <div class="absolute -top-4 -right-4 w-20 h-20 bg-halal-gold/30 rounded-full animate-ping"></div>
                    <div class="absolute top-1/2 -left-8 w-16 h-16 bg-green-400/30 rounded-full animate-pulse"></div>
                    
                    <!-- Main product image -->
                    <div class="relative z-10 rounded-3xl overflow-hidden shadow-2xl shadow-black/30 transform hover:scale-[1.02] transition-transform duration-500">
                        <img src="{{ $hero('hero_main_image', 'https://images.unsplash.com/photo-1607623814075-e51df1bdc82f?w=600') }}" 
                            alt="{{ $hero('hero_main_image_alt', 'Fresh Halal Meat') }}" 
                            class="w-full h-[350px] md:h-[450px] object-cover">
                        <div class="absolute inset-0 bg-gradient-to-t from-black/50 to-transparent"></div>
                        
                        <!-- Floating badge on image -->
                        <div class="absolute bottom-4 left-4 right-4 bg-white/95 backdrop-blur-sm rounded-2xl p-4 shadow-lg">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm text-gray-500">{{ $hero('hero_special_label', "Today's Special") }}</p>
                                    <p class="font-bold text-halal-dark text-lg">{{ $hero('hero_special_title', 'Premium Beef - 20% OFF') }}</p>
                                </div>
                                <a href="{{ route($hero('hero_special_link', 'products.index'), $heroJson('hero_special_params') ?? ['category' => 'fresh-meat']) }}" class="bg-halal-green text-white px-4 py-2 rounded-lg text-sm font-medium hover:text-amber-400 transition-colors">
                                    {{ $hero('hero_special_button', 'Order Now') }}
                                </a>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Floating cards -->
                    <div class="absolute bottom-24 left-4 bg-white rounded-2xl p-4 shadow-xl z-20 animate-bounce hidden md:block" style="animation-duration: 3s;">
                        <div class="flex items-center space-x-3">
                            <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center">
                                <i class="{{ $hero('hero_delivery_icon', 'bi bi-clock') }} text-halal-green text-xl"></i>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500">{{ $hero('hero_delivery_label', 'Delivery Time') }}</p>
                                <p class="font-bold text-gray-800">{{ $hero('hero_delivery_value', '30-60 Min') }}</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="absolute top-4 right-4 md:right-8 bg-white rounded-2xl p-3 shadow-xl z-20">
                        <div class="flex items-center space-x-2">
                            <div class="flex -space-x-2">
                                <div class="w-8 h-8 bg-halal-green rounded-full flex items-center justify-center text-white text-xs font-bold">R</div>
                                <div class="w-8 h-8 bg-halal-gold rounded-full flex items-center justify-center text-white text-xs font-bold">A</div>
                                <div class="w-8 h-8 bg-blue-500 rounded-full flex items-center justify-center text-white text-xs font-bold">S</div>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500">{{ $hero('hero_customers_label', 'Happy') }}</p>
                                <p class="font-bold text-gray-800 text-sm">{{ $hero('hero_customers_value', 'Customers') }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Features Bar -->
    <div class="absolute bottom-0 left-0 right-0 bg-white/10 backdrop-blur-md border-t border-white/10">
        <div class="container mx-auto px-4">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 py-4">
                <div class="flex items-center justify-center text-white py-2">
                    <div class="w-12 h-12 bg-halal-gold/20 rounded-xl flex items-center justify-center mr-3">
                        <i class="{{ $hero('hero_feature1_icon', 'bi bi-truck') }} text-halal-gold text-xl"></i>
                    </div>
                    <div>
                        <p class="font-medium">{{ $hero('hero_feature1_title', 'Free Delivery') }}</p>
                        <p class="text-xs text-gray-300">{{ $hero('hero_feature1_subtitle', 'Orders over  Tk500') }}</p>
                    </div>
                </div>
                <div class="flex items-center justify-center text-white py-2">
                    <div class="w-12 h-12 bg-green-500/20 rounded-xl flex items-center justify-center mr-3">
                        <i class="{{ $hero('hero_feature2_icon', 'bi bi-shield-check') }} text-green-400 text-xl"></i>
                    </div>
                    <div>
                        <p class="font-medium">{{ $hero('hero_feature2_title', '100% Halal') }}</p>
                        <p class="text-xs text-gray-300">{{ $hero('hero_feature2_subtitle', 'Certified Quality') }}</p>
                    </div>
                </div>
                <div class="flex items-center justify-center text-white py-2">
                    <div class="w-12 h-12 bg-blue-500/20 rounded-xl flex items-center justify-center mr-3">
                        <i class="{{ $hero('hero_feature3_icon', 'bi bi-cash-coin') }} text-blue-400 text-xl"></i>
                    </div>
                    <div>
                        <p class="font-medium">{{ $hero('hero_feature3_title', 'Best Prices') }}</p>
                        <p class="text-xs text-gray-300">{{ $hero('hero_feature3_subtitle', 'Guaranteed') }}</p>
                    </div>
                </div>
                <div class="flex items-center justify-center text-white py-2">
                    <div class="w-12 h-12 bg-purple-500/20 rounded-xl flex items-center justify-center mr-3">
                        <i class="{{ $hero('hero_feature4_icon', 'bi bi-headset') }} text-purple-400 text-xl"></i>
                    </div>
                    <div>
                        <p class="font-medium">{{ $hero('hero_feature4_title', '24/7 Support') }}</p>
                        <p class="text-xs text-gray-300">{{ $hero('hero_feature4_subtitle', 'Always Here') }}</p>
                    </div>
                </div>
    </div>
</section>
@endif

{{-- Render sections in the saved order --}}
@foreach($sectionOrder as $section)
    @if($section === 'categories')
        @if($home('homepage_show_categories_section', '1') == '1')
        @php
            // Get category settings
            $categoryStyle = $home('homepage_category_style', 'grid');
            $categoryColumns = (int) $home('homepage_category_columns', '6');
            $categoryTitle = $home('homepage_categories_title', 'Shop by Category');
            $categorySubtitle = $home('homepage_categories_subtitle', 'Browse our wide range of premium halal products, carefully sourced and quality assured');
        @endphp
        @if($categories->count() > 0)
        
        <!-- Categories Section - {{ $categoryStyle == 'grid' ? 'Grid' : 'Card' }} Style -->
        <section class="py-16 bg-gradient-to-b from-white via-gray-50 to-white relative overflow-hidden" data-section="categories">
            <!-- Decorative Background Elements -->
            <div class="absolute inset-0 pointer-events-none">
                <div class="absolute top-10 left-10 w-32 h-32 bg-halal-green/5 rounded-full blur-2xl"></div>
                <div class="absolute bottom-10 right-10 w-40 h-40 bg-halal-gold/5 rounded-full blur-2xl"></div>
                <div class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 w-96 h-96 bg-green-50/50 rounded-full blur-3xl"></div>
            </div>
            
            <div class="container mx-auto px-4 relative z-10">
                <!-- Section Header -->
                <div class="text-center mb-12">
                    <div class="inline-flex items-center bg-halal-green/10 text-halal-green px-4 py-2 rounded-full text-sm font-medium mb-4">
                        <i class="bi bi-grid-3x3-gap-fill mr-2"></i>
                        Explore Our Collection
                    </div>
                    <h2 class="font-poppins text-3xl md:text-4xl font-bold text-gray-800 mb-3">
                        {!! $categoryTitle !!}
                    </h2>
                    <p class="text-gray-600 max-w-2xl mx-auto text-lg">{{ $categorySubtitle }}</p>
                    
                    <!-- Decorative Line -->
                    <div class="flex items-center justify-center mt-6 space-x-2">
                        <div class="w-12 h-1 bg-halal-green rounded-full"></div>
                        <div class="w-3 h-3 bg-halal-gold rounded-full"></div>
                        <div class="w-12 h-1 bg-halal-green rounded-full"></div>
                    </div>
                </div>
                
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
                        'container' => 'bi bi-box-seam',
                        'mug' => 'bi bi-cup-straw',
                        'glass-holder' => 'bi bi-cup',
                        'washing-net' => 'bi bi-funnel',
                        'cup' => 'bi bi-cup',
                        'tray' => 'bi bi-layers',
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
                        'ss-plate' => 'bi bi-circle',
                        'ss-milk-pan' => 'bi bi-cup',
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
                        'houseware' => ['bg' => 'from-emerald-500 to-emerald-600', 'light' => 'bg-emerald-50', 'text' => 'text-emerald-500'],
                        'furniture' => ['bg' => 'from-amber-500 to-amber-600', 'light' => 'bg-amber-50', 'text' => 'text-amber-500'],
                        'cookware' => ['bg' => 'from-red-500 to-red-600', 'light' => 'bg-red-50', 'text' => 'text-red-500'],
                    ];
                    $defaultIcon = 'bi bi-tag-fill';
                    $defaultColor = ['bg' => 'from-halal-green to-green-600', 'light' => 'bg-green-50', 'text' => 'text-halal-green'];
                @endphp
                @if($categoryStyle == 'grid')
                    <!-- Grid Style Categories - Centered -->
                    <div class="flex flex-wrap justify-center gap-5">
                        
                        @foreach($categories as $category)
                            @php
                                $icon = $category->icon ?? ($categoryIcons[$category->slug] ?? $defaultIcon);
                                $color = $categoryColors[$category->slug] ?? $defaultColor;
                            @endphp
                            <a href="{{ route('products.index', ['category' => $category->slug]) }}" 
                               class="group relative bg-white rounded-2xl p-5 text-center shadow-sm hover:shadow-xl transition-all duration-500 transform hover:-translate-y-2 border border-gray-100 hover:border-transparent overflow-hidden"
                               style="width: calc({{ 100 / $categoryColumns }}% - 1rem); min-width: 140px; max-width: 180px;">
                                
                                <!-- Hover Background Gradient -->
                                <div class="absolute inset-0 bg-gradient-to-br {{ $color['bg'] }} opacity-0 group-hover:opacity-100 transition-opacity duration-500 rounded-2xl"></div>
                                
                                <!-- Card Content -->
                                <div class="relative z-10">
                                    <!-- Icon Container -->
                                    <div class="w-16 h-16 mx-auto mb-3 {{ $color['light'] }} rounded-2xl flex items-center justify-center group-hover:bg-white/20 transition-all duration-500 transform group-hover:scale-110 group-hover:rotate-3">
                                        <i class="{{ $icon }} text-2xl {{ $color['text'] }} group-hover:text-white transition-colors duration-300"></i>
                                    </div>
                                    
                                    <!-- Category Name -->
                                    <h3 class="font-semibold text-gray-800 group-hover:text-white transition-colors duration-300 mb-1 text-sm">
                                        {{ $category->name }}
                                    </h3>
                                    
                                    <!-- Product Count Badge -->
                                    <span class="inline-flex items-center text-xs {{ $color['text'] }} group-hover:text-white/80 transition-colors duration-300">
                                        <i class="bi bi-box-seam mr-1"></i>
                                        {{ $category->total_products_count ?? 0 }} Products
                                    </span>
                                    
                                    <!-- Arrow Indicator -->
                                    <div class="mt-2 opacity-0 group-hover:opacity-100 transition-all duration-300 transform translate-y-2 group-hover:translate-y-0">
                                        <span class="inline-flex items-center text-white text-xs font-medium">
                                            Explore <i class="bi bi-arrow-right ml-1"></i>
                                        </span>
                                    </div>
                                </div>
                                
                                <!-- Corner Decoration -->
                                <div class="absolute -top-4 -right-4 w-12 h-12 {{ $color['light'] }} rounded-full opacity-50 group-hover:opacity-0 transition-opacity duration-300"></div>
                            </a>
                        @endforeach
                    </div>
                @else
                    <!-- Card Style Categories - Centered -->
                    <div class="flex flex-wrap justify-center gap-6">
                        @php
                            $cardColors = [
                                'houseware' => 'from-emerald-500 to-emerald-600',
                                'furniture' => 'from-amber-500 to-amber-600',
                                'cookware' => 'from-red-500 to-red-600',
                            ];
                            $defaultCardColor = 'from-halal-green to-green-600';
                        @endphp
                        
                        @foreach($categories as $category)
                            @php
                                $icon = $category->icon ?? ($categoryIcons[$category->slug] ?? 'bi bi-tag-fill');
                                $cardColor = $cardColors[$category->slug] ?? $defaultCardColor;
                            @endphp
                            <a href="{{ route('products.index', ['category' => $category->slug]) }}" 
                               class="group relative bg-gradient-to-br {{ $cardColor }} rounded-2xl p-6 text-white shadow-lg hover:shadow-2xl transition-all duration-500 transform hover:-translate-y-2 overflow-hidden"
                               style="width: calc({{ 100 / $categoryColumns }}% - 1.5rem); min-width: 160px; max-width: 250px;">
                                
                                <!-- Background Pattern -->
                                <div class="absolute inset-0 opacity-10">
                                    <div class="absolute -top-10 -right-10 w-32 h-32 bg-white rounded-full"></div>
                                    <div class="absolute -bottom-10 -left-10 w-24 h-24 bg-white rounded-full"></div>
                                </div>
                                
                                <!-- Card Content -->
                                <div class="relative z-10 text-center">
                                    <!-- Icon -->
                                    <div class="w-16 h-16 mx-auto mb-3 bg-white/20 rounded-2xl flex items-center justify-center backdrop-blur-sm group-hover:bg-white/30 transition-all duration-300 transform group-hover:scale-110">
                                        <i class="{{ $icon }} text-3xl text-white"></i>
                                    </div>
                                    
                                    <!-- Category Name -->
                                    <h3 class="font-bold text-lg mb-1">{{ $category->name }}</h3>
                                    
                                    <!-- Product Count -->
                                    <p class="text-white/80 text-xs mb-2">
                                        <i class="bi bi-box-seam mr-1"></i>
                                        {{ $category->total_products_count ?? 0 }} Products
                                    </p>
                                    
                                    <!-- Arrow Button -->
                                    <div class="inline-flex items-center bg-white/20 hover:bg-white/30 px-3 py-1 rounded-full text-xs font-medium transition-all duration-300">
                                        Shop Now <i class="bi bi-arrow-right ml-1 group-hover:translate-x-1 transition-transform"></i>
                                    </div>
                                </div>
                            </a>
                        @endforeach
                    </div>
                @endif
                
                <!-- View All Categories Button -->
                <div class="text-center mt-10">
                    <a href="{{ route('products.index') }}" 
                       class="inline-flex items-center bg-halal-dark text-white px-8 py-3 rounded-full font-medium hover:bg-halal-green hover:text-white transition-all duration-300 shadow-lg hover:shadow-xl transform hover:-translate-y-1">
                        <i class="bi bi-grid-fill mr-2"></i>
                        View All Categories
                        <i class="bi bi-arrow-right ml-2"></i>
                    </a>
                </div>
            </div>
        </section>
        @else
        <section class="py-16 bg-gradient-to-b from-white via-gray-50 to-white relative overflow-hidden" data-section="categories">
            <div class="container mx-auto px-4 text-center py-8">
                <p class="text-gray-500">No categories available yet.</p>
            </div>
        </section>
        @endif
        @endif

    @elseif($section === 'featured')
        <!-- Featured Products -->
        @if($featuredProducts->count() > 0 && $home('homepage_show_featured_section', '1') == '1')
        <section class="py-12" data-section="featured">
            <div class="container mx-auto px-4">
                <div class="flex items-center justify-between mb-8">
                    <div>
                        <h2 class="font-poppins text-3xl font-bold text-gray-800"><i class="bi bi-star-fill text-halal-gold mr-2"></i>{{ $home('homepage_featured_title', 'Featured Products') }}</h2>
                        <p class="text-gray-600 mt-1">{{ $home('homepage_featured_subtitle', 'Handpicked premium quality products for you') }}</p>
                    </div>
                    <a href="{{ route('products.index') }}" class="text-halal-green hover:text-halal-dark font-medium flex items-center">
                        View All <i class="bi bi-arrow-right ml-1"></i>
                    </a>
                </div>
                
                <!-- Product Slider -->
                <div class="product-slider-container relative">
                    <div class="product-slider" id="featured-slider" data-columns="{{ $productColumns }}">
                        <div class="swiper-wrapper">
                            @foreach($featuredProducts as $product)
                                <div class="swiper-slide" style="width: {{ 100 / $productColumns }}%;">
                                    @include('themes.general.partials.product-card', ['product' => $product])
                                </div>
                            @endforeach
                        </div>
                        <!-- Navigation Buttons -->
                        <div class="swiper-button-next featured-slider-next"></div>
                        <div class="swiper-button-prev featured-slider-prev"></div>
                        <!-- Pagination -->
                        <div class="swiper-pagination featured-slider-pagination"></div>
                    </div>
                </div>
            </div>
        </section>
        @endif

    @elseif($section === 'banner')
        <!-- Banner Section -->
        @if($home('homepage_show_banner_section', '1') == '1')
        @php
            // Count visible banners for dynamic grid
            $visibleBanners = [];
            for($j = 1; $j <= 4; $j++) {
                if($home('banner' . $j . '_visible', '1') == '1') {
                    $visibleBanners[] = $j;
                }
            }
            $bannerCount = count($visibleBanners);
            $bannerGridClass = match($bannerCount) {
                1 => 'grid md:grid-cols-1 max-w-md mx-auto',
                2 => 'grid md:grid-cols-2',
                3 => 'grid md:grid-cols-3',
                default => 'grid md:grid-cols-2 lg:grid-cols-4',
            };
            $bannerConfigs = [
                1 => ['gradient' => 'from-halal-green to-green-600', 'badge_bg' => 'bg-halal-gold', 'text_muted' => 'text-green-100'],
                2 => ['gradient' => 'from-halal-gold to-yellow-500', 'badge_bg' => 'bg-halal-dark', 'text_muted' => 'text-yellow-100'],
                3 => ['gradient' => 'from-blue-500 to-blue-600', 'badge_bg' => 'bg-white', 'badge_text' => 'text-blue-600', 'text_muted' => 'text-blue-100'],
                4 => ['gradient' => 'from-red-500 to-red-600', 'badge_bg' => 'bg-white', 'badge_text' => 'text-red-600', 'text_muted' => 'text-red-100'],
            ];
        @endphp
        @if($bannerCount > 0)
        <section class="py-8" data-section="banner">
            <div class="container mx-auto px-4">
                <div class="{{ $bannerGridClass }} gap-6">
                    @foreach($visibleBanners as $i)
                        @php
                            $title = $home('banner' . $i . '_title', '');
                            $hasContent = !empty($title);
                        @endphp
                        @if($hasContent)
                        <div class="bg-gradient-to-r {{ $bannerConfigs[$i]['gradient'] }} rounded-2xl p-6 text-white relative overflow-hidden">
                            <div class="absolute right-0 top-0 opacity-20">
                                <i class="bi {{ $home('banner' . $i . '_icon', 'bi-star-fill') }} text-[100px] transform rotate-12"></i>
                            </div>
                            <div class="relative z-10">
                                <span class="{{ $bannerConfigs[$i]['badge_bg'] }} {{ $bannerConfigs[$i]['badge_text'] ?? 'text-white' }} px-3 py-1 rounded-full text-sm font-medium">{{ $home('banner' . $i . '_badge', 'Offer') }}</span>
                                <h3 class="font-poppins text-xl font-bold mt-3">{{ $title }}</h3>
                                <p class="{{ $bannerConfigs[$i]['text_muted'] }} mt-2 text-sm">{{ $home('banner' . $i . '_description', '') }}</p>
                                @php $link = $home('banner' . $i . '_link', ''); @endphp
                                @if($link)
                                    <a href="{{ route('products.index', ['category' => $link]) }}" class="inline-block mt-3 bg-white text-gray-800 px-4 py-2 rounded-full text-sm font-medium hover:bg-gray-100 transition-colors">
                                        {{ $home('banner' . $i . '_button_text', 'Shop Now') }} <i class="bi bi-arrow-right ml-1"></i>
                                    </a>
                                @else
                                    <span class="inline-block mt-3 bg-white/20 text-white px-4 py-2 rounded-full text-sm font-medium">
                                        {{ $home('banner' . $i . '_button_text', 'Shop Now') }} <i class="bi bi-arrow-right ml-1"></i>
                                    </span>
                                @endif
                            </div>
                        </div>
                        @endif
                    @endforeach
                </div>
            </div>
        </section>
        @endif
        @endif

    @elseif($section === 'new_arrivals')
        <!-- New Arrivals -->
        @if($latestProducts->count() > 0 && $home('homepage_show_new_arrivals_section', '1') == '1')
        <section class="py-12 bg-gray-50" data-section="new_arrivals">
            <div class="container mx-auto px-4">
                <div class="flex items-center justify-between mb-8">
                    <div>
                        <h2 class="font-poppins text-3xl font-bold text-gray-800">{{ $home('homepage_new_arrivals_title', 'New Arrivals') }}</h2>
                        <p class="text-gray-600 mt-1">{{ $home('homepage_new_arrivals_subtitle', 'Fresh products just arrived in our store') }}</p>
                    </div>
                    <a href="{{ route('products.index', ['sort' => 'newest']) }}" class="text-halal-green hover:text-halal-dark font-medium flex items-center">
                        View All <i class="bi bi-arrow-right ml-1"></i>
                    </a>
                </div>
                
                <!-- Product Slider -->
                <div class="product-slider-container relative">
                    <div class="product-slider" id="new-arrivals-slider" data-columns="{{ $productColumns }}">
                        <div class="swiper-wrapper">
                            @foreach($latestProducts as $product)
                                <div class="swiper-slide" style="width: {{ 100 / $productColumns }}%;">
                                    @include('themes.general.partials.product-card', ['product' => $product])
                                </div>
                            @endforeach
                        </div>
                        <!-- Navigation Buttons -->
                        <div class="swiper-button-next new-arrivals-slider-next"></div>
                        <div class="swiper-button-prev new-arrivals-slider-prev"></div>
                        <!-- Pagination -->
                        <div class="swiper-pagination new-arrivals-slider-pagination"></div>
                    </div>
                </div>
            </div>
        </section>
        @endif

    @elseif($section === 'category_products')
        <!-- Category Product Sliders -->
        @if(($home('homepage_show_category_products_section', '1') == '1'))
        @php
            $categoryOrderIds = json_decode($home('homepage_category_products_order', '[]'), true);
            if (empty($categoryOrderIds)) {
                $categoryOrderIds = json_decode($home('homepage_selected_categories', '[]'), true);
            }
            $catProductsCount = (int) $home('homepage_category_products_count', '10');
        @endphp
        @if(!empty($categoryOrderIds))
        <section class="py-12 bg-gray-50" data-section="category_products">
            <div class="container mx-auto px-4">
                @foreach($categoryOrderIds as $catId)
                    @php
                        $catModel = \App\Models\Category::find($catId);
                        if (!$catModel) continue;
                        $childIds = \App\Models\Category::where('parent_id', $catModel->id)->pluck('id')->toArray();
                        $allIds = array_merge([$catModel->id], $childIds);
                        $products = \App\Models\Product::active()->whereIn('category_id', $allIds)->inStock()->latest()->take($catProductsCount)->get();
                    @endphp
                    @if($products->count() > 0)
                    <div class="mb-10 last:mb-0">
                        <div class="flex items-center justify-between mb-6">
                            <div>
                                <h2 class="font-poppins text-2xl font-bold text-gray-800">
                                    <i class="bi bi-tag-fill text-halal-green me-2"></i>
                                    {{ $catModel->name }}
                                </h2>
                            </div>
                            <a href="{{ route('products.index', ['category' => $catModel->slug]) }}" class="text-halal-green hover:text-halal-dark font-medium flex items-center text-sm">
                                View All <i class="bi bi-arrow-right ml-1"></i>
                            </a>
                        </div>
                        <div class="product-slider-container relative">
                            <div class="product-slider" id="category-slider-{{ $loop->index }}" data-columns="{{ $productColumns }}">
                                <div class="swiper-wrapper">
                                    @foreach($products as $product)
                                        <div class="swiper-slide" style="width: {{ 100 / $productColumns }}%;">
                                            @include('themes.general.partials.product-card', ['product' => $product])
                                        </div>
                                    @endforeach
                                </div>
                                <div class="swiper-button-next category-slider-next-{{ $loop->index }}"></div>
                                <div class="swiper-button-prev category-slider-prev-{{ $loop->index }}"></div>
                                <div class="swiper-pagination category-slider-pagination-{{ $loop->index }}"></div>
                            </div>
                        </div>
                    </div>
                    @endif
                @endforeach
            </div>
        </section>
        @endif
        @endif

    @elseif($section === 'why_choose_us')
        <!-- Why Choose Us -->
        @if($home('homepage_show_why_choose_us_section', '1') == '1')
        <section class="py-16 bg-halal-dark text-white" data-section="why_choose_us">
            <div class="container mx-auto px-4">
                <div class="text-center mb-12">
                    <h2 class="font-poppins text-3xl font-bold">{{ $home('why_choose_us_title', 'Why Choose Us?') }}</h2>
                    <p class="text-gray-400 mt-2">{{ $home('why_choose_us_subtitle', 'We are committed to providing the best halal products') }}</p>
                </div>
                
                <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-8">
                    @for($i = 1; $i <= 4; $i++)
                        @php
                            $icon = $home('why_choose_us_icon_' . $i, 'bi-patch-check-fill');
                            $title = $home('why_choose_us_title_' . $i, 'Feature ' . $i);
                            $desc = $home('why_choose_us_desc_' . $i, 'Feature description');
                        @endphp
                        @if($title && $title !== 'Feature ' . $i)
                        <div class="text-center">
                            <div class="w-20 h-20 mx-auto bg-halal-green rounded-full flex items-center justify-center mb-4">
                                <i class="bi {{ $icon }} text-3xl text-halal-gold"></i>
                            </div>
                            <h3 class="font-poppins text-xl font-bold mb-2">{{ $title }}</h3>
                            <p class="text-gray-400">{{ $desc }}</p>
                        </div>
                        @endif
                    @endfor
                </div>
            </div>
        </section>
        @endif

    @elseif($section === 'sale')
        <!-- Sale Products -->
        @if($saleProducts->count() > 0 && $home('homepage_show_sale_section', '1') == '1')
        <section class="py-12" data-section="sale">
            <div class="container mx-auto px-4">
                <div class="flex items-center justify-between mb-8">
                    <div>
                        <h2 class="font-poppins text-3xl font-bold text-gray-800">
                            <i class="bi bi-fire text-red-500"></i> {{ $home('homepage_sale_title', 'Hot Deals') }}
                        </h2>
                        <p class="text-gray-600 mt-1">{{ $home('homepage_sale_subtitle', 'Limited time offers - Grab them before they are gone!') }}</p>
                    </div>
                    <a href="{{ route('products.index', ['sort' => 'discount']) }}" class="text-halal-green hover:text-halal-dark font-medium flex items-center">
                        View All <i class="bi bi-arrow-right ml-1"></i>
                    </a>
                </div>
                
                <!-- Product Slider -->
                <div class="product-slider-container relative">
                    <div class="product-slider" id="sale-slider" data-columns="{{ $productColumns }}">
                        <div class="swiper-wrapper">
                            @foreach($saleProducts as $product)
                                <div class="swiper-slide" style="width: {{ 100 / $productColumns }}%;">
                                    @include('themes.general.partials.product-card', ['product' => $product])
                                </div>
                            @endforeach
                        </div>
                        <!-- Navigation Buttons -->
                        <div class="swiper-button-next sale-slider-next"></div>
                        <div class="swiper-button-prev sale-slider-prev"></div>
                        <!-- Pagination -->
                        <div class="swiper-pagination sale-slider-pagination"></div>
                    </div>
                </div>
            </div>
        </section>
        @endif

    @elseif($section === 'testimonials')
        <!-- Testimonials -->
        @if($home('homepage_show_testimonials_section', '1') == '1')
        <section class="py-12 bg-gray-50" data-section="testimonials">
            <div class="container mx-auto px-4">
                <div class="text-center mb-10">
                    <h2 class="font-poppins text-3xl font-bold text-gray-800">{{ $home('testimonials_title', 'What Our Customers Say') }}</h2>
                    <p class="text-gray-600 mt-2">{{ $home('testimonials_subtitle', 'Trusted by thousands of customers across Bangladesh') }}</p>
                </div>
                
                <div class="grid md:grid-cols-3 gap-6">
                    @for($i = 1; $i <= 3; $i++)
                        @php
                            $name = $home('testimonial' . $i . '_name', '');
                            $location = $home('testimonial' . $i . '_location', '');
                            $text = $home('testimonial' . $i . '_text', '');
                            $rating = (int) $home('testimonial' . $i . '_rating', '5');
                            $colors = ['bg-halal-green', 'bg-halal-gold', 'bg-halal-dark'];
                        @endphp
                        @if($name && $text)
                        <div class="bg-white p-6 rounded-xl shadow-md">
                            <div class="flex text-halal-gold mb-4">
                                @for($s = 1; $s <= 5; $s++)
                                    @if($s <= $rating)
                                        <i class="bi bi-star-fill"></i>
                                    @elseif($s - 0.5 <= $rating)
                                        <i class="bi bi-star-half"></i>
                                    @else
                                        <i class="bi bi-star"></i>
                                    @endif
                                @endfor
                            </div>
                            <p class="text-gray-600 mb-4">"{{ $text }}"</p>
                            <div class="flex items-center">
                                <div class="w-12 h-12 {{ $colors[$i - 1] ?? 'bg-halal-green' }} rounded-full flex items-center justify-center text-white font-bold">
                                    {{ strtoupper(substr($name, 0, 1) . substr($name, strpos($name, ' ') !== false ? strpos($name, ' ') + 1 : 1, 1)) }}
                                </div>
                                <div class="ml-3">
                                    <h4 class="font-medium text-gray-800">{{ $name }}</h4>
                                    <p class="text-sm text-gray-500">{{ $location }}</p>
                                </div>
                            </div>
                        </div>
                        @endif
                    @endfor
                </div>
            </div>
        </section>
        @endif

    @elseif($section === 'blog')
        <!-- Blog Section -->
        @if($latestBlogs->count() > 0 && $home('homepage_show_blog_section', '1') == '1')
        <section class="py-12" data-section="blog">
            <div class="container mx-auto px-4">
                <div class="flex items-center justify-between mb-8">
                    <div>
                        <h2 class="font-poppins text-3xl font-bold text-gray-800">From Our Blog</h2>
                        <p class="text-gray-600 mt-1">Tips, recipes, and halal food insights</p>
                    </div>
                    <a href="{{ route('blogs.index') }}" class="text-halal-green hover:text-halal-dark font-medium flex items-center">
                        View All <i class="bi bi-arrow-right ml-1"></i>
                    </a>
                </div>
                
                <div class="grid md:grid-cols-3 gap-6">
                    @foreach($latestBlogs as $blog)
                    <article class="bg-white rounded-xl shadow-md overflow-hidden group">
                        <div class="overflow-hidden">
                            @if($blog->featured_image)
                                <img src="{{ Storage::url($blog->featured_image) }}" 
                                    alt="{{ $blog->title }}" 
                                    class="w-full h-48 object-cover group-hover:scale-105 transition-transform duration-300">
                            @else
                                <img src="https://placehold.co/400x250?text=Blog" 
                                    alt="{{ $blog->title }}" 
                                    class="w-full h-48 object-cover group-hover:scale-105 transition-transform duration-300">
                            @endif
                        </div>
                        <div class="p-5">
                            <div class="flex items-center text-sm text-gray-500 mb-2">
                                <i class="bi bi-calendar3 mr-1"></i>
                                {{ $blog->published_at ? $blog->published_at->format('M d, Y') : $blog->created_at->format('M d, Y') }}
                                <span class="mx-2">-</span>
                                <i class="bi bi-person mr-1"></i>
                                {{ $blog->author->name ?? 'Admin' }}
                            </div>
                            <h3 class="font-poppins text-lg font-bold text-gray-800 mb-2 hover:text-halal-green transition-colors">
                                <a href="{{ route('blogs.show', $blog->slug) }}">{{ Str::limit($blog->title, 50) }}</a>
                            </h3>
                            <p class="text-gray-600 text-sm">{{ Str::limit(strip_tags($blog->content), 100) }}</p>
                            <a href="{{ route('blogs.show', $blog->slug) }}" class="inline-block mt-3 text-halal-green font-medium hover:text-halal-dark">
                                Read More <i class="bi bi-arrow-right ml-1"></i>
                            </a>
                        </div>
                    </article>
                    @endforeach
                </div>
            </div>
        </section>
        @endif
    @elseif($section === 'dynamic_banners')
        <!-- Dynamic Banners Section - Admin Created Banners -->
        @php
            $homeTopBanners = isset($homeTopBanners) && is_object($homeTopBanners) ? $homeTopBanners : collect();
            $homeMiddleBanners = isset($homeMiddleBanners) && is_object($homeMiddleBanners) ? $homeMiddleBanners : collect();
            $homeBottomBanners = isset($homeBottomBanners) && is_object($homeBottomBanners) ? $homeBottomBanners : collect();
            $hasDynamicBanners = $homeTopBanners->count() > 0 || $homeMiddleBanners->count() > 0 || $homeBottomBanners->count() > 0;
        @endphp
        @if($hasDynamicBanners)
            {{-- Home Top Banners --}}
            @if($homeTopBanners->count() > 0)
            <section class="py-8" data-section="dynamic-banner-top">
                <div class="container mx-auto px-4">
                    <div class="grid md:grid-cols-{{ min($homeTopBanners->count(), 3) }} gap-6">
                        @foreach($homeTopBanners as $banner)
                            @php
                                $bannerImage = $banner->image;
                                if($bannerImage && !str_starts_with($bannerImage, '/storage/') && !str_starts_with($bannerImage, 'http')) {
                                    $bannerImage = '/storage/' . $bannerImage;
                                }
                            @endphp
                            <a href="{{ $banner->link ?? '#' }}" class="group relative block overflow-hidden rounded-2xl">
                                <img src="{{ $bannerImage }}" alt="{{ $banner->title }}" class="w-full h-48 md:h-56 object-cover transform group-hover:scale-105 transition-transform duration-500">
                                <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-black/20 to-transparent flex flex-col justify-end p-6">
                                    @if($banner->title)
                                        <h3 class="text-white font-bold text-xl">{{ $banner->title }}</h3>
                                    @endif
                                    @if($banner->description)
                                        <p class="text-white/80 text-sm mt-1">{{ $banner->description }}</p>
                                    @endif
                                    @if($banner->button_text)
                                        <span class="inline-block mt-3 px-4 py-2 rounded-full text-sm font-medium w-fit" 
                                              style="background-color: {{ $banner->button_color ?? '#D4AF37' }}; color: #fff;">
                                            {{ $banner->button_text }}
                                        </span>
                                    @endif
                                </div>
                            </a>
                        @endforeach
                    </div>
                </div>
            </section>
            @endif

            {{-- Home Middle Banners --}}
            @if($homeMiddleBanners->count() > 0)
            <section class="py-8 bg-gray-50" data-section="dynamic-banner-middle">
                <div class="container mx-auto px-4">
                    <div class="grid md:grid-cols-2 gap-6">
                        @foreach($homeMiddleBanners as $banner)
                            @php
                                $bannerImage = $banner->image;
                                if($bannerImage && !str_starts_with($bannerImage, '/storage/') && !str_starts_with($bannerImage, 'http')) {
                                    $bannerImage = '/storage/' . $bannerImage;
                                }
                            @endphp
                            <a href="{{ $banner->link ?? '#' }}" class="group relative block overflow-hidden rounded-2xl">
                                <img src="{{ $bannerImage }}" alt="{{ $banner->title }}" class="w-full h-64 object-cover transform group-hover:scale-105 transition-transform duration-500">
                                <div class="absolute inset-0 bg-gradient-to-r from-black/60 to-transparent flex flex-col justify-center p-8">
                                    @if($banner->title)
                                        <h3 class="text-white font-bold text-2xl">{{ $banner->title }}</h3>
                                    @endif
                                    @if($banner->description)
                                        <p class="text-white/80 mt-2">{{ $banner->description }}</p>
                                    @endif
                                    @if($banner->button_text)
                                        <span class="inline-block mt-4 px-6 py-2 rounded-full text-sm font-medium w-fit" 
                                              style="background-color: {{ $banner->button_color ?? '#D4AF37' }}; color: #fff;">
                                            {{ $banner->button_text }}
                                        </span>
                                    @endif
                                </div>
                            </a>
                        @endforeach
                    </div>
                </div>
            </section>
            @endif

            {{-- Home Bottom Banners --}}
            @if($homeBottomBanners->count() > 0)
            <section class="py-8" data-section="dynamic-banner-bottom">
                <div class="container mx-auto px-4">
                    <div class="grid md:grid-cols-{{ min($homeBottomBanners->count(), 4) }} gap-6">
                        @foreach($homeBottomBanners as $banner)
                            @php
                                $bannerImage = $banner->image;
                                if($bannerImage && !str_starts_with($bannerImage, '/storage/') && !str_starts_with($bannerImage, 'http')) {
                                    $bannerImage = '/storage/' . $bannerImage;
                                }
                            @endphp
                            <a href="{{ $banner->link ?? '#' }}" class="group relative block overflow-hidden rounded-xl">
                                <img src="{{ $bannerImage }}" alt="{{ $banner->title }}" class="w-full h-36 object-cover transform group-hover:scale-105 transition-transform duration-500">
                                <div class="absolute inset-0 bg-black/40 flex items-center justify-center">
                                    @if($banner->title)
                                        <h3 class="text-white font-semibold text-center px-2">{{ $banner->title }}</h3>
                                    @endif
                                </div>
                            </a>
                        @endforeach
                    </div>
                </div>
            </section>
            @endif
        @endif
    @endif
@endforeach
@endsection

@push('styles')
<style>
    /* Hero Slider Styles - Custom Implementation */
    .hero-slider-section {
        position: relative;
        overflow: hidden;
        min-height: 500px;
    }
    
    @media (min-width: 768px) {
        .hero-slider-section {
            min-height: 600px;
        }
    }
    
    .hero-slider {
        position: relative;
        height: 500px;
        width: 100%;
    }
    
    @media (min-width: 768px) {
        .hero-slider {
            height: 600px;
        }
    }
    
    .hero-slider-wrapper {
        position: relative;
        height: 100%;
        width: 100%;
    }
    
    .hero-slide {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        opacity: 0;
        visibility: hidden;
        transition: opacity 0.8s ease-in-out, visibility 0.8s;
        z-index: 1;
    }
    
    .hero-slide.active {
        opacity: 1;
        visibility: visible;
        z-index: 2;
    }
    
    .hero-slide-img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        object-position: center;
    }
    
    .hero-slide-overlay {
        position: absolute;
        inset: 0;
        background: linear-gradient(to right, rgba(30, 58, 38, 0.9), rgba(30, 58, 38, 0.7), transparent);
        z-index: 1;
    }
    
    .hero-slide-content {
        position: absolute;
        top: 50%;
        left: 0;
        right: 0;
        transform: translateY(-50%);
        z-index: 2;
        padding: 0 2rem;
    }
    
    .hero-slide-subtitle {
        color: #D4AF37;
        font-size: 1.25rem;
        font-weight: 500;
        margin-bottom: 0.5rem;
    }
    
    .hero-slide-title {
        font-family: 'Poppins', sans-serif;
        font-size: 2.5rem;
        font-weight: 700;
        line-height: 1.2;
        margin-bottom: 1.5rem;
        color: white;
    }
    
    @media (min-width: 768px) {
        .hero-slide-title {
            font-size: 3.5rem;
        }
        .hero-slide-content {
            padding: 0 4rem;
        }
    }
    
    @media (min-width: 1024px) {
        .hero-slide-title {
            font-size: 4.5rem;
        }
        .hero-slide-content {
            padding: 0 6rem;
        }
    }
    
    .hero-slide-btn {
        display: inline-flex;
        align-items: center;
        padding: 1rem 2rem;
        border-radius: 0.75rem;
        font-weight: 600;
        text-decoration: none;
        transition: all 0.3s ease;
    }
    
    .hero-slide-btn:hover {
        transform: translateY(-2px);
    }
    
    .hero-features-bar {
        position: absolute;
        bottom: 0;
        left: 0;
        right: 0;
        background: rgba(255, 255, 255, 0.1);
        backdrop-filter: blur(10px);
        border-top: 1px solid rgba(255, 255, 255, 0.1);
        z-index: 20;
    }
    
    /* Slider Controls */
    .hero-slider-prev,
    .hero-slider-next {
        position: absolute;
        top: 50%;
        transform: translateY(-50%);
        width: 50px;
        height: 50px;
        background-color: rgba(30, 58, 38, 0.5);
        border: 2px solid rgba(255, 255, 255, 0.3);
        border-radius: 50%;
        color: white;
        font-size: 1.5rem;
        cursor: pointer;
        transition: all 0.3s ease;
        z-index: 10;
    }
    
    .hero-slider-prev:hover,
    .hero-slider-next:hover {
        background-color: #D4AF37;
        border-color: #D4AF37;
    }
    
    .hero-slider-prev {
        left: 20px;
    }
    
    .hero-slider-next {
        right: 20px;
    }
    
    @media (max-width: 480px) {
        .hero-slider-prev { left: 8px; width: 36px; height: 36px; font-size: 1rem; }
        .hero-slider-next { right: 8px; width: 36px; height: 36px; font-size: 1rem; }
    }
    
    /* Slider Indicators */
    .hero-slider-indicators {
        position: absolute;
        bottom: 100px;
        left: 50%;
        transform: translateX(-50%);
        display: flex;
        gap: 10px;
        z-index: 10;
    }
    
    .hero-indicator {
        width: 12px;
        height: 12px;
        border-radius: 50%;
        background-color: rgba(255, 255, 255, 0.5);
        border: none;
        cursor: pointer;
        transition: all 0.3s ease;
    }
    
    .hero-indicator.active {
        background-color: #D4AF37;
        width: 30px;
        border-radius: 6px;
    }
    
    /* Product Slider Styles - Swiper */
    .product-slider-container {
        position: relative;
        padding: 0 10px;
    }
    
    .product-slider {
        overflow: hidden;
        padding-bottom: 50px;
    }
    
    .product-slider .swiper-slide {
        height: auto;
    }
    
    .product-slider .swiper-slide > div {
        height: 100%;
    }
    
    /* Swiper Navigation Buttons */
    .swiper-button-prev,
    .swiper-button-next {
        width: 36px;
        height: 36px;
        background-color: #2D5A27;
        border-radius: 50%;
        color: white;
        font-size: 14px;
        transition: all 0.3s ease;
        position: absolute;
        top: 50%;
        transform: translateY(-50%);
        z-index: 20;
        cursor: pointer;
    }
    
    .swiper-button-prev:hover,
    .swiper-button-next:hover {
        background-color: #D4AF37;
    }
    
    .swiper-button-prev::after,
    .swiper-button-next::after {
        font-size: 12px;
        font-weight: bold;
    }
    
    .swiper-button-prev {
        left: 5px;
    }
    
    .swiper-button-next {
        right: 5px;
    }
    
    /* Swiper Pagination */
    .swiper-pagination-bullet {
        width: 10px;
        height: 10px;
        background-color: #ccc;
        opacity: 1;
    }
    
    .swiper-pagination-bullet-active {
        background-color: #2D5A27;
        width: 20px;
        border-radius: 5px;
    }
    
    /* Responsive adjustments */
    @media (max-width: 768px) {
        .product-slider-container {
            padding: 0 30px;
        }
        
        .swiper-button-prev,
        .swiper-button-next {
            width: 30px;
            height: 30px;
        }
        
        .swiper-button-prev {
            left: 10px;
        }
        
        .swiper-button-next {
            right: 10px;
        }
        
        .swiper-button-prev::after,
        .swiper-button-next::after {
            font-size: 12px;
        }
    }
</style>
@endpush

@push('scripts')
<script>
    // Hero Slider Functionality
    document.addEventListener('DOMContentLoaded', function() {
        const slider = document.getElementById('heroSlider');
        if (!slider) return;
        
        const slides = slider.querySelectorAll('.hero-slide');
        const indicators = slider.querySelectorAll('.hero-indicator');
        const prevBtn = slider.querySelector('.hero-slider-prev');
        const nextBtn = slider.querySelector('.hero-slider-next');
        
        let currentSlide = 0;
        let slideInterval;
        
        function showSlide(index) {
            slides.forEach((slide, i) => {
                slide.classList.toggle('active', i === index);
            });
            indicators.forEach((indicator, i) => {
                indicator.classList.toggle('active', i === index);
            });
            currentSlide = index;
        }
        
        function nextSlide() {
            showSlide((currentSlide + 1) % slides.length);
        }
        
        function prevSlide() {
            showSlide((currentSlide - 1 + slides.length) % slides.length);
        }
        
        function startAutoSlide() {
            slideInterval = setInterval(nextSlide, 5000);
        }
        
        function stopAutoSlide() {
            clearInterval(slideInterval);
        }
        
        // Event listeners
        if (nextBtn) {
            nextBtn.addEventListener('click', () => {
                stopAutoSlide();
                nextSlide();
                startAutoSlide();
            });
        }
        
        if (prevBtn) {
            prevBtn.addEventListener('click', () => {
                stopAutoSlide();
                prevSlide();
                startAutoSlide();
            });
        }
        
        indicators.forEach((indicator, index) => {
            indicator.addEventListener('click', () => {
                stopAutoSlide();
                showSlide(index);
                startAutoSlide();
            });
        });
        
        // Start auto slide
        startAutoSlide();
    });
    
    // Initialize Product Sliders with Swiper
    document.addEventListener('DOMContentLoaded', function() {
        // Single column setting for all product sections
        const productColumns = {{ $productColumns ?? 6 }};
        
        // Function to create responsive breakpoints based on column setting
        function createBreakpoints(maxColumns) {
            return {
                0: {
                    slidesPerView: 2,
                    spaceBetween: 12,
                },
                640: {
                    slidesPerView: Math.min(2, maxColumns),
                    spaceBetween: 16,
                },
                768: {
                    slidesPerView: Math.min(3, maxColumns),
                    spaceBetween: 20,
                },
                1024: {
                    slidesPerView: Math.min(4, maxColumns),
                    spaceBetween: 20,
                },
                1280: {
                    slidesPerView: maxColumns,
                    spaceBetween: 24,
                },
            };
        }
        
        // Initialize Featured Products Slider
        const featuredSlider = document.getElementById('featured-slider');
        if (featuredSlider) {
            new Swiper('#featured-slider', {
                slidesPerView: 2,
                spaceBetween: 16,
                loop: true,
                autoplay: {
                    delay: 4000,
                    disableOnInteraction: false,
                    pauseOnMouseEnter: true,
                },
                pagination: {
                    el: '.featured-slider-pagination',
                    clickable: true,
                },
                navigation: {
                    nextEl: '.featured-slider-next',
                    prevEl: '.featured-slider-prev',
                },
                breakpoints: createBreakpoints(productColumns),
            });
        }
        
        // Initialize New Arrivals Slider
        const newArrivalsSlider = document.getElementById('new-arrivals-slider');
        if (newArrivalsSlider) {
            new Swiper('#new-arrivals-slider', {
                slidesPerView: 2,
                spaceBetween: 16,
                loop: true,
                loopAdditionalSlides: 2,
                autoplay: {
                    delay: 4000,
                    disableOnInteraction: false,
                    pauseOnMouseEnter: true,
                },
                pagination: {
                    el: '.new-arrivals-slider-pagination',
                    clickable: true,
                },
                navigation: {
                    nextEl: '.new-arrivals-slider-next',
                    prevEl: '.new-arrivals-slider-prev',
                },
                breakpoints: createBreakpoints(productColumns),
            });
        }

        // Initialize Category Product Sliders
        for (let i = 0; i < 3; i++) {
            const catSlider = document.getElementById('category-slider-' + i);
            if (catSlider) {
                new Swiper('#category-slider-' + i, {
                    slidesPerView: 2,
                    spaceBetween: 16,
                    loop: true,
                    loopAdditionalSlides: 2,
                    autoplay: {
                        delay: 4000,
                        disableOnInteraction: false,
                        pauseOnMouseEnter: true,
                    },
                    pagination: {
                        el: '.category-slider-pagination-' + i,
                        clickable: true,
                    },
                    navigation: {
                        nextEl: '.category-slider-next-' + i,
                        prevEl: '.category-slider-prev-' + i,
                    },
                    breakpoints: createBreakpoints(productColumns),
                });
            }
        }

        // Initialize Sale Products Slider
        const saleSlider = document.getElementById('sale-slider');
        if (saleSlider) {
            new Swiper('#sale-slider', {
                slidesPerView: 2,
                spaceBetween: 16,
                loop: true,
                loopAdditionalSlides: 2,
                autoplay: {
                    delay: 4000,
                    disableOnInteraction: false,
                    pauseOnMouseEnter: true,
                },
                pagination: {
                    el: '.sale-slider-pagination',
                    clickable: true,
                },
                navigation: {
                    nextEl: '.sale-slider-next',
                    prevEl: '.sale-slider-prev',
                },
                breakpoints: createBreakpoints(productColumns),
            });
        }
    });
</script>
@endpush
