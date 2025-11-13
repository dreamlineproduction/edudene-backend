<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use App\Models\Message;

// CRITICAL: The event must implement ShouldBroadcast to be picked up by the broadcast system.
class NewChatMessage implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $message;

    /**
     * Create a new event instance.
     *
     * We only need the Message model since it contains the chat_id.
     */
    public function __construct(Message $message)
    {
       $this->message = $message->load('sender'); 
    }

   

    
    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel
     */
    public function broadcastOn(): Channel
    {
        return new PrivateChannel('chat.' . $this->message->chat_id);
    }

    /**
     * Broadcast event name (optional, for clarity).
     */
    public function broadcastAs(): string
    {
        return 'message.sent';
    }

    

    /**
     * The data that should be broadcasted.
     *
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        // Return the message data, which includes the loaded 'sender' relationship
        return [
            'message' => $this->message->toArray(),
        ];
    }    
}