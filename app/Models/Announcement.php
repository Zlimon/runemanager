<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

#[Fillable(['user_id', 'title', 'body', 'expires_at'])]
class Announcement extends Model
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
            'expires_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * OSRS accounts that have seen this announcement in-game (so the plugin
     * doesn't show it again).
     */
    public function acknowledgedBy(): BelongsToMany
    {
        return $this->belongsToMany(Account::class, 'announcement_account')->withTimestamps();
    }

    /**
     * Not yet expired (no expiry, or expiry in the future), newest first.
     */
    #[Scope]
    protected function active(Builder $query): void
    {
        $query
            ->where(fn (Builder $q) => $q->whereNull('expires_at')->orWhere('expires_at', '>', now()))
            ->latest();
    }
}
