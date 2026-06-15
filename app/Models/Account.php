<?php

namespace App\Models;

use App\Enums\AccountTypesEnum;
use Illuminate\Database\Eloquent\Builder;
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

/**
 * @property int $id
 * @property int|null $user_id
 * @property AccountTypesEnum $account_type
 * @property int|null $clan_rank
 * @property string|null $clan_title
 * @property string $username
 * @property int $rank
 * @property int $level
 * @property int $xp
 * @property Carbon|null $last_seen_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read User $user
 *
 * @method static \Database\Factories\AccountFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder|Account newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Account newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Account query()
 * @method static \Illuminate\Database\Eloquent\Builder|Account whereAccountType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Account whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Account whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Account whereLevel($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Account whereRank($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Account whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Account whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Account whereUsername($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Account whereXp($value)
 *
 * @mixin \Eloquent
 */
class Account extends Model
{
    use HasFactory;

    public function getRouteKeyName(): string
    {
        return 'username';
    }

    /**
     * Case-insensitive username substring search, shared by the account index
     * filter and the header typeahead. Uses LOWER(...) LIKE so it's portable
     * across Postgres (case-sensitive LIKE) and SQLite, and a no-op for a blank
     * term so callers can pass the raw query straight through.
     */
    public function scopeSearchUsername(Builder $query, ?string $term): Builder
    {
        $term = trim((string) $term);

        if ($term === '') {
            return $query;
        }

        return $query->whereRaw('LOWER(username) LIKE ?', ['%'.Str::lower($term).'%']);
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
     * Accounts currently sharing a recent enough position to appear on the map.
     */
    public function scopeOnMap(Builder $query): Builder
    {
        return $query
            ->whereNotNull('world_x')
            ->where('position_updated_at', '>=', now()->subMinutes((int) config('runemanager.map.visible_within_minutes')));
    }

    /**
     * Online while the most recent plugin heartbeat is within the configured
     * presence window (see config/runemanager.php). Derived rather than stored
     * so it expires on its own when the plugin stops pinging.
     */
    public function isOnline(): bool
    {
        return $this->last_seen_at !== null
            && $this->last_seen_at->gt(now()->subMinutes((int) config('runemanager.presence.online_within_minutes')));
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getUserIconAttribute(): ?string
    {
        return $this->user->icon;
    }

    public function hiscore(): HasOne
    {
        return $this->hasOne(AccountHiscore::class);
    }

    public function diary(): HasOne
    {
        return $this->hasOne(AccountDiary::class);
    }

    /**
     * Public URLs for the player 3D model uploaded by the RuneLite plugin,
     * or null when no avatar has been pushed yet. The MTL is optional (colour-
     * only exports may omit it). ?v= busts the browser cache on re-upload.
     *
     * @return array{obj_url: string, mtl_url: ?string, updated_at: string}|null
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

    public function usernameHistory(): HasMany
    {
        return $this->hasMany(UsernameHistory::class)->orderByDesc('detected_at');
    }

    public function getSkillsAttribute(): SupportCollection
    {
        $entries = $this->hiscore?->entries['skills'] ?? [];

        return Skill::all()->map(fn (Skill $skill) => [
            'name' => $skill->name,
            'slug' => $skill->slug,
            'rank' => $entries[$skill->slug]['rank'] ?? 0,
            'level' => $entries[$skill->slug]['level'] ?? 1,
            'xp' => $entries[$skill->slug]['xp'] ?? 0,
        ]);
    }

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

    public function getBossesAttribute(): SupportCollection
    {
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
    }

    /**
     * Clue scrolls in tier order — present every tier even if the player has
     * no kc yet so the UI grid is always the same shape.
     */
    public function getCluesAttribute(): SupportCollection
    {
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
    }

    public function inventory(): HasOne
    {
        return $this->hasOne(Inventory::class);
    }

    // MongoDB relationship
    public function getInventoryAttribute(): ?Inventory
    {
        return Inventory::where('account_id', $this->id)->first();
    }

    public function lootingBag(): HasOne
    {
        return $this->hasOne(LootingBag::class);
    }

    // MongoDB relationship
    public function getLootingBagAttribute(): ?LootingBag
    {
        return LootingBag::where('account_id', $this->id)->first();
    }

    public function bank(): HasOne
    {
        return $this->hasOne(Bank::class);
    }

    // MongoDB relationship
    public function getBankAttribute(): ?Bank
    {
        return Bank::where('account_id', $this->id)->first();
    }

    public function quests(): HasOne
    {
        return $this->hasOne(Quest::class);
    }

    // MongoDB relationship
    public function getQuestsAttribute(): ?Quest
    {
        return Quest::where('account_id', $this->id)->first();
    }

    public function equipment(): HasOne
    {
        return $this->hasOne(Equipment::class);
    }

    public function collectionLog(): HasOne
    {
        return $this->hasOne(CollectionLog::class);
    }

    // MongoDB relationship
    public function getCollectionLogAttribute(): ?CollectionLog
    {
        return CollectionLog::where('account_id', $this->id)->first();
    }

    /**
     * SPEC §5.2 — append-only loot history. Cross-database (Mongo) so it's a
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

    //    public function log() {
    //        return $this->hasMany(Log::class);
    //    }
    //
    //    public function logBroadcast() {
    //        return $this->hasManyThrough(Broadcast::class, Log::class);
    //    }
    //
    //    public function group() {
    //        return $this->belongsToMany(Group::class);
    //    }
}
