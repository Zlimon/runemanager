<?php

namespace App\Models;

use App\Support\CombatAchievements;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * SPEC §5.2/§7.1 — an account's Combat Achievement progress: total points and
 * the count of completed tasks per tier ({tier: int}), upserted on each plugin
 * snapshot push. Individual task unlocks are not stored here; they reach the
 * live feed via a separate push.
 *
 * @property int $account_id
 * @property int $points
 * @property array<string, int> $tiers
 */
class AccountCombatAchievement extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'account_id' => 'int',
        'points' => 'int',
        'tiers' => 'array',
    ];

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function completedTaskCount(): int
    {
        return CombatAchievements::completedTaskCount($this->tiers ?? []);
    }
}
