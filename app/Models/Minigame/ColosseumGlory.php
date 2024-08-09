<?php

namespace App\Models\Minigame;

use App\Models\Account;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ColosseumGlory extends Model
{
    protected $table = 'colosseum_glory';

    protected $fillable = [
        'obtained',
        'kill_count',
    ];

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }
}