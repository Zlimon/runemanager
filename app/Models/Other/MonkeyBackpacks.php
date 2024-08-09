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
 * @method static \Illuminate\Database\Eloquent\Builder|MonkeyBackpacks newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|MonkeyBackpacks newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|MonkeyBackpacks query()
 * @method static \Illuminate\Database\Eloquent\Builder|MonkeyBackpacks whereAccountId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MonkeyBackpacks whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MonkeyBackpacks whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MonkeyBackpacks whereKillCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MonkeyBackpacks whereObtained($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MonkeyBackpacks whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class MonkeyBackpacks extends Model
{
    protected $table = 'monkey_backpacks';

    protected $fillable = [
        'obtained',
        'kill_count',
    ];

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }
}