<?php

namespace App\Boss;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Boss\Sarachnis
 *
 * @property int $id
 * @property int $account_id
 * @property int $kill_count
 * @property int $rank
 * @property int $obtained
 * @property int $sraracha
 * @property int $jar_of_eyes
 * @property int $giant_egg_sac(full)
 * @property int $sarachnis_cudgel
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Account $account
 * @method static \Illuminate\Database\Eloquent\Builder|Sarachnis newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Sarachnis newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Sarachnis query()
 * @method static \Illuminate\Database\Eloquent\Builder|Sarachnis whereAccountId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Sarachnis whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Sarachnis whereGiantEggSac(full)($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Sarachnis whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Sarachnis whereJarOfEyes($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Sarachnis whereKillCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Sarachnis whereObtained($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Sarachnis whereRank($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Sarachnis whereSarachnisCudgel($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Sarachnis whereSraracha($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Sarachnis whereUpdatedAt($value)
 * @mixin \Eloquent
 * @method static \Illuminate\Database\Eloquent\Builder|Sarachnis whereGiantEggSac(full)($value)
 */
class Sarachnis extends Model
{
    protected $table = 'sarachnis';

    protected $fillable = [
        'obtained',
        'kill_count',
        'sraracha',
        'jar_of_eyes',
        'giant_egg_sac(full)',
        'sarachnis_cudgel',
    ];

    protected $hidden = ['user_id'];

    public function account()
    {
        return $this->belongsTo(\App\Account::class);
    }
}
