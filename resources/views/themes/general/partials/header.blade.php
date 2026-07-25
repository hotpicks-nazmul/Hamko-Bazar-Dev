@php
// Home Page Settings - Batched query (1 query instead of 7)
$homepageSettings = \App\Models\Setting::where('group', 'homepage')->pluck('value', 'key')->toArray();

$topBarPhone = $homepageSettings['top_bar_phone'] ?? '+880 1700-000000';
$topBarEmail = $homepageSettings['top_bar_email'] ?? 'info@halalfoodstore.com';
$topBarDelivery = $homepageSettings['top_bar_delivery_message'] ?? 'Free Delivery on orders over ৳500';

$siteName = $homepageSettings['site_name'] ?? 'Halal Food';
$siteTagline = $homepageSettings['site_tagline'] ?? 'Premium Quality Store';
$siteLogoIcon = $homepageSettings['site_logo_icon'] ?? 'bi bi-shop';
$siteLogoImage = $homepageSettings['site_logo'] ?? '';

// Menu Styling Settings - Batched query (1 query instead of 6)
$menuSettings = \App\Models\Setting::whereIn('key', ['menu_hover_color', 'menu_text_hover_color', 'menu_active_color', 'menu_active_text_color', 'menu_font_size', 'menu_font_weight'])->pluck('value', 'key')->toArray();

$menuHoverColor = $menuSettings['menu_hover_color'] ?? '#ffffff';
$menuTextHoverColor = $menuSettings['menu_text_hover_color'] ?? '#4f46e5';
$menuActiveColor = $menuSettings['menu_active_color'] ?? '#ffffff';
$menuActiveTextColor = $menuSettings['menu_active_text_color'] ?? '#4f46e5';
$menuFontSize = $menuSettings['menu_font_size'] ?? '14';
$menuFontWeight = $menuSettings['menu_font_weight'] ?? '400';
@endphp

<!-- Top Bar -->
<div class="bg-halal-dark text-white py-1">
    <div class="container mx-auto px-4">
        <div class="flex flex-wrap justify-between items-center text-xs">
            <div class="flex items-center space-x-4">
                <span class="flex items-center">
                    <i class="bi bi-telephone-fill mr-2 text-halal-gold"></i>
                    {{ $topBarPhone }}
                </span>
                <span class="hidden md:flex items-center">
                    <i class="bi bi-envelope-fill mr-2 text-halal-gold"></i>
                    {{ $topBarEmail }}
                </span>
            </div>
            <div class="flex items-center space-x-4">
                <span class="flex items-center text-halal-gold">
                    <i class="bi bi-truck mr-1"></i>
                    {{ $topBarDelivery }}
                </span>
                
                <!-- Language Switcher -->
                @include('themes.general.partials.language-switcher')
                
                <!-- Currency Switcher -->
                @include('themes.general.partials.currency-switcher')
            </div>
        </div>
    </div>
</div>

<style>
    .scrollbar-hide {
        -ms-overflow-style: none;
        scrollbar-width: none;
    }
    .scrollbar-hide::-webkit-scrollbar {
        display: none;
    }
    .nav-menu-link {
        font-size: {{ $menuFontSize }}px !important;
        font-weight: {{ $menuFontWeight }} !important;
    }
    .nav-menu-link:hover {
        background-color: {{ $menuHoverColor }} !important;
        color: {{ $menuTextHoverColor }} !important;
    }
    .nav-menu-link.active {
        background-color: {{ $menuActiveColor }} !important;
        color: {{ $menuActiveTextColor }} !important;
    }
    /* Clip mega menu overflow without breaking hover dropdown */
    .category-mega-wrapper {
        overflow: hidden;
    }
    .category-mega-wrapper:hover {
        overflow: visible;
    }
</style>

