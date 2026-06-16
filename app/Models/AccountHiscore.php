<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AccountHiscore extends Model
{
    use HasFactory;

    protected $fillable = [
        'account_id',
        'entries',
        'fetched_at',
    ];

    protected $casts = [
        'entries' => 'array',
        'fetched_at' => 'datetime',
    ];

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }
}
