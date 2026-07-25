<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Category;
use App\Models\Blog;
use App\Models\Banner;
use App\Models\Setting;
use App\Services\ThemeService;

class HomeController extends Controller
{
    protected $theme;

    public function __construct(ThemeService $theme)
    {
        $this->theme = $theme;
    }

    /**
     * Display the home page.
     */
    public function index()
    {
        // Get product count settings from database
        $featuredCount = (int) Setting::where('key', 'homepage_featured_products_count')->value('value') ?: 8;
        $newArrivalsCount = (int) Setting::where('key', 'homepage_new_arrivals_count')->value('value') ?: 8;
        $saleCount = (int) Setting::where('key', 'homepage_sale_products_count')->value('value') ?: 8;

        // Get featured products
        $featuredProducts = Product::active()
            ->featured()
            ->with('category')
            ->inStock()
            ->take($featuredCount)
            ->get();

        // Get latest products
        $latestProducts = Product::active()
            ->with('category')
            ->inStock()
            ->latest()
            ->take($newArrivalsCount)
            ->get();

        // Get categories based on settings
        $selectedCategoryIds = json_decode(Setting::where('key', 'homepage_selected_categories')->value('value') ?? '[]', true);
        
        if (!empty($selectedCategoryIds)) {
            // Get selected categories in the order they were saved
            $categories = Category::active()
                ->whereIn('id', $selectedCategoryIds)
                ->with(['children' => function ($query) {
                    $query->active()->ordered();
                }])
                ->get()
                ->sortBy(function($category) use ($selectedCategoryIds) {
                    return array_search($category->id, $selectedCategoryIds);
                });
        } else {
            // Fallback to default behavior
            $categories = Category::active()
                ->parents()
                ->ordered()
                ->with(['children' => function ($query) {
                    $query->active()->ordered();
                }])
                ->take(6)
                ->get();
        }

        // Get sale products
        $saleProducts = Product::active()
            ->whereNotNull('sale_price')
            ->where('sale_price', '<', \DB::raw('price'))
            ->with('category')
            ->inStock()
            ->take($saleCount)
            ->get();

        // Get latest blog posts
        $latestBlogs = Blog::published()
            ->latest()
            ->with('author')
            ->take(3)
            ->get();

        // Get banners for different positions
        $homeTopBanners = Banner::getActiveByPosition('home_top', 3);
        $homeMiddleBanners = Banner::getActiveByPosition('home_middle', 2);
        $homeBottomBanners = Banner::getActiveByPosition('home_bottom', 4);

        // Get section order from settings
        $sectionOrderSetting = Setting::where('key', 'homepage_section_order')->first();
        $sectionOrder = $sectionOrderSetting ? json_decode($sectionOrderSetting->value, true) : [
            'categories',
            'featured',
            'banner',
            'new_arrivals',
            'category_products',
            'sale',
            'why_choose_us',
            'blog',
            'testimonials'
        ];
        
        // Ensure category_products exists in the order if loaded from DB
        if (!in_array('category_products', $sectionOrder)) {
            $newArrivalsIndex = array_search('new_arrivals', $sectionOrder);
            if ($newArrivalsIndex !== false) {
                array_splice($sectionOrder, $newArrivalsIndex + 1, 0, ['category_products']);
            } else {
                $sectionOrder[] = 'category_products';
            }
            // Save updated order to database
            Setting::updateOrCreate(
                ['key' => 'homepage_section_order'],
                ['value' => json_encode($sectionOrder), 'group' => 'homepage']
            );
        }

        // Get category products for sliders
        $categoryProductData = [];
        $categorySlugs = ['houseware', 'furniture', 'cookware'];
        $categoryNames = ['Houseware', 'Furniture', 'Cookware'];
        
        foreach ($categorySlugs as $slug) {
            $cat = Category::where('slug', $slug)->first();
            if ($cat) {
                // Get all subcategory IDs including the parent
                $childIds = Category::where('parent_id', $cat->id)->pluck('id')->toArray();
                $allIds = array_merge([$cat->id], $childIds);
                
                $products = Product::active()
                    ->whereIn('category_id', $allIds)
                    ->with('category')
                    ->inStock()
                    ->latest()
                    ->take(10)
                    ->get();
                
                $categoryProductData[] = [
                    'slug' => $slug,
                    'name' => $cat->name,
                    'products' => $products,
                ];
            }
        }

        // Get active theme
        $activeTheme = $this->theme->getActiveTheme();

        return view('themes.' . $activeTheme . '.home.index', compact(
            'featuredProducts',
            'latestProducts',
            'categories',
            'saleProducts',
            'latestBlogs',
            'sectionOrder',
            'homeTopBanners',
            'homeMiddleBanners',
            'homeBottomBanners',
            'categoryProductData'
        ));
    }
}
