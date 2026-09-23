<?php

namespace Database\Seeders;

use App\Helpers\SettingHelper;
use App\Http\Requests\StoreCalendarEventRequest;
use App\Models\ResourcePack;
use App\Models\Setting;
use App\Support\Instance;
use Illuminate\Database\Seeder;

/**
 * Configures the instance itself, before any content seeder runs.
 *
 * `EnsureInstanceConfigured` renders `SetupInProgress/Index` in place of every
 * page until `instance_configured` is set, so without this a fully seeded
 * database is still nothing but a setup screen. The mode matters just as much:
 * it decides whether the roster, clan ranks and the group bank exist at all,
 * and the content seeders read it back from here.
 *
 * Every value except `instance_configured` is written only when the key is
 * unset, so an operator who changed a setting by hand keeps it on a re-run.
 */
class SettingsSeeder extends Seeder
{
    private const CLAN_NAME = 'Gielinor Guardians';

    private const GROUP_NAME = 'Rune Manor';

    private const DESCRIPTION = 'A demo RuneManager instance — every page is filled with generated data, and nothing here is a real OSRS account.';

    /**
     * Calendar colours the demo treats as important. Both are in
     * {@see StoreCalendarEventRequest::COLORS}, and `CalendarEventFactory`
     * paints some events with them so the highlight visibly does something.
     *
     * @var list<string>
     */
    private const HIGHLIGHT_COLORS = ['red', 'purple'];

    public function run(): void
    {
        $mode = $this->resolveMode();

        $this->setUnlessSet('instance_mode', $mode);
        $this->setUnlessSet('clan_name', $mode === Instance::MODE_CLAN ? self::CLAN_NAME : '');
        $this->setUnlessSet('group_name', $mode === Instance::MODE_GROUP ? self::GROUP_NAME : '');
        $this->setUnlessSet('instance_description', self::DESCRIPTION);
        $this->setUnlessSet('public_anonymize_accounts', false, 'bool');

        // Operational settings, seeded from the config defaults the admin forms
        // fall back to, so Settings → Feed & Sync opens on real stored values.
        $this->setUnlessSet('hiscore_refresh_minutes', 60, 'int');
        $this->setUnlessSet('feed_level_up_thresholds', implode(', ', config('runemanager.feed.level_up_thresholds', [])));
        $this->setUnlessSet('feed_loot_min_value', (int) config('runemanager.feed.loot_min_value', 0), 'int');
        $this->setUnlessSet('calendar_highlight_colors', json_encode(self::HIGHLIGHT_COLORS));

        // This seeder runs before any pack is installed, so a fresh run links
        // nothing here; whichever seeder creates the pack rows calls
        // linkDefaultResourcePack() itself once they exist.
        $pack = self::linkDefaultResourcePack();

        // Last, and unconditionally: it is the flag that opens the instance.
        SettingHelper::setSetting('instance_configured', true, 'bool');

        $this->command->info(sprintf(
            'SettingsSeeder: instance configured in %s mode%s.',
            $mode,
            $pack !== null ? sprintf(' (default pack: %s)', $pack->alias ?: $pack->name) : '',
        ));
    }

    /**
     * Point `resource_pack_id` at the bundled Vanilla row, or at whatever pack
     * is installed if Vanilla is not. Vanilla is the icon fallback for every
     * viewer, so it is the right default whenever it exists.
     *
     * Idempotent, and safe to call before any pack has been installed: it
     * leaves the setting alone when it already names a pack that exists, and
     * returns null when there is nothing to link. Public so the seeder that
     * installs the pack rows can link them the moment they exist, without
     * having to know which settings key holds the instance default.
     */
    public static function linkDefaultResourcePack(): ?ResourcePack
    {
        $current = Instance::defaultResourcePackId();

        if ($current !== null) {
            $linked = ResourcePack::query()->find($current);

            if ($linked !== null) {
                return $linked;
            }
        }

        $pack = ResourcePack::query()->where('name', ResourcePack::VANILLA)->first()
            ?? ResourcePack::query()->oldest('id')->first();

        if ($pack === null) {
            return null;
        }

        SettingHelper::setSetting('resource_pack_id', $pack->id, 'int');

        return $pack;
    }

    /**
     * The mode this demo is seeded for, in precedence order: the mode already
     * stored (a re-run, or a command that wrote it first, is honoured), then
     * `runemanager.seeding.demo_mode`, then CLAN.
     *
     * CLAN is the default because it exercises the most surfaces — ranks,
     * titles and the unclaimed roster accounts that registration claims. Seed
     * another with `DEMO_INSTANCE_MODE=group ./vendor/bin/sail artisan db:seed`.
     */
    private function resolveMode(): string
    {
        $stored = (string) SettingHelper::getSetting('instance_mode', '');

        if (in_array($stored, Instance::MODES, true)) {
            return $stored;
        }

        $requested = mb_strtolower(trim((string) config('runemanager.seeding.demo_mode')));

        return in_array($requested, Instance::MODES, true) ? $requested : Instance::MODE_CLAN;
    }

    /**
     * Write $key unless it already holds a value. An empty string counts as a
     * value — `clan_name` is deliberately cleared outside CLAN mode, and a
     * re-run must not put the demo name back.
     */
    private function setUnlessSet(string $key, mixed $value, string $type = 'string'): void
    {
        // Setting::has(), not getSetting(): the getter falls back to the
        // defaults declared in config/settings.php, so every key would look
        // like it already held a value and the seeder would write nothing.
        if (Setting::has($key)) {
            return;
        }

        SettingHelper::setSetting($key, $value, $type);
    }
}
