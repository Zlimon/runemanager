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
 * @method static \Illuminate\Database\Eloquent\Builder|RoguesDen newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|RoguesDen newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|RoguesDen query()
 * @method static \Illuminate\Database\Eloquent\Builder|RoguesDen whereAccountId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RoguesDen whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RoguesDen whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RoguesDen whereKillCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RoguesDen whereObtained($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RoguesDen whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class RoguesDen extends Model
{
    protected $table = 'rogues_den';

    protected $fillable = [
        'obtained',
        'kill_count',
    ];

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }
}