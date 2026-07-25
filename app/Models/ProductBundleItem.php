<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductBundleItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_bundle_id',
        'product_id',
        'quantity',
        'custom_price',
        'sort_order',
    ];

    protected $casts = [
        'custom_price' => 'decimal:2',
    ];

    /**
     * Get the bundle this item belongs to.
     */
    public function bundle()
    {
        return $this->belongsTo(ProductBundle::class, 'product_bundle_id');
    }

    /**
     * Get the product for this item.
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the effective price for this item.
     */
    public function getEffectivePriceAttribute(): float
    {
        return $this->custom_price ?? $this->product->final_price;
    }

    /**
     * Get the total price for this item (price * quantity).
     */
    public function getTotalPriceAttribute(): float
    {
        return $this->effective_price * $this->quantity;
    }
}
