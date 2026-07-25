<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Symfony\Component\HttpFoundation\Response;
use App\Services\ThemeService;
use App\Models\Category;
use App\Models\Setting;

class ThemeMiddleware
{
    protected ThemeService $themeService;

    public function __construct(ThemeService $themeService)
    {
        $this->themeService = $themeService;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Skip DB queries during installation
        $isInstalled = $this->isAppInstalled();

        // Skip for install routes (always allow through without DB queries)
        if ($request->is('install/*') || $request->routeIs('install.*')) {
            if (!$isInstalled) {
                return $next($request);
            }
        }

        // Skip theme middleware for admin routes
        if ($request->is('admin/*')) {
            return $next($request);
        }

        if (!$isInstalled) {
            return $next($request);
        }

        // Get active theme
        $activeTheme = $this->themeService->getActiveTheme();

        // Share theme data with all views
        view()->share('activeTheme', $activeTheme);
        view()->share('themeSettings', $this->themeService->getThemeSettings());
        
        // Share categories with all views for header navigation (unlimited depth)
        $allCategories = Category::where('status', 'active')
            ->withCount('products')
            ->orderBy('name')
            ->get();
        $grouped = $allCategories->groupBy('parent_id');
        $buildTree = function($parentId) use ($grouped, &$buildTree) {
            $children = $grouped->get($parentId, collect());
            return $children->map(function($cat) use ($buildTree) {
                $cat->setRelation('children', $buildTree($cat->id));
                return $cat;
            });
        };
        $categories = $buildTree(null);
        view()->share('categories', $categories);

        // Share SEO settings with all views
        $seoSettings = Setting::whereIn('key', [
            'site_name',
            'site_favicon',
            'site_meta_title',
            'site_meta_description',
            'site_meta_keywords',
            'google_analytics_id',
            'google_search_console',
            'facebook_pixel_id',
            'og_title',
            'og_description',
            'og_image',
            'twitter_card_type',
        ])->pluck('value', 'key');
        view()->share('seoSettings', $seoSettings);

        return $next($request);
    }

    /**
     * Check if the application is installed (install.lock exists).
     */
    protected function isAppInstalled(): bool
    {
        try {
            return File::exists(storage_path('framework/install.lock'));
        } catch (\Exception $e) {
            return false;
        }
    }
}
