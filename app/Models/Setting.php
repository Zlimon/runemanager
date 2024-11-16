<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

/**
 * @property int $id
 * @property string $key
 * @property string $value
 * @property string $type
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 *
 * @method static \Illuminate\Database\Eloquent\Builder|Setting newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Setting newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Setting query()
 * @method static \Illuminate\Database\Eloquent\Builder|Setting whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Setting whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Setting whereKey($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Setting whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Setting whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Setting whereValue($value)
 *
 * @mixin \Eloquent
 */
class Setting extends Model
{
    use HasFactory;

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * Get a settings value
     *
     * @param  null  $default
     * @return bool|int|mixed
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
     * Add a settings value
     *
     * @param  string  $type
     */
    public static function add($key, $value, $type = 'string'): bool
    {
        if (self::has($key)) {
            return self::set($key, $value, $type);
        }

        return self::create(['key' => $key, 'value' => $value, 'type' => $type]) ? $value : false;
    }

    /**
     * Set a value for setting
     *
     * @param  string  $type
     */
    public static function set($key, $value, $type = 'string'): bool
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

    /**
     * Remove a setting
     */
    public static function remove($key): bool
    {
        if (self::has($key)) {
            return self::whereName($key)->delete();
        }

        return false;
    }

    /**
     * Check if setting exists
     */
    public static function has($key): bool
    {
        return (bool) self::getAllSettings()->whereStrict('key', $key)->count();
    }

    /**
     * Get the validation rules for setting fields
     */
    public static function getValidationRules(): array
    {
        return self::getDefinedSettingFields()->pluck('rules', 'key')
            ->reject(
                function ($value) {
                    return is_null($value);
                }
            )->toArray();
    }

    /**
     * Get the data type of setting
     */
    public static function getDataType($field): mixed
    {
        $type = self::getDefinedSettingFields()
            ->pluck('data', 'key')
            ->get($field);

        return is_null($type) ? 'string' : $type;
    }

    /**
     * Get default value for a setting
     */
    public static function getDefaultValueForField($field): mixed
    {
        return self::getDefinedSettingFields()
            ->pluck('value', 'key')
            ->get($field);
    }

    /**
     * Get default value from config if no value passed
     */
    private static function getDefaultValue($key, $default): mixed
    {
        return is_null($default) ? self::getDefaultValueForField($key) : $default;
    }

    /**
     * Get all the settings fields from config
     */
    private static function getDefinedSettingFields(): Collection
    {
        return collect(config('settings'))->pluck('elements')->flatten(1);
    }

    /**
     * Caste value into respective type
     */
    private static function castValue($value, $castTo): bool|int
    {
        switch ($castTo) {
            case 'int':
            case 'integer':
                return intval($value);
                break;

            case 'bool':
            case 'boolean':
                return boolval($value);
                break;

            default:
                return $value;
        }
    }

    /**
     * Get all the settings
     */
    public static function getAllSettings(): mixed
    {
        return self::all();
    }
}
