<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductBundlePurchase extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_bundle_id',
        'user_id',
        'order_id',
        'price_paid',
    ];

    protected $casts = [
        'price_paid' => 'decimal:2',
    ];

    /**
     * Get the bundle for this purchase.
     */
    public function bundle()
    {
        return $this->belongsTo(ProductBundle::class, 'product_bundle_id');
    }

    /**
     * Get the user who made the purchase.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the order for this purchase.
     */
    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
