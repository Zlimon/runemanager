<?php

namespace App\Helpers;

use App\Models\Setting;

class SettingHelper
{
    public static function setSetting(string $key, $value, string $type = 'string'): bool
    {
        return Setting::set($key, $value, $type);
    }

    /**
     * A setting's value cast to its stored type, or `$default` when it is unset.
     */
    public static function getSetting(string $key, $default = null): mixed
    {
        $value = Setting::get($key);

        return is_null($value) ? value($default) : $value;
    }
}
