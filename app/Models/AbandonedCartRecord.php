<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AbandonedCartRecord extends Model
{
    use HasFactory;

    protected $table = 'abandoned_cart_records';

    protected $fillable = [
        'cart_id',
        'user_id',
        'customer_email',
        'customer_name',
        'cart_total',
        'item_count',
        'abandoned_at',
        'status',
        'last_email_sent_at',
        'email_sent_count',
        'notes',
    ];

    protected $casts = [
        'cart_total' => 'decimal:2',
        'item_count' => 'integer',
        'email_sent_count' => 'integer',
        'abandoned_at' => 'datetime',
        'last_email_sent_at' => 'datetime',
    ];

    public const STATUS_PENDING = 'pending';
    public const STATUS_ABANDONED = 'abandoned';
    public const STATUS_EMAIL_SENT = 'email_sent';
    public const STATUS_RECOVERED = 'recovered';
    public const STATUS_FAILED = 'failed';

    public function cart()
    {
        return $this->belongsTo(Cart::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function scopeAbandoned($query)
    {
        return $query->where('status', self::STATUS_ABANDONED);
    }

    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopeRecovered($query)
    {
        return $query->where('status', self::STATUS_RECOVERED);
    }

    public function scopeReadyForEmail($query)
    {
        return $query->whereIn('status', [self::STATUS_ABANDONED, self::STATUS_EMAIL_SENT])
            ->where('email_sent_count', '<', 3);
    }

    public function getStatusBadgeAttribute()
    {
        $badges = [
            self::STATUS_PENDING => 'warning',
            self::STATUS_ABANDONED => 'danger',
            self::STATUS_EMAIL_SENT => 'info',
            self::STATUS_RECOVERED => 'success',
            self::STATUS_FAILED => 'secondary',
        ];

        return $badges[$this->status] ?? 'secondary';
    }
}
