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
 * @method static \Illuminate\Database\Eloquent\Builder|FossilIslandNotes newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|FossilIslandNotes newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|FossilIslandNotes query()
 * @method static \Illuminate\Database\Eloquent\Builder|FossilIslandNotes whereAccountId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FossilIslandNotes whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FossilIslandNotes whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FossilIslandNotes whereKillCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FossilIslandNotes whereObtained($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FossilIslandNotes whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class FossilIslandNotes extends Model
{
    protected $table = 'fossil_island_notes';

    protected $fillable = [
        'obtained',
        'kill_count',
    ];

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }
}