<!-- Main Header -->
<header class="bg-white shadow-md sticky top-0 z-40">
    <div class="container mx-auto px-4">
        <div class="flex items-center justify-between py-2">
            <!-- Logo -->
            <a href="{{ route('home') }}" class="flex items-center space-x-2">
                @php
                    $logoPath = trim($siteLogoImage ?? '');
                    $hasLogo = !empty($logoPath) && $logoPath !== '';
                @endphp
                @if($hasLogo)
                    <img src="{{ $logoPath }}" alt="{{ $siteName }}" class="h-8 w-auto object-contain">
                @else
                    <div class="w-8 h-8 gradient-halal rounded-full flex items-center justify-center">
                        <i class="{{ $siteLogoIcon }} text-white text-sm"></i>
                    </div>
                @endif
                <div>
                    <h1 class="font-poppins text-base md:text-lg font-bold text-halal-green truncate max-w-[120px] md:max-w-none">{{ $siteName }}</h1>
                    <p class="hidden md:block text-[10px] text-gray-500 -mt-1">{{ $siteTagline }}</p>
                </div>
            </a>
            
            <!-- Search Bar -->
            <div class="hidden md:flex flex-1 max-w-xl mx-8">
                <form action="{{ route('products.index') }}" method="GET" class="w-full">
                    <div class="relative" id="searchContainer">
                        <input type="text" name="search" id="searchInput" placeholder="Search for fresh halal meat, groceries..." 
                            class="w-full pl-4 pr-14 py-2 border border-gray-200 rounded-full focus:border-halal-green focus:outline-none transition-colors text-sm"
                            autocomplete="off">
                        <button type="submit" class="absolute right-1 top-1/2 -translate-y-1/2 bg-halal-green text-white w-8 h-8 rounded-full hover:text-amber-400 transition-colors flex items-center justify-center">
                            <i class="bi bi-search text-sm"></i>
                        </button>
                        
                        <!-- Live Search Results Dropdown -->
                        <div id="searchResults" class="absolute top-full left-0 right-0 mt-2 bg-white rounded-xl shadow-2xl border border-gray-100 max-h-96 overflow-y-auto z-50 hidden">
                            <!-- Results will be populated by JavaScript -->
                        </div>
                    </div>
                </form>
            </div>
            
            <!-- Right Actions -->
            <div class="flex items-center space-x-4">
                <!-- Account -->
                @auth
                <div class="relative group">
                    <button class="flex items-center space-x-1 text-gray-700 hover:text-halal-green transition-colors">
                        <i class="bi bi-person-circle text-2xl"></i>
                        <span class="hidden lg:block">{{ Auth::user()->name }}</span>
                        <i class="bi bi-chevron-down text-xs"></i>
                    </button>
                    <div class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg py-2 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200">
                        <a href="{{ route('account.dashboard') }}" class="flex items-center px-4 py-2 text-gray-700 hover:bg-green-50 hover:text-halal-green">
                            <i class="bi bi-grid mr-2"></i>Dashboard
                        </a>
                        <a href="{{ route('account.profile') }}" class="flex items-center px-4 py-2 text-gray-700 hover:bg-green-50 hover:text-halal-green">
                            <i class="bi bi-person mr-2"></i>My Profile
                        </a>
                        <a href="{{ route('account.orders') }}" class="flex items-center px-4 py-2 text-gray-700 hover:bg-green-50 hover:text-halal-green">
                            <i class="bi bi-bag mr-2"></i>My Orders
                        </a>
                        <a href="{{ route('account.wishlist') }}" class="flex items-center px-4 py-2 text-gray-700 hover:bg-green-50 hover:text-halal-green">
                            <i class="bi bi-heart mr-2"></i>Wishlist
                        </a>
                        <a href="{{ route('account.addresses') }}" class="flex items-center px-4 py-2 text-gray-700 hover:bg-green-50 hover:text-halal-green">
                            <i class="bi bi-geo-alt mr-2"></i>Addresses
                        </a>
                        <hr class="my-2">
                        <form action="{{ route('logout') }}" method="POST">
                            @csrf
                            <button type="submit" class="w-full text-left flex items-center px-4 py-2 text-red-600 hover:bg-red-50">
                                <i class="bi bi-box-arrow-right mr-2"></i>Logout
                            </button>
                        </form>
                    </div>
                </div>
                @else
                <div class="hidden lg:flex items-center space-x-3">
                    <a href="{{ route('login') }}" class="flex items-center space-x-1 text-gray-700 hover:text-halal-green transition-colors">
                        <i class="bi bi-person-circle text-2xl"></i>
                        <span class="hidden lg:block">Login</span>
                    </a>
                    <a href="{{ route('register') }}" class="bg-halal-green text-white px-4 py-2 rounded-full hover:text-amber-400 transition-colors text-sm font-medium">
                        Register
                    </a>
                </div>
                @endauth
                
                <!-- Wishlist -->
                @php $wishlistCount = auth()->check() ? \Cache::remember('wishlist_count_' . auth()->id(), 300, function() { return \App\Models\Wishlist::where('user_id', auth()->id())->count(); }) : 0; @endphp
                <button onclick="openWishlistSidebar()" class="relative text-gray-700 hover:text-halal-green transition-colors">
                    <i class="bi bi-heart text-2xl"></i>
                    <span class="absolute -top-1 -right-1 w-5 h-5 bg-halal-gold text-white text-xs rounded-full flex items-center justify-center wishlist-count {{ $wishlistCount == 0 ? 'hidden' : '' }}">{{ $wishlistCount }}</span>
                </button>
                
                <!-- Cart -->
                <button onclick="openCartSidebar()" class="relative flex items-center space-x-2 bg-halal-green text-white px-3 py-2 rounded-full hover:text-amber-400 transition-colors">
                    <i class="bi bi-cart3 text-xl"></i>
                    <span class="hidden lg:block font-medium">Cart</span>
                    <span class="cart-count bg-halal-gold text-white text-xs w-5 h-5 rounded-full flex items-center justify-center">0</span>
                </button>
            </div>
            
            <!-- Mobile Menu Toggle -->
            <button onclick="toggleMobileMenu()" class="lg:hidden text-gray-700 ml-2 flex-shrink-0">
                <i class="bi bi-list text-2xl"></i>
            </button>
            </div>
        </div>
    </div>
    
