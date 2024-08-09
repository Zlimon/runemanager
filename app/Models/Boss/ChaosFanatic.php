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
 * @method static \Illuminate\Database\Eloquent\Builder|ChaosFanatic newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ChaosFanatic newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ChaosFanatic query()
 * @method static \Illuminate\Database\Eloquent\Builder|ChaosFanatic whereAccountId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ChaosFanatic whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ChaosFanatic whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ChaosFanatic whereKillCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ChaosFanatic whereObtained($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ChaosFanatic whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class ChaosFanatic extends Model
{
    protected $table = 'chaos_fanatic';

    protected $fillable = [
        'obtained',
        'kill_count',
    ];

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }
}