<?php

namespace App\Models;

use App\Enums\AccountTypesEnum;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection as SupportCollection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Account extends Model
{
    use HasFactory;

    /**
     * Standalone activity slugs the OSRS hiscores expose that AREN'T bosses —
     * minigame rankings, point counters, chest opens, etc. Used to filter the
     * Bosses tab to only NPC-style entries.
     */
    private const NON_BOSS_ACTIVITY_SLUGS = [
        'lms_rank', 'pvp_arena_rank', 'soul_wars_zeal', 'rifts_closed',
        'colosseum_glory', 'collections_logged', 'league_points', 'grid_points',
        'deadman_points', 'lunar_chests', 'barrows_chests',
        'bounty_hunter_hunter', 'bounty_hunter_rogue',
        'bounty_hunter_legacy_hunter', 'bounty_hunter_legacy_rogue',
    ];

    public function getRouteKeyName(): string
    {
        return 'username';
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'account_type' => AccountTypesEnum::class,
            'avatar_uploaded_at' => 'datetime',
            'last_seen_at' => 'datetime',
            'position_updated_at' => 'datetime',
            'vitals_updated_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function hiscore(): HasOne
    {
        return $this->hasOne(AccountHiscore::class);
    }

    public function diary(): HasOne
    {
        return $this->hasOne(AccountDiary::class);
    }

    public function combatAchievement(): HasOne
    {
        return $this->hasOne(AccountCombatAchievement::class);
    }

    public function equipment(): HasOne
    {
        return $this->hasOne(Equipment::class);
    }

    public function usernameHistory(): HasMany
    {
        return $this->hasMany(UsernameHistory::class)->orderByDesc('detected_at');
    }

    /**
     * Case-insensitive username substring search, shared by the account index
     * filter and the header typeahead. Uses LOWER(...) LIKE so it's portable
     * across Postgres (case-sensitive LIKE) and SQLite, and a no-op for a blank
     * term so callers can pass the raw query straight through.
     */
    #[Scope]
    protected function searchUsername(Builder $query, ?string $term): void
    {
        $term = trim((string) $term);

        if ($term === '') {
            return;
        }

        $query->whereRaw('LOWER(username) LIKE ?', ['%'.Str::lower($term).'%']);
    }

    /**
     * Accounts whose most recent plugin heartbeat is within the presence window.
     * The query form of {@see isOnline()}, except that it includes the window's
     * start instant, where isOnline() is strict.
     */
    #[Scope]
    protected function online(Builder $query): void
    {
        $query->where('last_seen_at', '>=', self::onlineSince());
    }

    /**
     * The highest total levels first, ties broken by XP. Accounts whose
     * hiscores have not synced yet (level 0) are left out.
     */
    #[Scope]
    protected function topByLevel(Builder $query, int $limit): void
    {
        $query->where('level', '>', 0)
            ->orderByDesc('level')
            ->orderByDesc('xp')
            ->limit($limit);
    }

    /**
     * An unsaved, unclaimed roster account: username only, no user and no hash,
     * linked by the owning player on their first plugin login. The account
     * type is a placeholder until that login confirms it.
     */
    public static function rosterPlaceholder(string $username, AccountTypesEnum $type): self
    {
        $account = new self;
        $account->username = $username;
        $account->user_id = null;
        $account->account_hash = null;
        $account->account_type = $type;
        $account->rank = 0;
        $account->level = 0;
        $account->xp = 0;

        return $account;
    }

    /**
     * Accounts currently sharing a recent enough position to appear on the map.
     */
    #[Scope]
    protected function onMap(Builder $query): void
    {
        $query
            ->whereNotNull('world_x')
            ->where('position_updated_at', '>=', now()->subMinutes((int) config('runemanager.map.visible_within_minutes')));
    }

    /**
     * This account's place among the instance's accounts by total XP — the
     * "RuneManager rank" on Account Show, as distinct from its official hiscores
     * rank. Accounts on equal XP share a position, the usual competition order.
     *
     * Only accounts with XP take part. One whose hiscores have not synced yet
     * carries 0, and ranking it would claim last place and inflate every other
     * account's "of N"; it is unranked instead, and left out of the total.
     *
     * Two plain counts are enough: an instance's accounts table is small, and
     * this is only ever computed for the one account being viewed, never across
     * a list.
     *
     * @return array{position: int, total: int}|null
     */
    public function instanceRank(): ?array
    {
        if (($this->xp ?? 0) <= 0) {
            return null;
        }

        return [
            'position' => self::query()->where('xp', '>', $this->xp)->count() + 1,
            'total' => self::query()->where('xp', '>', 0)->count(),
        ];
    }

    /**
     * Live status-orb values (HP/prayer current+max, run energy, special attack),
     * or null until the plugin has pushed them. Drives the Account Show orbs.
     *
     * @return array{hitpoints: int, hitpoints_max: int, prayer: int, prayer_max: int, run_energy: int, special_attack: int}|null
     */
    public function vitalsPayload(): ?array
    {
        if ($this->vitals_updated_at === null) {
            return null;
        }

        return [
            'hitpoints' => (int) $this->hitpoints,
            'hitpoints_max' => (int) $this->hitpoints_max,
            'prayer' => (int) $this->prayer,
            'prayer_max' => (int) $this->prayer_max,
            'run_energy' => (int) $this->run_energy,
            'special_attack' => (int) $this->special_attack,
        ];
    }

    /**
     * On the Live Map while the most recent position push is within the
     * configured window (see config/runemanager.php). Derived, like online
     * status, so it disappears on its own when sharing stops or the player
     * logs out.
     */
    public function isOnMap(): bool
    {
        return $this->world_x !== null
            && $this->position_updated_at !== null
            && $this->position_updated_at->gt(now()->subMinutes((int) config('runemanager.map.visible_within_minutes')));
    }

    /**
     * Online while the most recent plugin heartbeat is within the configured
     * presence window (see config/runemanager.php). Derived rather than stored
     * so it expires on its own when the plugin stops pinging.
     */
    public function isOnline(): bool
    {
        return $this->last_seen_at !== null
            && $this->last_seen_at->gt(self::onlineSince());
    }

    /** The start of the presence window (see config/runemanager.php). */
    private static function onlineSince(): Carbon
    {
        return now()->subMinutes((int) config('runemanager.presence.online_within_minutes'));
    }

    /**
     * Public URLs for the player 3D model uploaded by the RuneLite plugin,
     * or null when no avatar has been pushed yet. The MTL is optional (colour-
     * only exports may omit it). ?v= busts the browser cache on re-upload.
     *
     * @return array{obj_url: string, mtl_url: ?string, npc_obj_url: ?string, npc_mtl_url: ?string, updated_at: string}|null
     */
    public function avatarPayload(): ?array
    {
        if (! $this->avatar_uploaded_at) {
            return null;
        }

        $disk = Storage::disk('public');
        $directory = "avatars/{$this->id}";
        $version = $this->avatar_uploaded_at->timestamp;

        return [
            'obj_url' => $disk->url("{$directory}/avatar.obj")."?v={$version}",
            'mtl_url' => $disk->exists("{$directory}/avatar.mtl")
                ? $disk->url("{$directory}/avatar.mtl")."?v={$version}"
                : null,
            // The opponent model, present only when the last snapshot was mid-combat.
            'npc_obj_url' => $disk->exists("{$directory}/avatar_npc.obj")
                ? $disk->url("{$directory}/avatar_npc.obj")."?v={$version}"
                : null,
            'npc_mtl_url' => $disk->exists("{$directory}/avatar_npc.mtl")
                ? $disk->url("{$directory}/avatar_npc.mtl")."?v={$version}"
                : null,
            'updated_at' => $this->avatar_uploaded_at->toIso8601String(),
        ];
    }

    protected function userIcon(): Attribute
    {
        return Attribute::make(get: fn (): ?string => $this->user->icon);
    }

    /**
     * Every skill in display order with this account's rank/level/xp, defaulting
     * the ones the hiscores have no entry for.
     */
    protected function skills(): Attribute
    {
        return Attribute::make(get: function (): SupportCollection {
            $entries = $this->hiscore?->entries['skills'] ?? [];

            return Skill::all()->map(fn (Skill $skill) => [
                'name' => $skill->name,
                'slug' => $skill->slug,
                'rank' => $entries[$skill->slug]['rank'] ?? 0,
                'level' => $entries[$skill->slug]['level'] ?? 1,
                'xp' => $entries[$skill->slug]['xp'] ?? 0,
            ]);
        });
    }

    protected function bosses(): Attribute
    {
        return Attribute::make(get: function (): SupportCollection {
            $entries = $this->hiscore?->entries['activities'] ?? [];

            return collect($entries)
                ->reject(fn ($_, string $slug) => str_starts_with($slug, 'clue_scrolls_')
                    || in_array($slug, self::NON_BOSS_ACTIVITY_SLUGS, true))
                ->map(fn ($entry, string $slug) => [
                    'name' => Str::title(str_replace('_', ' ', $slug)),
                    'slug' => $slug,
                    'rank' => $entry['rank'] ?? 0,
                    'score' => $entry['score'] ?? 0,
                ])
                ->values();
        });
    }

    /**
     * Clue scrolls in tier order — present every tier even if the player has
     * no kc yet so the UI grid is always the same shape.
     */
    protected function clues(): Attribute
    {
        return Attribute::make(get: function (): SupportCollection {
            $entries = $this->hiscore?->entries['activities'] ?? [];
            $tiers = ['beginner', 'easy', 'medium', 'hard', 'elite', 'master', 'all'];

            return collect($tiers)->map(function (string $tier) use ($entries) {
                $slug = "clue_scrolls_{$tier}";

                return [
                    'name' => Str::title($tier),
                    'slug' => $slug,
                    'rank' => $entries[$slug]['rank'] ?? 0,
                    'score' => $entries[$slug]['score'] ?? 0,
                ];
            });
        });
    }

    /*
     * The snapshot documents below live in MongoDB, so they can't be Eloquent
     * relations of this PostgreSQL model; each is a direct lookup instead. The
     * lookups aren't cached on the instance, so a read after a push is fresh.
     */

    protected function inventory(): Attribute
    {
        return Attribute::make(
            get: fn (): ?Inventory => Inventory::where('account_id', $this->id)->first(),
        )->withoutObjectCaching();
    }

    protected function lootingBag(): Attribute
    {
        return Attribute::make(
            get: fn (): ?LootingBag => LootingBag::where('account_id', $this->id)->first(),
        )->withoutObjectCaching();
    }

    protected function bank(): Attribute
    {
        return Attribute::make(
            get: fn (): ?Bank => Bank::where('account_id', $this->id)->first(),
        )->withoutObjectCaching();
    }

    protected function quests(): Attribute
    {
        return Attribute::make(
            get: fn (): ?Quest => Quest::where('account_id', $this->id)->first(),
        )->withoutObjectCaching();
    }

    protected function collectionLog(): Attribute
    {
        return Attribute::make(
            get: fn (): ?CollectionLog => CollectionLog::where('account_id', $this->id)->first(),
        )->withoutObjectCaching();
    }

    /**
     * Append-only loot history. Cross-database (Mongo) so it's a
     * direct query rather than an Eloquent relation. Newest-first because every
     * read path (recent drops panel, future live feed) wants chronological order.
     *
     * @return Collection<int, Loot>
     */
    public function recentLoot(int $limit = 25): Collection
    {
        return Loot::where('account_id', $this->id)
            ->orderBy('killed_at', 'desc')
            ->limit($limit)
            ->get();
    }

    public function latestLootKilledAt(): ?Carbon
    {
        // Selective `first(['killed_at'])` combined with orderByDesc trips the
        // Mongo driver's enum serialisation; pull the whole doc instead.
        $row = Loot::where('account_id', $this->id)
            ->orderBy('killed_at', 'desc')
            ->first();

        return $row?->killed_at;
    }
}
