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
 * @method static \Illuminate\Database\Eloquent\Builder|MoonsOfPeril newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|MoonsOfPeril newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|MoonsOfPeril query()
 * @method static \Illuminate\Database\Eloquent\Builder|MoonsOfPeril whereAccountId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MoonsOfPeril whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MoonsOfPeril whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MoonsOfPeril whereKillCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MoonsOfPeril whereObtained($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MoonsOfPeril whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class MoonsOfPeril extends Model
{
    protected $table = 'moons_of_peril';

    protected $fillable = [
        'obtained',
        'kill_count',
    ];

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }
}