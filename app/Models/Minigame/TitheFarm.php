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
 * @method static \Illuminate\Database\Eloquent\Builder|TitheFarm newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TitheFarm newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TitheFarm query()
 * @method static \Illuminate\Database\Eloquent\Builder|TitheFarm whereAccountId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TitheFarm whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TitheFarm whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TitheFarm whereKillCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TitheFarm whereObtained($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TitheFarm whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class TitheFarm extends Model
{
    protected $table = 'tithe_farm';

    protected $fillable = [
        'obtained',
        'kill_count',
    ];

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }
}