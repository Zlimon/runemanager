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
 * @method static \Illuminate\Database\Eloquent\Builder|VolcanicMine newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|VolcanicMine newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|VolcanicMine query()
 * @method static \Illuminate\Database\Eloquent\Builder|VolcanicMine whereAccountId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VolcanicMine whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VolcanicMine whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VolcanicMine whereKillCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VolcanicMine whereObtained($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VolcanicMine whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class VolcanicMine extends Model
{
    protected $table = 'volcanic_mine';

    protected $fillable = [
        'obtained',
        'kill_count',
    ];

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }
}