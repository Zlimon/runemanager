<?php

namespace App\Models;

use App\Enums\AccountTypesEnum;
use App\Http\Resources\SkillResource;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 *
 *
 * @property int $id
 * @property int $user_id
 * @property AccountTypesEnum $account_type
 * @property string $username
 * @property int $rank
 * @property int $level
 * @property int $xp
 * @property int $online
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User $user
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

    public function getUserIconAttribute(): string
    {
        return $this->user->icon ?? '';
    }

    public function getSkillsAttribute()
    {
        return Skill::all()->map(function ($skill) {
            $skills = (new SkillResource($this->skill($skill)->first()))->resolve();
            $skills['name'] = $skill['name'];
            $skills['slug'] = $skill['slug'];

            return $skills;
        });
    }

    public function skill(Skill $skill): HasOne
    {
        return $this->hasOne($skill->model);
    }

    public function collection(Collection $collection): HasOne
    {
        return $this->hasOne($collection->model);
    }

    // MongoDB relationship
    public function getInventoryAttribute(): Inventory
    {
        return Inventory::where('account_id', $this->id)->first();
    }

    // MongoDB relationship
    public function getLootingBagAttribute(): LootingBag
    {
        return LootingBag::where('account_id', $this->id)->first() ?? new LootingBag();
    }

    // MongoDB relationship
    public function getBankAttribute(): Bank
    {
        return Bank::where('account_id', $this->id)->first();
    }

    public function equipment(): HasOne
    {
        return $this->hasOne(Equipment::class);
    }

    // MongoDB relationship
    public function getQuestAttribute(): Quest
    {
        return Quest::where('account_id', $this->id)->first();
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
