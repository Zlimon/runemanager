<?php

namespace App\Support;

use App\Helpers\SettingHelper;
use App\Models\ResourcePack;
use App\Models\User;
use Illuminate\Support\Facades\Storage;

/**
 * Instance-level configuration (SPEC §2 Modes, §12.4). Mode, names, branding,
 * sync cadence and feed thresholds are stored as settings and editable from the
 * admin panel; readers here fall back to config defaults.
 */
class Instance
{
    public const MODE_CLAN = 'clan';

    public const MODE_GROUP = 'group';

    public const MODE_CASUAL = 'casual';

    public const MODES = [self::MODE_CLAN, self::MODE_GROUP, self::MODE_CASUAL];

    /** Cookie holding a logged-out visitor's light/dark choice ('1' | '0'). */
    public const DARK_MODE_COOKIE = 'dark_mode';

    public static function mode(): string
    {
        $mode = (string) SettingHelper::getSetting('instance_mode', self::MODE_CASUAL);

        return in_array($mode, self::MODES, true) ? $mode : self::MODE_CASUAL;
    }

    public static function isClan(): bool
    {
        return self::mode() === self::MODE_CLAN;
    }

    public static function isGroup(): bool
    {
        return self::mode() === self::MODE_GROUP;
    }

    /**
     * Whether the owner has completed first-time setup. Until then the mode can
     * be set freely; afterwards, changing it triggers a destructive reset.
     */
    public static function isConfigured(): bool
    {
        return (bool) SettingHelper::getSetting('instance_configured', false);
    }

    /**
     * CLAN/GROUP gate registration on selecting a pre-created roster account.
     */
    public static function requiresRosterClaim(): bool
    {
        return self::isClan() || self::isGroup();
    }

    /**
     * The configured clan (CLAN) or group (GROUP) name, or null in CASUAL mode.
     */
    public static function name(): ?string
    {
        if (self::isClan()) {
            return SettingHelper::getSetting('clan_name') ?: null;
        }

        if (self::isGroup()) {
            return SettingHelper::getSetting('group_name') ?: null;
        }

        return null;
    }

    /**
     * Admin-set instance description (SPEC §12.4), or null when unset.
     */
    public static function description(): ?string
    {
        return SettingHelper::getSetting('instance_description') ?: null;
    }

    /**
     * Public URL for an uploaded branding asset (logo/banner), or null. The
     * stored value is a path on the public disk.
     */
    public static function logoUrl(): ?string
    {
        return self::assetUrl('logo_path');
    }

    public static function bannerUrl(): ?string
    {
        return self::assetUrl('banner_path');
    }

    private static function assetUrl(string $key): ?string
    {
        $path = SettingHelper::getSetting($key);

        return $path ? Storage::disk('public')->url($path) : null;
    }

    /**
     * How often the hiscores sweep runs (minutes); the scheduler maps this to a
     * cron cadence. SPEC §12.4.
     */
    public static function hiscoreRefreshMinutes(): int
    {
        $minutes = (int) SettingHelper::getSetting('hiscore_refresh_minutes', 0);

        return $minutes > 0 ? $minutes : 60;
    }

    /** The hiscores refresh cadence as a cron expression. */
    public static function hiscoreRefreshCron(): string
    {
        $minutes = self::hiscoreRefreshMinutes();

        if ($minutes < 60) {
            return '*/'.max(1, $minutes).' * * * *';
        }

        $hours = intdiv($minutes, 60);

        return match (true) {
            $hours >= 24 => '0 0 * * *',
            $hours === 1 => '0 * * * *',
            default => '0 */'.$hours.' * * *',
        };
    }

    /**
     * Notable level-up thresholds for the live feed (SPEC §8.2/§12.4). Falls
     * back to the config defaults when unset.
     *
     * @return list<int>
     */
    public static function feedLevelUpThresholds(): array
    {
        $raw = SettingHelper::getSetting('feed_level_up_thresholds');

        if (! $raw) {
            return config('runemanager.feed.level_up_thresholds', []);
        }

        $values = array_values(array_unique(array_filter(array_map(
            fn ($v) => (int) trim((string) $v),
            explode(',', (string) $raw),
        ), fn (int $v) => $v >= 2 && $v <= 99)));

        sort($values);

        return $values ?: config('runemanager.feed.level_up_thresholds', []);
    }

    /**
     * Minimum loot GP value for a feed LOOT_DROP (SPEC §8.2/§12.4).
     */
    public static function feedLootMinValue(): int
    {
        $value = SettingHelper::getSetting('feed_loot_min_value');

        return $value !== null && $value !== ''
            ? (int) $value
            : (int) config('runemanager.feed.loot_min_value', 0);
    }

    /**
     * Whether usernames are hidden on the public landing page (SPEC §12.4) —
     * the showcase of top accounts then masks names so the leaderboard can be
     * shown to logged-out visitors without exposing who's who.
     */
    public static function publicAnonymizeAccounts(): bool
    {
        return (bool) SettingHelper::getSetting('public_anonymize_accounts', false);
    }

    /**
     * The owner's instance-wide default appearance, overriding a resource pack's
     * own (often unreliable) dark_mode flag. Null means "follow the pack".
     */
    public static function defaultDarkMode(): ?bool
    {
        return match ((string) SettingHelper::getSetting('default_dark_mode', '')) {
            'dark' => true,
            'light' => false,
            default => null,
        };
    }

    /**
     * Resolve the effective dark-mode flag for a viewer, in precedence order:
     * the user's own explicit toggle, then a logged-out visitor's cookie choice,
     * then the owner's instance default, and finally the resource pack's flag.
     */
    public static function resolveDarkMode(?User $user, ?ResourcePack $pack): bool
    {
        if ($user !== null && $user->dark_mode !== null) {
            return (bool) $user->dark_mode;
        }

        if ($user === null) {
            $cookie = request()->cookie(self::DARK_MODE_COOKIE);
            if ($cookie === '1' || $cookie === '0') {
                return $cookie === '1';
            }
        }

        return self::defaultDarkMode() ?? (bool) $pack?->dark_mode;
    }
}
