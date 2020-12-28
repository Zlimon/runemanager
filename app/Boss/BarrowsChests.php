<?php

namespace App\Boss;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Boss\BarrowsChests
 *
 * @property int $id
 * @property int $account_id
 * @property int $kill_count
 * @property int $rank
 * @property int $obtained
 * @property int $karils_coif
 * @property int $ahrims_hood
 * @property int $dharoks_helm
 * @property int $guthans_helm
 * @property int $torags_helm
 * @property int $veracs_helm
 * @property int $karils_leathertop
 * @property int $ahrims_robetop
 * @property int $dharoks_platebody
 * @property int $guthans_platebody
 * @property int $torags_platebody
 * @property int $veracs_brassard
 * @property int $karils_leatherskirt
 * @property int $ahrims_robeskirt
 * @property int $dharoks_platelegs
 * @property int $guthans_chainskirt
 * @property int $torags_platelegs
 * @property int $veracs_plateskirt
 * @property int $karils_crossbow
 * @property int $ahrims_staff
 * @property int $dharoks_greataxe
 * @property int $guthans_warspear
 * @property int $torags_hammers
 * @property int $veracs_flail
 * @property int $bolt_rack
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Account $account
 * @method static \Illuminate\Database\Eloquent\Builder|BarrowsChests newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|BarrowsChests newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|BarrowsChests query()
 * @method static \Illuminate\Database\Eloquent\Builder|BarrowsChests whereAccountId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BarrowsChests whereAhrimsHood($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BarrowsChests whereAhrimsRobeskirt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BarrowsChests whereAhrimsRobetop($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BarrowsChests whereAhrimsStaff($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BarrowsChests whereBoltRack($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BarrowsChests whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BarrowsChests whereDharoksGreataxe($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BarrowsChests whereDharoksHelm($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BarrowsChests whereDharoksPlatebody($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BarrowsChests whereDharoksPlatelegs($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BarrowsChests whereGuthansChainskirt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BarrowsChests whereGuthansHelm($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BarrowsChests whereGuthansPlatebody($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BarrowsChests whereGuthansWarspear($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BarrowsChests whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BarrowsChests whereKarilsCoif($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BarrowsChests whereKarilsCrossbow($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BarrowsChests whereKarilsLeatherskirt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BarrowsChests whereKarilsLeathertop($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BarrowsChests whereKillCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BarrowsChests whereObtained($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BarrowsChests whereRank($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BarrowsChests whereToragsHammers($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BarrowsChests whereToragsHelm($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BarrowsChests whereToragsPlatebody($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BarrowsChests whereToragsPlatelegs($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BarrowsChests whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BarrowsChests whereVeracsBrassard($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BarrowsChests whereVeracsFlail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BarrowsChests whereVeracsHelm($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BarrowsChests whereVeracsPlateskirt($value)
 * @mixin \Eloquent
 */
class BarrowsChests extends Model
{
    protected $table = 'barrows_chests';

    protected $fillable = [
        'obtained',
        'kill_count',
        'karils_coif',
        'ahrims_hood',
        'dharoks_helm',
        'guthans_helm',
        'torags_helm',
        'veracs_helm',
        'karils_leathertop',
        'ahrims_robetop',
        'dharoks_platebody',
        'guthans_platebody',
        'torags_platebody',
        'veracs_brassard',
        'karils_leatherskirt',
        'ahrims_robeskirt',
        'dharoks_platelegs',
        'guthans_chainskirt',
        'torags_platelegs',
        'veracs_plateskirt',
        'karils_crossbow',
        'ahrims_staff',
        'dharoks_greataxe',
        'guthans_warspear',
        'torags_hammers',
        'veracs_flail',
        'bolt_rack',
    ];

    protected $hidden = ['user_id'];

    public function account()
    {
        return $this->belongsTo(\App\Account::class);
    }
}
