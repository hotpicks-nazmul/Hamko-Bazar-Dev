<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Setting;
use Spatie\Sitemap\SitemapGenerator;

class SeoController extends Controller
{
    public function index()
    {
        $settings = Setting::whereIn('key', [
            'site_meta_title', 'site_meta_description', 'site_meta_keywords',
            'google_analytics_id', 'google_search_console_code', 'facebook_pixel_id',
            'og_title', 'og_description', 'og_image',
            'twitter_card_type',
        ])->pluck('value', 'key');

        $redirects = json_decode(Setting::get('seo_redirects', '[]'), true);
        
        return view('admin.seo.index', compact('settings', 'redirects'));
    }

    public function updateMeta(Request $request)
    {
        $validated = $request->validate([
            'site_meta_title' => 'nullable|string|max:100',
            'site_meta_description' => 'nullable|string|max:300',
            'site_meta_keywords' => 'nullable|string|max:500',
            'google_analytics_id' => 'nullable|string|max:50',
            'google_search_console_code' => 'nullable|string|max:500',
            'facebook_pixel_id' => 'nullable|string|max:50',
            'og_title' => 'nullable|string|max:100',
            'og_description' => 'nullable|string|max:300',
            'og_image' => 'nullable|string|max:500',
            'twitter_card_type' => 'nullable|in:summary,summary_large_image',
        ]);

        foreach ($validated as $key => $value) {
            Setting::updateOrCreate(['key' => $key], ['value' => $value, 'group' => 'seo']);
        }

        return back()->with('success', 'SEO settings updated successfully.');
    }

    public function generateSitemap()
    {
        try {
            SitemapGenerator::create(config('app.url'))
                ->writeToFile(storage_path('app/sitemap.xml'));

            return back()->with('success', 'Sitemap generated successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to generate sitemap: ' . $e->getMessage());
        }
    }

    public function redirects()
    {
        $redirects = json_decode(Setting::get('seo_redirects', '[]'), true);
        return view('admin.seo.index', compact('redirects'));
    }

    public function storeRedirect(Request $request)
    {
        $request->validate([
            'from' => 'required|string|max:500',
            'to' => 'required|string|max:500',
            'type' => 'required|in:301,302',
        ]);

        $redirects = json_decode(Setting::get('seo_redirects', '[]'), true);
        $redirects[] = [
            'from' => $request->from,
            'to' => $request->to,
            'type' => $request->type,
        ];

        Setting::updateOrCreate(['key' => 'seo_redirects'], ['value' => json_encode($redirects), 'group' => 'seo']);

        return back()->with('success', 'Redirect added successfully.');
    }

    public function deleteRedirect($index)
    {
        $redirects = json_decode(Setting::get('seo_redirects', '[]'), true);
        
        if (isset($redirects[$index])) {
            unset($redirects[$index]);
            Setting::updateOrCreate(['key' => 'seo_redirects'], ['value' => json_encode(array_values($redirects)), 'group' => 'seo']);
        }

        return back()->with('success', 'Redirect deleted successfully.');
    }
}
