<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Category;
use App\Models\Banner;
use App\Models\Blog;
use App\Models\Setting;
use App\Services\ThemeService;

class HomeController extends Controller
{
    protected ThemeService $themeService;

    public function __construct(ThemeService $themeService)
    {
        $this->themeService = $themeService;
    }

    /**
     * Display the home page.
     */
    public function index()
    {
        $theme = $this->themeService->getActiveTheme();
        
        // Get product count settings from database
        $featuredCount = (int) Setting::where('key', 'homepage_featured_products_count')->value('value') ?: 8;
        $newArrivalsCount = (int) Setting::where('key', 'homepage_new_arrivals_count')->value('value') ?: 8;
        $saleCount = (int) Setting::where('key', 'homepage_sale_products_count')->value('value') ?: 8;
        
        // Get featured products
        $featuredProducts = Product::where('is_featured', true)
            ->where('is_active', true)
            ->with('category')
            ->where('quantity', '>', 0)
            ->take($featuredCount)
            ->get();
        
        // Get latest products
        $latestProducts = Product::where('is_active', true)
            ->with('category')
            ->where('quantity', '>', 0)
            ->latest()
            ->take($newArrivalsCount)
            ->get();
        
        // Get categories
        $categories = Category::where('status', 'active')
            ->whereNull('parent_id')
            ->with('children.children')
            ->take(6)
            ->get();
        
        // Get sale products
        $saleProducts = Product::where('is_active', true)
            ->whereNotNull('sale_price')
            ->where('sale_price', '>', 0)
            ->with('category')
            ->where('quantity', '>', 0)
            ->take($saleCount)
            ->get();

        // Get latest blog posts
        $latestBlogs = Blog::where('status', 'published')
            ->where('published_at', '<=', now())
            ->latest('published_at')
            ->with('author')
            ->take(3)
            ->get();

        // Get section order from settings
        $sectionOrderSetting = Setting::where('key', 'homepage_section_order')->first();
        $sectionOrder = $sectionOrderSetting ? json_decode($sectionOrderSetting->value, true) : [
            'categories',
            'featured',
            'banner',
            'new_arrivals',
            'why_choose_us',
            'sale',
            'testimonials',
            'blog'
        ];

        return view('themes.' . $theme . '.home.index', compact(
            'featuredProducts',
            'latestProducts',
            'categories',
            'saleProducts',
            'latestBlogs',
            'sectionOrder'
        ));
    }
}
