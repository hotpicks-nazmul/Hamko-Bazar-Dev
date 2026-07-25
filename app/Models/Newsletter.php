<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Newsletter extends Model
{
    use HasFactory;

    protected $fillable = [
        'subject',
        'content',
        'status',
        'sent_at',
        'recipients_count',
        'scheduled_at',
        'recipients_type',
        'created_by',
    ];

    protected $casts = [
        'sent_at' => 'datetime',
        'scheduled_at' => 'datetime',
        'recipients_count' => 'integer',
    ];

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($newsletter) {
            if (empty($newsletter->status)) {
                $newsletter->status = 'draft';
            }
        });
    }

    /**
     * Get the user who created this newsletter.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Check if the newsletter has been sent.
     */
    public function isSent(): bool
    {
        return $this->status === 'sent' && $this->sent_at !== null;
    }

    /**
     * Check if the newsletter is a draft.
     */
    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }

    /**
     * Check if the newsletter is scheduled.
     */
    public function isScheduled(): bool
    {
        return $this->status === 'scheduled' && $this->scheduled_at !== null;
    }

    /**
     * Get the status badge color.
     */
    public function getStatusBadgeAttribute(): string
    {
        return match($this->status) {
            'sent' => 'success',
            'scheduled' => 'warning',
            'draft' => 'secondary',
            'failed' => 'danger',
            default => 'secondary',
        };
    }

    /**
     * Get the plain text content (stripped from HTML).
     */
    public function getPlainContentAttribute(): string
    {
        return strip_tags($this->content);
    }

    /**
     * Get a snippet of the content.
     */
    public function getContentSnippetAttribute(int $length = 100): string
    {
        $plain = $this->plain_content;
        if (strlen($plain) > $length) {
            return substr($plain, 0, $length) . '...';
        }
        return $plain;
    }
}