</header>

<!-- Mobile Actions Bar -->
<div class="flex md:hidden items-center justify-around bg-white border-b border-gray-200 py-2 px-4 overflow-x-auto scrollbar-hide">
    @auth
    <a href="{{ route('account.dashboard') }}" class="flex flex-col items-center text-gray-600 hover:text-halal-green text-xs">
        <i class="bi bi-person-circle text-xl"></i>
        <span>Account</span>
    </a>
    @else
    <a href="{{ route('login') }}" class="flex flex-col items-center text-gray-600 hover:text-halal-green text-xs">
        <i class="bi bi-person-circle text-xl"></i>
        <span>Login</span>
    </a>
    @endauth
    
    <a href="{{ route('register') }}" class="flex flex-col items-center text-gray-600 hover:text-halal-green text-xs">
        <i class="bi bi-person-plus text-xl"></i>
        <span>Register</span>
    </a>
    
    @php $wishlistCount = auth()->check() ? \Cache::remember('wishlist_count_' . auth()->id(), 300, function() { return \App\Models\Wishlist::where('user_id', auth()->id())->count(); }) : 0; @endphp
    <button onclick="openWishlistSidebar()" class="flex flex-col items-center text-gray-600 hover:text-halal-green text-xs relative">
        <i class="bi bi-heart text-xl"></i>
        <span>Wishlist</span>
        <span class="absolute -top-1 right-1 w-4 h-4 bg-halal-gold text-white text-[9px] rounded-full flex items-center justify-center wishlist-count {{ $wishlistCount == 0 ? 'hidden' : '' }}">{{ $wishlistCount }}</span>
    </button>
    
    <button onclick="openCartSidebar()" class="flex flex-col items-center text-gray-600 hover:text-halal-green text-xs relative">
        <i class="bi bi-cart3 text-xl"></i>
        <span>Cart</span>
        <span class="absolute -top-1 right-1 w-4 h-4 bg-halal-gold text-white text-[9px] rounded-full flex items-center justify-center cart-count">0</span>
    </button>
