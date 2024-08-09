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
 * @method static \Illuminate\Database\Eloquent\Builder|GloughsExperiments newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|GloughsExperiments newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|GloughsExperiments query()
 * @method static \Illuminate\Database\Eloquent\Builder|GloughsExperiments whereAccountId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GloughsExperiments whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GloughsExperiments whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GloughsExperiments whereKillCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GloughsExperiments whereObtained($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GloughsExperiments whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class GloughsExperiments extends Model
{
    protected $table = 'gloughs_experiments';

    protected $fillable = [
        'obtained',
        'kill_count',
    ];

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }
}