<?php

namespace App\Models\Boss;

use App\Models\Account;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Boss\DagannothSupreme
 *
 * @property int $id
 * @property int $account_id
 * @property int $kill_count
 * @property int $rank
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Account $account
 * @method static \Illuminate\Database\Eloquent\Builder|DagannothSupreme newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|DagannothSupreme newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|DagannothSupreme query()
 * @method static \Illuminate\Database\Eloquent\Builder|DagannothSupreme whereAccountId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DagannothSupreme whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DagannothSupreme whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DagannothSupreme whereKillCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DagannothSupreme whereRank($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DagannothSupreme whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class DagannothSupreme extends Model
{
    protected $table = 'dagannoth_supreme';

    protected $fillable = [
        'kill_count',
    ];

    protected $hidden = ['user_id'];

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }
}
