<?php

namespace App\Http\Controllers;

use App\Enums\AccountTypesEnum;
use App\Helpers\SettingHelper;
use App\Jobs\FetchResourcePackJob;
use App\Models\Account;
use App\Models\Announcement;
use App\Models\ResourcePack;
use App\Models\User;
use App\Services\Instance\ResetInstanceData;
use App\Services\ResourcePacks\InstallResourcePack;
use App\Services\ResourcePacks\ResourcePackHub;
use App\Support\Instance;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
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

    public function settings(InstallResourcePack $installer): Response
    {
        $installer->ensureVanilla();

        return Inertia::render('Admin/Settings', [
            'settings' => [
                'instance_mode' => Instance::mode(),
                'clan_name' => (string) SettingHelper::getSetting('clan_name', ''),
                'group_name' => (string) SettingHelper::getSetting('group_name', ''),
                'instance_description' => (string) SettingHelper::getSetting('instance_description', ''),
                'resource_pack_id' => (int) SettingHelper::getSetting('resource_pack_id', 0),
                'hiscore_refresh_minutes' => Instance::hiscoreRefreshMinutes(),
                'feed_level_up_thresholds' => implode(', ', Instance::feedLevelUpThresholds()),
                'feed_loot_min_value' => Instance::feedLootMinValue(),
            ],
            'branding' => [
                'logo_url' => Instance::logoUrl(),
                'banner_url' => Instance::bannerUrl(),
            ],
            'configured' => Instance::isConfigured(),
            'accountCount' => Account::count(),
            'modes' => Instance::MODES,
            'packs' => ResourcePack::pickerList(),
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
            'instance_description' => ['nullable', 'string', 'max:2000'],
            'resource_pack_id' => ['nullable', 'integer', 'exists:resource_packs,id'],
            'confirm' => ['nullable', 'string'],
        ]);

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
        SettingHelper::setSetting('instance_configured', true, 'bool');

        return back()->with('status', $switchingToRoster && $hasAccounts
            ? 'Instance reset and reconfigured.'
            : 'Instance settings updated.');
    }

    /**
     * SPEC §12.4 — operational config: hiscores refresh cadence and live-feed
     * milestone thresholds. The thresholds string is stored raw and sanitised on
     * read (see Instance::feedLevelUpThresholds).
     */
    public function updateConfig(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'hiscore_refresh_minutes' => ['required', 'integer', Rule::in([30, 60, 180, 360, 720, 1440])],
            'feed_level_up_thresholds' => ['nullable', 'string', 'max:200', 'regex:/^[0-9,\s]*$/'],
            'feed_loot_min_value' => ['required', 'integer', 'min:0', 'max:2147483647'],
        ]);

        SettingHelper::setSetting('hiscore_refresh_minutes', (int) $validated['hiscore_refresh_minutes'], 'int');
        SettingHelper::setSetting('feed_level_up_thresholds', $validated['feed_level_up_thresholds'] ?? '');
        SettingHelper::setSetting('feed_loot_min_value', (int) $validated['feed_loot_min_value'], 'int');

        return back()->with('status', 'Feed & sync settings updated.');
    }

    /**
     * SPEC §12.4 — instance branding (logo + banner) image uploads, stored on
     * the public disk. POST (multipart); a `remove_*` flag clears an asset.
     */
    public function updateBranding(Request $request): RedirectResponse
    {
        $request->validate([
            'logo' => ['nullable', 'image', 'max:1024'],
            'banner' => ['nullable', 'image', 'max:2048'],
            'remove_logo' => ['nullable', 'boolean'],
            'remove_banner' => ['nullable', 'boolean'],
        ]);

        $this->storeBrandingAsset($request, 'logo', 'logo_path', $request->boolean('remove_logo'));
        $this->storeBrandingAsset($request, 'banner', 'banner_path', $request->boolean('remove_banner'));

        return back()->with('status', 'Branding updated.');
    }

    private function storeBrandingAsset(Request $request, string $field, string $key, bool $remove): void
    {
        if (($remove || $request->hasFile($field)) && ($old = SettingHelper::getSetting($key))) {
            Storage::disk('public')->delete($old);
            SettingHelper::setSetting($key, '');
        }

        if ($request->hasFile($field)) {
            SettingHelper::setSetting($key, $request->file($field)->store('branding', 'public'));
        }
    }

    /**
     * SPEC §6 — browse the resource-pack hub and install packs.
     */
    public function packs(ResourcePackHub $hub): Response
    {
        return Inertia::render('Admin/Packs', [
            'packs' => $hub->available(),
            'defaultId' => ((int) SettingHelper::getSetting('resource_pack_id', 0)) ?: null,
        ]);
    }

    public function installPack(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100', 'regex:/^pack-[A-Za-z0-9_-]+$/'],
        ]);

        FetchResourcePackJob::dispatch($validated['name']);

        return back()->with('status', "Installing {$validated['name']} — it'll appear shortly.");
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
