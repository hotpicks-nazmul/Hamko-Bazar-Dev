<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Helpers\ImageHelper;
use App\Models\Setting;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class HomePageController extends Controller
{
    /**
     * Show the home page settings form.
     */
    public function index()
    {
        $homeSettings = Setting::where('group', 'homepage')->get()->keyBy('key');
        
        // Get section order with default fallback
        $sectionOrder = json_decode($homeSettings['homepage_section_order']->value ?? '[]', true);
        if (empty($sectionOrder)) {
            $sectionOrder = [
                'categories',
                'featured',
                'banner',
                'new_arrivals',
                'why_choose_us',
                'sale',
                'testimonials',
                'blog'
            ];
        }
        
        // Define all available sections with their labels and icons
        $availableSections = [
            'categories' => [
                'label' => 'Categories',
                'icon' => 'bi-grid-3x3-gap',
                'description' => 'Product categories showcase'
            ],
            'featured' => [
                'label' => 'Featured Products',
                'icon' => 'bi-star-fill',
                'description' => 'Handpicked premium products'
            ],
            'banner' => [
                'label' => 'Banner Section',
                'icon' => 'bi-megaphone',
                'description' => 'Promotional banners'
            ],
            'new_arrivals' => [
                'label' => 'New Arrivals',
                'icon' => 'bi-box-seam',
                'description' => 'Latest products in store'
            ],
            'category_products' => [
                'label' => 'Category Products',
                'icon' => 'bi-collection',
                'description' => 'Products by category (Houseware, Furniture, Cookware)'
            ],
            'why_choose_us' => [
                'label' => 'Why Choose Us',
                'icon' => 'bi-patch-check',
                'description' => 'Trust badges and features'
            ],
            'sale' => [
                'label' => 'Hot Deals',
                'icon' => 'bi-fire',
                'description' => 'Products on sale'
            ],
            'testimonials' => [
                'label' => 'Testimonials',
                'icon' => 'bi-chat-quote',
                'description' => 'Customer reviews'
            ],
            'blog' => [
                'label' => 'Blog Section',
                'icon' => 'bi-newspaper',
                'description' => 'Latest blog posts'
            ],
        ];
        
        // Get all active categories for selection
        $allCategories = Category::active()
            ->parents()
            ->ordered()
            ->with(['children' => function ($query) {
                $query->active()->ordered();
            }])
            ->get();
        
        // Get selected categories for homepage
        $selectedCategoryIds = json_decode($homeSettings['homepage_selected_categories']->value ?? '[]', true);
        
        return view('admin.homepage.index', compact('homeSettings', 'sectionOrder', 'availableSections', 'allCategories', 'selectedCategoryIds'));
    }

    /**
     * Update the home page settings.
     */
    public function update(Request $request)
    {
        $validated = $request->validate([
            'top_bar_phone' => 'nullable|string|max:255',
            'top_bar_email' => 'nullable|email|max:255',
            'top_bar_delivery_message' => 'nullable|string|max:255',
            'homepage_product_columns' => 'required|integer|min:2|max:6',
            'homepage_featured_products_count' => 'required|integer|min:4|max:100',
            'homepage_new_arrivals_count' => 'required|integer|min:4|max:100',
            'homepage_sale_products_count' => 'required|integer|min:4|max:100',
            'homepage_show_featured_section' => 'nullable|in:0,1',
            'homepage_show_categories_section' => 'nullable|in:0,1',
            'homepage_show_new_arrivals_section' => 'nullable|in:0,1',
            'homepage_show_category_products_section' => 'nullable|in:0,1',
            'homepage_category_products_count' => 'nullable|integer|min:4|max:50',
            'homepage_category_products_order' => 'nullable|string',
            'homepage_show_sale_section' => 'nullable|in:0,1',
            'homepage_show_testimonials_section' => 'nullable|in:0,1',
            'homepage_show_blog_section' => 'nullable|in:0,1',
            'homepage_show_banner_section' => 'nullable|in:0,1',
            'homepage_show_why_choose_us_section' => 'nullable|in:0,1',
            'homepage_show_shop_now_button' => 'nullable|in:0,1',
            'homepage_show_add_to_cart' => 'nullable|in:0,1',
            'homepage_show_wishlist' => 'nullable|in:0,1',
            'homepage_show_quick_view' => 'nullable|in:0,1',
            'homepage_show_product_code' => 'nullable|in:0,1',
            'homepage_show_rating' => 'nullable|in:0,1',
            'homepage_categories_title' => 'nullable|string|max:255',
            'homepage_categories_subtitle' => 'nullable|string|max:255',
            'homepage_featured_title' => 'nullable|string|max:255',
            'homepage_featured_subtitle' => 'nullable|string|max:255',
            'homepage_new_arrivals_title' => 'nullable|string|max:255',
            'homepage_new_arrivals_subtitle' => 'nullable|string|max:255',
            'homepage_sale_title' => 'nullable|string|max:255',
            'homepage_sale_subtitle' => 'nullable|string|max:255',
            'site_name' => 'nullable|string|max:255',
            'site_tagline' => 'nullable|string|max:255',
            'site_logo' => 'nullable|image|mimes:jpeg,png,jpg,svg,webp|max:2048',
            'site_logo_icon' => 'nullable|string|max:255',
            // Category Section Settings
            'homepage_selected_categories' => 'nullable|array',
            'homepage_selected_categories.*' => 'integer|exists:categories,id',
            'homepage_category_style' => 'nullable|string|in:grid,cards',
            'homepage_category_columns' => 'nullable|integer|min:3|max:8',
            // Why Choose Us Section
            'why_choose_us_title' => 'nullable|string|max:255',
            'why_choose_us_subtitle' => 'nullable|string|max:255',
            'why_choose_us_icon_1' => 'nullable|string|max:255',
            'why_choose_us_title_1' => 'nullable|string|max:255',
            'why_choose_us_desc_1' => 'nullable|string|max:500',
            'why_choose_us_icon_2' => 'nullable|string|max:255',
            'why_choose_us_title_2' => 'nullable|string|max:255',
            'why_choose_us_desc_2' => 'nullable|string|max:500',
            'why_choose_us_icon_3' => 'nullable|string|max:255',
            'why_choose_us_title_3' => 'nullable|string|max:255',
            'why_choose_us_desc_3' => 'nullable|string|max:500',
            'why_choose_us_icon_4' => 'nullable|string|max:255',
            'why_choose_us_title_4' => 'nullable|string|max:255',
            'why_choose_us_desc_4' => 'nullable|string|max:500',
            // Banner Section
            'banner1_visible' => 'nullable|in:0,1',
            'banner2_visible' => 'nullable|in:0,1',
            'banner3_visible' => 'nullable|in:0,1',
            'banner4_visible' => 'nullable|in:0,1',
            'banner1_badge' => 'nullable|string|max:255',
            'banner1_title' => 'nullable|string|max:255',
            'banner1_description' => 'nullable|string|max:500',
            'banner1_button_text' => 'nullable|string|max:255',
            'banner1_link' => 'nullable|string|max:255',
            'banner1_icon' => 'nullable|string|max:255',
            'banner2_badge' => 'nullable|string|max:255',
            'banner2_title' => 'nullable|string|max:255',
            'banner2_description' => 'nullable|string|max:500',
            'banner2_button_text' => 'nullable|string|max:255',
            'banner2_link' => 'nullable|string|max:255',
            'banner2_icon' => 'nullable|string|max:255',
            'banner3_badge' => 'nullable|string|max:255',
            'banner3_title' => 'nullable|string|max:255',
            'banner3_description' => 'nullable|string|max:500',
            'banner3_button_text' => 'nullable|string|max:255',
            'banner3_link' => 'nullable|string|max:255',
            'banner3_icon' => 'nullable|string|max:255',
            'banner4_badge' => 'nullable|string|max:255',
            'banner4_title' => 'nullable|string|max:255',
            'banner4_description' => 'nullable|string|max:500',
            'banner4_button_text' => 'nullable|string|max:255',
            'banner4_link' => 'nullable|string|max:255',
            'banner4_icon' => 'nullable|string|max:255',
            // Testimonials Section
            'testimonials_title' => 'nullable|string|max:255',
            'testimonials_subtitle' => 'nullable|string|max:255',
            'testimonial1_name' => 'nullable|string|max:255',
            'testimonial1_location' => 'nullable|string|max:255',
            'testimonial1_text' => 'nullable|string|max:500',
            'testimonial1_rating' => 'nullable|integer|min:1|max:5',
            'testimonial2_name' => 'nullable|string|max:255',
            'testimonial2_location' => 'nullable|string|max:255',
            'testimonial2_text' => 'nullable|string|max:500',
            'testimonial2_rating' => 'nullable|integer|min:1|max:5',
            'testimonial3_name' => 'nullable|string|max:255',
            'testimonial3_location' => 'nullable|string|max:255',
            'testimonial3_text' => 'nullable|string|max:500',
            'testimonial3_rating' => 'nullable|integer|min:1|max:5',
        ]);
        
        $settings = $request->except(['_token', '_method']);
        
        // Handle selected categories - store as JSON
        if ($request->has('homepage_selected_categories')) {
            $settings['homepage_selected_categories'] = json_encode($request->input('homepage_selected_categories'));
        } else {
            $settings['homepage_selected_categories'] = json_encode([]);
        }
        
        // Handle checkbox fields that need default to '0' when unchecked
        $checkboxFields = [
            'homepage_show_featured_section',
            'homepage_show_categories_section',
            'homepage_show_new_arrivals_section',
            'homepage_show_category_products_section',
            'homepage_show_sale_section',
            'homepage_show_testimonials_section',
            'homepage_show_blog_section',
            'homepage_show_banner_section',
            'homepage_show_why_choose_us_section',
            'homepage_show_shop_now_button',
            'homepage_show_add_to_cart',
            'homepage_show_wishlist',
            'homepage_show_quick_view',
            'homepage_show_product_code',
            'homepage_show_rating',
            'banner1_visible',
            'banner2_visible',
            'banner3_visible',
            'banner4_visible',
        ];
        
        foreach ($checkboxFields as $field) {
            $settings[$field] = $request->has($field) ? '1' : '0';
        }
        
        // Handle logo file upload
        if ($request->hasFile('site_logo')) {
            if (ImageHelper::isValidImage($request->file('site_logo'))) {
                $imageResult = ImageHelper::processImage(
                    $request->file('site_logo'),
                    'logo',
                    512,
                    150,
                    85
                );
                $settings['site_logo'] = $imageResult['path'];
                
                // Delete old logo if exists
                $oldSetting = Setting::where('key', 'site_logo')->first();
                if ($oldSetting && $oldSetting->value) {
                    ImageHelper::deleteImage($oldSetting->value);
                }
            }
        }
        
        foreach ($settings as $key => $value) {
            Setting::set($key, $value ?? '', 'homepage');
        }
        
        return redirect()->route('admin.homepage.index')
            ->with('success', 'Home page settings updated successfully!');
    }
    
    /**
     * Update the section order via AJAX.
     */
    public function updateSectionOrder(Request $request)
    {
        $validated = $request->validate([
            'sections' => 'required|array',
            'sections.*' => 'string|in:categories,featured,banner,new_arrivals,category_products,why_choose_us,sale,testimonials,blog',
        ]);
        
        Setting::set('homepage_section_order', json_encode($validated['sections']), 'homepage');
        
        return response()->json([
            'success' => true,
            'message' => 'Section order updated successfully!'
        ]);
    }
}
