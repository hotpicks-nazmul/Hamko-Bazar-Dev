<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class UserTyping implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $conversationId;
    public $isTyping;
    public $userType; // 'user' or 'admin'

    /**
     * Create a new event instance.
     */
    public function __construct($conversationId, $isTyping, $userType = 'user')
    {
        $this->conversationId = $conversationId;
        $this->isTyping = $isTyping;
        $this->userType = $userType;
    }

    /**
     * Get the channels the event should broadcast on.
     */
    public function broadcastOn()
    {
        return new Channel('chat.' . $this->conversationId);
    }

    /**
     * Get the event name to broadcast.
     */
    public function broadcastAs()
    {
        return 'user.typing';
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith()
    {
        return [
            'conversation_id' => $this->conversationId,
            'is_typing' => $this->isTyping,
            'who' => $this->userType,
        ];
    }
}
