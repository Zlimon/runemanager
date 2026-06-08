<?php

namespace App\Events;

use App\Models\Account;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Broadcast when the plugin pushes new status-orb values, so an open Account
 * Show page updates its HP/prayer/run/special orbs in real time. Unlike the
 * snapshot data sets, the values ride along in the payload — they change often
 * and we don't want a reload per tick.
 */
class AccountVitalsUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public Account $account) {}

    /**
     * @return array<int, PrivateChannel>
     */
    public function broadcastOn(): array
    {
        return [new PrivateChannel('account.'.$this->account->id)];
    }

    public function broadcastAs(): string
    {
        return 'VitalsUpdated';
    }

    /**
     * @return array<string, int>
     */
    public function broadcastWith(): array
    {
        return $this->account->vitalsPayload() ?? [];
    }
}
