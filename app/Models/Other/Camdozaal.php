<?php

namespace App\Models\Other;

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
 * @method static \Illuminate\Database\Eloquent\Builder|Camdozaal newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Camdozaal newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Camdozaal query()
 * @method static \Illuminate\Database\Eloquent\Builder|Camdozaal whereAccountId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Camdozaal whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Camdozaal whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Camdozaal whereKillCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Camdozaal whereObtained($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Camdozaal whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Camdozaal extends Model
{
    protected $table = 'camdozaal';

    protected $fillable = [
        'obtained',
        'kill_count',
    ];

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }
}