<?php

namespace App\Models\Minigame;

use App\Models\Account;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * 
 *
 * @property int $id
 * @property int $account_id
 * @property int $obtained
 * @property int $kill_count
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read Account $account
 * @method static \Illuminate\Database\Eloquent\Builder|GiantsFoundry newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|GiantsFoundry newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|GiantsFoundry query()
 * @method static \Illuminate\Database\Eloquent\Builder|GiantsFoundry whereAccountId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GiantsFoundry whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GiantsFoundry whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GiantsFoundry whereKillCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GiantsFoundry whereObtained($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GiantsFoundry whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class GiantsFoundry extends Model
{
    protected $table = 'giants_foundry';

    protected $fillable = [
        'obtained',
        'kill_count',
    ];

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }
}