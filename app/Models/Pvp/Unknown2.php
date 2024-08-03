<?php

namespace App\Models\Pvp;

use App\Models\Account;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Unknown2 extends Model
{
    protected $table = 'unknown2';

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