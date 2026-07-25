<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\ImageHelper;
use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Models\Slider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class HeroController extends Controller
{
    /**
     * Show the hero settings form.
     */
    public function index()
    {
        $heroSettings = Setting::where('group', 'hero')->get()->keyBy('key');
        $sliders = Slider::where('is_active', true)->orderBy('order')->get();
        
        return view('admin.hero.index', compact('heroSettings', 'sliders'));
    }

    /**
     * Update the hero settings.
     */
    public function update(Request $request)
    {
        $validated = $request->validate([
            'hero_background_image' => 'nullable|image|max:5120',
            'hero_main_image' => 'nullable|image|max:5120',
            'hero_badge_icon' => 'nullable|string|max:255',
            'hero_badge_text' => 'nullable|string|max:255',
            'hero_title_line1' => 'nullable|string|max:255',
            'hero_title_highlight1' => 'nullable|string|max:255',
            'hero_title_line2' => 'nullable|string|max:255',
            'hero_title_line3' => 'nullable|string|max:255',
            'hero_description' => 'nullable|string',
            'hero_cta1_text' => 'nullable|string|max:255',
            'hero_cta1_link' => 'nullable|string|max:255',
            'hero_cta1_icon' => 'nullable|string|max:255',
            'hero_cta2_text' => 'nullable|string|max:255',
            'hero_cta2_link' => 'nullable|string|max:255',
            'hero_cta2_params' => 'nullable|string',
            'hero_cta2_icon' => 'nullable|string|max:255',
            'hero_cta2_badge' => 'nullable|string|max:255',
            'hero_feature1_icon' => 'nullable|string|max:255',
            'hero_feature1_title' => 'nullable|string|max:255',
            'hero_feature1_subtitle' => 'nullable|string|max:255',
            'hero_feature2_icon' => 'nullable|string|max:255',
            'hero_feature2_title' => 'nullable|string|max:255',
            'hero_feature2_subtitle' => 'nullable|string|max:255',
            'hero_feature3_icon' => 'nullable|string|max:255',
            'hero_feature3_title' => 'nullable|string|max:255',
            'hero_feature3_subtitle' => 'nullable|string|max:255',
            'hero_feature4_icon' => 'nullable|string|max:255',
            'hero_feature4_title' => 'nullable|string|max:255',
            'hero_feature4_subtitle' => 'nullable|string|max:255',
            'hero_special_label' => 'nullable|string|max:255',
            'hero_special_title' => 'nullable|string|max:255',
            'hero_special_button' => 'nullable|string|max:255',
            'hero_special_link' => 'nullable|string|max:255',
            'hero_delivery_icon' => 'nullable|string|max:255',
            'hero_delivery_label' => 'nullable|string|max:255',
            'hero_delivery_value' => 'nullable|string|max:255',
            'hero_customers_label' => 'nullable|string|max:255',
            'hero_customers_value' => 'nullable|string|max:255',
            'hero_main_image_alt' => 'nullable|string|max:255',
        ]);

        // Handle background image upload
        if ($request->hasFile('hero_background_image')) {
            if (ImageHelper::isValidImage($request->file('hero_background_image'))) {
                // Delete old image if exists
                $oldSetting = Setting::where('key', 'hero_background_image')->where('group', 'hero')->first();
                if ($oldSetting && $oldSetting->value) {
                    ImageHelper::deleteImage($oldSetting->value);
                }
                
                $imageResult = ImageHelper::processImage(
                    $request->file('hero_background_image'),
                    'hero',
                    1920,
                    0,
                    85
                );
                
                Setting::set('hero_background_image', $imageResult['path'], 'hero');
            }
        }

        // Handle main image upload
        if ($request->hasFile('hero_main_image')) {
            if (ImageHelper::isValidImage($request->file('hero_main_image'))) {
                // Delete old image if exists
                $oldSetting = Setting::where('key', 'hero_main_image')->where('group', 'hero')->first();
                if ($oldSetting && $oldSetting->value) {
                    ImageHelper::deleteImage($oldSetting->value);
                }
                
                $imageResult = ImageHelper::processImage(
                    $request->file('hero_main_image'),
                    'hero',
                    600,
                    0,
                    85
                );
                
                Setting::set('hero_main_image', $imageResult['path'], 'hero');
            }
        }

        // Save all other settings
        $settings = $request->except(['_token', '_method', 'hero_background_image', 'hero_main_image']);
        
        foreach ($settings as $key => $value) {
            Setting::set($key, $value, 'hero');
        }
        
        return redirect()->route('admin.hero.index')
            ->with('success', 'Hero settings updated successfully!');
    }

    /**
     * Update hero type setting.
     */
    public function updateType(Request $request)
    {
        $validated = $request->validate([
            'hero_type' => 'required|in:standard,slider',
        ]);
        
        Setting::set('hero_type', $validated['hero_type'], 'hero');
        
        return redirect()->route('admin.hero.index')
            ->with('success', 'Hero type updated to ' . ucfirst($validated['hero_type']) . '!');
    }

    /**
     * Set hero type directly via GET request.
     */
    public function setType($type)
    {
        if (!in_array($type, ['standard', 'slider'])) {
            return redirect()->route('admin.hero.index')
                ->with('error', 'Invalid hero type!');
        }
        
        Setting::set('hero_type', $type, 'hero');
        
        return redirect()->route('admin.hero.index')
            ->with('success', 'Hero type updated to ' . ucfirst($type) . '!');
    }
}