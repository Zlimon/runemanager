<?php

namespace App\Models\Raid;

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
 * @method static \Illuminate\Database\Eloquent\Builder|TombsOfAmascut newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TombsOfAmascut newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TombsOfAmascut query()
 * @method static \Illuminate\Database\Eloquent\Builder|TombsOfAmascut whereAccountId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TombsOfAmascut whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TombsOfAmascut whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TombsOfAmascut whereKillCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TombsOfAmascut whereObtained($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TombsOfAmascut whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class TombsOfAmascut extends Model
{
    protected $table = 'tombs_of_amascut';

    protected $fillable = [
        'obtained',
        'kill_count',
    ];

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }
}