<?php

namespace App\Models;

use App\Support\Diaries;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * SPEC §5.2 — an account's Achievement Diary completion, stored as a
 * {area: {tier: bool}} map (PostgreSQL jsonb), upserted on each plugin push.
 *
 * @property int $account_id
 * @property array<string, array<string, bool>> $diaries
 */
class AccountDiary extends Model
{
    protected $guarded = [];

    protected $casts = [
        'account_id' => 'int',
        'diaries' => 'array',
    ];

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function completedCount(): int
    {
        return Diaries::countCompleted($this->diaries ?? []);
    }
}
