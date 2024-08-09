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
 * @method static \Illuminate\Database\Eloquent\Builder|MyNotes newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|MyNotes newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|MyNotes query()
 * @method static \Illuminate\Database\Eloquent\Builder|MyNotes whereAccountId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MyNotes whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MyNotes whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MyNotes whereKillCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MyNotes whereObtained($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MyNotes whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class MyNotes extends Model
{
    protected $table = 'my_notes';

    protected $fillable = [
        'obtained',
        'kill_count',
    ];

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }
}