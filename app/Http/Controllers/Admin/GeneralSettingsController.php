<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\SettingHelper;
use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\ResourcePack;
use App\Services\Hiscores\GroupIronmanValidator;
use App\Services\Instance\ResetInstanceData;
use App\Services\ResourcePacks\InstallResourcePack;
use App\Support\Instance;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class GeneralSettingsController extends Controller
{
    /** General instance settings (mode, names, description, appearance defaults). */
    public function edit(InstallResourcePack $installer): Response
    {
        $installer->ensureVanilla();

        return Inertia::render('Admin/Settings/General/Index', [
            'settings' => [
                'instance_mode' => Instance::mode(),
                'clan_name' => (string) SettingHelper::getSetting('clan_name', ''),
                'group_name' => (string) SettingHelper::getSetting('group_name', ''),
                'instance_description' => (string) SettingHelper::getSetting('instance_description', ''),
                'resource_pack_id' => (int) SettingHelper::getSetting('resource_pack_id', 0),
                'default_dark_mode' => (string) SettingHelper::getSetting('default_dark_mode', ''),
                'public_anonymize_accounts' => Instance::publicAnonymizeAccounts(),
            ],
            'configured' => Instance::isConfigured(),
            'accountCount' => Account::count(),
            'modes' => Instance::MODES,
            'packs' => ResourcePack::pickerList(),
        ]);
    }

    /**
     * Save instance settings. Switching into a roster mode (clan/group) after
     * first-time setup wipes all account data and so requires a
     * typed confirmation. Switching to casual keeps existing accounts.
     */
    public function update(Request $request, ResetInstanceData $reset, GroupIronmanValidator $gimValidator): RedirectResponse
    {
        $validated = $request->validate([
            'instance_mode' => ['required', Rule::in(Instance::MODES)],
            'clan_name' => ['nullable', 'string', 'max:60'],
            'group_name' => ['nullable', 'string', 'max:60'],
            'instance_description' => ['nullable', 'string', 'max:2000'],
            'resource_pack_id' => ['nullable', 'integer', 'exists:resource_packs,id'],
            'default_dark_mode' => ['nullable', 'string', Rule::in(['', 'light', 'dark'])],
            'public_anonymize_accounts' => ['boolean'],
            'confirm' => ['nullable', 'string'],
        ]);

        // Validate the GIM group name against the official OSRS group
        // page when it's set/changed. A transient lookup failure returns null and
        // is allowed through ("validated where possible"); only a definitive
        // "not found" blocks the save, before any destructive reset runs.
        $groupName = trim((string) ($validated['group_name'] ?? ''));
        if ($validated['instance_mode'] === Instance::MODE_GROUP && $groupName !== ''
            && $groupName !== trim((string) SettingHelper::getSetting('group_name', ''))
            && $gimValidator->exists($groupName) === false) {
            throw ValidationException::withMessages([
                'group_name' => "No Group Ironman group named \"{$groupName}\" was found on the OSRS hiscores.",
            ]);
        }

        // Switching INTO a roster mode (clan/group) needs a fresh, roster-driven
        // account set — including casual -> clan/group. Switching to casual (or
        // staying put) keeps what's there. Confirmation is only demanded when
        // there's actually account data to lose.
        $switchingToRoster = $validated['instance_mode'] !== Instance::mode()
            && $validated['instance_mode'] !== Instance::MODE_CASUAL;
        $hasAccounts = Account::query()->exists();

        if ($switchingToRoster && $hasAccounts
            && mb_strtolower(trim((string) ($validated['confirm'] ?? ''))) !== $validated['instance_mode']) {
            throw ValidationException::withMessages([
                'confirm' => 'Type the new mode name to confirm — this deletes all account data.',
            ]);
        }

        if ($switchingToRoster) {
            $reset->run();
        }

        SettingHelper::setSetting('instance_mode', $validated['instance_mode']);
        SettingHelper::setSetting('clan_name', $validated['clan_name'] ?? '');
        SettingHelper::setSetting('group_name', $validated['group_name'] ?? '');
        SettingHelper::setSetting('instance_description', $validated['instance_description'] ?? '');
        SettingHelper::setSetting('resource_pack_id', (int) ($validated['resource_pack_id'] ?? 0), 'int');
        SettingHelper::setSetting('default_dark_mode', $validated['default_dark_mode'] ?? '');
        SettingHelper::setSetting('public_anonymize_accounts', $validated['public_anonymize_accounts'] ?? false, 'bool');
        SettingHelper::setSetting('instance_configured', true, 'bool');

        return back()->with('status', $switchingToRoster && $hasAccounts
            ? 'Instance reset and reconfigured.'
            : 'Instance settings updated.');
    }
}
