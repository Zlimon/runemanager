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
 * @method static \Illuminate\Database\Eloquent\Builder|Nightmare newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Nightmare newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Nightmare query()
 * @method static \Illuminate\Database\Eloquent\Builder|Nightmare whereAccountId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Nightmare whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Nightmare whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Nightmare whereKillCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Nightmare whereObtained($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Nightmare whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Nightmare extends Model
{
    protected $table = 'nightmare';

    protected $fillable = [
        'obtained',
        'kill_count',
    ];

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }
}