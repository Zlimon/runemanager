<?php

namespace App\Events;

use App\Http\Resources\FeedEventResource;
use App\Models\FeedEvent;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Broadcast when a new live-feed event is recorded (SPEC §8.3), so connected
 * browsers prepend it without polling. The feed is public, so this rides a
 * public channel — unauthenticated visitors receive it too.
 */
class FeedEventCreated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public FeedEvent $feedEvent) {}

    /**
     * @return array<int, Channel>
     */
    public function broadcastOn(): array
    {
        return [new Channel('feed')];
    }

    public function broadcastAs(): string
    {
        return 'FeedEventCreated';
    }

    /**
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        return (new FeedEventResource($this->feedEvent->loadMissing('account')))->resolve();
    }
}
