<?php

namespace App\Http\Resources;

use App\Models\FeedEvent;
use App\Services\Accounts\AdventurersLogCopy;
use App\Services\Accounts\FeedEventItems;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * One screenshot in the owner's gallery (Settings → Screenshots): the event
 * it belongs to, titled as the Adventurer's Log titles it, and the gated
 * `screenshots.file` URL.
 *
 * @property FeedEvent $resource
 */
class ScreenshotResource extends JsonResource
{
    /**
     * @param  array{ids: array<int, array<string, mixed>>, names: array<string, array{id: int, name: string, icon: ?string}>}  $items
     */
    public function __construct($resource, public array $items = ['ids' => [], 'names' => []])
    {
        parent::__construct($resource);
    }

    /**
     * A page of screenshots with their items looked up once, passed on
     * unresolved so it keeps `links` and `meta` for the pager.
     *
     * @param  LengthAwarePaginator<int, FeedEvent>  $page
     */
    public static function collectionWith(LengthAwarePaginator $page): AnonymousResourceCollection
    {
        $events = EloquentCollection::make($page->getCollection()->all());
        $events->loadMissing('account:id,username');
        $items = app(FeedEventItems::class)->lookup($events);

        $page->setCollection($events->map(fn (FeedEvent $event): self => new self($event, $items))->toBase());

        return self::collection($page);
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $event = $this->resource;
        $item = app(FeedEventItems::class)->item($event, $this->items);

        return [
            'id' => $event->id,
            'account' => [
                'id' => $event->account?->id,
                'username' => $event->account?->username,
            ],
            'type' => $event->type,
            'title' => AdventurersLogCopy::forFeedEvent($event, $item['name'] ?? null)['title'],
            'occurred_at' => $event->occurred_at?->toIso8601String(),
            'url' => route('screenshots.file', $event->id),
            'hidden' => $event->screenshot_hidden,
        ];
    }
}
