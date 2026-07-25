<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use App\Models\ChatMessage;

class ChatMessageSent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $message;

    public function __construct(ChatMessage $message)
    {
        $this->message = $message;
    }

    public function broadcastOn()
    {
        return [
            new Channel('chat.' . $this->message->chat_id),
            new Channel('chat-global'),
        ];
    }

    public function broadcastAs(): string
    {
        return 'message.sent';
    }

    public function broadcastWith(): array
    {
        $chat = $this->message->chat;
        $sender = $this->message->sender_type === 'admin'
            ? $this->message->sender_id
            : ($chat->user_id ?? null);

        $userName = 'Unknown';
        if ($this->message->sender_type === 'admin') {
            $admin = $chat->assignedTo;
            $userName = $admin ? $admin->name : 'Admin';
        } elseif ($chat->user) {
            $userName = $chat->user->name;
        } elseif ($chat->guest_name) {
            $userName = $chat->guest_name;
        }

        return [
            'id' => $this->message->id,
            'chat_id' => $this->message->chat_id,
            'sender_type' => $this->message->sender_type,
            'sender_id' => $sender,
            'message' => $this->message->message,
            'attachments' => $this->message->attachments,
            'created_at' => $this->message->created_at->toISOString(),
            'user_name' => $userName,
            'conversation' => [
                'id' => $chat->id,
                'status' => $chat->status,
                'user_id' => $chat->user_id,
                'guest_name' => $chat->guest_name,
            ]
        ];
    }
}
