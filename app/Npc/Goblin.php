<?php
// TODO remove later
namespace App\Npc;

use Illuminate\Database\Eloquent\Model;

class Goblin extends Model
{
    protected $table = 'goblin';

    protected $fillable = [
        'obtained',
        'kill_count',
        'bones',
        'water_rune',
        'coins',
        'hammer',
        'beer',
        'goblin_mail',
    ];

    protected $hidden = ['user_id'];

    public function account()
    {
        return $this->belongsTo(\App\Account::class);
    }
}
