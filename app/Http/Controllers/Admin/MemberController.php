<?php

namespace App\Http\Controllers\Admin;

use App\Enums\AccountTypesEnum;
use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Support\Instance;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class MemberController extends Controller
{
    /**
     * The member roster. In GROUP mode the admin manages it by hand;
     * in CLAN mode it's seeded by the owner's plugin (shown read-only-ish here).
     */
    public function index(): Response
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

        return Inertia::render('Admin/Members/Index', [
            'mode' => Instance::mode(),
            'manageable' => Instance::isGroup(),
            'accounts' => $accounts,
        ]);
    }

    public function store(Request $request): RedirectResponse
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

    public function destroy(int $account): RedirectResponse
    {
        abort_unless(Instance::requiresRosterClaim(), 403, 'Members are only managed in clan or group mode.');

        Account::findOrFail($account)->delete();

        return back()->with('status', 'Member removed.');
    }
}
