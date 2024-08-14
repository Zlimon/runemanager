<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

/**
 *
 *
 * @property string $_id 1000 occurrences
 * @property string|null $buy_limit 1000 occurrences
 * @property int|null $cost 1000 occurrences
 * @property bool|null $duplicate 1000 occurrences
 * @property bool|null $equipable 1000 occurrences
 * @property bool|null $equipable_by_player 1000 occurrences
 * @property bool|null $equipable_weapon 1000 occurrences
 * @property string|null $equipment 1000 occurrences
 * @property string|null $examine 1000 occurrences
 * @property string|null $highalch 1000 occurrences
 * @property string|null $icon 1000 occurrences
 * @property mixed $id 1000 occurrences
 * @property bool|null $incomplete 1000 occurrences
 * @property string|null $last_updated 1000 occurrences
 * @property string|null $linked_id_item 1000 occurrences
 * @property string|null $linked_id_noted 1000 occurrences
 * @property string|null $linked_id_placeholder 1000 occurrences
 * @property string|null $lowalch 1000 occurrences
 * @property bool|null $members 1000 occurrences
 * @property string|null $name 1000 occurrences
 * @property bool|null $noteable 1000 occurrences
 * @property bool|null $noted 1000 occurrences
 * @property bool|null $placeholder 1000 occurrences
 * @property bool|null $quest_item 1000 occurrences
 * @property string|null $release_date 1000 occurrences
 * @property bool|null $stackable 1000 occurrences
 * @property string|null $stacked 1000 occurrences
 * @property bool|null $tradeable 1000 occurrences
 * @property bool|null $tradeable_on_ge 1000 occurrences
 * @property string|null $weapon 1000 occurrences
 * @property string|null $weight 1000 occurrences
 * @property string|null $wiki_name 1000 occurrences
 * @property string|null $wiki_url 1000 occurrences
 * @method static \MongoDB\Laravel\Eloquent\Builder|Item addHybridHas(\Illuminate\Database\Eloquent\Relations\Relation $relation, string $operator = '>=', string $count = 1, string $boolean = 'and', ?\Closure $callback = null)
 * @method static \MongoDB\Laravel\Eloquent\Builder|Item aggregate($function = null, $columns = [])
 * @method static \MongoDB\Laravel\Eloquent\Builder|Item getConnection()
 * @method static \MongoDB\Laravel\Eloquent\Builder|Item insert(array $values)
 * @method static \MongoDB\Laravel\Eloquent\Builder|Item insertGetId(array $values, $sequence = null)
 * @method static \MongoDB\Laravel\Eloquent\Builder|Item newModelQuery()
 * @method static \MongoDB\Laravel\Eloquent\Builder|Item newQuery()
 * @method static \MongoDB\Laravel\Eloquent\Builder|Item query()
 * @method static \MongoDB\Laravel\Eloquent\Builder|Item raw($value = null)
 * @method static \MongoDB\Laravel\Eloquent\Builder|Item whereBuyLimit($value)
 * @method static \MongoDB\Laravel\Eloquent\Builder|Item whereCost($value)
 * @method static \MongoDB\Laravel\Eloquent\Builder|Item whereDuplicate($value)
 * @method static \MongoDB\Laravel\Eloquent\Builder|Item whereEquipable($value)
 * @method static \MongoDB\Laravel\Eloquent\Builder|Item whereEquipableByPlayer($value)
 * @method static \MongoDB\Laravel\Eloquent\Builder|Item whereEquipableWeapon($value)
 * @method static \MongoDB\Laravel\Eloquent\Builder|Item whereEquipment($value)
 * @method static \MongoDB\Laravel\Eloquent\Builder|Item whereExamine($value)
 * @method static \MongoDB\Laravel\Eloquent\Builder|Item whereHighalch($value)
 * @method static \MongoDB\Laravel\Eloquent\Builder|Item whereIcon($value)
 * @method static \MongoDB\Laravel\Eloquent\Builder|Item whereId($value)
 * @method static \MongoDB\Laravel\Eloquent\Builder|Item whereIncomplete($value)
 * @method static \MongoDB\Laravel\Eloquent\Builder|Item whereLastUpdated($value)
 * @method static \MongoDB\Laravel\Eloquent\Builder|Item whereLinkedIdItem($value)
 * @method static \MongoDB\Laravel\Eloquent\Builder|Item whereLinkedIdNoted($value)
 * @method static \MongoDB\Laravel\Eloquent\Builder|Item whereLinkedIdPlaceholder($value)
 * @method static \MongoDB\Laravel\Eloquent\Builder|Item whereLowalch($value)
 * @method static \MongoDB\Laravel\Eloquent\Builder|Item whereMembers($value)
 * @method static \MongoDB\Laravel\Eloquent\Builder|Item whereName($value)
 * @method static \MongoDB\Laravel\Eloquent\Builder|Item whereNoteable($value)
 * @method static \MongoDB\Laravel\Eloquent\Builder|Item whereNoted($value)
 * @method static \MongoDB\Laravel\Eloquent\Builder|Item wherePlaceholder($value)
 * @method static \MongoDB\Laravel\Eloquent\Builder|Item whereQuestItem($value)
 * @method static \MongoDB\Laravel\Eloquent\Builder|Item whereReleaseDate($value)
 * @method static \MongoDB\Laravel\Eloquent\Builder|Item whereStackable($value)
 * @method static \MongoDB\Laravel\Eloquent\Builder|Item whereStacked($value)
 * @method static \MongoDB\Laravel\Eloquent\Builder|Item whereTradeable($value)
 * @method static \MongoDB\Laravel\Eloquent\Builder|Item whereTradeableOnGe($value)
 * @method static \MongoDB\Laravel\Eloquent\Builder|Item whereWeapon($value)
 * @method static \MongoDB\Laravel\Eloquent\Builder|Item whereWeight($value)
 * @method static \MongoDB\Laravel\Eloquent\Builder|Item whereWikiName($value)
 * @method static \MongoDB\Laravel\Eloquent\Builder|Item whereWikiUrl($value)
 * @mixin \Eloquent
 */
class Item extends Model
{
    protected $connection = 'mongodb';

    protected $primaryKey = 'id';

    public function icon(): string
    {
        return ItemIcon::where('id', $this->id)->first()->icon;
    }

    public static function randomItemId(): int
    {
        return (int) Item::where([['noted', false], ['placeholder', false], ['duplicate', false]])->whereNotNull('release_date')->get()->pluck('id')->random();
    }
}
