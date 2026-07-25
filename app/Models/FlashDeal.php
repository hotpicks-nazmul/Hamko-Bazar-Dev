<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;

class FlashDeal extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'slug',
        'start_date',
        'end_date',
        'status',
        'background_color',
        'text_color',
        'banner_image',
        'is_featured',
        'sort_order',
    ];

    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'is_featured' => 'boolean',
        'sort_order' => 'integer',
    ];

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($flashDeal) {
            if (empty($flashDeal->slug)) {
                $flashDeal->slug = Str::slug($flashDeal->title);
            }
        });
    }

    /**
     * Get the products that belong to the flash deal.
     */
    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'flash_deal_products')
            ->withPivot('discount', 'discount_type', 'min_quantity', 'max_quantity', 'sold_count')
            ->withTimestamps();
    }

    /**
     * Check if the flash deal is currently active.
     */
    public function isActive(): bool
    {
        return $this->status === 'active' 
            && now()->between($this->start_date, $this->end_date);
    }

    /**
     * Check if the flash deal has expired.
     */
    public function isExpired(): bool
    {
        return now()->greaterThan($this->end_date);
    }

    /**
     * Get the status badge color.
     */
    public function getStatusBadgeAttribute(): string
    {
        return match($this->status) {
            'active' => 'success',
            'expired' => 'danger',
            default => 'secondary',
        };
    }

    /**
     * Get the discounted price for a product.
     */
    public function getDiscountedPrice($product): float
    {
        $pivot = $this->products()->where('product_id', $product->id)->first();
        
        if (!$pivot) {
            return $product->unit_price;
        }

        $price = $product->unit_price;
        
        if ($pivot->discount_type === 'percent') {
            $price = $price - ($price * $pivot->discount / 100);
        } else {
            $price = $price - $pivot->discount;
        }

        return max(0, $price);
    }
}
