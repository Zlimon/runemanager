<?php

namespace App\Models;

use App\Enums\AccountTypesEnum;
use App\Http\Resources\SkillResource;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
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

    public function getUserIconAttribute(): string
    {
        return $this->user->icon ?? 'iVBORw0KGgoAAAANSUhEUgAAACQAAAAgCAYAAAB6kdqOAAACPElEQVR4Xs2XW0sCQRTHO0/RY/QQFUE99BBEVEQkRQQRUQkWEhWViYh0FxERu4iIFF3o+hAFic/hdzxxZpxmZ8Yu5uzaHwZ2dtY9v3OZ2WNTk2cC1O80QATBRzK5U7lumABLpSe8v8/jzXUW4/EoAxrq6XEDSnqur0gBA3l8LGChkMbDwwj6/bM//KZmcYhcLomp1B4zUh2MR0ek6fw8g9HoBu7ubNuMEmA2m2AvjMW2MBRaQZ9vlM3T6QMNCvD2NucwDri5GWSRsgp0ehLHyckxR1T4EKA6FBmWxvm6eq8uAR5njrSXAkudMNbS0qxA6YYtwpBkanSvBQjVlpk+V6VGh4wXiw8OSMB8PuU4czwAU0POo0NFTteUUifM8/OlN1BS3DDtHieIiJYodot18514zcjUyHQKIDp75LrrMiHMSABeXZ59nj/qmjWpO8uE0AV4cXHiFhT3mIr1/b3IdtTvjLgCJc+hRCLGhijkn6NEUlOsr9YoYF/tYHAROzraK17yMTI8+GuvLcGQACORdRwY6Ne8BJyenqghSlbEUzQ1NV4l5ICBwJz3QMvL81Vg+Bq1FR4BOQ+0ajD8vvhsmGtWBfj6cs2aMAH2tcGvYOuS3DU0qBFbW11izVhfX6/R5+jPW4ehZpwOLup9aU41Qzuqra1VMSp2VrlcwoWFGfaMCWtFurfq3Amzvxdmkevu6jSe0d9al1TD5lwYF2eRCeupeF2Fw6uNBhECfHu7s/2/ql65WC9/1b+C8Uof5wvhG5Ohd9UAAAAASUVORK5CYII=';
    }

    public function skill(Skill $skill): HasOne
    {
        return $this->hasOne($skill->model);
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

    public function collection(Collection $collection): HasOne
    {
        return $this->hasOne($collection->model);
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
