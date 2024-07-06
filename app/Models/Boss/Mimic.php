<?php

namespace App\Models\Boss;

use App\Models\Account;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Boss\Mimic
 *
 * @property int $id
 * @property int $account_id
 * @property int $kill_count
 * @property int $rank
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Account $account
 * @method static \Illuminate\Database\Eloquent\Builder|Mimic newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Mimic newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Mimic query()
 * @method static \Illuminate\Database\Eloquent\Builder|Mimic whereAccountId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Mimic whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Mimic whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Mimic whereKillCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Mimic whereRank($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Mimic whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Mimic extends Model
{
    protected $table = 'mimic';

    protected $fillable = [
        'kill_count',
    ];

    protected $hidden = ['user_id'];

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }
}
