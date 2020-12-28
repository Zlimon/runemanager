<?php

namespace App\Boss;

use Illuminate\Database\Eloquent\Model;

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
