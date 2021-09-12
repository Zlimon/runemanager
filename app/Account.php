<?php

namespace App;

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
 * @property int $online
 * @property-read \App\Bank|null $bank
 * @property-read \App\Equipment|null $equipment
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Log[] $log
 * @property-read int|null $log_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Broadcast[] $logBroadcast
 * @property-read int|null $log_broadcast_count
 * @property-read \App\Quest|null $quest
 * @method static \Illuminate\Database\Eloquent\Builder|Account whereOnline($value)
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

    public function collection(Collection $collection) {
        return $this->hasOne($collection->model);
    }

    public function skill(Skill $skill) {
        return $this->hasOne($skill->model);
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

    public function group() {
        return $this->belongsToMany(Group::class);
    }
}
