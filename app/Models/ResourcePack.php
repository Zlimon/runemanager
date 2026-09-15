<?php

namespace App\Models;

use App\Helpers\SettingHelper;
use App\Support\Instance;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $name
 * @property string $alias
 * @property string $version
 * @property string $author
 * @property string $url
 * @property string $tags
 * @property int $dark_mode
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @method static \Illuminate\Database\Eloquent\Builder|ResourcePack newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ResourcePack newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ResourcePack query()
 * @method static \Illuminate\Database\Eloquent\Builder|ResourcePack whereAlias($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ResourcePack whereAuthor($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ResourcePack whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ResourcePack whereDarkMode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ResourcePack whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ResourcePack whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ResourcePack whereTags($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ResourcePack whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ResourcePack whereUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ResourcePack whereVersion($value)
 *
 * @mixin \Eloquent
 */
class ResourcePack extends Model
{
    use HasFactory;

    /** The bundled vanilla template pack, always offered as "Default Vanilla". */
    public const VANILLA = 'sample-vanilla';

    /** Cookie value a logged-out visitor stores to opt out of resource packs. */
    public const GUEST_NO_PACK = 'none';

    /** The member who installed this pack from the hub (null = instance-managed). */
    public function installedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'installed_by_user_id');
    }

    /** Members who have explicitly selected this pack as their personal theme. */
    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'resource_pack_id');
    }

    /** The bundled pack ships with the app and can never be deleted. */
    public function isVanilla(): bool
    {
        return $this->name === self::VANILLA;
    }

    /**
     * The pack to actually render for a viewer: their personal pick, then the
     * instance-global default, and finally the bundled Default Vanilla — so a
     * pack is always in effect and there's no separate "no resource pack"
     * rendering path. Null only if even the vanilla row is absent.
     */
    public static function effectiveFor(?User $user): ?self
    {
        // An explicit personal "no resource pack" wins over every default —
        // the viewer gets the plain, non-textured interface.
        if ($user?->disable_resource_pack) {
            return null;
        }

        // Guests carry the same preference in a cookie, since they have no row
        // to store it on (see Instance::RESOURCE_PACK_COOKIE).
        $guestChoice = $user === null ? self::guestPackChoice() : null;

        if ($guestChoice === self::GUEST_NO_PACK) {
            return null;
        }

        $id = $guestChoice ?? $user?->effectiveResourcePackId() ?? SettingHelper::getSetting('resource_pack_id');
        $pack = $id ? self::find($id) : null;

        return $pack ?? self::where('name', self::VANILLA)->first();
    }

    /**
     * A logged-out visitor's stored pack preference: the literal
     * {@see self::GUEST_NO_PACK}, an installed pack id, or null when they have
     * expressed no preference (or the cookie points at a deleted pack).
     */
    private static function guestPackChoice(): string|int|null
    {
        $cookie = request()->cookie(Instance::RESOURCE_PACK_COOKIE);

        if ($cookie === self::GUEST_NO_PACK) {
            return self::GUEST_NO_PACK;
        }

        if (is_numeric($cookie) && self::whereKey((int) $cookie)->exists()) {
            return (int) $cookie;
        }

        return null;
    }

    /**
     * Installed packs for the picker, vanilla pinned first then alphabetical.
     *
     * @return list<array{id: int, name: string, alias: string, icon_url: ?string, dark_mode: bool}>
     */
    public static function pickerList(): array
    {
        return self::query()->get()
            ->sortBy(fn (self $pack): string => $pack->name === self::VANILLA ? '' : mb_strtolower($pack->alias))
            ->map(fn (self $pack): array => $pack->toPickerArray())
            ->values()
            ->all();
    }

    /**
     * Public URL to the pack's icon.png thumbnail (shipped in every pack), or
     * null when the file isn't on disk.
     */
    public function getIconUrlAttribute(): ?string
    {
        $relative = "resource-packs/{$this->name}/icon.png";

        return is_file(public_path($relative)) ? '/'.$relative : null;
    }

    /**
     * Shape used by the resource-pack picker UI (owner-global + user-personal).
     *
     * @return array{id: int, name: string, alias: string, icon_url: ?string, dark_mode: bool}
     */
    public function toPickerArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'alias' => $this->alias,
            'icon_url' => $this->icon_url,
            'dark_mode' => (bool) $this->dark_mode,
            'installed_by_user_id' => $this->installed_by_user_id,
        ];
    }
}
