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
     * Legal moves between refund states. Terminal states admit nothing: a rejection cannot be
     * reopened by approving it, a processed refund cannot walk backwards, and money is only moved
     * by the approved → processed step.
     *
     * @var array<string, array<int, string>>
     */
    public const TRANSITIONS = [
        'pending' => ['approved', 'rejected'],
        'approved' => ['processed'],
        'rejected' => [],
        'processed' => [],
    ];

    /** May this refund move to $status? Re-setting the current status is always allowed. */
    public function canTransitionTo(string $status): bool
    {
        if ($status === $this->status) {
            return true;
        }

        return in_array($status, self::TRANSITIONS[$this->status] ?? [], true);
    }

    protected static function booted(): void
    {
        // refund_number is NOT NULL with no default: every insert used to need the caller to pass
        // one, which nothing did (there was no creation path at all).
        static::creating(function ($refund) {
            if (empty($refund->refund_number)) {
                $refund->refund_number = self::generateRefundNumber();
            }
        });
    }

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
     * Generate a unique refund number.
     *
     * This used self::withTrashed() on a model without the SoftDeletes trait, so it threw
     * BadMethodCallException the day anything called it. It also read the sequence out of the last
     * row's suffix, which collides as soon as two refunds are created in the same request.
     */
    public static function generateRefundNumber(): string
    {
        $prefix = 'REF-' . now()->format('Ymd') . '-';

        $last = self::where('refund_number', 'like', $prefix . '%')
            ->orderByDesc('id')
            ->value('refund_number');

        $sequence = $last ? ((int) substr($last, strlen($prefix)) + 1) : 1;

        do {
            $candidate = $prefix . str_pad((string) $sequence, 4, '0', STR_PAD_LEFT);
            $exists = self::where('refund_number', $candidate)->exists();
            $sequence++;
        } while ($exists);

        return $candidate;
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
