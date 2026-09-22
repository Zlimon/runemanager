<?php

namespace App\Http\Resources;

use App\Models\Item;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Resources\Json\JsonResource;

class AccountResource extends JsonResource
{
    /**
     * Item rows keyed by osrs id when a caller resolved a whole page's icons in
     * one query; null means this row looks its own up. Set after construction
     * rather than through the constructor, because Collection::mapInto() — how
     * a resource collection builds its rows — passes the key as the second
     * constructor argument.
     *
     * @var array<int, array<string, mixed>>|null
     */
    public ?array $iconsByOsrsId = null;

    /**
     * @param  array<int, array<string, mixed>>  $iconsByOsrsId
     */
    public function withIcons(array $iconsByOsrsId): static
    {
        $this->iconsByOsrsId = $iconsByOsrsId;

        return $this;
    }

    /**
     * Resolve every row's avatar icon in one Mongo query rather than one per
     * account: `icon` reaches Item::lookupByOsrsId through the owning user, so
     * a 16-row account page used to cost 16 aggregations. Callers keep eager
     * loading `user` — this only collapses the Mongo side.
     */
    public static function collection($resource): AnonymousResourceCollection
    {
        $collection = parent::collection($resource);

        $icons = Item::lookupByOsrsIds(
            $collection->collection
                ->map(fn (self $account): ?int => $account->resource?->user?->icon_id)
                ->filter()
                ->unique()
                ->values()
                ->all(),
        );

        $collection->collection->each(function (self $account) use ($icons): void {
            $account->iconsByOsrsId = $icons;
        });

        return $collection;
    }

    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'icon' => $this->resolveIcon(),
            'account_type' => $this->account_type,
            'clan_rank' => $this->clan_rank,
            'clan_title' => $this->clan_title,
            'username' => $this->username,
            'rank' => $this->rank,
            'level' => $this->level,
            'xp' => $this->xp,
            'online' => $this->isOnline(),
            'activity' => $this->activity,
            'activity_icon' => $this->activity_icon,
            'location' => $this->location,
            'world' => $this->world_number,
            'skills' => $this->whenAppended('skills', function () {
                return $this->skills;
            }),
            'bosses' => $this->whenAppended('bosses', function () {
                return $this->bosses;
            }),
            'clues' => $this->whenAppended('clues', function () {
                return $this->clues;
            }),
            'equipment' => $this->whenLoaded('equipment', function () {
                return $this->equipment ? (new EquipmentResource($this->equipment))->resolve() : null;
            }),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }

    /**
     * The batch map when one was handed over ({@see self::collection()}, or the
     * leaderboards, which wrap each row on its own), else this account's own
     * single lookup.
     */
    private function resolveIcon(): ?string
    {
        if ($this->iconsByOsrsId === null) {
            return $this->userIcon;
        }

        $iconId = $this->resource?->user?->icon_id;

        return $iconId === null ? null : ($this->iconsByOsrsId[(int) $iconId]['icon'] ?? null);
    }
}
