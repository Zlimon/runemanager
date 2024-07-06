<?php

namespace App\Models\Boss;

use App\Models\Account;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Boss\TzTokJad
 *
 * @property int $id
 * @property int $account_id
 * @property int $kill_count
 * @property int $rank
 * @property int $obtained
 * @property int $tzrek-jad
 * @property int $fire_cape
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Account $account
 * @method static \Illuminate\Database\Eloquent\Builder|TzTokJad newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TzTokJad newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TzTokJad query()
 * @method static \Illuminate\Database\Eloquent\Builder|TzTokJad whereAccountId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TzTokJad whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TzTokJad whereFireCape($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TzTokJad whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TzTokJad whereKillCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TzTokJad whereObtained($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TzTokJad whereRank($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TzTokJad whereTzrekJad($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TzTokJad whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class TzTokJad extends Model
{
    protected $table = 'tztok_jad';

    protected $fillable = [
        'obtained',
        'kill_count',
        'tzrek-jad',
        'fire_cape',
    ];

    protected $hidden = ['user_id'];

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }
}
