<?php

namespace App\Models\Skill;

use App\Models\Account;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * 
 *
 * @property int $id
 * @property int $account_id
 * @property int $rank
 * @property int $level
 * @property int $xp
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read Account $account
 * @method static \Illuminate\Database\Eloquent\Builder|Farming newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Farming newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Farming query()
 * @method static \Illuminate\Database\Eloquent\Builder|Farming whereAccountId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Farming whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Farming whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Farming whereLevel($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Farming whereRank($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Farming whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Farming whereXp($value)
 * @mixin \Eloquent
 */
class Farming extends Model
{
    protected $table = 'farming';

    protected $fillable = [
        'rank',
        'level',
        'xp',
    ];

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }
}
