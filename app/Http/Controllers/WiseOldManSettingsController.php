<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateWomAccountRequest;
use App\Models\Account;
use App\Services\WiseOldMan\StartWomTracking;
use App\Services\WiseOldMan\UpdateWomPreferences;
use App\Support\Instance;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response as HttpResponse;

/**
 * The Wise Old Man settings routes, kept working since the page was deployed.
 * The page itself is now part of Settings → Privacy.
 */
class WiseOldManSettingsController extends Controller
{
    /** The Wise Old Man choices moved to Settings → Privacy; old links and bookmarks land there. */
    public function index(): RedirectResponse
    {
        return to_route('privacy.index');
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
