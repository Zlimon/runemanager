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
 * @method static \Illuminate\Database\Eloquent\Builder|Obor newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Obor newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Obor query()
 * @method static \Illuminate\Database\Eloquent\Builder|Obor whereAccountId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Obor whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Obor whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Obor whereKillCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Obor whereObtained($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Obor whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Obor extends Model
{
    protected $table = 'obor';

    protected $fillable = [
        'obtained',
        'kill_count',
    ];

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }
}