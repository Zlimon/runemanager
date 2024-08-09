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
 * @method static \Illuminate\Database\Eloquent\Builder|DagannothPrime newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|DagannothPrime newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|DagannothPrime query()
 * @method static \Illuminate\Database\Eloquent\Builder|DagannothPrime whereAccountId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DagannothPrime whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DagannothPrime whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DagannothPrime whereKillCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DagannothPrime whereObtained($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DagannothPrime whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class DagannothPrime extends Model
{
    protected $table = 'dagannoth_prime';

    protected $fillable = [
        'obtained',
        'kill_count',
    ];

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }
}