<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateWomAccountRequest;
use App\Models\Account;
use App\Services\WiseOldMan\StartWomTracking;
use App\Services\WiseOldMan\UpdateWomPreferences;
use App\Services\WiseOldMan\WomAccountView;
use App\Support\Instance;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response as HttpResponse;
use Inertia\Inertia;
use Inertia\Response;

class WiseOldManSettingsController extends Controller
{
    /**
     * Settings → Wise Old Man: each of the user's accounts with its WOM link
     * status, the /progress visibility toggle, and the revocable opt-in to
     * tracking.
     */
    public function index(Request $request, WomAccountView $womView): Response
    {
        abort_unless(Instance::womEnabled(), 404);

        $user = $request->user();

        return Inertia::render('WiseOldMan/Index', [
            'accounts' => $user->account()
                ->with('womPlayer')
                ->orderBy('username')
                ->get()
                ->map(fn (Account $account): array => [
                    'id' => $account->id,
                    'username' => $account->username,
                    'account_type' => $account->account_type,
                    'wom' => $womView->summary($account, $user),
                ])
                ->all(),
        ]);
    }

    /** The owner opts in: a queued update asks WOM to track the account (creating its public profile if needed). */
    public function track(Request $request, Account $account, StartWomTracking $tracking): RedirectResponse
    {
        abort_unless(Instance::womEnabled(), 404);
        abort_unless($request->user()->id === $account->user_id, HttpResponse::HTTP_FORBIDDEN);

        $tracking->start($account);

        return back()->banner('Queued — Wise Old Man will update shortly.');
    }

    public function update(UpdateWomAccountRequest $request, Account $account, UpdateWomPreferences $preferences): RedirectResponse
    {
        abort_unless(Instance::womEnabled(), 404);

        $preferences->update(
            $account,
            $request->has('show_on_progress') ? $request->boolean('show_on_progress') : null,
            $request->has('tracking_opt_in') ? $request->boolean('tracking_opt_in') : null,
        );

        return $request->boolean('tracking_opt_in')
            ? back()->banner('Queued — Wise Old Man will update shortly.')
            : back();
    }
}
