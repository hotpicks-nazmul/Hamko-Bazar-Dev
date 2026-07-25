<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Banner extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'image',
        'thumbnail',
        'link',
        'position',
        'description',
        'button_text',
        'button_color',
        'text_color',
        'background_color',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Get position options for dropdown
     */
    public static function getPositionOptions()
    {
        return [
            'home_top' => 'Home - Top Banner',
            'home_middle' => 'Home - Middle Banner',
            'home_bottom' => 'Home - Bottom Banner',
            'sidebar' => 'Sidebar Banner',
            'category_page' => 'Category Page Banner',
            'product_page' => 'Product Page Banner',
        ];
    }

    /**
     * Get active banners by position
     */
    public static function getActiveByPosition($position, $limit = null)
    {
        $query = self::where('position', $position)
            ->where('is_active', true)
            ->orderBy('sort_order', 'asc');

        if ($limit) {
            $query->limit($limit);
        }

        return $query->get();
    }
}
