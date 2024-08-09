<?php

namespace App\Models\Boss;

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
 * @method static \Illuminate\Database\Eloquent\Builder|FortisColosseum newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|FortisColosseum newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|FortisColosseum query()
 * @method static \Illuminate\Database\Eloquent\Builder|FortisColosseum whereAccountId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FortisColosseum whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FortisColosseum whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FortisColosseum whereKillCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FortisColosseum whereObtained($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FortisColosseum whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class FortisColosseum extends Model
{
    protected $table = 'fortis_colosseum';

    protected $fillable = [
        'obtained',
        'kill_count',
    ];

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }
}