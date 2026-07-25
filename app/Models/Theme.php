<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Theme extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'category_type',
        'preview_image',
        'is_active',
        'settings',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'settings' => 'array',
    ];

    /**
     * Get the active theme.
     */
    public static function getActive(): ?self
    {
        return static::where('is_active', true)->first();
    }

    /**
     * Activate this theme.
     */
    public function activate(): void
    {
        // Deactivate all themes
        static::where('is_active', true)->update(['is_active' => false]);
        
        // Activate this theme
        $this->update(['is_active' => true]);
        
        // Update settings
        Setting::set('active_theme', $this->slug);
    }

    /**
     * Scope for active themes.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope by category type.
     */
    public function scopeByCategory($query, string $category)
    {
        return $query->where('category_type', $category);
    }

    /**
     * Get theme path.
     */
    public function getPath(): string
    {
        return resource_path('views/themes/' . $this->slug);
    }

    /**
     * Get theme config.
     */
    public function getConfig(): array
    {
        $configPath = $this->getPath() . '/theme.json';
        
        if (file_exists($configPath)) {
            return json_decode(file_get_contents($configPath), true);
        }
        
        return [];
    }
}
