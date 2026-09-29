<?php

namespace App\Http\Resources;

use App\Models\FeedEvent;
use App\Models\Item;
use App\Models\User;
use App\Services\Map\EventMap;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\ResourceCollection;

/**
 * Plain wire shape for live feed events. Account is included slim — the
 * UI links back to the account page, so we just need username + type for
 * the ironman badge. Loot-drop items are hydrated with their name + icon so
 * the feed can name the drop.
 *
 * `payload.position` (where it happened) is only kept for viewers allowed to
 * see it ({@see EventMap::positionVisibleTo()}). The viewer is the request's
 * user, or nobody for an `anonymous` resource (the public broadcast, which is
 * resolved inside the plugin's own push request). `screenshot_url` follows
 * the same viewer ({@see FeedEvent::screenshotVisibleTo()}) and points at
 * `screenshots.file`, never at the file itself.
 */
class FeedEventResource extends JsonResource
{
    /**
     * @param  array<int, array<string, mixed>>  $itemsById  Item rows keyed by osrs id (batch hydration).
     * @param  bool  $anonymous  Resolve for nobody in particular rather than the request's user.
     */
    public function __construct($resource, public array $itemsById = [], public bool $anonymous = false)
    {
        parent::__construct($resource);
    }

    /**
     * Hydrate loot-item names/icons across a whole feed page in one Mongo query.
     * The slim account (with `user_id`, which drives the owner's pin/delete
     * controls) is eager-loaded here, so callers only hand over the events.
     *
     * @param  EloquentCollection<int, FeedEvent>  $events
     */
    public static function collectionWith(EloquentCollection $events): ResourceCollection
    {
        $events->loadMissing('account:id,user_id,username,account_type,show_events_on_map');

        $itemIds = [];
        foreach ($events as $event) {
            if ($event->type === FeedEvent::TYPE_LOOT_DROP) {
                foreach ($event->payload['items'] ?? [] as $item) {
                    $itemIds[] = (int) ($item['id'] ?? 0);
                }
            }
        }

        $itemsById = Item::lookupByOsrsIds(array_values(array_unique($itemIds)));

        return self::collection(
            collect($events)->map(fn ($event) => new self($event, $itemsById)),
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $viewer = $this->anonymous ? null : $request->user();

        return [
            'id' => $this->id,
            'type' => $this->type,
            'payload' => $this->withoutHiddenPosition($this->resolvePayload(), $viewer),
            'screenshot_url' => $this->resource->screenshotVisibleTo($viewer)
                ? route('screenshots.file', $this->id)
                : null,
            // Only the owner and admins learn a screenshot is hidden (to badge it).
            ...($this->resource->screenshotManagedBy($viewer) ? [
                'screenshot_hidden' => $this->screenshot_path !== null && $this->screenshot_hidden,
            ] : []),
            'pinned' => $this->pinned_at !== null,
            // Whether the Adventurer's Log shows it, so a live page knows to refresh its log.
            'milestone' => $this->resource->isLogMilestone(),
            'occurred_at' => $this->occurred_at?->toIso8601String(),
            'account' => [
                'user_id' => $this->account?->user_id,
                'username' => $this->account?->username,
                'account_type' => $this->account?->account_type?->value
                    ?? $this->account?->account_type,
            ],
        ];
    }

    /**
     * Drop the event's position unless this viewer may see it.
     *
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    private function withoutHiddenPosition(array $payload, ?User $viewer): array
    {
        if (array_key_exists('position', $payload) && ! EventMap::positionVisibleTo($viewer, $this->account)) {
            unset($payload['position']);
        }

        return $payload;
    }

    /**
     * Enrich loot-drop items with name + icon; other payloads pass through.
     *
     * @return array<string, mixed>
     */
    private function resolvePayload(): array
    {
        $payload = $this->payload;

        if ($this->type !== FeedEvent::TYPE_LOOT_DROP || empty($payload['items'])) {
            return $payload;
        }

        // Batch map when provided (feed page), else look up this event's items
        // directly (single broadcast).
        $itemsById = $this->itemsById
            ?: Item::lookupByOsrsIds(array_column($payload['items'], 'id'));

        $payload['items'] = array_map(function (array $slot) use ($itemsById): array {
            $details = $itemsById[(int) $slot['id']] ?? null;

            return [
                'id' => (int) $slot['id'],
                'quantity' => (int) $slot['quantity'],
                'name' => $details['name'] ?? null,
                'icon' => $details['icon'] ?? null,
                'examine' => $details['examine'] ?? null,
            ];
        }, $payload['items']);

        return $payload;
    }
}
