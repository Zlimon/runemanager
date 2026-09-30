<?php

namespace App\Models;

use App\Support\Instance;
use App\Support\Roles;
use Illuminate\Database\Eloquent\Attributes\Appends;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Jetstream\HasProfilePhoto;
use Laravel\Jetstream\HasTeams;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

#[Fillable(['name', 'email', 'password', 'icon_id', 'resource_pack_id', 'dark_mode'])]
#[Hidden(['password', 'remember_token', 'two_factor_recovery_codes', 'two_factor_secret'])]
#[Appends(['profile_photo_url'])]
class User extends Authenticatable
{
    use HasApiTokens;
    use HasFactory;
    use HasProfilePhoto;
    use HasRoles, HasTeams {
        // Both traits ship a teams() relation; this app uses Jetstream's.
        HasTeams::teams insteadof HasRoles;
    }
    use Notifiable;
    use TwoFactorAuthenticatable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'dark_mode' => 'boolean',
            'disable_resource_pack' => 'boolean',
            'suspended_at' => 'datetime',
        ];
    }

    /**
     * A suspended user cannot log in on the website or the plugin, and any
     * session or token they still hold is refused (EnsureUserIsNotSuspended).
     */
    public function isSuspended(): bool
    {
        return $this->suspended_at !== null;
    }

    #[Scope]
    protected function suspended(Builder $query): void
    {
        $query->whereNotNull('suspended_at');
    }

    #[Scope]
    protected function notSuspended(Builder $query): void
    {
        $query->whereNull('suspended_at');
    }

    /**
     * Holders of the Owner role. Queried through the relation rather than
     * Spatie's `role()` scope, which throws while the role has not been seeded.
     */
    #[Scope]
    protected function owners(Builder $query): void
    {
        $query->whereHas('roles', fn (Builder $roles) => $roles->where('name', Roles::OWNER));
    }

    /**
     * Whether this user is the only Owner who can still log in. Suspending,
     * demoting or deleting them would leave the instance without an owner.
     * Both halves are read from the database rather than loaded relations, so
     * LastOwnerGuard sees a concurrent change once it holds the owner locks.
     */
    public function isLastActiveOwner(): bool
    {
        return $this->roles()->where('name', Roles::OWNER)->exists()
            && static::query()->owners()->notSuspended()->whereKeyNot($this->getKey())->doesntExist();
    }

    public function suspendedBy(): BelongsTo
    {
        return $this->belongsTo(self::class, 'suspended_by_user_id');
    }

    public function account(): HasMany
    {
        return $this->hasMany(Account::class);
    }

    public function calendarEvents(): HasMany
    {
        return $this->hasMany(CalendarEvent::class);
    }

    public function announcements(): HasMany
    {
        return $this->hasMany(Announcement::class);
    }

    /**
     * The icon image of the OSRS item the user picked as their avatar icon.
     *
     * `users.icon_id` is NOT NULL today, so the null branch is a guard rather
     * than a stored state: `lookupByOsrsId(null)` would otherwise still run its
     * Mongo aggregation (matching `id: ''`) before returning nothing. Account
     * lists resolve the rest of the rows in one batch, in AccountResource.
     */
    protected function icon(): Attribute
    {
        return Attribute::make(get: fn (): ?string => $this->icon_id === null
            ? null
            : data_get(Item::lookupByOsrsId($this->icon_id), 'icon'))->shouldCache();
    }

    public function resourcePack(): BelongsTo
    {
        return $this->belongsTo(ResourcePack::class);
    }

    /**
     * Resolve the pack that should actually be rendered for this user.
     *
     * Hierarchy (first non-null wins):
     *   1. The user's own override (users.resource_pack_id)
     *   2. The instance-global default (settings.resource_pack_id, set from Settings → General)
     *   3. null — render with no pack
     */
    public function effectiveResourcePackId(): ?int
    {
        if ($this->resource_pack_id !== null) {
            return $this->resource_pack_id;
        }

        return Instance::defaultResourcePackId();
    }
}
