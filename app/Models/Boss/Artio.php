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
 * @method static \Illuminate\Database\Eloquent\Builder|Artio newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Artio newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Artio query()
 * @method static \Illuminate\Database\Eloquent\Builder|Artio whereAccountId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Artio whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Artio whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Artio whereKillCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Artio whereObtained($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Artio whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Artio extends Model
{
    protected $table = 'artio';

    protected $fillable = [
        'obtained',
        'kill_count',
    ];

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }
}