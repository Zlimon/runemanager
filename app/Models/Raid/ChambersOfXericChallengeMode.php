<?php

namespace App\Models\Raid;

use App\Models\Account;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChambersOfXericChallengeMode extends Model
{
    protected $table = 'chambers_of_xeric_challenge_mode';

    protected $fillable = [
        'obtained',
        'kill_count',
    ];

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }
}