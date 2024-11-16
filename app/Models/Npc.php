<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

/**
 * @property string $_id 1000 occurrences
 * @property bool|null $aggressive 1000 occurrences
 * @property int|null $attack_bonus 1000 occurrences
 * @property int|null $attack_level 1000 occurrences
 * @property int|null $attack_magic 1000 occurrences
 * @property int|null $attack_ranged 1000 occurrences
 * @property string|null $attack_speed 1000 occurrences
 * @property string|null $attack_type 1000 occurrences
 * @property string|null $attributes 1000 occurrences
 * @property string|null $category 1000 occurrences
 * @property int|null $combat_level 1000 occurrences
 * @property int|null $defence_crush 1000 occurrences
 * @property int|null $defence_level 1000 occurrences
 * @property int|null $defence_magic 1000 occurrences
 * @property int|null $defence_ranged 1000 occurrences
 * @property int|null $defence_slash 1000 occurrences
 * @property int|null $defence_stab 1000 occurrences
 * @property string|null $drops 1000 occurrences
 * @property bool|null $duplicate 1000 occurrences
 * @property string|null $examine 1000 occurrences
 * @property string|null $hitpoints 1000 occurrences
 * @property mixed $id 1000 occurrences
 * @property bool|null $immune_poison 1000 occurrences
 * @property bool|null $immune_venom 1000 occurrences
 * @property bool|null $incomplete 1000 occurrences
 * @property string|null $last_updated 1000 occurrences
 * @property int|null $magic_bonus 1000 occurrences
 * @property int|null $magic_level 1000 occurrences
 * @property string|null $max_hit 1000 occurrences
 * @property bool|null $members 1000 occurrences
 * @property string|null $name 1000 occurrences
 * @property bool|null $poisonous 1000 occurrences
 * @property int|null $ranged_bonus 1000 occurrences
 * @property int|null $ranged_level 1000 occurrences
 * @property string|null $release_date 1000 occurrences
 * @property int|null $size 1000 occurrences
 * @property string|null $slayer_level 1000 occurrences
 * @property string|null $slayer_masters 1000 occurrences
 * @property bool|null $slayer_monster 1000 occurrences
 * @property string|null $slayer_xp 1000 occurrences
 * @property int|null $strength_bonus 1000 occurrences
 * @property int|null $strength_level 1000 occurrences
 * @property bool|null $venomous 1000 occurrences
 * @property string|null $wiki_name 1000 occurrences
 * @property string|null $wiki_url 1000 occurrences
 *
 * @method static \MongoDB\Laravel\Eloquent\Builder|Npc addHybridHas(\Illuminate\Database\Eloquent\Relations\Relation $relation, string $operator = '>=', string $count = 1, string $boolean = 'and', ?\Closure $callback = null)
 * @method static \MongoDB\Laravel\Eloquent\Builder|Npc aggregate($function = null, $columns = [])
 * @method static \MongoDB\Laravel\Eloquent\Builder|Npc getConnection()
 * @method static \MongoDB\Laravel\Eloquent\Builder|Npc insert(array $values)
 * @method static \MongoDB\Laravel\Eloquent\Builder|Npc insertGetId(array $values, $sequence = null)
 * @method static \MongoDB\Laravel\Eloquent\Builder|Npc newModelQuery()
 * @method static \MongoDB\Laravel\Eloquent\Builder|Npc newQuery()
 * @method static \MongoDB\Laravel\Eloquent\Builder|Npc query()
 * @method static \MongoDB\Laravel\Eloquent\Builder|Npc raw($value = null)
 * @method static \MongoDB\Laravel\Eloquent\Builder|Npc whereAggressive($value)
 * @method static \MongoDB\Laravel\Eloquent\Builder|Npc whereAttackBonus($value)
 * @method static \MongoDB\Laravel\Eloquent\Builder|Npc whereAttackLevel($value)
 * @method static \MongoDB\Laravel\Eloquent\Builder|Npc whereAttackMagic($value)
 * @method static \MongoDB\Laravel\Eloquent\Builder|Npc whereAttackRanged($value)
 * @method static \MongoDB\Laravel\Eloquent\Builder|Npc whereAttackSpeed($value)
 * @method static \MongoDB\Laravel\Eloquent\Builder|Npc whereAttackType($value)
 * @method static \MongoDB\Laravel\Eloquent\Builder|Npc whereAttributes($value)
 * @method static \MongoDB\Laravel\Eloquent\Builder|Npc whereCategory($value)
 * @method static \MongoDB\Laravel\Eloquent\Builder|Npc whereCombatLevel($value)
 * @method static \MongoDB\Laravel\Eloquent\Builder|Npc whereDefenceCrush($value)
 * @method static \MongoDB\Laravel\Eloquent\Builder|Npc whereDefenceLevel($value)
 * @method static \MongoDB\Laravel\Eloquent\Builder|Npc whereDefenceMagic($value)
 * @method static \MongoDB\Laravel\Eloquent\Builder|Npc whereDefenceRanged($value)
 * @method static \MongoDB\Laravel\Eloquent\Builder|Npc whereDefenceSlash($value)
 * @method static \MongoDB\Laravel\Eloquent\Builder|Npc whereDefenceStab($value)
 * @method static \MongoDB\Laravel\Eloquent\Builder|Npc whereDrops($value)
 * @method static \MongoDB\Laravel\Eloquent\Builder|Npc whereDuplicate($value)
 * @method static \MongoDB\Laravel\Eloquent\Builder|Npc whereExamine($value)
 * @method static \MongoDB\Laravel\Eloquent\Builder|Npc whereHitpoints($value)
 * @method static \MongoDB\Laravel\Eloquent\Builder|Npc whereId($value)
 * @method static \MongoDB\Laravel\Eloquent\Builder|Npc whereImmunePoison($value)
 * @method static \MongoDB\Laravel\Eloquent\Builder|Npc whereImmuneVenom($value)
 * @method static \MongoDB\Laravel\Eloquent\Builder|Npc whereIncomplete($value)
 * @method static \MongoDB\Laravel\Eloquent\Builder|Npc whereLastUpdated($value)
 * @method static \MongoDB\Laravel\Eloquent\Builder|Npc whereMagicBonus($value)
 * @method static \MongoDB\Laravel\Eloquent\Builder|Npc whereMagicLevel($value)
 * @method static \MongoDB\Laravel\Eloquent\Builder|Npc whereMaxHit($value)
 * @method static \MongoDB\Laravel\Eloquent\Builder|Npc whereMembers($value)
 * @method static \MongoDB\Laravel\Eloquent\Builder|Npc whereName($value)
 * @method static \MongoDB\Laravel\Eloquent\Builder|Npc wherePoisonous($value)
 * @method static \MongoDB\Laravel\Eloquent\Builder|Npc whereRangedBonus($value)
 * @method static \MongoDB\Laravel\Eloquent\Builder|Npc whereRangedLevel($value)
 * @method static \MongoDB\Laravel\Eloquent\Builder|Npc whereReleaseDate($value)
 * @method static \MongoDB\Laravel\Eloquent\Builder|Npc whereSize($value)
 * @method static \MongoDB\Laravel\Eloquent\Builder|Npc whereSlayerLevel($value)
 * @method static \MongoDB\Laravel\Eloquent\Builder|Npc whereSlayerMasters($value)
 * @method static \MongoDB\Laravel\Eloquent\Builder|Npc whereSlayerMonster($value)
 * @method static \MongoDB\Laravel\Eloquent\Builder|Npc whereSlayerXp($value)
 * @method static \MongoDB\Laravel\Eloquent\Builder|Npc whereStrengthBonus($value)
 * @method static \MongoDB\Laravel\Eloquent\Builder|Npc whereStrengthLevel($value)
 * @method static \MongoDB\Laravel\Eloquent\Builder|Npc whereVenomous($value)
 * @method static \MongoDB\Laravel\Eloquent\Builder|Npc whereWikiName($value)
 * @method static \MongoDB\Laravel\Eloquent\Builder|Npc whereWikiUrl($value)
 *
 * @mixin \Eloquent
 */
class Npc extends Model
{
    protected $connection = 'mongodb';

    protected $table = 'monsters';

    protected $primaryKey = 'id';
}
