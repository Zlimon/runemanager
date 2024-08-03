<?php

namespace App\Models\Raid;

use App\Models\Account;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TombsOfAmascutExpertMode extends Model
{
    protected $table = 'tombs_of_amascut_expert_mode';

    protected $fillable = [
        'obtained',
        'kill_count',
    ];

    protected $hidden = ['user_id'];

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }
}