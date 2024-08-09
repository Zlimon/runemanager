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
 * @method static \Illuminate\Database\Eloquent\Builder|TzkalZuk newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TzkalZuk newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TzkalZuk query()
 * @method static \Illuminate\Database\Eloquent\Builder|TzkalZuk whereAccountId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TzkalZuk whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TzkalZuk whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TzkalZuk whereKillCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TzkalZuk whereObtained($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TzkalZuk whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class TzkalZuk extends Model
{
    protected $table = 'tzkal_zuk';

    protected $fillable = [
        'obtained',
        'kill_count',
    ];

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }
}