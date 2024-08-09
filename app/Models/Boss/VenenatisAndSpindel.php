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
 * @method static \Illuminate\Database\Eloquent\Builder|VenenatisAndSpindel newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|VenenatisAndSpindel newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|VenenatisAndSpindel query()
 * @method static \Illuminate\Database\Eloquent\Builder|VenenatisAndSpindel whereAccountId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VenenatisAndSpindel whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VenenatisAndSpindel whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VenenatisAndSpindel whereKillCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VenenatisAndSpindel whereObtained($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VenenatisAndSpindel whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class VenenatisAndSpindel extends Model
{
    protected $table = 'venenatis_and_spindel';

    protected $fillable = [
        'obtained',
        'kill_count',
    ];

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }
}