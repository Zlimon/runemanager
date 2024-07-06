<?php

namespace App\Models\Skill;

use App\Models\Account;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Skill\Hitpoints
 *
 * @property int $id
 * @property int $account_id
 * @property int $rank
 * @property int $level
 * @property int $xp
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Account $account
 * @method static \Illuminate\Database\Eloquent\Builder|Hitpoints newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Hitpoints newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Hitpoints query()
 * @method static \Illuminate\Database\Eloquent\Builder|Hitpoints whereAccountId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Hitpoints whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Hitpoints whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Hitpoints whereLevel($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Hitpoints whereRank($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Hitpoints whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Hitpoints whereXp($value)
 * @mixin \Eloquent
 */
class Hitpoints extends Model
{
    protected $table = 'hitpoints';

    protected $fillable = ['level'];

    protected $hidden = ['user_id'];

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }
}
