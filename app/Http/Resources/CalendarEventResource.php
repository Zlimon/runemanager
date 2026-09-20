<?php

namespace App\Http\Resources;

use App\Models\CalendarEvent;
use App\Models\CalendarEventRsvp;
use App\Models\Item;
use App\Support\Instance;
use App\Support\Roles;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Wire shape for SPEC §10 calendar events. Permissions are resolved here rather
 * than in Vue, and the event's optional OSRS item icon is hydrated from the
 * static MongoDB catalogue.
 */
class CalendarEventResource extends JsonResource
{
    /**
     * @param  array<int, array<string, mixed>>  $itemsById  Item rows keyed by osrs id (batch hydration).
     * @param  list<string>|null  $highlightColors  Resolved once per collection; null = look up on demand.
     * @param  bool|null  $viewerCanRsvp  Resolved once per collection; null = look up on demand.
     */
    public function __construct(
        $resource,
        private array $itemsById = [],
        private ?array $highlightColors = null,
        private ?bool $viewerCanRsvp = null,
    ) {
        parent::__construct($resource);
    }

    /**
     * Resolve everything shared by the whole collection exactly once: the item
     * catalogue lookup (one Mongo aggregation), the highlight palette (one
     * settings read) and the viewer's RSVP eligibility. Doing it per event would
     * be an N+1 across the 42-day grid. Relations are eager-loaded here too, so
     * callers only have to hand over the models.
     *
     * @param  iterable<int, CalendarEvent>  $resource
     */
    public static function collection($resource): AnonymousResourceCollection
    {
        $events = $resource instanceof EloquentCollection
            ? $resource
            : EloquentCollection::make(collect($resource)->all());

        $events->loadMissing(['user:id,name', 'rsvps.account:id,username,account_type']);

        $itemsById = Item::lookupByOsrsIds(
            $events->pluck('item_id')->filter()->unique()->values()->all(),
        );
        $highlightColors = Instance::calendarHighlightColors();
        $viewerCanRsvp = self::viewerCanRsvp(request());

        return parent::collection($events->map(
            fn (CalendarEvent $event): self => new self($event, $itemsById, $highlightColors, $viewerCanRsvp),
        ));
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $highlightColors = $this->highlightColors ?? Instance::calendarHighlightColors();

        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'event_type' => $this->event_type->value,
            'event_type_label' => $this->event_type->label(),
            'color' => $this->color,
            'highlighted' => $this->color !== null && in_array($this->color, $highlightColors, true),
            'starts_at' => $this->starts_at?->toIso8601String(),
            'ends_at' => $this->ends_at?->toIso8601String(),
            'created_by' => [
                'id' => $this->user?->id,
                'name' => $this->user?->name,
            ],
            'item' => $this->resolveItem(),
            'rsvps' => $this->resolveRsvps(),
            'can' => [
                'update' => $this->viewerManages($request),
                'delete' => $this->viewerManages($request),
                // Attendance closes once the event is over, so the client stops
                // offering it rather than discovering the 403 on click.
                'rsvp' => ($this->viewerCanRsvp ?? self::viewerCanRsvp($request))
                    && ! $this->resource->hasPassed(),
            ],
        ];
    }

    /**
     * The event's icon item, or null when none is attached.
     *
     * @return array{id: int, name: string, icon: string|null}|null
     */
    private function resolveItem(): ?array
    {
        if ($this->item_id === null) {
            return null;
        }

        $itemId = (int) $this->item_id;

        // Batch map when provided (any collection), else look this one up.
        $itemsById = $this->itemsById ?: Item::lookupByOsrsIds([$itemId]);
        $details = $itemsById[$itemId] ?? null;

        if ($details === null) {
            return null;
        }

        return [
            'id' => $itemId,
            'name' => (string) ($details['name'] ?? ''),
            'icon' => $details['icon'] ?? null,
        ];
    }

    /**
     * @return list<array{account_id: int, username: string|null, account_type: string|null}>
     */
    private function resolveRsvps(): array
    {
        return $this->rsvps
            ->map(fn (CalendarEventRsvp $rsvp): array => [
                'account_id' => (int) $rsvp->account_id,
                'username' => $rsvp->account?->username,
                'account_type' => $rsvp->account?->account_type?->value
                    ?? $rsvp->account?->account_type,
            ])
            ->values()
            ->all();
    }

    /**
     * SPEC §10.2 — the creator may manage their own event, and anyone holding
     * `manage calendar` may manage everyone's.
     */
    private function viewerManages(Request $request): bool
    {
        $user = $request->user();

        return $user !== null
            && ($user->id === $this->user_id || $user->can(Roles::MANAGE_CALENDAR));
    }

    /**
     * RSVPs are per account, so only a member with an account can attend.
     * (`account()` is the User's has-many, singular name notwithstanding.)
     */
    private static function viewerCanRsvp(Request $request): bool
    {
        return (bool) $request->user()?->account()->exists();
    }
}
