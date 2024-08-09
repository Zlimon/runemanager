<?php

namespace App\Models\Minigame;

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
 * @method static \Illuminate\Database\Eloquent\Builder|PestControl newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PestControl newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PestControl query()
 * @method static \Illuminate\Database\Eloquent\Builder|PestControl whereAccountId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PestControl whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PestControl whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PestControl whereKillCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PestControl whereObtained($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PestControl whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class PestControl extends Model
{
    protected $table = 'pest_control';

    protected $fillable = [
        'obtained',
        'kill_count',
    ];

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }
}