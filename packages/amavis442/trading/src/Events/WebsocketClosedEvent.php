<?php

namespace Amavis442\Trading\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Support\Collection;

class WebsocketClosedEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    protected $code;
    protected $reason;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($code, $reason)
    {
        $this->code = $code;
        $this->reason = $reason;
    }

    public function getCode()
    {
        return $this->code;
    }

    public function getReason()
    {
        return $this->code;
    }


    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new PrivateChannel('channel-name');
    }
}
