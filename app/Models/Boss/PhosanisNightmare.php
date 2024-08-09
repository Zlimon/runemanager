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
 * @method static \Illuminate\Database\Eloquent\Builder|PhosanisNightmare newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PhosanisNightmare newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PhosanisNightmare query()
 * @method static \Illuminate\Database\Eloquent\Builder|PhosanisNightmare whereAccountId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PhosanisNightmare whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PhosanisNightmare whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PhosanisNightmare whereKillCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PhosanisNightmare whereObtained($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PhosanisNightmare whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class PhosanisNightmare extends Model
{
    protected $table = 'phosanis_nightmare';

    protected $fillable = [
        'obtained',
        'kill_count',
    ];

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }
}