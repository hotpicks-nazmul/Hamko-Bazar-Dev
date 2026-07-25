@php
// Get the menu by location
$menu = App\Models\Menu::where('location', $location ?? 'header')
    ->where('is_active', true)
    ->with(['items' => function($query) {
        $query->whereNull('parent_id')
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->with('children');
    }])
    ->first();

// Get theme settings for menu styling
$menuHoverColor = App\Models\Setting::where('key', 'menu_hover_color')->first()?->value ?? '#ffffff';
$menuTextHoverColor = App\Models\Setting::where('key', 'menu_text_hover_color')->first()?->value ?? '#4f46e5';
$menuActiveColor = App\Models\Setting::where('key', 'menu_active_color')->first()?->value ?? '#ffffff';
$menuActiveTextColor = App\Models\Setting::where('key', 'menu_active_text_color')->first()?->value ?? '#4f46e5';
$menuFontSize = App\Models\Setting::where('key', 'menu_font_size')->first()?->value ?? '14';
$menuFontWeight = App\Models\Setting::where('key', 'menu_font_weight')->first()?->value ?? '400';
@endphp

@if($menu && $menu->items->count() > 0)
<style>
    .nav-menu-items {
        list-style: none;
        margin: 0;
        padding: 0;
        display: flex;
        align-items: center;
    }
    
    .nav-menu-items .nav-item {
        position: relative;
    }
    
    .nav-menu-items .nav-item > a {
        display: block;
        padding: 8px 16px;
        color: inherit;
        text-decoration: none;
        font-size: {{ $menuFontSize }}px;
        font-weight: {{ $menuFontWeight }};
        transition: all 0.2s ease;
    }
    
    .nav-menu-items .nav-item > a:hover {
        background-color: {{ $menuHoverColor }};
        color: {{ $menuTextHoverColor }};
    }
    
    .nav-menu-items .nav-item.active > a {
        background-color: {{ $menuActiveColor }};
        color: {{ $menuActiveTextColor }};
    }
    
    .nav-menu-items .submenu {
        position: absolute;
        top: 100%;
        left: 0;
        min-width: 200px;
        background: white;
        border-radius: 4px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        opacity: 0;
        visibility: hidden;
        transform: translateY(10px);
        transition: all 0.2s ease;
        list-style: none;
        margin: 0;
        padding: 8px 0;
        z-index: 1000;
    }
    
    .nav-menu-items .nav-item:hover > .submenu {
        opacity: 1;
        visibility: visible;
        transform: translateY(0);
    }
    
    .nav-menu-items .submenu li a {
        display: block;
        padding: 10px 20px;
        color: #333;
        text-decoration: none;
        font-size: {{ $menuFontSize }}px;
        transition: all 0.2s ease;
    }
    
    .nav-menu-items .submenu li a:hover {
        background-color: {{ $menuHoverColor }};
        color: {{ $menuTextHoverColor }};
    }
</style>

<ul class="nav-menu-items">
    @foreach($menu->items as $item)
    <li class="nav-item {{ $item->children->count() > 0 ? 'has-submenu' : '' }}">
        <a href="{{ $item->full_url }}" 
           target="{{ $item->target }}" 
           class="{{ $item->css_class ?? '' }}"
           title="{{ $item->title }}">
            @if($item->icon)
            <i class="{{ $item->icon }} me-1"></i>
            @endif
            {{ $item->title }}
        </a>
        
        @if($item->children->count() > 0)
        <ul class="submenu">
            @foreach($item->children as $child)
            <li>
                <a href="{{ $child->full_url }}" 
                   target="{{ $child->target }}"
                   class="{{ $child->css_class ?? '' }}"
                   title="{{ $child->title }}">
                    @if($child->icon)
                    <i class="{{ $child->icon }} me-1"></i>
                    @endif
                    {{ $child->title }}
                </a>
            </li>
            @endforeach
        </ul>
        @endif
    </li>
    @endforeach
</ul>
@endif
