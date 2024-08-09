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
 * @method static \Illuminate\Database\Eloquent\Builder|Revenants newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Revenants newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Revenants query()
 * @method static \Illuminate\Database\Eloquent\Builder|Revenants whereAccountId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Revenants whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Revenants whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Revenants whereKillCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Revenants whereObtained($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Revenants whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Revenants extends Model
{
    protected $table = 'revenants';

    protected $fillable = [
        'obtained',
        'kill_count',
    ];

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }
}