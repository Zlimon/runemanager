<?php

namespace App\Models\Skill;

use App\Models\Account;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Skill\Thieving
 *
 * @property int $id
 * @property int $account_id
 * @property int $rank
 * @property int $level
 * @property int $xp
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Account $account
 * @method static \Illuminate\Database\Eloquent\Builder|Thieving newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Thieving newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Thieving query()
 * @method static \Illuminate\Database\Eloquent\Builder|Thieving whereAccountId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Thieving whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Thieving whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Thieving whereLevel($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Thieving whereRank($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Thieving whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Thieving whereXp($value)
 * @mixin \Eloquent
 */
class Thieving extends Model
{
    protected $table = 'thieving';

    protected $fillable = ['level'];

    protected $hidden = ['user_id'];

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }
}
