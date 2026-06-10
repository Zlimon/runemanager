<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
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
        ];
    }
}
