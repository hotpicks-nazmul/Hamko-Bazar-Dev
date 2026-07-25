<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Widget extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'widget_type',
        'title',
        'subtitle',
        'description',
        'content',
        'settings',
        'category_id',
        'product_limit',
        'sort_order',
        'status',
        'is_featured',
    ];

    protected $casts = [
        'settings' => 'array',
        'is_featured' => 'boolean',
        'sort_order' => 'integer',
        'product_limit' => 'integer',
    ];

    /**
     * Get settings as array (handle both JSON string and array).
     */
    public function getSettingsAttribute($value)
    {
        if (is_array($value)) {
            return $value;
        }
        if (is_string($value) && !empty($value)) {
            return json_decode($value, true) ?? [];
        }
        return [];
    }

    /**
     * Widget Types
     */
    const TYPE_FEATURED_PRODUCTS = 'featured_products';
    const TYPE_NEW_ARRIVALS = 'new_arrivals';
    const TYPE_BEST_SELLERS = 'best_sellers';
    const TYPE_CATEGORY_PRODUCTS = 'category_products';
    const TYPE_TOP_RATED = 'top_rated';
    const TYPE_SPECIAL_OFFER = 'special_offer';
    const TYPE_BANNER = 'banner';
    const TYPE_CUSTOM_HTML = 'custom_html';
    const TYPE_NEWSLETTER = 'newsletter';
    const TYPE_TESTIMONIALS = 'testimonials';
    const TYPE_SLIDER = 'slider';

    /**
     * Widget type options for admin
     */
    public static function getWidgetTypes(): array
    {
        return [
            self::TYPE_FEATURED_PRODUCTS => 'Featured Products',
            self::TYPE_NEW_ARRIVALS => 'New Arrivals',
            self::TYPE_BEST_SELLERS => 'Best Sellers',
            self::TYPE_CATEGORY_PRODUCTS => 'Category Products',
            self::TYPE_TOP_RATED => 'Top Rated Products',
            self::TYPE_SPECIAL_OFFER => 'Special Offer',
            self::TYPE_BANNER => 'Banner',
            self::TYPE_CUSTOM_HTML => 'Custom HTML',
            self::TYPE_NEWSLETTER => 'Newsletter',
            self::TYPE_TESTIMONIALS => 'Testimonials',
            self::TYPE_SLIDER => 'Slider',
        ];
    }

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($widget) {
            if (empty($widget->slug)) {
                $widget->slug = Str::slug($widget->name);
            }
            if (empty($widget->sort_order)) {
                $widget->sort_order = static::max('sort_order') + 1;
            }
        });

        static::updating(function ($widget) {
            if (empty($widget->slug)) {
                $widget->slug = Str::slug($widget->name);
            }
        });
    }

    /**
     * Get the category that owns the widget (for category products widget).
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    /**
     * Check if the widget is active.
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    /**
     * Get products based on widget type.
     */
    public function getProducts($limit = null)
    {
        $limit = $limit ?? $this->product_limit ?? 10;

        return match ($this->widget_type) {
            self::TYPE_FEATURED_PRODUCTS => Product::where('featured', 1)
                ->where('status', 'active')
                ->active()
                ->limit($limit)
                ->get(),
            self::TYPE_NEW_ARRIVALS => Product::where('status', 'active')
                ->active()
                ->orderBy('created_at', 'desc')
                ->limit($limit)
                ->get(),
            self::TYPE_BEST_SELLERS => Product::where('status', 'active')
                ->active()
                ->orderBy('num_of_sale', 'desc')
                ->limit($limit)
                ->get(),
            self::TYPE_CATEGORY_PRODUCTS => $this->category_id 
                ? Product::where('category_id', $this->category_id)
                    ->where('status', 'active')
                    ->active()
                    ->limit($limit)
                    ->get()
                : collect(),
            self::TYPE_TOP_RATED => Product::where('status', 'active')
                ->active()
                ->withCount('reviews')
                ->having('reviews_count', '>', 0)
                ->orderByAvgRating('desc')
                ->limit($limit)
                ->get(),
            self::TYPE_SPECIAL_OFFER => Product::where('status', 'active')
                ->active()
                ->where('has_discount', 1)
                ->orderBy('created_at', 'desc')
                ->limit($limit)
                ->get(),
            default => collect(),
        };
    }

    /**
     * Get status badge color.
     */
    public function getStatusBadgeAttribute(): string
    {
        return match($this->status) {
            'active' => 'success',
            'inactive' => 'danger',
            default => 'secondary',
        };
    }

    /**
     * Get widget icon based on type.
     */
    public function getWidgetIconAttribute(): string
    {
        return match($this->widget_type) {
            self::TYPE_FEATURED_PRODUCTS => 'bi-star',
            self::TYPE_NEW_ARRIVALS => 'bi-clock-history',
            self::TYPE_BEST_SELLERS => 'bi-trophy',
            self::TYPE_CATEGORY_PRODUCTS => 'bi-folder',
            self::TYPE_TOP_RATED => 'bi-star-fill',
            self::TYPE_SPECIAL_OFFER => 'bi-gift',
            self::TYPE_BANNER => 'bi-card-image',
            self::TYPE_CUSTOM_HTML => 'bi-code-slash',
            self::TYPE_NEWSLETTER => 'bi-envelope',
            self::TYPE_TESTIMONIALS => 'bi-chat-quote',
            self::TYPE_SLIDER => 'bi-sliders',
            default => 'bi-grid-3x3-gap',
        };
    }

    /**
     * Scope a query to only include active widgets.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope a query to only include featured widgets.
     */
    public function scopeFeatured($query)
    {
        return $query->where('is_featured', 1);
    }

    /**
     * Scope a query to order by sort order.
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order', 'asc');
    }
}

