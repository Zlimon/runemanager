<?php

namespace App\Models\Skill;

use App\Models\Account;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Skill\Attack
 *
 * @property int $id
 * @property int $account_id
 * @property int $rank
 * @property int $level
 * @property int $xp
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Account $account
 * @method static \Illuminate\Database\Eloquent\Builder|Attack newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Attack newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Attack query()
 * @method static \Illuminate\Database\Eloquent\Builder|Attack whereAccountId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Attack whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Attack whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Attack whereLevel($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Attack whereRank($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Attack whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Attack whereXp($value)
 * @mixin \Eloquent
 */
class Attack extends Model
{
    protected $table = 'attack';

    protected $fillable = ['level'];

    protected $hidden = ['user_id'];

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }
}
