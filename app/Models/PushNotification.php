<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PushNotification extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'message',
        'image',
        'target_type',
        'target_id',
        'action_url',
        'status',
        'scheduled_at',
        'sent_at',
        'recipients_count',
        'delivered_count',
        'clicked_count',
        'created_by',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'sent_at' => 'datetime',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    public function scopeScheduled($query)
    {
        return $query->where('status', 'scheduled');
    }

    public function scopeSent($query)
    {
        return $query->where('status', 'sent');
    }

    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    public function getStatusBadgeAttribute()
    {
        $badges = [
            'draft' => '<span class="badge bg-secondary">Draft</span>',
            'scheduled' => '<span class="badge bg-warning">Scheduled</span>',
            'sent' => '<span class="badge bg-success">Sent</span>',
            'failed' => '<span class="badge bg-danger">Failed</span>',
        ];
        return $badges[$this->status] ?? $badges['draft'];
    }

    public function getTargetTypeLabelAttribute()
    {
        $labels = [
            'all' => 'All Users',
            'specific_user' => 'Specific User',
            'user_group' => 'User Group',
            'product' => 'Product',
            'category' => 'Category',
        ];
        return $labels[$this->target_type] ?? 'All Users';
    }
}
