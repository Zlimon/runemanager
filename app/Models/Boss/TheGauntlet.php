<?php

namespace App\Models\Boss;

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
 * @method static \Illuminate\Database\Eloquent\Builder|TheGauntlet newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TheGauntlet newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TheGauntlet query()
 * @method static \Illuminate\Database\Eloquent\Builder|TheGauntlet whereAccountId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TheGauntlet whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TheGauntlet whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TheGauntlet whereKillCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TheGauntlet whereObtained($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TheGauntlet whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class TheGauntlet extends Model
{
    protected $table = 'the_gauntlet';

    protected $fillable = [
        'obtained',
        'kill_count',
    ];

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }
}