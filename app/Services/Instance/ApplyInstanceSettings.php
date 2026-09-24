<?php

namespace App\Services\Instance;

use App\Helpers\SettingHelper;
use App\Models\Account;
use App\Support\Instance;

/**
 * Save the general instance settings. Switching INTO a roster mode (clan/group)
 * needs a fresh, roster-driven account set — including casual -> clan/group —
 * so it wipes all account data first. Switching to casual, or staying put,
 * keeps what's there.
 */
class ApplyInstanceSettings
{
    public function __construct(private ResetInstanceData $reset) {}

    /** Whether saving this mode switches the instance into a roster mode and so resets it. */
    public function switchesToRoster(string $mode): bool
    {
        return $mode !== Instance::mode() && $mode !== Instance::MODE_CASUAL;
    }

    /**
     * @param  array{instance_mode: string, clan_name?: ?string, group_name?: ?string, instance_description?: ?string, resource_pack_id?: ?int, default_dark_mode?: ?string, public_anonymize_accounts?: bool}  $validated
     * @return bool whether existing account data was wiped
     */
    public function run(array $validated): bool
    {
        $switchingToRoster = $this->switchesToRoster($validated['instance_mode']);
        $hadAccounts = Account::query()->exists();

        if ($switchingToRoster) {
            $this->reset->run();
        }

        SettingHelper::setSetting('instance_mode', $validated['instance_mode']);
        SettingHelper::setSetting('clan_name', $validated['clan_name'] ?? '');
        SettingHelper::setSetting('group_name', $validated['group_name'] ?? '');
        SettingHelper::setSetting('instance_description', $validated['instance_description'] ?? '');
        SettingHelper::setSetting('resource_pack_id', (int) ($validated['resource_pack_id'] ?? 0), 'int');
        SettingHelper::setSetting('default_dark_mode', $validated['default_dark_mode'] ?? '');
        SettingHelper::setSetting('public_anonymize_accounts', $validated['public_anonymize_accounts'] ?? false, 'bool');
        SettingHelper::setSetting('instance_configured', true, 'bool');

        return $switchingToRoster && $hadAccounts;
    }
}
