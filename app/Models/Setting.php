<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Unguarded;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

/**
 * Instance-wide key-value settings; values are stored as strings and cast on
 * read by their `type` column. Reached through App\Helpers\SettingHelper (and
 * App\Support\Instance on top of it) rather than directly.
 */
#[Unguarded]
class Setting extends Model
{
    /**
     * A setting's value cast to its stored type, or the default — the given
     * one, else the one declared in config/settings.php — when it is unset.
     */
    public static function get($key, $default = null): mixed
    {
        if (self::has($key)) {
            $setting = self::getAllSettings()->where('key', $key)->first();

            return self::castValue($setting->value, $setting->type);
        }

        return self::getDefaultValue($key, $default);
    }

    public static function add($key, $value, string $type = 'string'): bool
    {
        if (self::has($key)) {
            return self::set($key, $value, $type);
        }

        return self::create(['key' => $key, 'value' => $value, 'type' => $type]) ? $value : false;
    }

    public static function set($key, $value, string $type = 'string'): bool
    {
        if ($setting = self::getAllSettings()->where('key', $key)->first()) {
            return $setting->update(
                [
                    'key' => $key,
                    'value' => $value,
                    'type' => $type,
                ]
            ) ? $value : false;
        }

        return self::add($key, $value, $type);
    }

    public static function has($key): bool
    {
        return (bool) self::getAllSettings()->whereStrict('key', $key)->count();
    }

    public static function getDefaultValueForField($field): mixed
    {
        return self::getDefinedSettingFields()
            ->pluck('value', 'key')
            ->get($field);
    }

    private static function getDefaultValue($key, $default): mixed
    {
        return is_null($default) ? self::getDefaultValueForField($key) : $default;
    }

    /**
     * Every setting field declared in config/settings.php.
     */
    private static function getDefinedSettingFields(): Collection
    {
        return collect(config('settings'))->pluck('elements')->flatten(1);
    }

    /**
     * Cast a stored value into its declared type. The `type` column is a
     * space-padded char(20), so trim before matching; returning `mixed` keeps
     * string settings intact (a `bool|int` return type silently coerced them).
     */
    private static function castValue($value, $castTo): mixed
    {
        return match (trim((string) $castTo)) {
            'int', 'integer' => intval($value),
            'bool', 'boolean' => boolval($value),
            default => $value,
        };
    }

    public static function getAllSettings(): mixed
    {
        return self::all();
    }
}
