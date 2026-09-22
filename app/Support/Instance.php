<?php

namespace App\Support;

use App\Helpers\SettingHelper;
use App\Models\ResourcePack;
use App\Models\User;
use Illuminate\Support\Facades\Storage;

/**
 * Instance-level configuration. Mode, names, branding, sync cadence and feed
 * thresholds are stored as settings and editable from the admin panel; readers
 * here fall back to config defaults.
 */
class Instance
{
    public const MODE_CLAN = 'clan';

    public const MODE_GROUP = 'group';

    public const MODE_CASUAL = 'casual';

    public const MODES = [self::MODE_CLAN, self::MODE_GROUP, self::MODE_CASUAL];

    /**
     * Calendar colours treated as important until the owner configures the list.
     *
     * @var list<string>
     */
    public const DEFAULT_CALENDAR_HIGHLIGHT_COLORS = ['red'];

    /** Cookie holding a logged-out visitor's light/dark choice ('1' | '0'). */
    public const DARK_MODE_COOKIE = 'dark_mode';

    /**
     * Cookie holding a logged-out visitor's resource-pack choice: the literal
     * {@see ResourcePack::GUEST_NO_PACK} or an installed pack id. Guests have no
     * row to carry `users.disable_resource_pack`, so without this they could
     * never reach the untextured interface — and so never get the light/dark
     * toggle, which only the untextured interface offers.
     */
    public const RESOURCE_PACK_COOKIE = 'resource_pack';

    /** DaisyUI theme for the untextured interface in light mode. */
    public const THEME_LIGHT = 'runemanager';

    /** DaisyUI theme for the untextured interface in dark mode. */
    public const THEME_DARK = 'runemanager-dark';

    /** DaisyUI theme for Vanilla's single fixed scheme. */
    public const THEME_VANILLA = 'runemanager-vanilla';

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
     * Admin-set instance description, or null when unset.
     */
    public static function description(): ?string
    {
        return SettingHelper::getSetting('instance_description') ?: null;
    }

    /**
     * The instance's single outbound webhook URL (Discord etc.) that notable
     * events — new announcements, calendar events, and anything we add later —
     * are forwarded to, or null when unset.
     */
    public static function webhookUrl(): ?string
    {
        return SettingHelper::getSetting('webhook_url') ?: null;
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
     * cron cadence.
     */
    public static function hiscoreRefreshMinutes(): int
    {
        try {
            $minutes = (int) SettingHelper::getSetting('hiscore_refresh_minutes', 0);
        } catch (\Throwable) {
            // routes/console.php reads this to build the scheduler's cron at load
            // time, so it runs on every artisan boot — including package:discover
            // before the database exists (a fresh checkout / CI's composer install).
            // Fall back to the default cadence rather than failing to boot.
            return 60;
        }

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
     * Notable level-up thresholds for the live feed. Falls
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
     * Minimum loot GP value for a feed LOOT_DROP.
     */
    public static function feedLootMinValue(): int
    {
        $value = SettingHelper::getSetting('feed_loot_min_value');

        return $value !== null && $value !== ''
            ? (int) $value
            : (int) config('runemanager.feed.loot_min_value', 0);
    }

    /**
     * Palette colour names that mark a calendar event as important —
     * an event whose colour is in this list is highlighted on the calendar. The
     * setting holds a JSON array of palette colour names (the same list as
     * StoreCalendarEventRequest::COLORS); a stored empty array means "highlight
     * nothing".
     *
     * @return list<string>
     */
    public static function calendarHighlightColors(): array
    {
        try {
            $raw = SettingHelper::getSetting('calendar_highlight_colors');
        } catch (\Throwable) {
            // Settings are read during artisan boot in places that run before the
            // database exists (see hiscoreRefreshMinutes); never hard-fail on it.
            return self::DEFAULT_CALENDAR_HIGHLIGHT_COLORS;
        }

        if ($raw === null || $raw === '') {
            return self::DEFAULT_CALENDAR_HIGHLIGHT_COLORS;
        }

        $decoded = is_array($raw) ? $raw : json_decode((string) $raw, true);

        if (! is_array($decoded)) {
            return self::DEFAULT_CALENDAR_HIGHLIGHT_COLORS;
        }

        return array_values(array_unique(array_map(
            fn ($color): string => (string) $color,
            array_filter($decoded, 'is_scalar'),
        )));
    }

    /**
     * Whether usernames are hidden on the public landing page —
     * the showcase of top accounts then masks names so the leaderboard can be
     * shown to logged-out visitors without exposing who's who.
     */
    public static function publicAnonymizeAccounts(): bool
    {
        return (bool) SettingHelper::getSetting('public_anonymize_accounts', false);
    }

    /**
     * The instance-wide default resource pack (set from Settings → General),
     * or null when unset. Stored as 0 when cleared.
     */
    public static function defaultResourcePackId(): ?int
    {
        return ((int) SettingHelper::getSetting('resource_pack_id', 0)) ?: null;
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
     *
     * Selecting a resource pack clears that explicit toggle (member row and
     * guest cookie alike — see SelectResourcePack::select()), so
     * the new pack's flag governs from here rather than a stale preference left
     * over from the previous pack. The order below is unchanged by that: the
     * rung is simply empty again.
     */
    public static function resolveDarkMode(?User $user, ?ResourcePack $pack): bool
    {
        // Vanilla ships one fixed scheme and hides the toggle, so it is
        // never "dark" regardless of what anyone has stored.
        if (! self::canToggleDarkMode($pack)) {
            return false;
        }

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

    /**
     * Whether the viewer may switch between light and dark.
     *
     * Only the untextured "no resource pack" interface supports both modes.
     * Vanilla is a single fixed scheme ({@see self::THEME_VANILLA}) and
     * hides the toggle; every other pack still rides on the two base themes
     * until resource packs get their own appearance pass.
     */
    public static function canToggleDarkMode(?ResourcePack $pack): bool
    {
        return ! ($pack?->isVanilla() ?? false);
    }

    /**
     * The DaisyUI theme name to stamp on <html data-theme>, given the viewer's
     * effective pack and resolved dark-mode flag.
     */
    public static function themeFor(?ResourcePack $pack, bool $darkMode): string
    {
        if ($pack?->isVanilla()) {
            return self::THEME_VANILLA;
        }

        return $darkMode ? self::THEME_DARK : self::THEME_LIGHT;
    }
}
