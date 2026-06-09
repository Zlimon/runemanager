<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;

/**
 * SPEC §5.2 — broadcast when any member's plugin pushes the shared group bank,
 * so an open Group Bank page reloads its data live. Public channel (the shared
 * bank view is visible to instance members); carries no payload — the page
 * refetches through its Inertia props.
 */
class GroupBankUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets;

    /**
     * @return array<int, Channel>
     */
    public function broadcastOn(): array
    {
        return [new Channel('group-bank')];
    }

    public function broadcastAs(): string
    {
        return 'GroupBankUpdated';
    }
}
