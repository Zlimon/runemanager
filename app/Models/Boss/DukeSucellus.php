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
 * @method static \Illuminate\Database\Eloquent\Builder|DukeSucellus newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|DukeSucellus newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|DukeSucellus query()
 * @method static \Illuminate\Database\Eloquent\Builder|DukeSucellus whereAccountId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DukeSucellus whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DukeSucellus whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DukeSucellus whereKillCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DukeSucellus whereObtained($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DukeSucellus whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class DukeSucellus extends Model
{
    protected $table = 'duke_sucellus';

    protected $fillable = [
        'obtained',
        'kill_count',
    ];

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }
}