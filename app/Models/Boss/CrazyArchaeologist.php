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
 * @method static \Illuminate\Database\Eloquent\Builder|CrazyArchaeologist newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CrazyArchaeologist newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CrazyArchaeologist query()
 * @method static \Illuminate\Database\Eloquent\Builder|CrazyArchaeologist whereAccountId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CrazyArchaeologist whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CrazyArchaeologist whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CrazyArchaeologist whereKillCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CrazyArchaeologist whereObtained($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CrazyArchaeologist whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class CrazyArchaeologist extends Model
{
    protected $table = 'crazy_archaeologist';

    protected $fillable = [
        'obtained',
        'kill_count',
    ];

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }
}