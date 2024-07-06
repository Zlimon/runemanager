<?php

namespace App\Helpers;

use App\Models\Setting;

class SettingHelper
{
    /**
     * @param string $key
     * @param $value
     * @param string $type
     * @return bool
     */
    public static function setSetting(string $key, $value, string $type = 'string'): bool
    {
        return Setting::set($key, $value);
    }

    /**
     * @param $key
     * @param $default
     * @return Setting|bool|int|mixed
     */
    public static function getSetting($key, $default = null): mixed
    {
        if (is_null($key)) {
            return new Setting;
        }

        if (is_array($key)) {
            return Setting::set($key[0], $key[1]);
        }

        $value = Setting::get($key);

        return is_null($value) ? value($default) : $value;
    }
}
