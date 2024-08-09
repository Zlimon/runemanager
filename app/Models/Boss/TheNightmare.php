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
 * @method static \Illuminate\Database\Eloquent\Builder|TheNightmare newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TheNightmare newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TheNightmare query()
 * @method static \Illuminate\Database\Eloquent\Builder|TheNightmare whereAccountId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TheNightmare whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TheNightmare whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TheNightmare whereKillCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TheNightmare whereObtained($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TheNightmare whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class TheNightmare extends Model
{
    protected $table = 'the_nightmare';

    protected $fillable = [
        'obtained',
        'kill_count',
    ];

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }
}