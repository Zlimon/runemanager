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
 * @method static \Illuminate\Database\Eloquent\Builder|TroubleBrewing newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TroubleBrewing newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TroubleBrewing query()
 * @method static \Illuminate\Database\Eloquent\Builder|TroubleBrewing whereAccountId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TroubleBrewing whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TroubleBrewing whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TroubleBrewing whereKillCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TroubleBrewing whereObtained($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TroubleBrewing whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class TroubleBrewing extends Model
{
    protected $table = 'trouble_brewing';

    protected $fillable = [
        'obtained',
        'kill_count',
    ];

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }
}