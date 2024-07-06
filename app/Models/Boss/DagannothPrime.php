<?php

namespace App\Models\Boss;

use App\Models\Account;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Boss\DagannothPrime
 *
 * @property int $id
 * @property int $account_id
 * @property int $kill_count
 * @property int $rank
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Account $account
 * @method static \Illuminate\Database\Eloquent\Builder|DagannothPrime newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|DagannothPrime newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|DagannothPrime query()
 * @method static \Illuminate\Database\Eloquent\Builder|DagannothPrime whereAccountId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DagannothPrime whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DagannothPrime whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DagannothPrime whereKillCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DagannothPrime whereRank($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DagannothPrime whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class DagannothPrime extends Model
{
    protected $table = 'dagannoth_prime';

    protected $fillable = [
        'kill_count',
    ];

    protected $hidden = ['user_id'];

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }
}
