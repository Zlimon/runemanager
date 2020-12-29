<?php

namespace App\Boss;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Boss\Kraken
 *
 * @property int $id
 * @property int $account_id
 * @property int $kill_count
 * @property int $rank
 * @property int $obtained
 * @property int $pet_kraken
 * @property int $kraken_tentacle
 * @property int $trident_of_the_seas_(full)
 * @property int $jar_of_dirt
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Account $account
 * @method static \Illuminate\Database\Eloquent\Builder|Kraken newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Kraken newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Kraken query()
 * @method static \Illuminate\Database\Eloquent\Builder|Kraken whereAccountId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Kraken whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Kraken whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Kraken whereJarOfDirt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Kraken whereKillCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Kraken whereKrakenTentacle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Kraken whereObtained($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Kraken wherePetKraken($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Kraken whereRank($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Kraken whereTridentOfTheSeas(full)($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Kraken whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Kraken extends Model
{
    protected $table = 'kraken';

    protected $fillable = [
        'obtained',
        'kill_count',
        'pet_kraken',
        'kraken_tentacle',
        'trident_of_the_seas_(full)',
        'jar_of_dirt',
    ];

    protected $hidden = ['user_id'];

    public function account()
    {
        return $this->belongsTo(\App\Account::class);
    }
}
