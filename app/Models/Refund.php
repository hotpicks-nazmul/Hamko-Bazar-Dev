<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Refund extends Model
{
    use HasFactory;

    protected $fillable = [
        'refund_number',
        'order_id',
        'user_id',
        'refund_amount',
        'reason',
        'reason_details',
        'admin_note',
        'status',
        'processed_at',
        'processed_by',
    ];

    protected $casts = [
        'refund_amount' => 'decimal:2',
        'processed_at' => 'datetime',
    ];

    /**
     * Reason options with labels
     */
    public static function getReasonOptions(): array
    {
        return [
            'product_damaged' => 'Product Damaged',
            'product_not_as_described' => 'Product Not As Described',
            'wrong_item_sent' => 'Wrong Item Sent',
            'product_not_received' => 'Product Not Received',
            'changed_mind' => 'Changed Mind',
            'other' => 'Other',
        ];
    }

    /**
     * Get the reason label
     */
    public function getReasonLabelAttribute(): string
    {
        return self::getReasonOptions()[$this->reason] ?? $this->reason;
    }

    /**
     * Get status badge class
     */
    public function getStatusBadgeClassAttribute(): string
    {
        return match($this->status) {
            'pending' => 'bg-warning',
            'approved' => 'bg-info',
            'rejected' => 'bg-danger',
            'processed' => 'bg-success',
            default => 'bg-secondary',
        };
    }

    /**
     * Get the order for this refund
     */
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Get the user who requested the refund
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the admin who processed the refund
     */
    public function processedBy()
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    /**
     * Generate a unique refund number
     */
    public static function generateRefundNumber(): string
    {
        $prefix = 'REF';
        $date = now()->format('Ymd');
        $lastRefund = self::withTrashed()
            ->whereDate('created_at', today())
            ->orderBy('id', 'desc')
            ->first();
        
        $sequence = $lastRefund ? (int) substr($lastRefund->refund_number, -4) + 1 : 1;
        $sequence = str_pad($sequence, 4, '0', STR_PAD_LEFT);
        
        return "{$prefix}-{$date}-{$sequence}";
    }

    /**
     * Scope for pending refunds
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope for approved refunds
     */
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    /**
     * Scope for rejected refunds
     */
    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }

    /**
     * Scope for processed refunds
     */
    public function scopeProcessed($query)
    {
        return $query->where('status', 'processed');
    }
}
