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
 * @method static \Illuminate\Database\Eloquent\Builder|Vorkath newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Vorkath newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Vorkath query()
 * @method static \Illuminate\Database\Eloquent\Builder|Vorkath whereAccountId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Vorkath whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Vorkath whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Vorkath whereKillCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Vorkath whereObtained($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Vorkath whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Vorkath extends Model
{
    protected $table = 'vorkath';

    protected $fillable = [
        'obtained',
        'kill_count',
    ];

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }
}