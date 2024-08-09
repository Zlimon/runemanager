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
 * @method static \Illuminate\Database\Eloquent\Builder|DagannothRex newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|DagannothRex newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|DagannothRex query()
 * @method static \Illuminate\Database\Eloquent\Builder|DagannothRex whereAccountId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DagannothRex whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DagannothRex whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DagannothRex whereKillCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DagannothRex whereObtained($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DagannothRex whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class DagannothRex extends Model
{
    protected $table = 'dagannoth_rex';

    protected $fillable = [
        'obtained',
        'kill_count',
    ];

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }
}