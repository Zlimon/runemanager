<?php

namespace App\Models;

use App\Support\Instance;
use Illuminate\Database\Eloquent\Attributes\Appends;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
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
        ];
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
     */
    protected function icon(): Attribute
    {
        return Attribute::make(get: fn (): ?string => data_get(Item::lookupByOsrsId($this->icon_id), 'icon'));
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
     *   2. The instance-global default (settings.resource_pack_id, set by `resourcepack:switch`)
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
