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
 * @method static \Illuminate\Database\Eloquent\Builder|FishingTrawler newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|FishingTrawler newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|FishingTrawler query()
 * @method static \Illuminate\Database\Eloquent\Builder|FishingTrawler whereAccountId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FishingTrawler whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FishingTrawler whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FishingTrawler whereKillCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FishingTrawler whereObtained($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FishingTrawler whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class FishingTrawler extends Model
{
    protected $table = 'fishing_trawler';

    protected $fillable = [
        'obtained',
        'kill_count',
    ];

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }
}