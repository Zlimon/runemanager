<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\SettingHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateGeneralSettingsRequest;
use App\Models\Account;
use App\Models\ResourcePack;
use App\Services\Instance\ApplyInstanceSettings;
use App\Services\ResourcePacks\InstallResourcePack;
use App\Support\Instance;
use Illuminate\Http\RedirectResponse;
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
                'resource_pack_id' => (int) Instance::defaultResourcePackId(),
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
    public function update(UpdateGeneralSettingsRequest $request, ApplyInstanceSettings $settings): RedirectResponse
    {
        $wiped = $settings->run($request->validated());

        return back()->with('status', $wiped
            ? 'Instance reset and reconfigured.'
            : 'Instance settings updated.');
    }
}
