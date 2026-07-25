<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Chat extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'session_id',
        'status',
        'assigned_to',
        'guest_name',
        'guest_phone',
        'user_is_typing',
        'admin_is_typing',
        'typing_expires_at',
    ];

    /**
     * Get the user.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the assigned admin.
     */
    public function assignedTo()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    /**
     * Get the messages.
     */
    public function messages()
    {
        return $this->hasMany(ChatMessage::class);
    }

    /**
     * Get unread messages count.
     */
    public function getUnreadCountAttribute()
    {
        return $this->messages()->where('is_read', false)->count();
    }

    /**
     * Get last message.
     */
    public function getLastMessageAttribute()
    {
        return $this->messages()->latest()->first();
    }

    /**
     * Scope for open chats.
     */
    public function scopeOpen($query)
    {
        return $query->where('status', 'open');
    }

    /**
     * Scope for pending chats.
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Close the chat.
     */
    public function close(): void
    {
        $this->update(['status' => 'closed']);
    }

    /**
     * Assign to admin.
     */
    public function assignTo($adminId): void
    {
        $this->update(['assigned_to' => $adminId, 'status' => 'open']);
    }

    /**
     * Mark all messages as read.
     */
    public function markAsRead(): void
    {
        $this->messages()->where('is_read', false)->update(['is_read' => true]);
    }

    /**
     * Add message.
     */
    public function addMessage(string $message, string $senderType, $senderId = null): ChatMessage
    {
        return $this->messages()->create([
            'sender_type' => $senderType,
            'sender_id' => $senderId,
            'message' => $message,
            'is_read' => false,
        ]);
    }
}
