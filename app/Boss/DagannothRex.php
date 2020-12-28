<?php

namespace App\Boss;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Boss\DagannothRex
 *
 * @property int $id
 * @property int $account_id
 * @property int $kill_count
 * @property int $rank
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Account $account
 * @method static \Illuminate\Database\Eloquent\Builder|DagannothRex newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|DagannothRex newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|DagannothRex query()
 * @method static \Illuminate\Database\Eloquent\Builder|DagannothRex whereAccountId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DagannothRex whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DagannothRex whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DagannothRex whereKillCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DagannothRex whereRank($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DagannothRex whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class DagannothRex extends Model
{
    protected $table = 'dagannoth_rex';

    protected $fillable = [
        'kill_count',
    ];

    protected $hidden = ['user_id'];

    public function account()
    {
        return $this->belongsTo(\App\Account::class);
    }
}
