<?php

namespace App\Models;

use App\Support\Instance;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * An account's Wise Old Man profile summary, refreshed by `wom:sync`. A row
 * with a null `wom_player_id` means WOM does not know the account (the last
 * lookup 404'd at `not_found_at`). We only POST a WOM update — which is what
 * creates a WOM profile — once the owner set `tracking_opt_in`.
 */
#[Fillable([
    'account_id',
    'wom_player_id',
    'type',
    'build',
    'status',
    'exp',
    'ehp',
    'ehb',
    'ttm',
    'tt200m',
    'combat_level',
    'registered_at',
    'wom_updated_at',
    'last_changed_at',
    'update_requested_at',
    'backfilled_at',
    'milestones_synced_at',
    'not_found_at',
    'synced_at',
    'tracking_opt_in',
    'show_on_progress',
])]
class WomPlayer extends Model
{
    use HasFactory;

    /**
     * The column defaults, so an unsaved row reads the same as a stored one.
     *
     * @var array<string, mixed>
     */
    protected $attributes = [
        'tracking_opt_in' => false,
        'show_on_progress' => true,
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'wom_player_id' => 'int',
            'exp' => 'int',
            'ehp' => 'float',
            'ehb' => 'float',
            'ttm' => 'float',
            'tt200m' => 'float',
            'combat_level' => 'int',
            'registered_at' => 'datetime',
            'wom_updated_at' => 'datetime',
            'last_changed_at' => 'datetime',
            'update_requested_at' => 'datetime',
            'backfilled_at' => 'datetime',
            'milestones_synced_at' => 'datetime',
            'not_found_at' => 'datetime',
            'synced_at' => 'datetime',
            'tracking_opt_in' => 'bool',
            'show_on_progress' => 'bool',
        ];
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function isLinked(): bool
    {
        return $this->wom_player_id !== null;
    }

    /** Rows that matched a profile on WOM. */
    #[Scope]
    protected function linked(Builder $query): void
    {
        $query->whereNotNull('wom_player_id');
    }

    /**
     * Linked profiles WOM rates `active` that gained something within the
     * admin's activity window ({@see Instance::womActiveWithinDays()}).
     */
    #[Scope]
    protected function active(Builder $query): void
    {
        $query->whereNotNull('wom_player_id')
            ->where('status', 'active')
            ->where('last_changed_at', '>=', now()->subDays(Instance::womActiveWithinDays()));
    }

    /** Rows whose owner has not hidden them from the /progress page. */
    #[Scope]
    protected function visibleOnProgress(Builder $query): void
    {
        $query->where('show_on_progress', true);
    }
}
