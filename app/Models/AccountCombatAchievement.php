<?php

namespace App\Models;

use App\Support\CombatAchievements;
use Illuminate\Database\Eloquent\Attributes\Unguarded;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * An account's Combat Achievement progress: total points and
 * the count of completed tasks per tier ({tier: int}), upserted on each plugin
 * snapshot push. Individual task unlocks are not stored here; they reach the
 * live feed via a separate push.
 */
#[Unguarded]
class AccountCombatAchievement extends Model
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
            'points' => 'int',
            'tiers' => 'array',
        ];
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function completedTaskCount(): int
    {
        return CombatAchievements::completedTaskCount($this->tiers ?? []);
    }
}
