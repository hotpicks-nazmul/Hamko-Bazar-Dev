<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Coupon extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'type',
        'value',
        'min_order_amount',
        'max_discount',
        'usage_limit',
        'used_count',
        'start_date',
        'end_date',
        'status',
    ];

    protected $casts = [
        'value' => 'decimal:2',
        'min_order_amount' => 'decimal:2',
        'max_discount' => 'decimal:2',
        'start_date' => 'datetime',
        'end_date' => 'datetime',
    ];

    /**
     * Check if coupon is active.
     */
    public function getIsActiveAttribute()
    {
        return $this->status === 'active';
    }

    /**
     * Get the minimum order attribute (alias for compatibility).
     */
    public function getMinOrderAttribute()
    {
        return $this->min_order_amount;
    }

    /**
     * Get the starts at attribute (alias for compatibility).
     */
    public function getStartsAtAttribute()
    {
        return $this->start_date;
    }

    /**
     * Get the expires at attribute (alias for compatibility).
     */
    public function getExpiresAtAttribute()
    {
        return $this->end_date;
    }

    /**
     * Check if coupon is valid for use.
     */
    public function isValid($orderTotal = 0)
    {
        if ($this->status !== 'active') {
            return false;
        }

        if ($this->usage_limit && $this->used_count >= $this->usage_limit) {
            return false;
        }

        if ($this->start_date && now()->lt($this->start_date)) {
            return false;
        }

        if ($this->end_date && now()->gt($this->end_date)) {
            return false;
        }

        if ($this->min_order_amount && $orderTotal < $this->min_order_amount) {
            return false;
        }

        return true;
    }

    /**
     * Calculate discount amount for given order total.
     */
    public function calculateDiscount($orderTotal)
    {
        if ($this->type === 'percentage') {
            $discount = ($orderTotal * $this->value) / 100;
            if ($this->max_discount) {
                $discount = min($discount, $this->max_discount);
            }
        } else {
            $discount = $this->value;
        }

        return min($discount, $orderTotal);
    }

    /**
     * Find a usable coupon by its code, or null.
     *
     * The checkout controller has always called this (and isApplicable/incrementUsage below); the
     * methods did not exist, so posting a coupon code answered "Call to undefined method" and the
     * order was rolled back. Codes are matched case-insensitively because the admin form upper-cases
     * them and customers type whatever they were sent.
     */
    public static function findValidByCode(?string $code): ?self
    {
        $code = strtoupper(trim((string) $code));

        if ($code === '') {
            return null;
        }

        $coupon = static::whereRaw('UPPER(code) = ?', [$code])->first();

        return $coupon && $coupon->isValid() ? $coupon : null;
    }

    /**
     * Is this coupon usable for an order of this subtotal?
     */
    public function isApplicable($orderTotal): bool
    {
        return $this->isValid($orderTotal);
    }

    /**
     * Record one use.
     *
     * Increment (not a read-modify-write) so two simultaneous checkouts cannot both write the same
     * number and lose one use.
     */
    public function incrementUsage(): void
    {
        static::whereKey($this->getKey())->increment('used_count');
        $this->used_count = (int) $this->used_count + 1;
        $this->syncOriginalAttribute('used_count');
    }

    /**
     * Scope to get active coupons.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope to get valid coupons (active and within date range).
     */
    public function scopeValid($query)
    {
        return $query->where('status', 'active')
            ->where(function ($q) {
                $q->whereNull('start_date')
                    ->orWhere('start_date', '<=', now());
            })
            ->where(function ($q) {
                $q->whereNull('end_date')
                    ->orWhere('end_date', '>=', now());
            });
    }
}
