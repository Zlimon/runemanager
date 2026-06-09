<?php

namespace App\Http\Controllers;

use App\Enums\AccountTypesEnum;
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
     * Save instance settings. Switching into a roster mode (clan/group) after
     * first-time setup wipes all account data (SPEC §5/§12) and so requires a
     * typed confirmation. Switching to casual keeps existing accounts.
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
        // Only roster modes need a fresh account set; casual reuses what's there.
        $destructive = $modeChanged && $validated['instance_mode'] !== Instance::MODE_CASUAL;

        if ($destructive && mb_strtolower(trim((string) ($validated['confirm'] ?? ''))) !== $validated['instance_mode']) {
            throw ValidationException::withMessages([
                'confirm' => 'Type the new mode name to confirm — this deletes all account data.',
            ]);
        }

        if ($destructive) {
            $reset->run();
        }

        SettingHelper::setSetting('instance_mode', $validated['instance_mode']);
        SettingHelper::setSetting('clan_name', $validated['clan_name'] ?? '');
        SettingHelper::setSetting('group_name', $validated['group_name'] ?? '');
        SettingHelper::setSetting('resource_pack_id', (int) ($validated['resource_pack_id'] ?? 0), 'int');
        SettingHelper::setSetting('instance_configured', true, 'bool');

        return back()->with('status', $destructive
            ? 'Instance reset and reconfigured.'
            : 'Instance settings updated.');
    }

    /**
     * SPEC §5.2 — the member roster. In GROUP mode the admin manages it by hand;
     * in CLAN mode it's seeded by the owner's plugin (shown read-only-ish here).
     */
    public function members(): Response
    {
        $accounts = Account::query()
            ->with('user:id,name')
            ->orderBy('username')
            ->get()
            ->map(fn (Account $account): array => [
                'id' => $account->id,
                'username' => $account->username,
                'claimed_by' => $account->user?->name,
                'clan_rank' => $account->clan_rank,
                'clan_title' => $account->clan_title,
            ])
            ->all();

        return Inertia::render('Admin/Members', [
            'mode' => Instance::mode(),
            'manageable' => Instance::isGroup(),
            'accounts' => $accounts,
        ]);
    }

    public function storeMember(Request $request): RedirectResponse
    {
        abort_unless(Instance::requiresRosterClaim(), 403, 'Members are only managed in clan or group mode.');

        $validated = $request->validate([
            'username' => ['required', 'string', 'max:12', 'regex:/^[A-Za-z0-9 _-]+$/', 'unique:accounts,username'],
        ]);

        // The account type is a placeholder until the member's first plugin
        // login confirms it (and, in GROUP mode, that they're a Group Ironman).
        $account = new Account;
        $account->username = trim($validated['username']);
        $account->user_id = null;
        $account->account_hash = null;
        $account->account_type = Instance::isGroup()
            ? AccountTypesEnum::GROUP_IRONMAN
            : AccountTypesEnum::NORMAL;
        $account->rank = 0;
        $account->level = 0;
        $account->xp = 0;
        $account->save();

        return back()->with('status', 'Member added.');
    }

    public function destroyMember(int $account): RedirectResponse
    {
        abort_unless(Instance::requiresRosterClaim(), 403, 'Members are only managed in clan or group mode.');

        Account::findOrFail($account)->delete();

        return back()->with('status', 'Member removed.');
    }
}
