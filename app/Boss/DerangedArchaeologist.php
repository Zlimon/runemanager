<?php

namespace App\Boss;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Boss\DerangedArchaeologist
 *
 * @property int $id
 * @property int $account_id
 * @property int $kill_count
 * @property int $rank
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Account $account
 * @method static \Illuminate\Database\Eloquent\Builder|DerangedArchaeologist newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|DerangedArchaeologist newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|DerangedArchaeologist query()
 * @method static \Illuminate\Database\Eloquent\Builder|DerangedArchaeologist whereAccountId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DerangedArchaeologist whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DerangedArchaeologist whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DerangedArchaeologist whereKillCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DerangedArchaeologist whereRank($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DerangedArchaeologist whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class DerangedArchaeologist extends Model
{
    protected $table = 'deranged_archaeologist';

    protected $fillable = [
        'kill_count',
    ];

    protected $hidden = ['user_id'];

    public function account()
    {
        return $this->belongsTo(\App\Account::class);
    }
}
