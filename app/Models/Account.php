<?php

namespace App\Models;

use App\Enums\AccountTypesEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection as SupportCollection;

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
