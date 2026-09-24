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
     * Container key holding this request's memoized row set.
     *
     * The container is the store rather than a static property so the memo
     * dies with the request — and with each test's freshly built application
     * — instead of outliving either.
     */
    private const MEMO_KEY = 'settings.all';

    /**
     * Drop the memo so the next read re-queries.
     *
     * The write events cover the paths that bypass this class — a factory, a
     * seeder, a direct delete. They are not enough on their own: a model boots
     * once per process, so these listeners stay bound to the dispatcher of the
     * application that booted the class, and a later application (each test
     * builds one) never fires them. Hence {@see self::set()} and
     * {@see self::add()} also flush directly.
     */
    protected static function booted(): void
    {
        static::saved(fn () => self::flushMemo());
        static::deleted(fn () => self::flushMemo());
    }

    public static function flushMemo(): void
    {
        app()->forgetInstance(self::MEMO_KEY);
    }

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

    /**
     * Whether the row was written. Reporting the write — not the value — is
     * what keeps storing a falsy value such as `0` or `''` from returning
     * false through the `bool` return type.
     */
    public static function add($key, $value, string $type = 'string'): bool
    {
        return self::set($key, $value, $type);
    }

    /**
     * Whether the row was written; see {@see self::add()} on the return value.
     */
    public static function set($key, $value, string $type = 'string'): bool
    {
        // Deliberately queried rather than read from the memo: a write must see
        // the table as it is now. A memo primed earlier in the process can name
        // a row that no longer exists (an artisan boot reads settings, then
        // `migrate:fresh` drops them), and updating it would write nothing.
        $setting = self::query()->where('key', $key)->first();

        $written = $setting !== null
            ? $setting->update(['key' => $key, 'value' => $value, 'type' => $type])
            : (bool) self::create(['key' => $key, 'value' => $value, 'type' => $type]);

        self::flushMemo();

        return $written;
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

    /**
     * Every stored setting row, queried once per request.
     *
     * HandleInertiaRequests and app.blade.php both walk most of
     * App\Support\Instance, and every Setting::get() calls this twice (once
     * through has()) — so rendering the accounts index used to run sixteen
     * full-table selects where it now runs one.
     */
    public static function getAllSettings(): Collection
    {
        if (! app()->bound(self::MEMO_KEY)) {
            // Scoped, not a plain instance: a long-running worker resets scoped
            // bindings between jobs, so the memo cannot outlive one unit of work.
            app()->scoped(self::MEMO_KEY, fn (): Collection => self::all());
        }

        return app(self::MEMO_KEY);
    }
}
