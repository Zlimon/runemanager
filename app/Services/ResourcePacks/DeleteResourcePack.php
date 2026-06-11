<?php

namespace App\Services\ResourcePacks;

use App\Helpers\SettingHelper;
use App\Models\ResourcePack;
use App\Models\User;
use Illuminate\Support\Facades\File;

/**
 * Remove an installed resource pack: detach everyone using it, clear the
 * instance-global default if it pointed here, drop the extracted assets and
 * delete the row. Callers are responsible for authorization and for the
 * "is this deletable?" guards ({@see ResourcePack::isVanilla()} / the global
 * default check); {@see isInstanceDefault()} is offered for the latter.
 */
class DeleteResourcePack
{
    public function isInstanceDefault(ResourcePack $pack): bool
    {
        return (int) SettingHelper::getSetting('resource_pack_id', 0) === $pack->id;
    }

    public function run(ResourcePack $pack): void
    {
        // Anyone with this as their personal pick falls back to the default.
        User::query()
            ->where('resource_pack_id', $pack->id)
            ->update(['resource_pack_id' => null]);

        if ($this->isInstanceDefault($pack)) {
            SettingHelper::setSetting('resource_pack_id', 0, 'int');
        }

        $dir = public_path('resource-packs/'.$pack->name);
        if (File::isDirectory($dir)) {
            File::deleteDirectory($dir);
        }

        $pack->delete();
    }
}
