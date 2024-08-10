<?php

namespace App\Models\Pvp;

use App\Models\Account;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BountyHunterLegacyRogue extends Model
{
    protected $table = 'bounty_hunter_legacy_rogue';

    protected $fillable = [
        'obtained',
        'kill_count',
    ];

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }
}