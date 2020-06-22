<?php

namespace App\Boss;

use Illuminate\Database\Eloquent\Model;

class BarrowsChests extends Model
{
    protected $fillable = [
        'obtained',
        'kill_count',
        'torags_helm',
        'kill_count',
        'guthans_chainskirt',
        'dharoks_platelegs',
        'karils_coif',
        'karils_leathertop',
        'dharoks_platebody',
        'ahrims_staff',
        'bolt_rack',
        'ahrims_robetop',
        'guthans_platebody',
        'veracs_flail',
        'guthans_helm',
        'torags_platelegs',
        'torags_hammers',
        'veracs_helm',
        'guthans_warspear',
        'ahrims_robeskirt',
        'karils_crossbow',
        'ahrims_hood',
        'torags_platebody',
        'dharoks_greataxe',
        'veracs_brassard',
        'veracs_plateskirt',
        'obtained',
        'dharoks_helm',
        'karils_leatherskirt',
    ];

    protected $hidden = ['user_id'];
}
