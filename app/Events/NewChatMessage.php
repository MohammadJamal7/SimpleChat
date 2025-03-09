<?php

namespace App\Events;

use App\Models\Message;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NewChatMessage implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * The message instance.
     *
     * @var Message
     */
    public $message;

    /**
     * Create a new event instance.
     *
     * @param  \App\Models\Message  $message
     * @return void
     */
    public function __construct(Message $message)
    {
        $this->message = $message; // Store the message instance
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return Channel|array
     */
    public function broadcastOn()
    {
        return new Channel('chat'); // Broadcasting on the 'chat' channel
    }

    /**
     * Get the broadcast data for the event.
     *
     * @return array
     */
    public function broadcastWith()
    {
        // Ensure only relevant data is broadcasted
        return [
            'message' => $this->message->message, // The actual message text
            'user_id' => $this->message->user_id, // The ID of the user who sent the message
            'user_name' => $this->message->user->name, // User's name (if you want to send it)
            'created_at' => $this->message->created_at->toDateTimeString(), // Message timestamp

//            'message'=>'test',
//            'user_id' =>'1',
//            'user_name'=>'Mohamad',

        ];
    }
}
