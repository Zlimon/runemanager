<?php

namespace App\Http\Controllers\Admin;

use App\Enums\AccountTypesEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreMemberRequest;
use App\Models\Account;
use App\Support\Instance;
use Illuminate\Http\RedirectResponse;
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

    public function store(StoreMemberRequest $request): RedirectResponse
    {
        Account::rosterPlaceholder(
            trim($request->validated('username')),
            Instance::isGroup() ? AccountTypesEnum::GROUP_IRONMAN : AccountTypesEnum::NORMAL,
        )->save();

        return back()->with('status', 'Member added.');
    }

    public function destroy(int $account): RedirectResponse
    {
        abort_unless(Instance::requiresRosterClaim(), 403, 'Members are only managed in clan or group mode.');

        Account::findOrFail($account)->delete();

        return back()->with('status', 'Member removed.');
    }
}
