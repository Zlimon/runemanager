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
 * @method static \Illuminate\Database\Eloquent\Builder|ChaosDruids newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ChaosDruids newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ChaosDruids query()
 * @method static \Illuminate\Database\Eloquent\Builder|ChaosDruids whereAccountId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ChaosDruids whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ChaosDruids whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ChaosDruids whereKillCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ChaosDruids whereObtained($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ChaosDruids whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class ChaosDruids extends Model
{
    protected $table = 'chaos_druids';

    protected $fillable = [
        'obtained',
        'kill_count',
    ];

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }
}