<?php

namespace App\Models;

use App\Enums\AccountTypesEnum;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection as SupportCollection;
use Illuminate\Support\Str;

/**
 * @property int $id
 * @property int $user_id
 * @property AccountTypesEnum $account_type
 * @property string $username
 * @property int $rank
 * @property int $level
 * @property int $xp
 * @property int $online
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
 * @method static \Illuminate\Database\Eloquent\Builder|Account whereOnline($value)
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
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'account_type' => AccountTypesEnum::class,
        ];
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
