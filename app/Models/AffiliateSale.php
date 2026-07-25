<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Affiliate Sale Model
 * 
 * TODO: Implement full functionality
 */
class AffiliateSale extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'affiliate_id',
        'click_id',
        'order_id',
        'product_id',
        'sale_amount',
        'commission_rate',
        'commission_amount',
        'status',
        'sale_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'sale_amount' => 'decimal:2',
        'commission_rate' => 'decimal:2',
        'commission_amount' => 'decimal:2',
        'sale_at' => 'datetime',
    ];

    /**
     * Get the affiliate that owns the sale.
     */
    public function affiliate()
    {
        return $this->belongsTo(Affiliate::class);
    }

    /**
     * Get the click that led to this sale.
     */
    public function click()
    {
        return $this->belongsTo(AffiliateClick::class, 'click_id');
    }

    /**
     * Get the order associated with the sale.
     */
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Get the product associated with the sale.
     */
    public function product()
    {
        return $this->belongsTo(AffiliateProduct::class, 'product_id');
    }

    /**
     * Get pending sales.
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Get approved sales.
     */
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    /**
     * Get paid sales.
     */
    public function scopePaid($query)
    {
        return $query->where('status', 'paid');
    }

    /**
     * Calculate commission amount.
     */
    public static function calculateCommission($saleAmount, $commissionRate)
    {
        return ($saleAmount * $commissionRate) / 100;
    }
}
