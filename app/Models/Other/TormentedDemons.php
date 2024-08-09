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
 * @method static \Illuminate\Database\Eloquent\Builder|TormentedDemons newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TormentedDemons newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TormentedDemons query()
 * @method static \Illuminate\Database\Eloquent\Builder|TormentedDemons whereAccountId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TormentedDemons whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TormentedDemons whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TormentedDemons whereKillCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TormentedDemons whereObtained($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TormentedDemons whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class TormentedDemons extends Model
{
    protected $table = 'tormented_demons';

    protected $fillable = [
        'obtained',
        'kill_count',
    ];

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }
}