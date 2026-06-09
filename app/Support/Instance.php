<?php

namespace App\Support;

use App\Helpers\SettingHelper;

/**
 * Instance-level configuration (SPEC §2 Modes). The mode and clan/group name
 * are stored as settings and editable from the admin panel.
 */
class Instance
{
    public const MODE_CLAN = 'clan';

    public const MODE_GROUP = 'group';

    public const MODE_CASUAL = 'casual';

    public const MODES = [self::MODE_CLAN, self::MODE_GROUP, self::MODE_CASUAL];

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
}
