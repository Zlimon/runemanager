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
 * @method static \Illuminate\Database\Eloquent\Builder|PhantomMuspah newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PhantomMuspah newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PhantomMuspah query()
 * @method static \Illuminate\Database\Eloquent\Builder|PhantomMuspah whereAccountId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PhantomMuspah whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PhantomMuspah whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PhantomMuspah whereKillCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PhantomMuspah whereObtained($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PhantomMuspah whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class PhantomMuspah extends Model
{
    protected $table = 'phantom_muspah';

    protected $fillable = [
        'obtained',
        'kill_count',
    ];

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }
}