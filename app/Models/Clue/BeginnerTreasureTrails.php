<?php

namespace App\Models\Clue;

use App\Models\Account;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Clues\BeginnerTreasureTrails
 *
 * @property int $id
 * @property int $account_id
 * @property int $kill_count
 * @property int $rank
 * @property int $obtained
 * @property int $mole_slippers
 * @property int $frog_slippers
 * @property int $bear_feet
 * @property int $demon_feet
 * @property int $jester_cape
 * @property int $shoulder_parrot
 * @property int $monks_robe_top_(t)
 * @property int $monks_robe_(t)
 * @property int $amulet_of_defence_(t)
 * @property int $sandwich_lady_hat
 * @property int $sandwich_lady_top
 * @property int $sandwich_lady_bottom
 * @property int $rune_scimitar_ornament_kit_(guthix)
 * @property int $rune_scimitar_ornament_kit_(saradomin)
 * @property int $rune_scimitar_ornament_kit_(zamorak)
 * @property int $black_pickaxe
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Account $account
 * @method static \Illuminate\Database\Eloquent\Builder|BeginnerTreasureTrails newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|BeginnerTreasureTrails newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|BeginnerTreasureTrails query()
 * @method static \Illuminate\Database\Eloquent\Builder|BeginnerTreasureTrails whereAccountId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BeginnerTreasureTrails whereAmuletOfDefence(t)($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BeginnerTreasureTrails whereBearFeet($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BeginnerTreasureTrails whereBlackPickaxe($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BeginnerTreasureTrails whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BeginnerTreasureTrails whereDemonFeet($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BeginnerTreasureTrails whereFrogSlippers($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BeginnerTreasureTrails whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BeginnerTreasureTrails whereJesterCape($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BeginnerTreasureTrails whereKillCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BeginnerTreasureTrails whereMoleSlippers($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BeginnerTreasureTrails whereMonksRobe(t)($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BeginnerTreasureTrails whereMonksRobeTop(t)($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BeginnerTreasureTrails whereObtained($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BeginnerTreasureTrails whereRank($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BeginnerTreasureTrails whereRuneScimitarOrnamentKit(guthix)($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BeginnerTreasureTrails whereRuneScimitarOrnamentKit(saradomin)($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BeginnerTreasureTrails whereRuneScimitarOrnamentKit(zamorak)($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BeginnerTreasureTrails whereSandwichLadyBottom($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BeginnerTreasureTrails whereSandwichLadyHat($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BeginnerTreasureTrails whereSandwichLadyTop($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BeginnerTreasureTrails whereShoulderParrot($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BeginnerTreasureTrails whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BeginnerTreasureTrails whereAmuletOfDefence(t)($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BeginnerTreasureTrails whereMonksRobe(t)($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BeginnerTreasureTrails whereMonksRobeTop(t)($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BeginnerTreasureTrails whereRuneScimitarOrnamentKit(guthix)($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BeginnerTreasureTrails whereRuneScimitarOrnamentKit(saradomin)($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BeginnerTreasureTrails whereRuneScimitarOrnamentKit(zamorak)($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BeginnerTreasureTrails whereAmuletOfDefence(t)($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BeginnerTreasureTrails whereMonksRobe(t)($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BeginnerTreasureTrails whereMonksRobeTop(t)($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BeginnerTreasureTrails whereRuneScimitarOrnamentKit(guthix)($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BeginnerTreasureTrails whereRuneScimitarOrnamentKit(saradomin)($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BeginnerTreasureTrails whereRuneScimitarOrnamentKit(zamorak)($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BeginnerTreasureTrails whereAmuletOfDefence(t)($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BeginnerTreasureTrails whereMonksRobe(t)($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BeginnerTreasureTrails whereMonksRobeTop(t)($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BeginnerTreasureTrails whereRuneScimitarOrnamentKit(guthix)($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BeginnerTreasureTrails whereRuneScimitarOrnamentKit(saradomin)($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BeginnerTreasureTrails whereRuneScimitarOrnamentKit(zamorak)($value)
 * @mixin \Eloquent
 */
class BeginnerTreasureTrails extends Model
{
    protected $table = 'beginner_treasure_trails';

    protected $fillable = [
        'obtained',
        'kill_count',
        'mole_slippers',
        'frog_slippers',
        'bear_feet',
        'demon_feet',
        'jester_cape',
        'shoulder_parrot',
        'monks_robe_top_(t)',
        'monks_robe_(t)',
        'amulet_of_defence_(t)',
        'sandwich_lady_hat',
        'sandwich_lady_top',
        'sandwich_lady_bottom',
        'rune_scimitar_ornament_kit_(guthix)',
        'rune_scimitar_ornament_kit_(saradomin)',
        'rune_scimitar_ornament_kit_(zamorak)',
        'black_pickaxe',
    ];

    protected $hidden = ['user_id'];

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }
}
