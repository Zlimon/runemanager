<?php

namespace App\Boss;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Boss\Cerberus
 *
 * @property int $id
 * @property int $account_id
 * @property int $kill_count
 * @property int $rank
 * @property int $obtained
 * @property int $hellpuppy
 * @property int $eternal_crystal
 * @property int $pegasian_crystal
 * @property int $primordial_crystal
 * @property int $jar_of_souls
 * @property int $smouldering_stone
 * @property int $key_master_teleport
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Account $account
 * @method static \Illuminate\Database\Eloquent\Builder|Cerberus newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Cerberus newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Cerberus query()
 * @method static \Illuminate\Database\Eloquent\Builder|Cerberus whereAccountId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Cerberus whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Cerberus whereEternalCrystal($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Cerberus whereHellpuppy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Cerberus whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Cerberus whereJarOfSouls($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Cerberus whereKeyMasterTeleport($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Cerberus whereKillCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Cerberus whereObtained($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Cerberus wherePegasianCrystal($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Cerberus wherePrimordialCrystal($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Cerberus whereRank($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Cerberus whereSmoulderingStone($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Cerberus whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Cerberus extends Model
{
    protected $table = 'cerberus';

    protected $fillable = [
        'obtained',
        'kill_count',
        'hellpuppy',
        'eternal_crystal',
        'pegasian_crystal',
        'primordial_crystal',
        'jar_of_souls',
        'smouldering_stone',
        'key_master_teleport',
    ];

    protected $hidden = ['user_id'];

    public function account()
    {
        return $this->belongsTo(\App\Account::class);
    }
}
