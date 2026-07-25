<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\ThemeService;

class ThemeController extends Controller
{
    protected ThemeService $themeService;

    public function __construct(ThemeService $themeService)
    {
        $this->themeService = $themeService;
    }

    public function index()
    {
        $themes = $this->themeService->getAvailableThemes();
        $activeTheme = $this->themeService->getActiveTheme();
        
        // Add active flag to each theme
        foreach ($themes as $key => $theme) {
            $themes[$key]['active'] = ($key === $activeTheme);
        }
        
        return view('admin.theme.index', compact('themes', 'activeTheme'));
    }

    public function activate(Request $request)
    {
        $request->validate([
            'theme' => 'required|string',
        ]);

        $activated = $this->themeService->activateTheme($request->theme);

        if ($activated) {
            return back()->with('success', 'Theme activated successfully.');
        }

        return back()->with('error', 'Failed to activate theme. Theme may not exist.');
    }

    public function settings()
    {
        $theme = $this->themeService->getActiveTheme();
        $config = $this->themeService->getThemeConfig($theme);
        $settings = $this->themeService->getThemeCustomizableSettings();
        
        return view('admin.theme.settings', compact('theme', 'settings', 'config'));
    }

    public function updateSettings(Request $request)
    {
        // The form posts exactly the keys the theme exposes; `$request->except('_token')` wrote *any* posted
        // key into `settings`, so a crafted request could rewrite unrelated configuration (payment keys,
        // currency, OTP) from the theme screen.
        $allowed = [
            'primary_color', 'secondary_color', 'accent_color', 'gold_color',
            'heading_font', 'body_font',
            'menu_hover_color', 'menu_text_hover_color', 'menu_active_color',
            'menu_active_text_color', 'menu_font_size', 'menu_font_weight',
        ];

        $settings = array_intersect_key($request->all(), array_flip($allowed));

        $request->validate([
            'menu_font_size' => 'nullable|string|max:8',
            'menu_font_weight' => 'nullable|string|max:8',
        ]);

        $this->themeService->updateThemeSettings($settings);

        return back()->with('success', 'Theme settings updated successfully.');
    }

    public function reset()
    {
        $this->themeService->resetThemeSettings();

        return back()->with('success', 'Theme settings reset to default.');
    }
}
