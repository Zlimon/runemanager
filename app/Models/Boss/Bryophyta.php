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
 * @method static \Illuminate\Database\Eloquent\Builder|Bryophyta newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Bryophyta newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Bryophyta query()
 * @method static \Illuminate\Database\Eloquent\Builder|Bryophyta whereAccountId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Bryophyta whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Bryophyta whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Bryophyta whereKillCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Bryophyta whereObtained($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Bryophyta whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Bryophyta extends Model
{
    protected $table = 'bryophyta';

    protected $fillable = [
        'obtained',
        'kill_count',
    ];

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }
}