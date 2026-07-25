<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Color extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'code',
        'hex_code',
        'image',
        'description',
        'display_order',
        'is_active',
        'is_filterable',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_filterable' => 'boolean',
    ];

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($color) {
            if (empty($color->slug)) {
                $color->slug = Str::slug($color->name);
            }
            if (empty($color->code)) {
                $color->code = strtoupper(substr(preg_replace('/[^a-zA-Z]/', '', $color->name), 0, 3));
            }
        });

        static::updating(function ($color) {
            if ($color->isDirty('name') && empty($color->slug)) {
                $color->slug = Str::slug($color->name);
            }
        });
    }

    /**
     * Get the values for this color.
     */
    public function values()
    {
        return $this->hasMany(ColorValue::class)->orderBy('display_order');
    }

    /**
     * Get active values for this color.
     */
    public function activeValues()
    {
        return $this->hasMany(ColorValue::class)
            ->where('is_active', true)
            ->orderBy('display_order');
    }

    /**
     * Get products that have this color via JSON column.
     */
    public function products()
    {
        return $this->belongsToMany(Product::class, 'product_colors')
            ->withPivot(['image', 'quantity', 'price_adjustment', 'sku'])
            ->withTimestamps();
    }

    /**
     * Get products that have this color in the JSON colors column.
     */
    public function productsWithJson()
    {
        return Product::whereNotNull('colors')
            ->where('colors', '!=', '[]');
    }

    /**
     * Get the count of products using this color (from JSON column).
     */
    public function getProductsCountAttribute()
    {
        $colorId = $this->id;
        return $this->productsWithJson()->get()->filter(function ($product) use ($colorId) {
            $colorsData = json_decode($product->colors, true);
            if (!$colorsData || !is_array($colorsData)) {
                return false;
            }
            foreach ($colorsData as $colorItem) {
                if (is_string($colorItem)) {
                    $color = json_decode($colorItem, true);
                } elseif (is_array($colorItem)) {
                    $color = $colorItem;
                } else {
                    continue;
                }
                if (isset($color['color_id']) && $color['color_id'] == $colorId) {
                    return true;
                }
            }
            return false;
        })->count();
    }

    /**
     * Get products using this color.
     */
    public function getProductsAttribute()
    {
        $colorId = $this->id;
        return $this->productsWithJson()->get()->filter(function ($product) use ($colorId) {
            $colorsData = json_decode($product->colors, true);
            if (!$colorsData || !is_array($colorsData)) {
                return false;
            }
            foreach ($colorsData as $colorItem) {
                if (is_string($colorItem)) {
                    $color = json_decode($colorItem, true);
                } elseif (is_array($colorItem)) {
                    $color = $colorItem;
                } else {
                    continue;
                }
                if (isset($color['color_id']) && $color['color_id'] == $colorId) {
                    return true;
                }
            }
            return false;
        });
    }

    /**
     * Scope for active colors.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for filterable colors.
     */
    public function scopeFilterable($query)
    {
        return $query->where('is_filterable', true);
    }

    /**
     * Scope for ordered display.
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('display_order')->orderBy('name');
    }

    /**
     * Get the display badge with color preview.
     */
    public function getColorBadgeAttribute()
    {
        return '<span style="background-color: ' . $this->hex_code . '; width: 20px; height: 20px; display: inline-block; border-radius: 50%; border: 1px solid #ddd; vertical-align: middle;"></span> ' . $this->name;
    }

    /**
     * Get contrast text color (black or white) based on hex code.
     */
    public function getContrastColorAttribute()
    {
        $hex = ltrim($this->hex_code, '#');
        $r = hexdec(substr($hex, 0, 2));
        $g = hexdec(substr($hex, 2, 2));
        $b = hexdec(substr($hex, 4, 2));
        $luminance = ($r * 0.299 + $g * 0.587 + $b * 0.114) / 255;
        return $luminance > 0.5 ? '#000000' : '#FFFFFF';
    }
}
