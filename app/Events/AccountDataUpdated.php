<?php

namespace App\Events;

use App\Models\Account;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Broadcast when a plugin push changes one of an account's data sets (inventory,
 * bank, equipment, looting bag, loot). The Account Show page listens on the
 * account's channel and reloads just that prop, so an open profile updates live.
 *
 * Only signals *what* changed — the page refetches the data through its normal
 * Inertia props, keeping a single source of truth.
 */
class AccountDataUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public Account $account, public string $type) {}

    /**
     * @return array<int, PrivateChannel>
     */
    public function broadcastOn(): array
    {
        return [new PrivateChannel('account.'.$this->account->id)];
    }

    public function broadcastAs(): string
    {
        return 'DataUpdated';
    }

    /**
     * @return array<string, string>
     */
    public function broadcastWith(): array
    {
        return ['type' => $this->type];
    }
}
