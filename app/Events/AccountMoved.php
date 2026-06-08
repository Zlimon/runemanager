<?php

namespace App\Events;

use App\Models\Account;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Broadcast when an account pushes a new position, so the Live Map can move its
 * marker in real time. Broadcast immediately (ShouldBroadcastNow) — positions
 * are time-sensitive and we don't want them sitting on a queue.
 *
 * The payload is intentionally light (no avatar) since it fires often; markers
 * carry their icon from the initial page load / the account_type.
 */
class AccountMoved implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public Account $account) {}

    /**
     * @return array<int, PrivateChannel>
     */
    public function broadcastOn(): array
    {
        return [new PrivateChannel('map')];
    }

    public function broadcastAs(): string
    {
        return 'AccountMoved';
    }

    /**
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        return [
            'username' => $this->account->username,
            'account_type' => $this->account->account_type->value,
            'x' => $this->account->world_x,
            'y' => $this->account->world_y,
            'plane' => $this->account->world_plane,
        ];
    }
}
