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
 * @method static \Illuminate\Database\Eloquent\Builder|TheatreOfBlood newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TheatreOfBlood newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TheatreOfBlood query()
 * @method static \Illuminate\Database\Eloquent\Builder|TheatreOfBlood whereAccountId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TheatreOfBlood whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TheatreOfBlood whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TheatreOfBlood whereKillCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TheatreOfBlood whereObtained($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TheatreOfBlood whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class TheatreOfBlood extends Model
{
    protected $table = 'theatre_of_blood';

    protected $fillable = [
        'obtained',
        'kill_count',
    ];

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }
}