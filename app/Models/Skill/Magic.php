<?php

namespace App\Models\Skill;

use App\Models\Account;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $account_id
 * @property int $rank
 * @property int $level
 * @property int $xp
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read Account $account
 *
 * @method static \Illuminate\Database\Eloquent\Builder|Magic newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Magic newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Magic query()
 * @method static \Illuminate\Database\Eloquent\Builder|Magic whereAccountId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Magic whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Magic whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Magic whereLevel($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Magic whereRank($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Magic whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Magic whereXp($value)
 *
 * @mixin \Eloquent
 */
class Magic extends Model
{
    protected $table = 'magic';

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
