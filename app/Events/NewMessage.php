<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class NewMessage implements ShouldBroadcast 
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $message;
    /**
     * Create a new event instance.
     */
    public function __construct($message)
    {

        $this->message = $message->load('user');
    }

    public function broadcastOn()
    {
        return new PresenceChannel('chat-room.' . $this->message->chat_room_id);
    }

    public function broadcastWith()
    {
        return [
            'id' => $this->message->id,
            'content' => $this->message->content,
            'chat_room_id' => $this->message->chat_room_id,
            'user_id' => $this->message->user->id,
            'user_name' => $this->message->user->name,
            'created_at' => $this->message->created_at->toIso8601String(),
        ];
    }
}
