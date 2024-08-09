<?php

namespace App\Models\Minigame;

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
 * @method static \Illuminate\Database\Eloquent\Builder|TempleTrekking newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TempleTrekking newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TempleTrekking query()
 * @method static \Illuminate\Database\Eloquent\Builder|TempleTrekking whereAccountId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TempleTrekking whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TempleTrekking whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TempleTrekking whereKillCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TempleTrekking whereObtained($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TempleTrekking whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class TempleTrekking extends Model
{
    protected $table = 'temple_trekking';

    protected $fillable = [
        'obtained',
        'kill_count',
    ];

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }
}