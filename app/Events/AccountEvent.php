<?php

namespace App\Events;

use App\Account;
use App\Broadcast;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AccountEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $broadcast;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(Account $account, Broadcast $broadcast)
    {
        $this->broadcast = $broadcast::with('log')->with('log.category')->whereHas('log', function ($query) use($account) {
            return $query->where('account_id', '=', $account->id);
        })->where('type', 'event')->orderByDesc('id')->first();
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new Channel('account');
    }
}
