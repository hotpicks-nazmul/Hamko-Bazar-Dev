<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmailTemplate extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'slug',
        'subject',
        'body',
        'variables',
        'is_active',
        'event',
        'recipient_type',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_active' => 'boolean',
        'variables' => 'array',
    ];

    /**
     * Get the variables available for this template.
     */
    public function getVariablesListAttribute(): array
    {
        return $this->variables ?? [];
    }

    /**
     * Scope a query to only include active templates.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to filter by recipient type.
     */
    public function scopeForRecipientType($query, $type)
    {
        return $query->where('recipient_type', $type);
    }

    /**
     * Scope a query to filter by event.
     */
    public function scopeForEvent($query, $event)
    {
        return $query->where('event', $event);
    }

    /**
     * Get recipient type label.
     */
    public function getRecipientTypeLabelAttribute(): string
    {
        return match ($this->recipient_type) {
            'customer' => 'Customer',
            'seller' => 'Seller',
            'admin' => 'Admin',
            default => ucfirst($this->recipient_type ?? 'Unknown'),
        };
    }

    /**
     * Get event label.
     */
    public function getEventLabelAttribute(): string
    {
        return match ($this->event) {
            'order_placed' => 'Order Placed',
            'order_shipped' => 'Order Shipped',
            'order_delivered' => 'Order Delivered',
            'order_cancelled' => 'Order Cancelled',
            'password_reset' => 'Password Reset',
            'customer_registered' => 'Customer Registered',
            'refund_processed' => 'Refund Processed',
            'new_order' => 'New Order',
            'payout_processed' => 'Payout Processed',
            'low_stock' => 'Low Stock',
            'contact_form' => 'Contact Form',
            'newsletter_subscription' => 'Newsletter Subscription',
            default => ucfirst(str_replace('_', ' ', $this->event ?? 'Unknown')),
        };
    }

    /**
     * Render the template with provided variables.
     */
    public function render(array $variables): array
    {
        return [
            'subject' => $this->renderSubject($variables),
            'body' => $this->renderBody($variables),
        ];
    }

    /**
     * Render the subject line with variables.
     */
    public function renderSubject(array $variables): string
    {
        $subject = $this->subject;
        foreach ($variables as $key => $value) {
            $subject = str_replace('{{' . $key . '}}', $value, $subject);
        }
        return $subject;
    }

    /**
     * Render the body with variables.
     */
    public function renderBody(array $variables): string
    {
        $body = $this->body;
        foreach ($variables as $key => $value) {
            if (is_array($value)) {
                // Handle array variables (like order_items)
                $body = str_replace('{{' . $key . '}}', implode(', ', $value), $body);
            } else {
                $body = str_replace('{{' . $key . '}}', $value, $body);
            }
        }
        return $body;
    }

    /**
     * Find template by slug.
     */
    public static function findBySlug(string $slug): ?self
    {
        return static::where('slug', $slug)->first();
    }

    /**
     * Find template by event and recipient type.
     */
    public static function findByEvent(string $event, string $recipientType): ?self
    {
        return static::where('event', $event)
            ->where('recipient_type', $recipientType)
            ->where('is_active', true)
            ->first();
    }
}
