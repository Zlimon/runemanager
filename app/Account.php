<?php

namespace App;

use App\Skill\Attack;
use App\Skill\Defence;
use App\Skill\Strength;
use App\Skill\Hitpoints;
use App\Skill\Ranged;
use App\Skill\Prayer;
use App\Skill\Magic;
use App\Skill\Cooking;
use App\Skill\Woodcutting;
use App\Skill\Fletching;
use App\Skill\Fishing;
use App\Skill\Firemaking;
use App\Skill\Crafting;
use App\Skill\Smithing;
use App\Skill\Mining;
use App\Skill\Herblore;
use App\Skill\Agility;
use App\Skill\Thieving;
use App\Skill\Slayer;
use App\Skill\Farming;
use App\Skill\Runecraft;
use App\Skill\Hunter;
use App\Skill\Construction;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Account
 *
 * @property int $id
 * @property int $user_id
 * @property string $account_type
 * @property string $username
 * @property int $rank
 * @property int $level
 * @property int $xp
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Notification[] $notification
 * @property-read int|null $notification_count
 * @property-read \App\User $user
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
 * @mixin \Eloquent
 */
class Account extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id', 'account_type', 'username', 'rank', 'level', 'xp'
    ];

    public function getRouteKeyName()
    {
        return 'username';
    }

    public function user() {
        return $this->belongsTo(User::class);
    }

    public function log() {
        return $this->hasMany(Log::class);
    }

    public function logBroadcast() {
        return $this->hasManyThrough(Broadcast::class, Log::class);
    }

    public function equipment() {
        return $this->hasOne(Equipment::class);
    }

    public function bank() {
        return $this->hasOne(Bank::class);
    }

    public function quest() {
        return $this->hasOne(Quest::class);
    }

    public function attack() {
        return $this->hasOne(Attack::class);
    }
    public function defence() {
        return $this->hasOne(Defence::class);
    }
    public function strength() {
        return $this->hasOne(Strength::class);
    }
    public function hitpoints() {
        return $this->hasOne(Hitpoints::class);
    }
    public function ranged() {
        return $this->hasOne(Ranged::class);
    }
    public function prayer() {
        return $this->hasOne(Prayer::class);
    }
    public function magic() {
        return $this->hasOne(Magic::class);
    }
    public function cooking() {
        return $this->hasOne(Cooking::class);
    }
    public function woodcutting() {
        return $this->hasOne(Woodcutting::class);
    }
    public function fletching() {
        return $this->hasOne(Fletching::class);
    }
    public function fishing() {
        return $this->hasOne(Fishing::class);
    }
    public function firemaking() {
        return $this->hasOne(Firemaking::class);
    }
    public function crafting() {
        return $this->hasOne(Crafting::class);
    }
    public function smithing() {
        return $this->hasOne(Smithing::class);
    }
    public function mining() {
        return $this->hasOne(Mining::class);
    }
    public function herblore() {
        return $this->hasOne(Herblore::class);
    }
    public function agility() {
        return $this->hasOne(Agility::class);
    }
    public function thieving() {
        return $this->hasOne(Thieving::class);
    }
    public function slayer() {
        return $this->hasOne(Slayer::class);
    }
    public function farming() {
        return $this->hasOne(Farming::class);
    }
    public function runecraft() {
        return $this->hasOne(Runecraft::class);
    }
    public function hunter() {
        return $this->hasOne(Hunter::class);
    }
    public function construction() {
        return $this->hasOne(Construction::class);
    }
}
