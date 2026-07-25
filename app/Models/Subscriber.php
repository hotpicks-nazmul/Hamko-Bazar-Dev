<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Subscriber extends Model
{
    use HasFactory;

    protected $fillable = [
        'email',
        'phone',
        'name',
        'status',
        'unsubscribed_at',
        'user_id',
    ];

    protected $casts = [
        'unsubscribed_at' => 'datetime',
    ];

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($subscriber) {
            if (empty($subscriber->status)) {
                $subscriber->status = 'active';
            }
        });
    }

    /**
     * Get the user associated with this subscriber (if registered).
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Check if the subscriber is active.
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    /**
     * Check if the subscriber has unsubscribed.
     */
    public function isUnsubscribed(): bool
    {
        return $this->status === 'unsubscribed' || $this->unsubscribed_at !== null;
    }

    /**
     * Unsubscribe the subscriber.
     */
    public function unsubscribe(): void
    {
        $this->status = 'unsubscribed';
        $this->unsubscribed_at = now();
        $this->save();
    }

    /**
     * Resubscribe the subscriber.
     */
    public function resubscribe(): void
    {
        $this->status = 'active';
        $this->unsubscribed_at = null;
        $this->save();
    }

    /**
     * Scope to get only active subscribers.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope to get only unsubscribed subscribers.
     */
    public function scopeUnsubscribed($query)
    {
        return $query->where('status', 'unsubscribed');
    }
}
