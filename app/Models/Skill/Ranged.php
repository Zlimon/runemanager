<?php

namespace App\Models\Skill;

use App\Models\Account;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Skill\Ranged
 *
 * @property int $id
 * @property int $account_id
 * @property int $rank
 * @property int $level
 * @property int $xp
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Account $account
 * @method static \Illuminate\Database\Eloquent\Builder|Ranged newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Ranged newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Ranged query()
 * @method static \Illuminate\Database\Eloquent\Builder|Ranged whereAccountId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Ranged whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Ranged whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Ranged whereLevel($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Ranged whereRank($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Ranged whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Ranged whereXp($value)
 * @mixin \Eloquent
 */
class Ranged extends Model
{
    protected $table = 'ranged';

    protected $fillable = ['level'];

    protected $hidden = ['user_id'];

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }
}
