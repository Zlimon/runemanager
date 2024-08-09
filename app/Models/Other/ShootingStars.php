<?php

namespace App\Models\Other;

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
 * @method static \Illuminate\Database\Eloquent\Builder|ShootingStars newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ShootingStars newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ShootingStars query()
 * @method static \Illuminate\Database\Eloquent\Builder|ShootingStars whereAccountId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ShootingStars whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ShootingStars whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ShootingStars whereKillCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ShootingStars whereObtained($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ShootingStars whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class ShootingStars extends Model
{
    protected $table = 'shooting_stars';

    protected $fillable = [
        'obtained',
        'kill_count',
    ];

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }
}