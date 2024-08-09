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
 * @method static \Illuminate\Database\Eloquent\Builder|ShadesOfMortton newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ShadesOfMortton newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ShadesOfMortton query()
 * @method static \Illuminate\Database\Eloquent\Builder|ShadesOfMortton whereAccountId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ShadesOfMortton whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ShadesOfMortton whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ShadesOfMortton whereKillCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ShadesOfMortton whereObtained($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ShadesOfMortton whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class ShadesOfMortton extends Model
{
    protected $table = 'shades_of_mortton';

    protected $fillable = [
        'obtained',
        'kill_count',
    ];

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }
}