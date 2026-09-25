<?php

namespace App\Models;

use App\Services\ResourcePacks\PackPalette;
use App\Support\Instance;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\File;

class ResourcePack extends Model
{
    /** The bundled vanilla template pack, always offered as "Vanilla". */
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
     * instance-global default, and finally the bundled Vanilla. Null when the
     * viewer has opted out of resource packs, or if even the vanilla row is
     * absent.
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

        $id = $guestChoice ?? $user?->effectiveResourcePackId() ?? Instance::defaultResourcePackId();
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
     * The viewer's own packs, for the quick switcher in the nav dropdown: the
     * ones they installed themselves, alphabetically.
     *
     * The pack they currently have selected is always included, even when
     * somebody else installed it. Without that the switcher could not show
     * what is actually in effect, and a member who picked another member's
     * pack on the Appearance page would have no way back to it from here.
     *
     * Packs still being fetched are left out — their row exists before their
     * files do, so selecting one would strip the interface bare until the job
     * finished ({@see InstallResourcePack::isReady()}).
     *
     * @return list<array{id: int, name: string, alias: string, icon_url: ?string}>
     */
    public static function navListFor(User $user): array
    {
        return self::query()
            ->where('version', '!=', 'pending')
            ->where(fn (Builder $query) => $query
                ->where('installed_by_user_id', $user->id)
                ->orWhere('id', $user->resource_pack_id))
            ->orderByRaw('LOWER(alias)')
            ->get()
            ->map(fn (self $pack): array => [
                'id' => $pack->id,
                'name' => $pack->name,
                'alias' => $pack->alias,
                'icon_url' => $pack->icon_url,
            ])
            ->values()
            ->all();
    }

    /**
     * Installed packs for the picker, each shaped by {@see self::toPickerArray()},
     * vanilla pinned first then alphabetical.
     *
     * @return list<array<string, mixed>>
     */
    public static function pickerList(): array
    {
        return self::query()->withCount('users')->get()
            ->sortBy(fn (self $pack): string => $pack->name === self::VANILLA ? '' : mb_strtolower($pack->alias))
            ->map(fn (self $pack): array => $pack->toPickerArray())
            ->values()
            ->all();
    }

    /**
     * Public URL to the pack's icon.png thumbnail (shipped in every pack), or
     * null when the file isn't on disk.
     */
    protected function iconUrl(): Attribute
    {
        return Attribute::make(get: function (): ?string {
            $relative = "resource-packs/{$this->name}/icon.png";

            return is_file(public_path($relative)) ? '/'.$relative : null;
        });
    }

    /**
     * The pack's stylesheet URL, cache-busted on the CSS file's mtime so a
     * template edit (which does not touch updated_at) is picked up without a
     * hard refresh. Null when the row exists but its files do not, a state the
     * admin packs screen surfaces and callers must survive rather than render a
     * dead <link> for.
     */
    public function stylesheetUrl(): ?string
    {
        $relative = "resource-packs/{$this->name}/resource-pack.css";
        $path = public_path($relative);

        if (! File::exists($path)) {
            return null;
        }

        return asset($relative).'?v='.filemtime($path);
    }

    /**
     * The accent to paint functional controls with: {@see $accent_color} moved
     * away from {@see $background_color}, hue intact, until the pair clears the
     * WCAG 1.4.11 contrast bar — and the accent itself, unchanged, when it
     * already does. That keeps the pack author's authentic accent available for
     * decorative borders and pack textures while inputs and other boundaries get
     * an edge that is reliably visible.
     *
     * Computed on read rather than stored: it is a pure function of two columns
     * the row already carries, so there is nothing to migrate or keep in sync.
     * Null unless both of those are present.
     */
    protected function accentUiColor(): Attribute
    {
        return Attribute::make(get: function (): ?string {
            if ($this->background_color === null || $this->accent_color === null) {
                return null;
            }

            return (new PackPalette)->uiAccent($this->accent_color, $this->background_color);
        });
    }

    /**
     * Shape used by the resource-pack picker UI (owner-global + user-personal).
     *
     * Carries more than the grid draws: the confirmation dialog shown before a
     * switch describes what the pack will do to the interface, and the sampled
     * palette plus dark_mode are the two things a viewer cannot infer from the
     * icon — picking a pack repaints every surface from that palette and moves
     * the whole interface between the light and dark themes.
     *
     * @return array{id: int, name: string, alias: string, icon_url: ?string, dark_mode: bool, installed_by_user_id: ?int, author: ?string, version: ?string, tags: list<string>, background_color: ?string, accent_color: ?string, users_count: ?int}
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
            'author' => $this->author ?: null,
            'version' => $this->version ?: null,
            'tags' => array_values(array_filter(array_map('trim', explode(',', (string) $this->tags)))),
            'background_color' => $this->background_color,
            'accent_color' => $this->accent_color,
            // Only present when the query asked for it (pickerList() does), so
            // reading it never falls back to a query per pack.
            'users_count' => $this->users_count,
        ];
    }
}
