<?php

namespace App\Events;

use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class DuplicateFundWarningEvent implements ShouldBroadcast
{
    use Dispatchable, SerializesModels;

    public string $fundName;
    public string $managerName;
    public $result;

    /**
     * Create a new event instance.
     */
    public function __construct($fundName, $managerName, $result)
    {
        $this->fundName = $fundName;
        $this->managerName = $managerName;
        $this->result = $result;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array
     */
    public function broadcastOn()
    {
        return [
            new PrivateChannel('channel-name'),
        ];
    }
}
