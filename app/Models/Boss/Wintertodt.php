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
 * @method static \Illuminate\Database\Eloquent\Builder|Wintertodt newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Wintertodt newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Wintertodt query()
 * @method static \Illuminate\Database\Eloquent\Builder|Wintertodt whereAccountId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Wintertodt whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Wintertodt whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Wintertodt whereKillCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Wintertodt whereObtained($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Wintertodt whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Wintertodt extends Model
{
    protected $table = 'wintertodt';

    protected $fillable = [
        'obtained',
        'kill_count',
    ];

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }
}