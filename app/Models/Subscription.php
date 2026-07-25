<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Subscription extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'subscription_number',
        'user_id',
        'product_id',
        'order_id',
        'plan_name',
        'description',
        'billing_frequency',
        'quantity',
        'unit_price',
        'total_price',
        'start_date',
        'next_billing_date',
        'end_date',
        'total_billing_cycles',
        'completed_billing_cycles',
        'status',
        'payment_status',
        'cancelled_at',
        'cancellation_reason',
        'cancelled_by',
        'shipping_first_name',
        'shipping_last_name',
        'shipping_email',
        'shipping_phone',
        'shipping_address',
        'shipping_city',
        'shipping_state',
        'shipping_postcode',
        'shipping_country',
        'notes',
        'last_billing_at',
        'activated_at',
    ];

    protected $casts = [
        'start_date' => 'date',
        'next_billing_date' => 'date',
        'end_date' => 'date',
        'cancelled_at' => 'datetime',
        'last_billing_at' => 'datetime',
        'activated_at' => 'datetime',
        'unit_price' => 'decimal:2',
        'total_price' => 'decimal:2',
    ];

    /**
     * Get the user that owns the subscription.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the product associated with the subscription.
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the order associated with the subscription.
     */
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Get the admin who cancelled the subscription.
     */
    public function cancelledBy()
    {
        return $this->belongsTo(User::class, 'cancelled_by');
    }

    /**
     * Generate a unique subscription number.
     */
    public static function generateSubscriptionNumber(): string
    {
        $prefix = 'SUB';
        $date = now()->format('Ymd');
        $lastSubscription = self::withTrashed()
            ->whereDate('created_at', today())
            ->orderBy('id', 'desc')
            ->first();
        
        $sequence = $lastSubscription ? (int) substr($lastSubscription->subscription_number, -4) + 1 : 1;
        $sequence = str_pad($sequence, 4, '0', STR_PAD_LEFT);
        
        return "{$prefix}-{$date}-{$sequence}";
    }

    /**
     * Get the status badge class for Bootstrap.
     */
    public function getStatusBadgeClassAttribute(): string
    {
        return match($this->status) {
            'active' => 'bg-success',
            'paused' => 'bg-warning',
            'cancelled' => 'bg-danger',
            'expired' => 'bg-secondary',
            'pending' => 'bg-info',
            default => 'bg-secondary',
        };
    }

    /**
     * Get the payment status badge class.
     */
    public function getPaymentStatusBadgeClassAttribute(): string
    {
        return match($this->payment_status) {
            'paid' => 'bg-success',
            'pending' => 'bg-warning',
            'failed' => 'bg-danger',
            'refunded' => 'bg-info',
            default => 'bg-secondary',
        };
    }

    /**
     * Get the billing frequency label.
     */
    public function getBillingFrequencyLabelAttribute(): string
    {
        return match($this->billing_frequency) {
            'weekly' => 'Weekly',
            'bi_weekly' => 'Bi-Weekly',
            'monthly' => 'Monthly',
            'quarterly' => 'Quarterly',
            'semi_annually' => 'Semi-Annually',
            'annually' => 'Annually',
            default => ucfirst($this->billing_frequency),
        };
    }

    /**
     * Scope for active subscriptions.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope for pending subscriptions.
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope for cancelled subscriptions.
     */
    public function scopeCancelled($query)
    {
        return $query->where('status', 'cancelled');
    }

    /**
     * Scope for subscriptions due for billing.
     */
    public function scopeDueForBilling($query)
    {
        return $query->where('status', 'active')
                     ->whereDate('next_billing_date', '<=', today());
    }

    /**
     * Check if subscription is active.
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    /**
     * Check if subscription is paused.
     */
    public function isPaused(): bool
    {
        return $this->status === 'paused';
    }

    /**
     * Check if subscription is cancelled.
     */
    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }

    /**
     * Check if subscription has unlimited billing cycles.
     */
    public function hasUnlimitedCycles(): bool
    {
        return is_null($this->total_billing_cycles);
    }

    /**
     * Check if subscription has reached its end.
     */
    public function hasReachedEnd(): bool
    {
        if ($this->end_date && now()->startOfDay()->gt($this->end_date)) {
            return true;
        }

        if (!$this->hasUnlimitedCycles() && $this->completed_billing_cycles >= $this->total_billing_cycles) {
            return true;
        }

        return false;
    }

    /**
     * Get remaining billing cycles.
     */
    public function getRemainingCycles(): ?int
    {
        if ($this->hasUnlimitedCycles()) {
            return null;
        }

        return max(0, $this->total_billing_cycles - $this->completed_billing_cycles);
    }

    /**
     * Calculate next billing date based on frequency.
     */
    public function calculateNextBillingDate(): \Carbon\Carbon
    {
        $currentDate = $this->next_billing_date ?? now();

        return match($this->billing_frequency) {
            'weekly' => $currentDate->copy()->addWeek(),
            'bi_weekly' => $currentDate->copy()->addWeeks(2),
            'monthly' => $currentDate->copy()->addMonth(),
            'quarterly' => $currentDate->copy()->addMonths(3),
            'semi_annually' => $currentDate->copy()->addMonths(6),
            'annually' => $currentDate->copy()->addYear(),
            default => $currentDate->copy()->addMonth(),
        };
    }

    /**
     * Get the full shipping name.
     */
    public function getShippingFullNameAttribute(): string
    {
        return "{$this->shipping_first_name} {$this->shipping_last_name}";
    }

    /**
     * Get the full shipping address.
     */
    public function getShippingFullAddressAttribute(): string
    {
        return "{$this->shipping_address}, {$this->shipping_city}, {$this->shipping_state} {$this->shipping_postcode}, {$this->shipping_country}";
    }
}