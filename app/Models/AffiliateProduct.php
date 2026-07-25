<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * Affiliate Product Model
 * 
 * TODO: Implement full functionality
 */
class AffiliateProduct extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'category_id',
        'name',
        'slug',
        'description',
        'image',
        'price',
        'commission_rate',
        'external_url',
        'status',
        'clicks',
        'conversions',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'price' => 'decimal:2',
        'commission_rate' => 'decimal:2',
    ];

    /**
     * Get the category that owns the product.
     */
    public function category()
    {
        return $this->belongsTo(AffiliateCategory::class, 'category_id');
    }

    /**
     * Get the links for the product.
     */
    public function links()
    {
        return $this->hasMany(AffiliateLink::class, 'product_id');
    }

    /**
     * Get active products.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Increment click count.
     */
    public function incrementClicks()
    {
        $this->increment('clicks');
    }

    /**
     * Increment conversion count.
     */
    public function incrementConversions()
    {
        $this->increment('conversions');
    }

    /**
     * Auto-generate slug from name.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($product) {
            if (empty($product->slug)) {
                $product->slug = Str::slug($product->name);
            }
        });
    }
}
