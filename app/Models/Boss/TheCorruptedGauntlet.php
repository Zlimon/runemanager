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
 * @method static \Illuminate\Database\Eloquent\Builder|TheCorruptedGauntlet newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TheCorruptedGauntlet newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TheCorruptedGauntlet query()
 * @method static \Illuminate\Database\Eloquent\Builder|TheCorruptedGauntlet whereAccountId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TheCorruptedGauntlet whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TheCorruptedGauntlet whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TheCorruptedGauntlet whereKillCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TheCorruptedGauntlet whereObtained($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TheCorruptedGauntlet whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class TheCorruptedGauntlet extends Model
{
    protected $table = 'the_corrupted_gauntlet';

    protected $fillable = [
        'obtained',
        'kill_count',
    ];

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }
}