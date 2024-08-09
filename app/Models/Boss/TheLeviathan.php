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
 * @method static \Illuminate\Database\Eloquent\Builder|TheLeviathan newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TheLeviathan newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TheLeviathan query()
 * @method static \Illuminate\Database\Eloquent\Builder|TheLeviathan whereAccountId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TheLeviathan whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TheLeviathan whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TheLeviathan whereKillCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TheLeviathan whereObtained($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TheLeviathan whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class TheLeviathan extends Model
{
    protected $table = 'the_leviathan';

    protected $fillable = [
        'obtained',
        'kill_count',
    ];

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }
}