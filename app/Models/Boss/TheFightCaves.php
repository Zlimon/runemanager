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
 * @method static \Illuminate\Database\Eloquent\Builder|TheFightCaves newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TheFightCaves newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TheFightCaves query()
 * @method static \Illuminate\Database\Eloquent\Builder|TheFightCaves whereAccountId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TheFightCaves whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TheFightCaves whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TheFightCaves whereKillCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TheFightCaves whereObtained($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TheFightCaves whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class TheFightCaves extends Model
{
    protected $table = 'the_fight_caves';

    protected $fillable = [
        'obtained',
        'kill_count',
    ];

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }
}