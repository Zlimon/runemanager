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
 * @method static \Illuminate\Database\Eloquent\Builder|ChambersOfXeric newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ChambersOfXeric newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ChambersOfXeric query()
 * @method static \Illuminate\Database\Eloquent\Builder|ChambersOfXeric whereAccountId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ChambersOfXeric whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ChambersOfXeric whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ChambersOfXeric whereKillCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ChambersOfXeric whereObtained($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ChambersOfXeric whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class ChambersOfXeric extends Model
{
    protected $table = 'chambers_of_xeric';

    protected $fillable = [
        'obtained',
        'kill_count',
    ];

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }
}