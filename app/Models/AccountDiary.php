<?php

namespace App\Models;

use App\Support\Diaries;
use Illuminate\Database\Eloquent\Attributes\Unguarded;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * An account's Achievement Diary completion, stored as a
 * {area: {tier: bool}} map (PostgreSQL jsonb), upserted on each plugin push.
 */
#[Unguarded]
class AccountDiary extends Model
{
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'account_id' => 'int',
            'diaries' => 'array',
        ];
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function completedCount(): int
    {
        return Diaries::countCompleted($this->diaries ?? []);
    }
}
