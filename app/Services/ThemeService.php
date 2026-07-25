<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\File;

class ThemeService
{
    protected $themesPath;
    protected $activeTheme;

    public function __construct()
    {
        $this->themesPath = resource_path('views/themes');
    }

    /**
     * Get all available themes.
     */
    public function getAvailableThemes(): array
    {
        $themes = [];
        
        if (!File::isDirectory($this->themesPath)) {
            return $themes;
        }

        $directories = File::directories($this->themesPath);

        foreach ($directories as $directory) {
            $themeName = basename($directory);
            $themeJson = $directory . '/theme.json';

            if (File::exists($themeJson)) {
                $config = json_decode(File::get($themeJson), true);
                $themes[$themeName] = [
                    'name' => $config['name'] ?? $themeName,
                    'description' => $config['description'] ?? '',
                    'version' => $config['version'] ?? '1.0.0',
                    'author' => $config['author'] ?? '',
                    'category' => $config['category'] ?? 'general',
                    'screenshot' => $config['screenshot'] ?? null,
                    'directory' => $themeName,
                ];
            } else {
                $themes[$themeName] = [
                    'name' => ucfirst($themeName),
                    'description' => 'No description available',
                    'version' => '1.0.0',
                    'author' => 'Unknown',
                    'category' => 'general',
                    'screenshot' => null,
                    'directory' => $themeName,
                ];
            }
        }

        return $themes;
    }

    /**
     * Get the active theme.
     */
    public function getActiveTheme(): string
    {
        if ($this->activeTheme) {
            return $this->activeTheme;
        }

        $this->activeTheme = Setting::get('active_theme', 'general');
        return $this->activeTheme;
    }

    /**
     * Activate a theme.
     */
    public function activateTheme(string $theme): bool
    {
        $themes = $this->getAvailableThemes();

        if (!isset($themes[$theme])) {
            return false;
        }

        Setting::set('active_theme', $theme);
        $this->activeTheme = $theme;

        return true;
    }

    /**
     * Get theme settings.
     */
    public function getThemeSettings(): array
    {
        $theme = $this->getActiveTheme();
        $settings = Setting::where('group', 'theme_' . $theme)->pluck('value', 'key')->toArray();

        return $settings;
    }

    /**
     * Update theme settings.
     */
    public function updateThemeSettings(array $settings): void
    {
        $theme = $this->getActiveTheme();

        // Handle simple key-value settings (colors, fonts)
        foreach ($settings as $key => $value) {
            if ($key !== 'features' && is_string($value)) {
                Setting::set($key, $value, 'theme_' . $theme);
            }
        }
        
        // Handle features array
        if (isset($settings['features']) && is_array($settings['features'])) {
            Setting::set('features', json_encode($settings['features']), 'theme_' . $theme);
        }
    }

    /**
     * Reset theme settings to default.
     */
    public function resetThemeSettings(): void
    {
        $theme = $this->getActiveTheme();
        
        Setting::where('group', 'theme_' . $theme)->delete();
    }

    /**
     * Get theme configuration.
     */
    public function getThemeConfig(string $theme = null): array
    {
        $theme = $theme ?? $this->getActiveTheme();
        $themePath = $this->themesPath . '/' . $theme . '/theme.json';

        if (File::exists($themePath)) {
            return json_decode(File::get($themePath), true);
        }

        return [];
    }

    /**
     * Get theme customizable settings (colors, fonts, etc.)
     */
    public function getThemeCustomizableSettings(): array
    {
        $theme = $this->getActiveTheme();
        $config = $this->getThemeConfig($theme);
        
        // Get current saved settings
        $savedSettings = $this->getThemeSettings();
        
        // Merge with defaults
        $defaults = [
            'primary_color' => $config['colors']['primary'] ?? '#2D5A27',
            'secondary_color' => $config['colors']['secondary'] ?? '#7c3aed',
            'accent_color' => $config['colors']['accent'] ?? '#10b981',
            'gold_color' => $config['colors']['gold'] ?? '#d4af37',
            'heading_font' => $config['fonts']['heading'] ?? 'Inter',
            'body_font' => $config['fonts']['body'] ?? 'Inter',
            // Menu styling defaults
            'menu_hover_color' => '#ffffff',
            'menu_text_hover_color' => '#4f46e5',
            'menu_active_color' => '#ffffff',
            'menu_active_text_color' => '#4f46e5',
            'menu_font_size' => '14',
            'menu_font_weight' => '400',
        ];

        // Get features if saved
        $features = [];
        if (isset($savedSettings['features'])) {
            $features = json_decode($savedSettings['features'], true) ?? [];
        } elseif (isset($config['features'])) {
            $features = $config['features'];
        }

        return array_merge($defaults, $savedSettings, ['features' => $features]);
    }

    /**
     * Render a theme view.
     */
    public function view(string $view, array $data = [])
    {
        $theme = $this->getActiveTheme();
        $themeView = 'themes.' . $theme . '.' . $view;
        
        return view($themeView, $data);
    }
}
