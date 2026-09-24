<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;

/**
 * Broadcast when any member's plugin pushes the shared group bank,
 * so an open Group Bank page reloads its data live.
 *
 * Private, like the page it drives: only an authenticated user may subscribe
 * (see routes/channels.php). The event carries no payload — the page refetches
 * through its Inertia props — so it is purely a "something changed" nudge.
 */
class GroupBankUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets;

    /**
     * @return array<int, Channel>
     */
    public function broadcastOn(): array
    {
        return [new PrivateChannel('group-bank')];
    }

    public function broadcastAs(): string
    {
        return 'GroupBankUpdated';
    }
}
