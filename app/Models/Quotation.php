<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Quotation extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'quotation_number',
        'user_id',
        'converted_order_id',
        'converted_by',
        'converted_at',
        'customer_name',
        'customer_email',
        'customer_phone',
        'customer_address',
        'customer_city',
        'customer_state',
        'customer_postcode',
        'customer_country',
        'notes',
        'terms_conditions',
        'valid_until',
        'subtotal',
        'tax',
        'discount',
        'total',
        'status',
        'sent_at',
        'accepted_at',
        'rejected_at',
    ];

    protected $casts = [
        'valid_until' => 'date',
        'converted_at' => 'datetime',
        'sent_at' => 'datetime',
        'accepted_at' => 'datetime',
        'rejected_at' => 'datetime',
        'subtotal' => 'decimal:2',
        'tax' => 'decimal:2',
        'discount' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    /**
     * Get the user that owns the quotation.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the items for the quotation.
     */
    public function items()
    {
        return $this->hasMany(QuotationItem::class)->orderBy('sort_order');
    }

    /**
     * Get the converted order.
     */
    public function convertedOrder()
    {
        return $this->belongsTo(Order::class, 'converted_order_id');
    }

    /**
     * Generate a unique quotation number.
     */
    public static function generateQuotationNumber(): string
    {
        $prefix = 'QT';
        $date = now()->format('Ymd');
        $lastQuotation = self::withTrashed()
            ->whereDate('created_at', today())
            ->orderBy('id', 'desc')
            ->first();
        
        $sequence = $lastQuotation ? (int) substr($lastQuotation->quotation_number, -4) + 1 : 1;
        $sequence = str_pad($sequence, 4, '0', STR_PAD_LEFT);
        
        return "{$prefix}-{$date}-{$sequence}";
    }

    /**
     * Get the status badge class for Bootstrap.
     */
    public function getStatusBadgeClassAttribute(): string
    {
        return match($this->status) {
            'pending' => 'bg-warning',
            'sent' => 'bg-info',
            'accepted' => 'bg-success',
            'rejected' => 'bg-danger',
            'expired' => 'bg-secondary',
            'converted' => 'bg-primary',
            default => 'bg-secondary',
        };
    }

    /**
     * Check if the quotation is expired.
     */
    public function getIsExpiredAttribute(): bool
    {
        return $this->valid_until < now()->startOfDay() && $this->status !== 'converted';
    }

    /**
     * Check if the quotation can be edited.
     */
    public function getCanEditAttribute(): bool
    {
        return in_array($this->status, ['pending', 'sent']) && !$this->is_expired;
    }

    /**
     * Check if the quotation can be converted to order.
     */
    public function getCanConvertAttribute(): bool
    {
        return in_array($this->status, ['pending', 'sent', 'accepted']) && !$this->is_expired && !$this->converted_order_id;
    }

    /**
     * Scope to get pending quotations.
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope to get sent quotations.
     */
    public function scopeSent($query)
    {
        return $query->where('status', 'sent');
    }

    /**
     * Scope to get accepted quotations.
     */
    public function scopeAccepted($query)
    {
        return $query->where('status', 'accepted');
    }

    /**
     * Scope to get converted quotations.
     */
    public function scopeConverted($query)
    {
        return $query->where('status', 'converted');
    }

    /**
     * Scope to get expired quotations.
     */
    public function scopeExpired($query)
    {
        return $query->where('valid_until', '<', now()->startOfDay())
                     ->whereNotIn('status', ['converted', 'rejected']);
    }

    /**
     * Calculate totals based on items.
     */
    public function calculateTotals(): void
    {
        $this->subtotal = $this->items->sum('total');
        $this->total = $this->subtotal + $this->tax - $this->discount;
        $this->save();
    }

    /**
     * Mark as sent.
     */
    public function markAsSent(): void
    {
        $this->update([
            'status' => 'sent',
            'sent_at' => now(),
        ]);
    }

    /**
     * Mark as accepted.
     */
    public function markAsAccepted(): void
    {
        $this->update([
            'status' => 'accepted',
            'accepted_at' => now(),
        ]);
    }

    /**
     * Mark as rejected.
     */
    public function markAsRejected(): void
    {
        $this->update([
            'status' => 'rejected',
            'rejected_at' => now(),
        ]);
    }

    /**
     * Mark as converted to order.
     */
    public function markAsConverted(int $orderId, string $convertedBy): void
    {
        $this->update([
            'status' => 'converted',
            'converted_order_id' => $orderId,
            'converted_by' => $convertedBy,
            'converted_at' => now(),
        ]);
    }

    /**
     * Get customer full address.
     */
    public function getCustomerFullAddressAttribute(): string
    {
        $parts = array_filter([
            $this->customer_address,
            $this->customer_city,
            $this->customer_state,
            $this->customer_postcode,
            $this->customer_country,
        ]);
        
        return implode(', ', $parts);
    }
}