</div>
    <nav class="hidden lg:block bg-halal-green text-white">
        <div class="container mx-auto px-4">
            <div class="flex items-center justify-between">
                <!-- Categories Mega Menu -->
                <div class="relative group flex-shrink-0 category-mega-wrapper">
                    <button class="flex items-center gap-2.5 bg-halal-dark hover:bg-halal-light transition-all duration-200 px-5 py-2.5 whitespace-nowrap rounded-b-none border-b-2 border-transparent group-hover:border-amber-400 relative">
                        <svg class="w-[18px] h-[18px] text-amber-400 flex-shrink-0" fill="currentColor" viewBox="0 0 16 16">
                            <path d="M1 2.5A1.5 1.5 0 0 1 2.5 1h3A1.5 1.5 0 0 1 7 2.5v3A1.5 1.5 0 0 1 5.5 7h-3A1.5 1.5 0 0 1 1 5.5v-3zm8 0A1.5 1.5 0 0 1 10.5 1h3A1.5 1.5 0 0 1 15 2.5v3A1.5 1.5 0 0 1 13.5 7h-3A1.5 1.5 0 0 1 9 5.5v-3zm-8 8A1.5 1.5 0 0 1 2.5 9h3A1.5 1.5 0 0 1 7 10.5v3A1.5 1.5 0 0 1 5.5 15h-3A1.5 1.5 0 0 1 1 13.5v-3zm8 0A1.5 1.5 0 0 1 10.5 9h3a1.5 1.5 0 0 1 1.5 1.5v3a1.5 1.5 0 0 1-1.5 1.5h-3A1.5 1.5 0 0 1 9 13.5v-3z"/>
                        </svg>
                        <div class="flex flex-col items-start leading-tight">
                            <span class="text-[10px] text-white/60 uppercase tracking-wider font-medium">Shop by</span>
                            <span class="font-semibold text-sm tracking-wide -mt-0.5">Category</span>
                        </div>
                        <svg class="w-3.5 h-3.5 text-white/60 group-hover:text-white transition-all duration-300 ml-1 group-hover:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </button>
                    
                    <!-- Mega Menu Dropdown -->
                    <div class="absolute left-0 top-full mt-0 w-[880px] bg-white rounded-xl shadow-2xl shadow-black/15 opacity-0 invisible group-hover:opacity-100 group-hover:visible group-hover:translate-y-0 translate-y-2 z-[60] border border-gray-200/90 overflow-hidden transition-all duration-200">
                        <style>
                            .mega-sidebar-item {
                                position: relative;
                                transition: all 0.15s ease;
                            }
                            .mega-sidebar-item::before {
                                content: '';
                                position: absolute;
                                left: 0;
                                top: 50%;
                                transform: translateY(-50%) scaleY(0);
                                width: 3px;
                                height: 24px;
                                background: #2D5A27;
                                border-radius: 0 3px 3px 0;
                                transition: transform 0.15s ease;
                            }
                            .mega-sidebar-item.is-active::before,
                            .mega-sidebar-item:hover::before {
                                transform: translateY(-50%) scaleY(1);
                            }
                            .mega-sidebar-item.is-active {
                                background: white;
                                box-shadow: 0 1px 3px rgba(0,0,0,0.06);
                                border-left-color: rgba(45,90,39,0.8);
                                color: #2D5A27;
                            }
                            .right-panel-content {
                                animation: panelFade 0.15s ease-out;
                            }
                            @keyframes panelFade {
                                from { opacity: 0; transform: translateY(3px); }
                                to   { opacity: 1; transform: translateY(0); }
                            }
                        </style>
                        <div class="flex min-h-[420px]">
                            <!-- Left: Main Categories -->
                            <div class="w-64 bg-gray-50/90 py-3 border-r border-gray-200/60 flex-shrink-0" id="mega-sidebar">
                                @php
                                    $mainCategories = $categories;
                                    if(isset($mainCategories) && $mainCategories->count() > 0) {
                                        $firstParent = $mainCategories->first();
                                        if($firstParent && $firstParent->parent_id !== null) {
                                            $mainCategories = \App\Models\Category::where('status', 'active')
                                                ->whereNull('parent_id')
                                                ->with('children.children')
                                                ->get();
                                        }
                                    }
                                    $catIcons = [
                                        'houseware' => 'basket2',
                                        'furniture' => 'lamp',
                                        'cookware' => 'egg-fried',
                                    ];
                                    $catShort = [
                                        'houseware' => 'hw',
                                        'furniture' => 'fur',
                                        'cookware' => 'cw',
                                    ];
                                @endphp
                                @foreach($mainCategories as $cat)
                                @php
                                    $slug = strtolower($cat->slug);
                                    $short = $catShort[$slug] ?? 'cat' . $loop->index;
                                @endphp
                                <div class="px-2">
                                    <a href="{{ route('products.category', $cat->slug) }}" 
                                       data-panel="{{ $short }}"
                                       class="mega-sidebar-item sidebar-trigger flex items-center gap-3 px-4 py-2.5 rounded-xl text-sm font-medium text-gray-700 hover:text-halal-green hover:bg-white hover:shadow-[0_1px_3px_rgba(0,0,0,0.06)] transition-all duration-150 border-l-[3px] border-transparent hover:border-halal-green/80">
                                        <span class="w-9 h-9 rounded-xl bg-gradient-to-br from-halal-green/[0.12] to-halal-green/[0.06] flex items-center justify-center flex-shrink-0 sidebar-icon transition-all duration-150">
                                            <i class="bi bi-{{ $catIcons[$slug] ?? 'folder' }} text-halal-green text-base"></i>
                                        </span>
                                        <span class="flex-1 truncate">{{ $cat->name }}</span>
                                        <span class="flex items-center gap-1.5 flex-shrink-0">
                                            <span class="text-[11px] font-medium text-gray-400 bg-white/80 px-2 py-0.5 rounded-full border border-gray-100/60 sidebar-pill transition-all duration-150">{{ $cat->total_products_count }}</span>
                                        </span>
                                    </a>
                                </div>
                                @endforeach
                                
                                <!-- All Categories Footer Link -->
                                <div class="mt-2 mx-2 pt-2 border-t border-gray-200/60">
                                    <a href="{{ route('categories.index') }}" class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-sm text-gray-500 hover:text-halal-green transition-all duration-150 hover:bg-white group">
                                        <span class="w-9 h-9 rounded-xl bg-gray-100/80 flex items-center justify-center flex-shrink-0 group-hover:bg-halal-green/[0.1] transition-colors">
                                            <i class="bi bi-grid-3x3-gap text-gray-400 group-hover:text-halal-green text-base"></i>
                                        </span>
                                        <span>Browse All Categories</span>
                                        <svg class="w-3.5 h-3.5 ml-auto text-gray-300 group-hover:text-halal-green transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
                                        </svg>
                                    </a>
                                </div>
                            </div>
                            
                            <!-- Right: Category Content Panel (changes on hover) -->
                            <div class="flex-1 bg-gradient-to-br from-gray-50/20 to-white overflow-y-auto max-h-[520px]">
                                @php $firstShort = ''; @endphp
                                @foreach($mainCategories as $cat)
                                @php
                                    $slug = strtolower($cat->slug);
                                    $short = $catShort[$slug] ?? 'cat' . $loop->index;
                                    if ($loop->first) $firstShort = $short;
                                    
                                    $children = $cat->children->where('status', 'active')->filter(fn($c) => $c->total_products_count > 0);
                                    $count = $children->count();
                                    $cols = $count > 12 ? 3 : ($count > 6 ? 2 : 1);
                                    $colSize = (int)ceil($count / max($cols, 1));
                                    $chunks = $children->chunk($colSize ?: 1);
                                @endphp
                                
                                <div id="panel-{{ $short }}" class="right-panel-content {{ $loop->first ? 'block' : 'hidden' }}">
                                    <div class="p-6 pb-4">
                                        <!-- Header -->
                                        <div class="flex items-center justify-between mb-4 pb-3 border-b border-gray-100">
                                            <div class="flex items-center gap-2.5">
                                                <span class="w-8 h-8 rounded-lg bg-gradient-to-br from-halal-green/[0.12] to-halal-green/[0.06] flex items-center justify-center">
                                                    <i class="bi bi-{{ $catIcons[$slug] ?? 'folder' }} text-halal-green text-sm"></i>
                                                </span>
                                                <div>
                                                    <h3 class="text-sm font-semibold text-gray-800">{{ $cat->name }}</h3>
                                                    <p class="text-[11px] text-gray-400">{{ $cat->total_products_count }} products</p>
                                                </div>
                                            </div>
                                            <a href="{{ route('products.category', $cat->slug) }}" class="inline-flex items-center gap-1.5 text-xs font-medium text-halal-green hover:text-halal-light transition-colors px-3 py-1.5 rounded-lg hover:bg-halal-green/[0.06]">
                                                View All
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                                </svg>
                                            </a>
                                        </div>
                                        
                                        <!-- Subcategories Grid -->
                                        @if($count > 0)
                                        @php
                                            // Separate subcats with/without 3rd-level children
                                            $hasChildren = $children->filter(fn($c) => $c->children->where('status', 'active')->count() > 0);
                                            $flatChildren = $children->filter(fn($c) => $c->children->where('status', 'active')->count() === 0);
                                            $totalCols = $count > 12 ? 3 : ($count > 6 ? 2 : 1);
                                        @endphp
                                        
                                        @if($hasChildren->count() > 0)
                                        <div class="grid @if($hasChildren->count() >= 3) grid-cols-3 @elseif($hasChildren->count() == 2) grid-cols-2 @else grid-cols-1 @endif gap-x-5 gap-y-4 mb-4">
                                            @foreach($hasChildren as $subCat)
                                            <div>
                                                <a href="{{ route('products.category', $subCat->slug) }}" 
                                                   class="block text-xs font-semibold text-halal-green uppercase tracking-wider mb-1.5 hover:text-halal-light transition-colors">
                                                    {{ $subCat->name }}
                                                    <span class="text-[10px] text-gray-400 font-normal normal-case ml-1">({{ $subCat->total_products_count }})</span>
                                                </a>
                                                <div class="space-y-0.5">
                                                    @foreach($subCat->children->where('status', 'active')->filter(fn($gc) => $gc->total_products_count > 0) as $grandchild)
                                                    <a href="{{ route('products.category', $grandchild->slug) }}" 
                                                       class="group/sub flex items-center justify-between px-2.5 py-1.5 rounded-lg text-sm text-gray-600 hover:text-halal-green transition-all duration-150 hover:bg-green-50/60">
                                                        <span class="flex items-center gap-2">
                                                            <span class="w-1 h-1 rounded-full bg-gray-300 group-hover/sub:bg-halal-green transition-all duration-150"></span>
                                                            <span>{{ $grandchild->name }}</span>
                                                        </span>
                                                        <span class="text-[11px] text-gray-400 group-hover/sub:text-halal-green/60">{{ $grandchild->total_products_count }}</span>
                                                    </a>
                                                    @endforeach
                                                </div>
                                            </div>
                                            @endforeach
                                        </div>
                                        @endif
                                        
                                        @if($flatChildren->count() > 0)
                                        @php
                                            $flatCols = $flatChildren->count() > 12 ? 3 : ($flatChildren->count() > 6 ? 2 : 1);
                                            $chunkSize = (int)ceil($flatChildren->count() / max($flatCols, 1));
                                            $flatChunks = $flatChildren->chunk($chunkSize ?: 1);
                                        @endphp
                                        <div class="grid @if($flatCols > 2) grid-cols-3 @elseif($flatCols > 1) grid-cols-2 @else grid-cols-1 @endif gap-x-4 gap-y-0.5">
                                            @foreach($flatChunks as $chunk)
                                            <div class="space-y-0.5">
                                                @foreach($chunk as $child)
                                                <a href="{{ route('products.category', $child->slug) }}" 
                                                   class="group/sub flex items-center justify-between px-3 py-2 rounded-lg text-sm text-gray-600 hover:text-halal-green transition-all duration-150 hover:bg-gradient-to-r hover:from-halal-green/[0.06] hover:to-transparent">
                                                    <span class="flex items-center gap-2.5">
                                                        <span class="w-1 h-1 rounded-full bg-gray-300 group-hover/sub:bg-halal-green transition-all duration-150 group-hover/sub:w-2 group-hover/sub:h-2"></span>
                                                        <span>{{ $child->name }}</span>
                                                    </span>
                                                    <span class="text-[11px] text-gray-400 group-hover/sub:text-halal-green/60 transition-colors duration-150">{{ $child->total_products_count }}</span>
                                                </a>
                                                @endforeach
                                            </div>
                                            @endforeach
                                        </div>
                                        @endif
                                        @endif
                                    </div>
                                    
                                    <!-- Bottom CTA -->
                                    <div class="px-6 py-3 bg-gradient-to-r from-halal-green/[0.04] to-transparent border-t border-gray-100/80">
                                        <a href="{{ route('products.category', $cat->slug) }}" class="inline-flex items-center gap-2 text-xs font-medium text-halal-green/80 hover:text-halal-green transition-colors group/cta">
                                            <span>Browse all {{ $cat->name }}</span>
                                            <span class="text-halal-green font-semibold">{{ $cat->total_products_count }}</span>
                                            <span>products</span>
                                            <svg class="w-3.5 h-3.5 group-hover/cta:translate-x-0.5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
                                            </svg>
                                        </a>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    
                    <script>
                    (function() {
                        var sidebar = document.getElementById('mega-sidebar');
                        if (!sidebar) return;
                        var triggers = sidebar.querySelectorAll('.sidebar-trigger');
                        var allPanels = document.querySelectorAll('.right-panel-content');
                        
                        triggers.forEach(function(trigger) {
                            trigger.addEventListener('mouseenter', function() {
                                var target = this.getAttribute('data-panel');
                                
                                // Update sidebar active states
                                triggers.forEach(function(t) { t.classList.remove('is-active'); });
                                this.classList.add('is-active');
                                
                                // Switch right panel
                                allPanels.forEach(function(p) {
                                    p.classList.remove('block');
                                    p.classList.add('hidden');
                                });
                                var activePanel = document.getElementById('panel-' + target);
                                if (activePanel) {
                                    activePanel.classList.remove('hidden');
                                    activePanel.classList.add('block');
                                }
                            });
                        });
                        
                        // Activate first by default
                        if (triggers.length > 0) triggers[0].classList.add('is-active');
                    })();
                    </script>
                </div>
                
                <!-- Nav Links -->
                <div class="flex lg:flex items-center space-x-1 lg:ml-6 overflow-x-auto scrollbar-hide whitespace-nowrap">
                    <a href="{{ route('home') }}" class="px-3 py-2 hover:bg-halal-dark/70 rounded-lg transition-colors nav-menu-link text-sm {{ request()->routeIs('home') ? 'bg-halal-dark active' : '' }}">Home</a>
                    <a href="{{ route('products.index') }}" class="px-3 py-2 hover:bg-halal-dark/70 rounded-lg transition-colors nav-menu-link text-sm {{ request()->fullUrlIs(route('products.index')) || (request()->routeIs('products.*') && !request()->except(['page', 'category', 'search', 'brand', 'min_price', 'max_price', 'featured', 'on_sale', 'in_stock', 'rating']) ) ? 'bg-halal-dark active' : '' }}">Shop</a>
                    <a href="{{ route('products.index', ['sort' => 'newest']) }}" class="px-3 py-2 hover:bg-halal-dark/70 rounded-lg transition-colors nav-menu-link text-sm {{ request()->fullUrlIs(route('products.index', ['sort' => 'newest']).'*') ? 'bg-halal-dark active' : '' }}">New Arrivals</a>
                    <a href="{{ route('products.index', ['sort' => 'discount']) }}" class="px-3 py-2 hover:bg-halal-dark/70 rounded-lg transition-colors nav-menu-link text-sm {{ request()->fullUrlIs(route('products.index', ['sort' => 'discount']).'*') ? 'bg-halal-dark active' : '' }}">Deals</a>
                    <a href="{{ route('blogs.index') }}" class="px-3 py-2 hover:bg-halal-dark/70 rounded-lg transition-colors nav-menu-link text-sm {{ request()->routeIs('blogs.*') ? 'bg-halal-dark active' : '' }}">Blog</a>
                    <a href="{{ route('pages.contact') }}" class="px-3 py-2 hover:bg-halal-dark/70 rounded-lg transition-colors nav-menu-link text-sm">Contact</a>
                </div>
                
                <!-- Special Offer -->
                <div class="ml-auto flex items-center flex-shrink-0">
                    <span class="flex items-center text-amber-300 text-sm">
                        <i class="bi bi-fire text-lg mr-2"></i>
                        <span class="font-medium">Today's Deal: Up to 30% Off!</span>
                    </span>
                </div>
            </div>
        </div>
    </nav>



