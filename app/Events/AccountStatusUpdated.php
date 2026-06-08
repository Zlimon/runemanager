<?php

namespace App\Events;

use App\Models\Account;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Broadcast when an account's in-game activity changes (Discord-plugin style:
 * "Fishing", "Fighting Vorkath", "Idle"). Goes to both the account's own channel
 * (the Account Show header) and the shared map channel (the Live Map marker
 * card), carrying the value inline.
 */
class AccountStatusUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public Account $account) {}

    /**
     * @return array<int, PrivateChannel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('account.'.$this->account->id),
            new PrivateChannel('map'),
        ];
    }

    public function broadcastAs(): string
    {
        return 'StatusUpdated';
    }

    /**
     * @return array<string, ?string>
     */
    public function broadcastWith(): array
    {
        return [
            'username' => $this->account->username,
            'activity' => $this->account->activity,
            'location' => $this->account->location,
        ];
    }
}
