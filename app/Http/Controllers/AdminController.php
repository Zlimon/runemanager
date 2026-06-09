<?php

namespace App\Http\Controllers;

use App\Helpers\SettingHelper;
use App\Models\Account;
use App\Models\Announcement;
use App\Models\ResourcePack;
use App\Models\User;
use App\Services\Instance\ResetInstanceData;
use App\Support\Instance;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

/**
 * SPEC §12 — admin backend. Gated by the `admin` ability (see AppServiceProvider).
 */
class AdminController extends Controller
{
    public function dashboard(): Response
    {
        return Inertia::render('Admin/Dashboard', [
            'stats' => [
                'users' => User::count(),
                'accounts' => Account::count(),
                'announcements' => Announcement::count(),
            ],
            'mode' => Instance::mode(),
            'instanceName' => Instance::name(),
        ]);
    }

    public function settings(): Response
    {
        return Inertia::render('Admin/Settings', [
            'settings' => [
                'instance_mode' => Instance::mode(),
                'clan_name' => (string) SettingHelper::getSetting('clan_name', ''),
                'group_name' => (string) SettingHelper::getSetting('group_name', ''),
                'resource_pack_id' => (int) SettingHelper::getSetting('resource_pack_id', 0),
            ],
            'configured' => Instance::isConfigured(),
            'modes' => Instance::MODES,
            'packs' => ResourcePack::query()->orderBy('alias')->get(['id', 'alias'])
                ->map(fn (ResourcePack $p): array => ['id' => $p->id, 'alias' => $p->alias])
                ->all(),
        ]);
    }

    /**
     * Save instance settings. Changing the mode after first-time setup wipes all
     * account data (SPEC §5/§12), so it requires a typed confirmation matching
     * the new mode name.
     */
    public function updateSettings(Request $request, ResetInstanceData $reset): RedirectResponse
    {
        $validated = $request->validate([
            'instance_mode' => ['required', Rule::in(Instance::MODES)],
            'clan_name' => ['nullable', 'string', 'max:60'],
            'group_name' => ['nullable', 'string', 'max:60'],
            'resource_pack_id' => ['nullable', 'integer', 'exists:resource_packs,id'],
            'confirm' => ['nullable', 'string'],
        ]);

        $modeChanged = Instance::isConfigured() && $validated['instance_mode'] !== Instance::mode();

        if ($modeChanged && mb_strtolower(trim((string) ($validated['confirm'] ?? ''))) !== $validated['instance_mode']) {
            throw ValidationException::withMessages([
                'confirm' => 'Type the new mode name to confirm — this deletes all account data.',
            ]);
        }

        if ($modeChanged) {
            $reset->run();
        }

        SettingHelper::setSetting('instance_mode', $validated['instance_mode']);
        SettingHelper::setSetting('clan_name', $validated['clan_name'] ?? '');
        SettingHelper::setSetting('group_name', $validated['group_name'] ?? '');
        SettingHelper::setSetting('resource_pack_id', (int) ($validated['resource_pack_id'] ?? 0), 'int');
        SettingHelper::setSetting('instance_configured', true, 'bool');

        return back()->with('status', $modeChanged
            ? 'Instance reset and reconfigured.'
            : 'Instance settings updated.');
    }
}
