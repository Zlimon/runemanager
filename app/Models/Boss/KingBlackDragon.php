<?php

namespace App\Models\Boss;

use App\Models\Account;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Boss\KingBlackDragon
 *
 * @property int $id
 * @property int $account_id
 * @property int $kill_count
 * @property int $rank
 * @property int $obtained
 * @property int $prince_black_dragon
 * @property int $kbd_heads
 * @property int $dragon_pickaxe
 * @property int $draconic_visage
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Account $account
 * @method static \Illuminate\Database\Eloquent\Builder|KingBlackDragon newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|KingBlackDragon newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|KingBlackDragon query()
 * @method static \Illuminate\Database\Eloquent\Builder|KingBlackDragon whereAccountId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|KingBlackDragon whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|KingBlackDragon whereDraconicVisage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|KingBlackDragon whereDragonPickaxe($value)
 * @method static \Illuminate\Database\Eloquent\Builder|KingBlackDragon whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|KingBlackDragon whereKbdHeads($value)
 * @method static \Illuminate\Database\Eloquent\Builder|KingBlackDragon whereKillCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|KingBlackDragon whereObtained($value)
 * @method static \Illuminate\Database\Eloquent\Builder|KingBlackDragon wherePrinceBlackDragon($value)
 * @method static \Illuminate\Database\Eloquent\Builder|KingBlackDragon whereRank($value)
 * @method static \Illuminate\Database\Eloquent\Builder|KingBlackDragon whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class KingBlackDragon extends Model
{
    protected $table = 'king_black_dragon';

    protected $fillable = [
        'obtained',
        'kill_count',
        'prince_black_dragon',
        'kbd_heads',
        'dragon_pickaxe',
        'draconic_visage',
    ];

    protected $hidden = ['user_id'];

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }
}
