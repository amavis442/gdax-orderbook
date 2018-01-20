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

class Position
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    protected $position;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($pair, $side, $size, $price, $status)
    {
        $this->position = new Collection([
                                             'pair'       => $pair,
                                             'side'       => $side,
                                             'size'       => $size,
                                             'price'      => $price,
                                             'status'     => $status,
                                             'created_at' => \Carbon\Carbon::now('Europe/Amsterdam'),
                                         ]);
    }

    public function getPosition()
    {
        return $this->position;
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
