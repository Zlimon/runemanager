<?php

namespace App\Models\Skill;

use App\Models\Account;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Skill\Defence
 *
 * @property int $id
 * @property int $account_id
 * @property int $rank
 * @property int $level
 * @property int $xp
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Account $account
 * @method static \Illuminate\Database\Eloquent\Builder|Defence newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Defence newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Defence query()
 * @method static \Illuminate\Database\Eloquent\Builder|Defence whereAccountId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Defence whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Defence whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Defence whereLevel($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Defence whereRank($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Defence whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Defence whereXp($value)
 * @mixin \Eloquent
 */
class Defence extends Model
{
    protected $table = 'defence';

    protected $fillable = ['level'];

    protected $hidden = ['user_id'];

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }
}
