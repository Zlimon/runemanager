<?php

namespace App\Http\Resources;

use App\Services\Accounts\RecentEvent;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Wire shape of one Adventurer's Log row. Feed rows also carry what the
 * owner needs to pin them and the screenshot (`screenshot_url` null when the
 * viewer may not see it; `screenshot_hidden` only for the owner and admins). `position` is where a feed
 * event happened, for the "Show on map" link; null when unknown and always
 * null for Wise Old Man rows.
 *
 * @property RecentEvent $resource
 */
class RecentEventResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $event = $this->resource;

        return [
            'id' => $event->id(),
            'source' => $event->source,
            'type' => $event->type,
            'title' => $event->title,
            'body' => $event->body,
            'occurred_at' => $event->occurredAt->toIso8601String(),
            'approximate' => $event->approximate,
            'icon' => $event->icon,
            'position' => $event->position,
            ...($event->source === RecentEvent::SOURCE_FEED ? [
                'feed_event_id' => $event->feedEventId,
                'pinned' => (bool) $event->pinned,
                'screenshot_url' => $event->screenshotUrl,
                ...($event->screenshotHidden !== null ? ['screenshot_hidden' => $event->screenshotHidden] : []),
            ] : []),
        ];
    }

    /**
     * The `{data, next_cursor}` envelope of one RecentEvents::page(), shared by
     * the Log's first page and the accounts.events JSON.
     *
     * @param  array{events: list<RecentEvent>, next_cursor: ?string}  $page
     * @return array{data: list<array<string, mixed>>, next_cursor: ?string}
     */
    public static function page(array $page, ?Request $request = null): array
    {
        return [
            'data' => static::collection($page['events'])->resolve($request),
            'next_cursor' => $page['next_cursor'],
        ];
    }
}
