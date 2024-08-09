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
 * @method static \Illuminate\Database\Eloquent\Builder|Cerberus newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Cerberus newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Cerberus query()
 * @method static \Illuminate\Database\Eloquent\Builder|Cerberus whereAccountId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Cerberus whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Cerberus whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Cerberus whereKillCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Cerberus whereObtained($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Cerberus whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Cerberus extends Model
{
    protected $table = 'cerberus';

    protected $fillable = [
        'obtained',
        'kill_count',
    ];

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }
}