<?php

namespace App\Services;

use App\Models\Widget;
use App\Models\Product;
use Illuminate\Support\Facades\Cache;

class WidgetHelper
{
    /**
     * Get all active widgets ordered by sort order.
     */
    public static function getActiveWidgets()
    {
        return Cache::remember('active_widgets', 3600, function () {
            return Widget::active()
                ->ordered()
                ->get();
        });
    }

    /**
     * Get featured widgets.
     */
    public static function getFeaturedWidgets()
    {
        return Cache::remember('featured_widgets', 3600, function () {
            return Widget::active()
                ->featured()
                ->ordered()
                ->get();
        });
    }

    /**
     * Get widgets by type.
     */
    public static function getWidgetsByType($type)
    {
        return Widget::active()
            ->where('widget_type', $type)
            ->ordered()
            ->get();
    }

    /**
     * Render a single widget.
     */
    public static function renderWidget($widget, $data = [])
    {
        if (!$widget instanceof Widget) {
            $widget = Widget::findOrFail($widget);
        }

        if (!$widget->isActive()) {
            return '';
        }

        return match($widget->widget_type) {
            Widget::TYPE_FEATURED_PRODUCTS => self::renderFeaturedProducts($widget, $data),
            Widget::TYPE_NEW_ARRIVALS => self::renderNewArrivals($widget, $data),
            Widget::TYPE_BEST_SELLERS => self::renderBestSellers($widget, $data),
            Widget::TYPE_CATEGORY_PRODUCTS => self::renderCategoryProducts($widget, $data),
            Widget::TYPE_TOP_RATED => self::renderTopRated($widget, $data),
            Widget::TYPE_SPECIAL_OFFER => self::renderSpecialOffer($widget, $data),
            Widget::TYPE_BANNER => self::renderBanner($widget, $data),
            Widget::TYPE_CUSTOM_HTML => self::renderCustomHtml($widget, $data),
            Widget::TYPE_NEWSLETTER => self::renderNewsletter($widget, $data),
            Widget::TYPE_TESTIMONIALS => self::renderTestimonials($widget, $data),
            Widget::TYPE_SLIDER => self::renderSlider($widget, $data),
            default => '',
        };
    }

    /**
     * Render featured products widget.
     */
    protected static function renderFeaturedProducts($widget, $data)
    {
        $products = $widget->getProducts();
        
        return view('themes.general.partials.widgets.featured_products', [
            'widget' => $widget,
            'products' => $products,
        ])->render();
    }

    /**
     * Render new arrivals widget.
     */
    protected static function renderNewArrivals($widget, $data)
    {
        $products = $widget->getProducts();
        
        return view('themes.general.partials.widgets.new_arrivals', [
            'widget' => $widget,
            'products' => $products,
        ])->render();
    }

    /**
     * Render best sellers widget.
     */
    protected static function renderBestSellers($widget, $data)
    {
        $products = $widget->getProducts();
        
        return view('themes.general.partials.widgets.best_sellers', [
            'widget' => $widget,
            'products' => $products,
        ])->render();
    }

    /**
     * Render category products widget.
     */
    protected static function renderCategoryProducts($widget, $data)
    {
        $products = $widget->getProducts();
        
        return view('themes.general.partials.widgets.category_products', [
            'widget' => $widget,
            'products' => $products,
        ])->render();
    }

    /**
     * Render top rated products widget.
     */
    protected static function renderTopRated($widget, $data)
    {
        $products = $widget->getProducts();
        
        return view('themes.general.partials.widgets.top_rated', [
            'widget' => $widget,
            'products' => $products,
        ])->render();
    }

    /**
     * Render special offer widget.
     */
    protected static function renderSpecialOffer($widget, $data)
    {
        $products = $widget->getProducts();
        
        return view('themes.general.partials.widgets.special_offer', [
            'widget' => $widget,
            'products' => $products,
        ])->render();
    }

    /**
     * Render banner widget.
     */
    protected static function renderBanner($widget, $data)
    {
        $settings = $widget->settings ?? [];
        
        return view('themes.general.partials.widgets.banner', [
            'widget' => $widget,
            'settings' => $settings,
        ])->render();
    }

    /**
     * Render custom HTML widget.
     */
    protected static function renderCustomHtml($widget, $data)
    {
        return view('themes.general.partials.widgets.custom_html', [
            'widget' => $widget,
            'content' => $widget->content,
        ])->render();
    }

    /**
     * Render newsletter widget.
     */
    protected static function renderNewsletter($widget, $data)
    {
        return view('themes.general.partials.widgets.newsletter', [
            'widget' => $widget,
        ])->render();
    }

    /**
     * Render testimonials widget.
     */
    protected static function renderTestimonials($widget, $data)
    {
        $settings = $widget->settings ?? [];
        
        return view('themes.general.partials.widgets.testimonials', [
            'widget' => $widget,
            'settings' => $settings,
        ])->render();
    }

    /**
     * Render slider widget.
     */
    protected static function renderSlider($widget, $data)
    {
        $settings = $widget->settings ?? [];
        
        return view('themes.general.partials.widgets.slider', [
            'widget' => $widget,
            'settings' => $settings,
        ])->render();
    }

    /**
     * Clear widget cache.
     */
    public static function clearCache()
    {
        Cache::forget('active_widgets');
        Cache::forget('featured_widgets');
    }
}