<!-- Live Search Script -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    let searchTimeout;
    const searchInput = document.getElementById('searchInput');
    const searchResults = document.getElementById('searchResults');

    // Header search input handler
    if (searchInput) {
        searchInput.addEventListener('input', function(e) {
            clearTimeout(searchTimeout);
            const query = e.target.value.trim();
            if (query.length < 2) {
                searchResults.classList.add('hidden');
                searchResults.innerHTML = '';
                return;
            }
            searchResults.classList.remove('hidden');
            searchResults.innerHTML = '<div class="p-4 text-center text-gray-500"><i class="bi bi-arrow-repeat animate-spin text-2xl"></i><p class="mt-2">Searching...</p></div>';
            searchTimeout = setTimeout(() => {
                fetch('{{ route('search.suggestions') }}?q=' + encodeURIComponent(query))
                    .then(response => response.json())
                    .then(data => {
                        renderSearchResults(data, query, searchResults);
                    })
                    .catch(error => {
                        console.error('Search error:', error);
                        searchResults.innerHTML = '<div class="p-4 text-center text-red-500"><i class="bi bi-exclamation-circle text-2xl"></i><p class="mt-2">Error loading results</p></div>';
                    });
            }, 300);
        });
        document.addEventListener('click', function(e) {
            const searchContainer = document.getElementById('searchContainer');
            if (searchContainer && !searchContainer.contains(e.target)) {
                searchResults.classList.add('hidden');
            }
        });
        searchInput.addEventListener('focus', function(e) {
            if (e.target.value.trim().length >= 2) {
                searchResults.classList.remove('hidden');
            }
        });
    }

    function renderSearchResults(data, query, resultsContainer) {
        const { products, categories } = data;
        if (products.length === 0 && categories.length === 0) {
            resultsContainer.innerHTML = '<div class="p-6 text-center"><i class="bi bi-search text-4xl text-gray-300"></i><p class="mt-2 text-gray-500">No results found for "' + query + '"</p><p class="text-sm text-gray-400 mt-1">Try different keywords</p></div>';
            return;
        }
        let html = '';
        if (categories.length > 0) {
            html += '<div class="p-3 bg-gray-50 border-b border-gray-100"><span class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Categories</span></div>';
            categories.forEach(category => {
                const imageUrl = category.image || 'https://placehold.co/50x50?text=C';
                html += '<a href="{{ route('products.index') }}?category=' + category.slug + '" class="flex items-center p-3 hover:bg-green-50 transition-colors border-b border-gray-50"><img src="' + escapeHtml(imageUrl) + '" alt="' + escapeHtml(category.name) + '" class="w-10 h-10 rounded-lg object-cover bg-gray-100"><div class="ml-3"><p class="font-medium text-gray-800">' + highlightMatch(category.name, query) + '</p><p class="text-xs text-gray-500">Browse category</p></div><i class="bi bi-chevron-right ml-auto text-gray-400"></i></a>';
            });
        }
        if (products.length > 0) {
            html += '<div class="p-3 bg-gray-50 border-b border-gray-100"><span class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Products</span></div>';
            products.forEach(product => {
                const price = product.sale_price || product.price;
                const originalPrice = product.sale_price ? product.price : null;
                let imageUrl = product.featured_image || 'https://placehold.co/60x60?text=P';
                if (imageUrl && !imageUrl.startsWith('http') && !imageUrl.startsWith('/storage/') && !imageUrl.startsWith('/uploads/')) {
                    imageUrl = '/storage/' + imageUrl;
                }
                html += '<a href="{{ route('products.show', '') }}/' + product.slug + '" class="flex items-center p-3 hover:bg-green-50 transition-colors border-b border-gray-50 last:border-b-0"><img src="' + escapeHtml(imageUrl) + '" alt="' + escapeHtml(product.name) + '" class="w-14 h-14 rounded-lg object-cover bg-gray-100"><div class="ml-3 flex-1"><p class="font-medium text-gray-800 line-clamp-1">' + highlightMatch(product.name, query) + '</p><div class="flex items-center mt-1"><span class="text-halal-green font-semibold">৳' + Number(price).toLocaleString() + '</span>' + (originalPrice ? '<span class="text-gray-400 text-sm line-through ml-2">৳' + Number(originalPrice).toLocaleString() + '</span>' : '') + '</div>' + (product.category ? '<span class="text-xs text-gray-500">' + escapeHtml(product.category.name) + '</span>' : '') + '</div><i class="bi bi-arrow-right ml-2 text-gray-400"></i></a>';
            });
        }
        html += '<a href="{{ route('products.index') }}?search=' + encodeURIComponent(query) + '" class="block p-3 text-center bg-halal-green text-white hover:bg-halal-dark transition-colors rounded-b-xl"><i class="bi bi-search mr-2"></i>View all results for "' + query + '"</a>';
        resultsContainer.innerHTML = html;
    }

    function escapeHtml(unsafe) {
        return String(unsafe).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#039;');
    }

    function highlightMatch(text, query) {
        if (!query) return escapeHtml(text);
        const escaped = escapeHtml(text);
        const regex = new RegExp('(' + query.replace(/[.*+?^${}()|[\]\\]/g, '\\$&') + ')', 'gi');
        return escaped.replace(regex, '<mark class="bg-yellow-200 px-0.5 rounded">$1</mark>');
    }
});
</script>
